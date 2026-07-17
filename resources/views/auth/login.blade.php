<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('تسجيل الدخول') }} — {{ $systemConfig['company_name'] ?? config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: #1f2937;
            background-image: url('{{ $systemConfig['login_bg_url'] ?? '/img/logo-color1.png' }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 1rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
            padding: 1.75rem 1.5rem 1.5rem;
            animation: fadeUp 0.45s ease-out;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .brand {
            text-align: center;
            margin-bottom: 1.25rem;
        }
        .brand img {
            max-width: 160px;
            max-height: 72px;
            object-fit: contain;
            margin: 0 auto 0.75rem;
            display: block;
        }
        .brand h1 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.4;
        }
        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.35rem;
        }
        .field { margin-bottom: 1rem; }
        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.7rem 0.85rem;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            background: #fff;
            color: #111827;
        }
        input:focus {
            border-color: #e11d48;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
        }
        .row-remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0 1.25rem;
            color: #4b5563;
            font-size: 0.9rem;
        }
        .row-remember input { width: 1rem; height: 1rem; accent-color: #e11d48; }
        .btn {
            width: 100%;
            border: none;
            border-radius: 0.5rem;
            padding: 0.8rem 1rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background: #e11d48;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn:hover { background: #be123c; }
        .btn:active { transform: scale(0.99); }
        .error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            padding: 0.65rem 0.85rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .status {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            border-radius: 0.5rem;
            padding: 0.65rem 0.85rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .field-error { color: #dc2626; font-size: 0.8rem; margin-top: 0.35rem; }
        .debug-box {
            margin-top: 1rem;
            width: 100%;
            max-width: 640px;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
            font-size: 0.78rem;
            line-height: 1.45;
            direction: ltr;
            text-align: left;
            box-shadow: 0 12px 30px rgba(0,0,0,0.35);
        }
        .debug-box h3 {
            color: #fbbf24;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .debug-box pre {
            white-space: pre-wrap;
            word-break: break-word;
            font-family: ui-monospace, Consolas, monospace;
        }
        .wrap {
            width: 100%;
            max-width: 640px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
    <div class="card">
        <div class="brand">
            @if(!empty($systemConfig['logo_url']))
                <img src="{{ $systemConfig['logo_url'] }}" alt="{{ $systemConfig['company_name'] ?? 'Logo' }}">
            @endif
            <h1>{{ $systemConfig['company_name'] ?? config('app.name') }}</h1>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login" autocomplete="on">
            @csrf

            <div class="field">
                <label for="email">اسم المستخدم / البريد</label>
                <input
                    id="email"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">كلمة المرور</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <label class="row-remember">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>تذكرني</span>
            </label>

            <button type="submit" class="btn">تسجيل الدخول</button>
        </form>
    </div>

    @if (config('app.login_debug') && session('login_debug'))
        <div class="debug-box">
            <h3>LOGIN DEBUG — انسخ هذا كله وأرسله</h3>
            <pre id="login-debug-json">{{ json_encode(session('login_debug'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            <button type="button" class="btn" style="margin-top:0.75rem;background:#334155;" onclick="navigator.clipboard.writeText(document.getElementById('login-debug-json').innerText)">نسخ النتيجة</button>
        </div>
    @endif
    </div>
</body>
</html>
