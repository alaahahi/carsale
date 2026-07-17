# -*- coding: utf-8 -*-
"""
Build a clean restore SQL for CENTRAL tenancy tables.
Avoids corrupted tenants.data (runaway JSON escaping for sarwan).
"""
import re
from pathlib import Path

src = Path(r"c:\Users\ALAA-PC\Downloads\intellij_2025kamo.sql")
dest = Path(r"c:\xampp\htdocs\carsale\database\restore_central_tenancy_from_2025kamo.sql")
content = src.read_text(encoding="utf-8", errors="replace")


def extract_create(table: str) -> str | None:
    m = re.search(rf"CREATE TABLE `{table}` \(.*?\) ENGINE=[^;]+;", content, re.S)
    return m.group(0) if m else None


def extract_alters(table: str) -> list[str]:
    return re.findall(rf"ALTER TABLE `{table}`[^;]+;", content, re.S)


def extract_inserts_simple(table: str) -> list[str]:
    """For small clean tables (domains, tenant_database_configs)."""
    out = []
    needle = f"INSERT INTO `{table}`"
    pos = 0
    while True:
        i = content.find(needle, pos)
        if i < 0:
            break
        # these tables are small — take until semicolon with quote awareness for ''
        j = i
        in_str = False
        while j < len(content):
            ch = content[j]
            if in_str:
                if ch == "\\" and j + 1 < len(content):
                    j += 2
                    continue
                if ch == "'":
                    if j + 1 < len(content) and content[j + 1] == "'":
                        j += 2
                        continue
                    in_str = False
                j += 1
                continue
            if ch == "'":
                in_str = True
                j += 1
                continue
            if ch == ";":
                out.append(content[i : j + 1])
                pos = j + 1
                break
            j += 1
        else:
            break
        # safety: skip absurd statements
        if len(out[-1]) > 5_000_000:
            print("SKIP huge insert for", table, len(out[-1]))
            out.pop()
    return out


def extract_tenants_clean() -> str:
    """
    Build clean tenants INSERT from row headers in the dump.
    Ignores corrupted data column content.
    """
    # Find value tuples that start a tenant uuid row
    # Pattern: ('uuid', 'created', 'updated', '...corrupted data...', 'name', ...
    # Better approach: read known rows from small first insert + parse ids from dump using regex on id fields

    rows = []
    # Match beginning of each tenant value row: ('uuid-...', 'datetime', 'datetime',
    for m in re.finditer(
        r"\('([0-9a-f-]{36})',\s*'([^']*)',\s*'([^']*)',\s*'",
        content,
    ):
        tid, created, updated = m.group(1), m.group(2), m.group(3)
        # Only keep if this looks like tenants section (before domains indexes around end)
        # Heuristic: tenant rows appear near INSERT INTO tenants
        # Find nearest INSERT INTO tenants before this match
        before = content.rfind("INSERT INTO `tenants`", 0, m.start())
        if before < 0 or m.start() - before > 20_000_000:
            continue
        # Extract name/email/phone/address/status after the data blob is hard.
        # Instead pull from VirtualColumn-like readable fragments near the start of data JSON
        window = content[m.start() : m.start() + 2500]
        name = _json_field(window, "name") or ""
        email = _json_field(window, "email") or ""
        phone = _json_field(window, "phone") or ""
        address = _json_field(window, "address") or ""
        status = _json_field(window, "status") or "active"
        plan = _json_field(window, "subscription_plan") or "basic"
        expires = _json_field(window, "subscription_expires_at")

        # Prefer explicit column values after data if present in short inserts only
        rows.append(
            {
                "id": tid,
                "created_at": created,
                "updated_at": updated,
                "name": name,
                "email": email,
                "phone": phone,
                "address": address,
                "status": status,
                "subscription_plan": plan,
                "subscription_expires_at": expires,
            }
        )

    # de-dupe by id keeping last
    uniq = {}
    for r in rows:
        uniq[r["id"]] = r
    rows = list(uniq.values())
    print("clean tenants rows", len(rows))
    for r in rows:
        label = f'{r["id"][:8]} {r["name"]} {r["status"]} {r["email"]}'
        print(" -", label.encode("ascii", "backslashreplace").decode("ascii"))

    if not rows:
        return ""

    def esc(v):
        if v is None:
            return "NULL"
        return "'" + str(v).replace("\\", "\\\\").replace("'", "''") + "'"

    lines = [
        "INSERT INTO `tenants` (`id`,`created_at`,`updated_at`,`data`,`name`,`domain`,`email`,`phone`,`address`,`status`,`subscription_plan`,`subscription_expires_at`,`settings`) VALUES"
    ]
    value_sql = []
    for r in rows:
        data = "{}"
        value_sql.append(
            "("
            + ",".join(
                [
                    esc(r["id"]),
                    esc(r["created_at"]),
                    esc(r["updated_at"]),
                    esc(data),
                    esc(r["name"]),
                    "NULL",
                    esc(r["email"]),
                    esc(r["phone"]),
                    esc(r["address"]),
                    esc(r["status"]),
                    esc(r["subscription_plan"]),
                    esc(r["subscription_expires_at"]) if r["subscription_expires_at"] else "NULL",
                    "NULL",
                ]
            )
            + ")"
        )
    lines.append(",\n".join(value_sql) + ";")
    return "\n".join(lines)


