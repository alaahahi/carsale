<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title data-i18n="pageTitle">الإحصائيات — {{ $systemConfig['company_name'] ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <style>
        :root {
            --color-primary: #1E40AF;
            --color-secondary: #3B82F6;
            --color-accent: #D97706;
            --color-background: #F1F5F9;
            --color-surface: #FFFFFF;
            --color-foreground: #0F172A;
            --color-muted: #64748B;
            --color-border: #E2E8F0;
            --color-success: #059669;
            --color-danger: #DC2626;
            --color-info: #0284C7;
            --radius: 10px;
            --shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Cairo', sans-serif;
            background:
                radial-gradient(1200px 500px at 100% -10%, rgba(30, 64, 175, 0.08), transparent 55%),
                radial-gradient(900px 400px at 0% 100%, rgba(217, 119, 6, 0.06), transparent 50%),
                var(--color-background);
            color: var(--color-foreground);
            min-height: 100dvh;
            line-height: 1.5;
            font-size: 16px;
        }

        a { color: inherit; text-decoration: none; }
        .wrap { max-width: 1280px; margin: 0 auto; padding: 1rem 1rem 2.5rem; }

        .topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding: 0.85rem 1rem;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .brand { display: flex; align-items: center; gap: 0.75rem; min-width: 0; }
        .brand-mark {
            width: 40px; height: 40px; border-radius: 8px;
            background: linear-gradient(145deg, var(--color-primary), var(--color-secondary));
            display: grid; place-items: center; flex-shrink: 0;
        }
        .brand-mark svg { width: 22px; height: 22px; color: #fff; }
        .brand h1 {
            margin: 0; font-size: 1.15rem; font-weight: 800; line-height: 1.3;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .brand p { margin: 0; color: var(--color-muted); font-size: 0.8rem; }

        .actions { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }
        .lang-switch {
            display: inline-flex;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .lang-btn {
            min-height: 44px;
            padding: 0.45rem 0.85rem;
            border: 0;
            background: transparent;
            font: inherit;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--color-muted);
            cursor: pointer;
            transition: background 150ms ease, color 150ms ease;
        }
        .lang-btn:hover { background: #F8FAFC; color: var(--color-foreground); }
        .lang-btn.active {
            background: var(--color-primary);
            color: #fff;
        }
        .lang-btn:focus-visible { outline: 3px solid rgba(30, 64, 175, 0.35); outline-offset: 1px; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
            min-height: 44px; padding: 0.55rem 1rem; border-radius: 8px;
            border: 1px solid transparent; font: inherit; font-weight: 700;
            cursor: pointer; transition: background 180ms ease, transform 150ms ease, border-color 180ms ease;
        }
        .btn:focus-visible { outline: 3px solid rgba(30, 64, 175, 0.35); outline-offset: 2px; }
        .btn:active { transform: scale(0.98); }
        .btn-primary { background: var(--color-primary); color: #fff; }
        .btn-primary:hover { background: #1D4ED8; }
        .btn-ghost { background: #fff; border-color: var(--color-border); color: var(--color-foreground); }
        .btn-ghost:hover { background: #F8FAFC; border-color: #CBD5E1; }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
            align-items: end;
            margin-bottom: 1.25rem;
            padding: 1rem;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .field label {
            display: block; font-size: 0.8rem; font-weight: 600;
            color: var(--color-muted); margin-bottom: 0.35rem;
        }
        .field input {
            width: 100%; min-height: 44px; padding: 0.5rem 0.75rem;
            border: 1px solid var(--color-border); border-radius: 8px;
            font: inherit; background: #fff; color: var(--color-foreground);
        }
        .field input:focus {
            outline: none; border-color: var(--color-secondary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .kpis {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        @media (min-width: 768px) {
            .kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .kpi {
            position: relative;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            padding: 0.9rem 1rem;
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: rise 280ms ease-out both;
        }
        .kpi:nth-child(2) { animation-delay: 40ms; }
        .kpi:nth-child(3) { animation-delay: 80ms; }
        .kpi:nth-child(4) { animation-delay: 120ms; }
        .kpi::before {
            content: '';
            position: absolute; inset-inline-start: 0; top: 0; bottom: 0; width: 4px;
            background: var(--accent, var(--color-primary));
        }
        .kpi .label {
            display: flex; align-items: center; gap: 0.4rem;
            color: var(--color-muted); font-size: 0.82rem; font-weight: 600; margin-bottom: 0.35rem;
        }
        .kpi .label svg { width: 16px; height: 16px; flex-shrink: 0; color: var(--accent, var(--color-primary)); }
        .kpi .value {
            font-size: clamp(1.15rem, 2.4vw, 1.55rem);
            font-weight: 800; font-variant-numeric: tabular-nums; letter-spacing: -0.02em;
        }
        .kpi .hint { margin-top: 0.25rem; font-size: 0.75rem; color: var(--color-muted); }

        .grid-2 {
            display: grid; gap: 0.75rem; margin-bottom: 1.25rem;
        }
        @media (min-width: 960px) {
            .grid-2 { grid-template-columns: 1.1fr 0.9fr; }
        }

        .panel {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1rem;
        }
        .panel h2 {
            margin: 0 0 0.85rem; font-size: 1rem; font-weight: 800;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .panel h2 span {
            display: inline-flex; width: 8px; height: 8px; border-radius: 999px;
            background: var(--color-accent);
        }

        .chart-box { position: relative; height: 260px; }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.6rem;
        }
        .meta {
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 0.7rem 0.8rem;
            background: #F8FAFC;
        }
        .meta .t { font-size: 0.75rem; color: var(--color-muted); font-weight: 600; }
        .meta .v { font-size: 1.05rem; font-weight: 800; font-variant-numeric: tabular-nums; margin-top: 0.15rem; }

        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table {
            width: 100%; border-collapse: collapse; min-width: 520px;
            font-variant-numeric: tabular-nums;
        }
        th, td {
            text-align: right; padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--color-border); font-size: 0.9rem;
        }
        th {
            color: var(--color-muted); font-weight: 700; font-size: 0.78rem;
            background: #F8FAFC; position: sticky; top: 0;
        }
        tbody tr { transition: background 150ms ease; }
        tbody tr:hover { background: #F1F5F9; }

        .pos { color: var(--color-success); }
        .neg { color: var(--color-danger); }

        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .kpi { animation: none; }
            .btn, tbody tr, .lang-btn { transition: none; }
        }
    </style>
</head>
<body>
    @php
        $fmt = fn ($n) => number_format((float) $n, 0, '.', ',');
        $money = fn ($n) => $fmt($n) . ' $';
        $company = $systemConfig['company_name'] ?? config('app.name');
    @endphp

    <div class="wrap">
        <header class="topbar">
            <div class="brand">
                <div class="brand-mark" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h2l1-3h8l1 3h2M5 13v5a1 1 0 001 1h1a1 1 0 001-1v-1h6v1a1 1 0 001 1h1a1 1 0 001-1v-5M7 10V7a1 1 0 011-1h5a1 1 0 011 1v3"/>
                    </svg>
                </div>
                <div style="min-width:0">
                    <h1 data-i18n="title">إحصائيات الأعمال</h1>
                    <p>{{ $company }} · {{ now()->format('Y-m-d H:i') }}</p>
                </div>
            </div>
            <div class="actions">
                <div class="lang-switch" role="group" aria-label="زمان / اللغة">
                    <button type="button" class="lang-btn" data-lang="ar">العربية</button>
                    <button type="button" class="lang-btn" data-lang="kr">کوردی</button>
                </div>
                <a class="btn btn-ghost" href="{{ route('dashboard') }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span data-i18n="home">الرئيسية</span>
                </a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <span data-i18n="print">طباعة</span>
                </button>
            </div>
        </header>

        <form class="filters" method="GET" action="{{ route('stats') }}" data-i18n-aria="filtersAria">
            <div class="field">
                <label for="from" data-i18n="fromDate">من تاريخ</label>
                <input id="from" type="date" name="from" value="{{ $from }}">
            </div>
            <div class="field">
                <label for="to" data-i18n="toDate">إلى تاريخ</label>
                <input id="to" type="date" name="to" value="{{ $to }}">
            </div>
            <div class="field" style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1">
                    <span data-i18n="applyFilter">تطبيق الفلتر</span>
                </button>
                <a href="{{ route('stats') }}" class="btn btn-ghost" style="flex:1;text-align:center;">
                    <span data-i18n="reset">إعادة</span>
                </a>
            </div>
        </form>

        <section class="kpis" data-i18n-aria="kpisAria">
            <article class="kpi" style="--accent: var(--color-primary)">
                <div class="label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17h8M5 10l2-4h10l2 4M5 10v7a1 1 0 001 1h1m11-8v7a1 1 0 01-1 1h-1"/></svg>
                    <span data-i18n="carsTotal">إجمالي السيارات</span>
                </div>
                <div class="value">{{ $fmt($stats['cars_total']) }}</div>
                <div class="hint">
                    <span data-i18n="stock">مخزن</span> {{ $fmt($stats['cars_in_stock']) }}
                    ·
                    <span data-i18n="sold">مباع</span> {{ $fmt($stats['cars_partial'] + $stats['cars_paid']) }}
                </div>
            </article>

            <article class="kpi" style="--accent: var(--color-info)">
                <div class="label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span data-i18n="sales">المبيعات</span>
                </div>
                <div class="value">{{ $money($stats['sales_amount']) }}</div>
                <div class="hint">
                    {{ $fmt($stats['sales_count']) }} <span data-i18n="ops">عملية</span>
                    ·
                    <span data-i18n="collected">محصّل</span> {{ $money($stats['sales_collected']) }}
                </div>
            </article>

            <article class="kpi" style="--accent: var(--color-success)">
                <div class="label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span data-i18n="profit">الربح</span>
                </div>
                <div class="value {{ $stats['profit'] >= 0 ? 'pos' : 'neg' }}">{{ $money($stats['profit']) }}</div>
                <div class="hint" data-i18n="profitHint">سعر البيع − التكلفة الكلية للمباع</div>
            </article>

            <article class="kpi" style="--accent: var(--color-danger)">
                <div class="label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span data-i18n="totalDebt">إجمالي الدين</span>
                </div>
                <div class="value neg">{{ $money($stats['total_debt']) }}</div>
                <div class="hint">
                    <span data-i18n="customers">عملاء</span> {{ $money($stats['customer_debt']) }}
                    ·
                    <span data-i18n="suppliers">موردين</span> {{ $money($stats['supplier_debt']) }}
                </div>
            </article>
        </section>

        <section class="grid-2">
            <div class="panel">
                <h2><span></span><span data-i18n="carsStatus">حالة السيارات</span></h2>
                <div class="chart-box">
                    <canvas id="carsChart" role="img" data-i18n-aria="carsChartAria"></canvas>
                </div>
            </div>

            <div class="panel">
                <h2><span></span><span data-i18n="extraDetails">تفاصيل إضافية</span></h2>
                <div class="meta-grid">
                    <div class="meta">
                        <div class="t" data-i18n="capital">رأس المال (مخزون + تكلفة)</div>
                        <div class="v">{{ $money($stats['total_capital']) }}</div>
                    </div>
                    <div class="meta">
                        <div class="t" data-i18n="income">إجمالي الدخل (حركات)</div>
                        <div class="v pos">{{ $money($stats['total_income']) }}</div>
                    </div>
                    <div class="meta">
                        <div class="t" data-i18n="expenses">إجمالي المصروف (حركات)</div>
                        <div class="v neg">{{ $money($stats['total_expenses']) }}</div>
                    </div>
                    <div class="meta">
                        <div class="t" data-i18n="netCash">صافي الحركات</div>
                        <div class="v {{ $stats['net_cash'] >= 0 ? 'pos' : 'neg' }}">{{ $money($stats['net_cash']) }}</div>
                    </div>
                    <div class="meta">
                        <div class="t" data-i18n="customerDebt">دين العملاء</div>
                        <div class="v neg">{{ $money($stats['customer_debt']) }}</div>
                    </div>
                    <div class="meta">
                        <div class="t" data-i18n="supplierDebt">دين الموردين</div>
                        <div class="v neg">{{ $money($stats['supplier_debt']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel" style="margin-bottom:1.25rem">
            <h2><span></span><span data-i18n="trendTitle">اتجاه المبيعات والربح (آخر 6 أشهر)</span></h2>
            <div class="chart-box" style="height:280px">
                <canvas id="trendChart" role="img" data-i18n-aria="trendChartAria"></canvas>
            </div>
            <div class="table-wrap" style="margin-top:1rem">
                <table>
                    <caption class="sr-only" data-i18n="monthTableCaption">جدول المبيعات الشهرية</caption>
                    <thead>
                        <tr>
                            <th scope="col" data-i18n="month">الشهر</th>
                            <th scope="col" data-i18n="salesCount">عدد المبيعات</th>
                            <th scope="col" data-i18n="sales">المبيعات</th>
                            <th scope="col" data-i18n="profit">الربح</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['monthly'] as $row)
                            <tr>
                                <td class="month-label" data-month="{{ $row['month'] }}" data-year="{{ $row['year'] }}">{{ $row['label'] }}</td>
                                <td>{{ $fmt($row['count']) }}</td>
                                <td>{{ $money($row['sales']) }}</td>
                                <td class="{{ $row['profit'] >= 0 ? 'pos' : 'neg' }}">{{ $money($row['profit']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        const COMPANY = @json($company);
        const MONTHLY = @json($stats['monthly']);
        const CARS_DATA = {
            inStock: {{ (int) $stats['cars_in_stock'] }},
            partial: {{ (int) $stats['cars_partial'] }},
            paid: {{ (int) $stats['cars_paid'] }}
        };

        const I18N = {
            ar: {
                pageTitle: 'الإحصائيات — ' + COMPANY,
                title: 'إحصائيات الأعمال',
                home: 'الرئيسية',
                print: 'طباعة',
                filtersAria: 'تصفية الإحصائيات',
                fromDate: 'من تاريخ',
                toDate: 'إلى تاريخ',
                applyFilter: 'تطبيق الفلتر',
                reset: 'إعادة',
                kpisAria: 'المؤشرات الرئيسية',
                carsTotal: 'إجمالي السيارات',
                stock: 'مخزن',
                sold: 'مباع',
                sales: 'المبيعات',
                ops: 'عملية',
                collected: 'محصّل',
                profit: 'الربح',
                profitHint: 'سعر البيع − التكلفة الكلية للمباع',
                totalDebt: 'إجمالي الدين',
                customers: 'عملاء',
                suppliers: 'موردين',
                carsStatus: 'حالة السيارات',
                carsChartAria: 'مخطط أعمدة لحالة السيارات',
                extraDetails: 'تفاصيل إضافية',
                capital: 'رأس المال (مخزون + تكلفة)',
                income: 'إجمالي الدخل (حركات)',
                expenses: 'إجمالي المصروف (حركات)',
                netCash: 'صافي الحركات',
                customerDebt: 'دين العملاء',
                supplierDebt: 'دين الموردين',
                trendTitle: 'اتجاه المبيعات والربح (آخر 6 أشهر)',
                trendChartAria: 'مخطط خطي للمبيعات والربح',
                monthTableCaption: 'جدول المبيعات الشهرية',
                month: 'الشهر',
                salesCount: 'عدد المبيعات',
                carsCountLabel: 'عدد السيارات',
                carUnit: 'سيارة',
                statusInStock: 'في المخزن',
                statusPartial: 'بيع جزئي',
                statusPaid: 'مدفوع كامل',
                months: ['كانون ٢','شباط','آذار','نيسان','أيار','حزيران','تموز','آب','أيلول','تشرين ١','تشرين ٢','كانون ١']
            },
            kr: {
                pageTitle: 'ئامارەکان — ' + COMPANY,
                title: 'ئامارەکانی کاروبار',
                home: 'سەرەکی',
                print: 'چاپکردن',
                filtersAria: 'فلتەرکردنی ئامارەکان',
                fromDate: 'لە بەرواری',
                toDate: 'بۆ بەرواری',
                applyFilter: 'جێبەجێکردنی فلتەر',
                reset: 'ڕیسێت',
                kpisAria: 'نیشانە سەرەکییەکان',
                carsTotal: 'کۆی سەیارەکان',
                stock: 'کۆگا',
                sold: 'فرۆشراو',
                sales: 'فرۆشتنەکان',
                ops: 'مامەڵە',
                collected: 'وەرگیراو',
                profit: 'قازانج',
                profitHint: 'نرخی فرۆشتن − کۆی تێچووی فرۆشراو',
                totalDebt: 'کۆی قەرز',
                customers: 'کڕیار',
                suppliers: 'دابینکەر',
                carsStatus: 'دۆخی سەیارەکان',
                carsChartAria: 'هێڵکاری ستوونی بۆ دۆخی سەیارەکان',
                extraDetails: 'وردەکاری زیاتر',
                capital: 'سەرمایە (کۆگا + تێچوو)',
                income: 'کۆی داهات (جووڵەکان)',
                expenses: 'کۆی خەرجی (جووڵەکان)',
                netCash: 'خاڵی جووڵەکان',
                customerDebt: 'قەرزی کڕیار',
                supplierDebt: 'قەرزی دابینکەر',
                trendTitle: 'ئاراستەی فرۆشتن و قازانج (دوا ٦ مانگ)',
                trendChartAria: 'هێڵکاری هێڵی بۆ فرۆشتن و قازانج',
                monthTableCaption: 'خشتەی فرۆشتنی مانگانە',
                month: 'مانگ',
                salesCount: 'ژمارەی فرۆشتن',
                carsCountLabel: 'ژمارەی سەیارە',
                carUnit: 'سەیارە',
                statusInStock: 'لە کۆگا',
                statusPartial: 'فرۆشتنی بەشەکی',
                statusPaid: 'تەواو پارەدراو',
                months: ['کانوونی ٢','شوبات','ئادار','نیسان','ئایار','حوزەیران','تەممووز','ئاب','ئەیلوول','تشرینی ١','تشرینی ٢','کانوونی ١']
            }
        };

        let carsChart = null;
        let trendChart = null;
        let currentLang = 'ar';

        function t(key) {
            return (I18N[currentLang] && I18N[currentLang][key]) || I18N.ar[key] || key;
        }

        function monthLabel(month, year) {
            const names = t('months');
            const name = names[(month - 1) % 12] || String(month);
            return name + ' ' + year;
        }

        function applyLang(lang) {
            currentLang = (lang === 'kr' || lang === 'ku') ? 'kr' : 'ar';
            document.documentElement.lang = currentLang === 'kr' ? 'ckb' : 'ar';
            document.documentElement.dir = 'rtl';

            document.querySelectorAll('[data-i18n]').forEach((el) => {
                const key = el.getAttribute('data-i18n');
                if (key === 'pageTitle') {
                    document.title = t(key);
                    return;
                }
                el.textContent = t(key);
            });

            document.querySelectorAll('[data-i18n-aria]').forEach((el) => {
                const key = el.getAttribute('data-i18n-aria');
                el.setAttribute('aria-label', t(key));
            });

            document.querySelectorAll('.month-label').forEach((el) => {
                const m = parseInt(el.getAttribute('data-month'), 10);
                const y = parseInt(el.getAttribute('data-year'), 10);
                if (m && y) el.textContent = monthLabel(m, y);
            });

            document.querySelectorAll('.lang-btn').forEach((btn) => {
                btn.classList.toggle('active', btn.getAttribute('data-lang') === currentLang);
            });

            try {
                localStorage.setItem('stats_lang', currentLang);
                localStorage.setItem('app_locale', currentLang);
            } catch (e) {}

            renderCharts();
        }

        function renderCharts() {
            if (typeof Chart === 'undefined') return;

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            Chart.defaults.font.family = 'Cairo, sans-serif';
            Chart.defaults.color = '#64748B';
            Chart.defaults.animation = reduceMotion ? false : { duration: 220 };

            const carsCtx = document.getElementById('carsChart');
            if (carsChart) carsChart.destroy();
            carsChart = new Chart(carsCtx, {
                type: 'bar',
                data: {
                    labels: [t('statusInStock'), t('statusPartial'), t('statusPaid')],
                    datasets: [{
                        label: t('carsCountLabel'),
                        data: [CARS_DATA.inStock, CARS_DATA.partial, CARS_DATA.paid],
                        backgroundColor: ['#1E40AF', '#D97706', '#059669'],
                        borderRadius: 6,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (c) => ' ' + c.parsed.y + ' ' + t('carUnit')
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#E2E8F0' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            const trendCtx = document.getElementById('trendChart');
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: MONTHLY.map((r) => monthLabel(r.month, r.year)),
                    datasets: [
                        {
                            label: t('sales'),
                            data: MONTHLY.map((r) => r.sales),
                            borderColor: '#1E40AF',
                            backgroundColor: 'rgba(30,64,175,0.12)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 3
                        },
                        {
                            label: t('profit'),
                            data: MONTHLY.map((r) => r.profit),
                            borderColor: '#059669',
                            backgroundColor: 'transparent',
                            borderDash: [6, 4],
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#E2E8F0' },
                            ticks: { callback: (v) => Number(v).toLocaleString('en-US') }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.lang-btn').forEach((btn) => {
                btn.addEventListener('click', () => applyLang(btn.getAttribute('data-lang')));
            });

            let saved = 'ar';
            try {
                saved = localStorage.getItem('stats_lang')
                    || localStorage.getItem('app_locale')
                    || 'ar';
            } catch (e) {}

            applyLang(saved);
        });
    </script>
</body>
</html>
