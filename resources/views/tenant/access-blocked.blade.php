<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title data-i18n="pageTitle">انتهاء الاشتراك — {{ $tenant->name ?? config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #e2e8f0;
        }
        .card {
            width: 100%;
            max-width: 520px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            animation: fadeUp 0.6s ease-out;
            position: relative;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .lang-switch {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 10;
            display: flex;
            gap: 0.35rem;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 2rem;
            padding: 0.25rem;
        }
        .lang-btn {
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            font-family: 'Cairo', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .lang-btn.active {
            background: rgba(255, 255, 255, 0.95);
            color: #1e1b4b;
        }
        .lang-btn:hover:not(.active) {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
            padding: 2.5rem 2rem 1.75rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.15) 0%, transparent 60%);
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 0 14px rgba(255, 255, 255, 0); }
        }
        .icon-wrap i { font-size: 2rem; color: #fff; }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            position: relative;
            line-height: 1.4;
        }
        .header p {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.85);
            position: relative;
        }
        .body { padding: 1.75rem 2rem 2rem; }
        .tenant-name {
            text-align: center;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 1.25rem;
        }
        .tenant-name strong { color: #cbd5e1; }
        .message {
            text-align: center;
            font-size: 1rem;
            line-height: 1.8;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }
        .safe-box {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            margin-bottom: 1.5rem;
        }
        .safe-box i {
            color: #34d399;
            font-size: 1.4rem;
            margin-top: 0.1rem;
            flex-shrink: 0;
        }
        .safe-box p {
            font-size: 0.9rem;
            line-height: 1.7;
            color: #a7f3d0;
        }
        .safe-box strong { color: #6ee7b7; }
        .warning-strip {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            color: #fcd34d;
            text-align: center;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .cta {
            display: block;
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 1rem;
            font-size: 1.05rem;
            font-weight: 700;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.35);
        }
        .cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(37, 211, 102, 0.45);
        }
        .cta i { margin-left: 0.5rem; }
        .phone {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #94a3b8;
            direction: ltr;
        }
        .phone span { color: #e2e8f0; font-weight: 600; }
        .footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="lang-switch" role="group" aria-label="تغيير اللغة">
            <button type="button" class="lang-btn active" data-lang="ar">العربية</button>
            <button type="button" class="lang-btn" data-lang="ku">کوردی</button>
        </div>

        <div class="header">
            <div class="icon-wrap">
                <i class="fas fa-lock"></i>
            </div>
            <h1 id="title-text"></h1>
            <p id="subtitle-text"></p>
        </div>

        <div class="body">
            @if($tenant->name)
                <div class="tenant-name" id="tenant-label">
                    <span id="account-label"></span> <strong>{{ $tenant->name }}</strong>
                </div>
            @endif

            <p class="message" id="message-text"></p>

            <div class="safe-box">
                <i class="fas fa-shield-halved"></i>
                <p id="safe-text"></p>
            </div>

            <div class="warning-strip" id="warning-text"></div>

            <a href="{{ $whatsappLink }}" id="whatsapp-cta" class="cta" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i>
                <span id="cta-text"></span>
            </a>

            <div class="phone">
                <span>{{ $whatsapp }}</span>
            </div>

            <div class="footer" id="footer-text"></div>
        </div>
    </div>

    <script>
        const REASON = @json($reason);
        const TENANT_NAME = @json($tenant->name ?? '');
        const WHATSAPP_BASE = @json($whatsappLink);

        const i18n = {
            ar: {
                pageTitle: 'انتهاء الاشتراك — ' + TENANT_NAME,
                titles: {
                    expired: 'انتهت مدة الاشتراك',
                    suspended: 'الحساب معلّق مؤقتاً',
                    inactive: 'الحساب غير مفعّل',
                },
                subtitle: 'يتعذّر الوصول إلى النظام في الوقت الحالي',
                accountLabel: 'الحساب:',
                messages: {
                    expired: 'انتهت الفترة التجريبية أو مدة اشتراكك. للاستمرار في استخدام النظام، يرجى التواصل مع المطور لتجديد الاشتراك.',
                    suspended: 'تم تعليق حسابك مؤقتاً. للتجديد وإعادة التفعيل، يرجى التواصل مع المطور.',
                    inactive: 'حسابك غير مفعّل حالياً. يرجى التواصل مع المطور لتفعيل الاشتراك.',
                },
                safeHtml: '<strong>اطمئن، بياناتك في أمان.</strong> جميع معلوماتك ومحتويات حسابك محفوظة بالكامل ولن يتم حذفها. بمجرد تجديد الاشتراك ستعود للعمل من حيث توقفت.',
                warning: 'لا يمكن إجراء أي تعديلات على الحساب حتى يتم تجديد الاشتراك',
                cta: 'تواصل مع المطور عبر واتساب',
                footer: 'للمساعدة والدعم الفني — فريق التطوير',
                whatsappMsg: 'مرحباً، أرغب بتجديد اشتراك حساب ' + TENANT_NAME + ' في نظام إدارة السيارات',
            },
            ku: {
                pageTitle: 'کۆتایی هاتنی بەشداریکردن — ' + TENANT_NAME,
                titles: {
                    expired: 'ماوەی بەشداریکردن تەواو بوو',
                    suspended: 'هەژمارەکە کاتییەوە ڕاگیراوە',
                    inactive: 'هەژمار چالاک نەکراوە',
                },
                subtitle: 'لە ئێستادا ناتوانیت بچیتە ناو سیستەمەوە',
                accountLabel: 'هەژمار:',
                messages: {
                    expired: 'ماوەی تاقیکردنەوە یان بەشداریکردنەکەت تەواو بووە. بۆ بەردەوامبوون لە بەکارهێنانی سیستەم، تکایە پەیوەندی بە گەشەپێدەرەوە بکە بۆ نوێکردنەوەی بەشداریکردن.',
                    suspended: 'هەژمارەکەت کاتییەوە ڕاگیراوە. بۆ نوێکردنەوە و چالاککردنەوە، تکایە پەیوەندی بە گەشەپێدەرەوە بکە.',
                    inactive: 'هەژمارەکەت لە ئێستادا چالاک نییە. تکایە پەیوەندی بە گەشەپێدەرەوە بکە بۆ چالاککردنی بەشداریکردن.',
                },
                safeHtml: '<strong>دڵنیابە، زانیارییەکانت پارێزراون.</strong> هەموو زانیاری و ناوەڕۆکی هەژمارەکەت بەتەواوی پارێزراوە و ناسڕدرێت. دوای نوێکردنەوەی بەشداریکردن دەگەڕیتەوە بۆ کار لەو شوێنەی وەستایت.',
                warning: 'ناتوانیت هیچ گۆڕانکارییەک بکەیت لەسەر هەژمار تا بەشداریکردن نوێ بکرێتەوە',
                cta: 'پەیوەندی بە گەشەپێدەرەوە بکە لە ڕێگەی واتساپ',
                footer: 'بۆ یارمەتی و پاڵپشتی تەکنیکی — تیمی گەشەپێدان',
                whatsappMsg: 'سڵاو، دەمەوێت بەشداریکردنی هەژماری ' + TENANT_NAME + ' لە سیستەمی بەڕێوەبردنی ئۆتۆمبێل نوێ بکمەوە',
            },
        };

        function getReasonKey() {
            return ['expired', 'suspended', 'inactive'].includes(REASON) ? REASON : 'suspended';
        }

        function applyLang(lang) {
            const t = i18n[lang];
            const reason = getReasonKey();

            document.documentElement.lang = lang === 'ku' ? 'ckb' : 'ar';
            document.title = t.pageTitle;

            document.getElementById('title-text').textContent = t.titles[reason];
            document.getElementById('subtitle-text').textContent = t.subtitle;

            const accountLabel = document.getElementById('account-label');
            if (accountLabel) accountLabel.textContent = t.accountLabel;

            document.getElementById('message-text').textContent = t.messages[reason];
            document.getElementById('safe-text').innerHTML = t.safeHtml;
            document.getElementById('warning-text').innerHTML =
                '<i class="fas fa-triangle-exclamation"></i> ' + t.warning;
            document.getElementById('cta-text').textContent = t.cta;
            document.getElementById('footer-text').textContent = t.footer;

            document.getElementById('whatsapp-cta').href =
                WHATSAPP_BASE + '?text=' + encodeURIComponent(t.whatsappMsg);

            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.lang === lang);
            });

            localStorage.setItem('access-blocked-lang', lang);
        }

        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.addEventListener('click', () => applyLang(btn.dataset.lang));
        });

        const savedLang = localStorage.getItem('access-blocked-lang');
        applyLang(savedLang === 'ku' ? 'ku' : 'ar');
    </script>
</body>
</html>