def _json_field(window: str, key: str):
    import codecs

    m = re.search(rf'\\"{key}\\":\\"(.*?)\\"', window)
    if not m:
        m = re.search(rf'"{key}":"(.*?)"', window)
    if not m:
        return None
    raw = m.group(1)
    # Dump stores double-escaped unicode: \\u0643 → decode twice → Arabic
    try:
        for _ in range(3):
            if "\\u" in raw or "\\/" in raw:
                nxt = codecs.decode(raw, "unicode_escape")
                if nxt == raw:
                    break
                raw = nxt
            else:
                break
        return raw
    except Exception:
        return raw.replace("\\/", "/")


out = [
    "-- CENTRAL restore: domains + tenants + tenant_database_configs ONLY",
    "-- Target DB: intellij_2025kamo (MAIN/central) — do NOT import into tenant DBs",
    "-- phpMyAdmin: select intellij_2025kamo → Import this file",
    "-- CLI: mysql -u USER -p intellij_2025kamo < restore_central_tenancy_from_2025kamo.sql",
    "-- WARNING: tenant subdomain 2025kamo points database_name=intellij_2025kamo (same as central).",
    "-- Never run tenants:repair-db --all / repair on 2025kamo — it drops central domains/tenants.",
    "SET FOREIGN_KEY_CHECKS=0;",
    'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
    "SET NAMES utf8mb4;",
]

# tenants first (parent)
create = extract_create("tenants")
out.append("\n-- -------- tenants --------")
out.append("DROP TABLE IF EXISTS `domains`;")
out.append("DROP TABLE IF EXISTS `tenant_database_configs`;")
out.append("DROP TABLE IF EXISTS `tenants`;")
out.append(create)
out.append(extract_tenants_clean())
out.extend(extract_alters("tenants"))

for t in ["domains", "tenant_database_configs"]:
    create = extract_create(t)
    out.append(f"\n-- -------- {t} --------")
    out.append(f"DROP TABLE IF EXISTS `{t}`;")
    out.append(create)
    inserts = extract_inserts_simple(t)
    print(t, "inserts", len(inserts), "sizes", [len(x) for x in inserts])
    out.extend(inserts)
    out.extend(extract_alters(t))

out.append("\nSET FOREIGN_KEY_CHECKS=1;")
text = "\n".join([x for x in out if x is not None and x != ""])
dest.write_text(text, encoding="utf-8")
print("Wrote", dest, "bytes", dest.stat().st_size)
