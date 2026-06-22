<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password — {{ config('app.name', 'TPSR') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body {
            background: #eef3fb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            font-family: 'Montserrat', sans-serif;
        }
        .auth-card {
            width: 520px;
            max-width: 100%;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 14px 28px rgba(15,23,42,0.18), 0 10px 10px rgba(15,23,42,0.12);
            padding: 48px 44px;
            text-align: center;
        }
        h1 { margin: 0 0 10px; font-size: 26px; font-weight: 800; color: #111827; }
        p { font-size: 13px; line-height: 1.6; color: #6b7280; margin: 0 0 24px; }
        .status-msg {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }
        .auth-input {
            background-color: #eef2f7;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 12px 14px;
            width: 100%;
            font-size: 13px;
            font-family: 'Montserrat', sans-serif;
            color: #111827;
            outline: none;
            transition: border-color 0.2s;
        }
        .auth-input:focus { border-color: #0b2f5b; }
        .auth-input.is-invalid { border-color: #ef4444; }
        .error-msg { color: #b91c1c; font-size: 11px; text-align: left; margin: 4px 0 0; }
        .auth-button {
            width: 100%;
            border-radius: 20px;
            border: none;
            background-color: #0b2f5b;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 16px;
            font-family: 'Montserrat', sans-serif;
            transition: background 0.2s;
        }
        .auth-button:hover { background-color: #0d3a72; }
        .back-link { display: block; margin-top: 20px; font-size: 12px; color: #6b7280; text-decoration: none; }
        .back-link:hover { color: #0b2f5b; }
        label { display: block; text-align: left; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    </style>
</head>
<body>
    <main class="auth-card">
        {{-- Brand Icon --}}
        <div style="margin-bottom:24px;">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(34,197,94,0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 448 512" fill="white">
                    <path d="M320 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM125.7 175.5c9.9-9.9 23.4-15.5 37.5-15.5c1.9 0 3.8 .1 5.6 .3L137.6 254c-9.3 28 1.7 58.8 26.8 74.5l86.2 51.7-27.4 87.4c-5.6 17.9 4.4 36.9 22.3 42.5s36.9-4.4 42.5-22.3l31.2-99.4c5.4-17.1-.1-35.8-13.9-47.2L282 299l30.8-77.3 10.1 20.1c5.1 10.2 14.3 17.8 25.2 21.1l29.3 8.9c18.2 5.5 37.4-4.8 42.9-23s-4.8-37.4-23-42.9l-19.7-6L337 163c-11.7-23.4-36.3-38.5-62.6-38.7c-.6 0-1.2 0-1.8 0c-17.2 0-33.7 6.4-46.2 18L178 191.7c-5.7 5.7-6.5 14.7-1.8 21.4l7.2 10.2 6-17.1c3.1-8.8 7.3-16.9 12.3-24.2l-76 76c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0L186.7 242l-61 61z"/>
                </svg>
            </div>
        </div>

        <h1>Lupa Password</h1>
        <p>Masukkan email yang terdaftar. Kami akan mengirimkan link untuk mereset password Anda.</p>

        {{-- Status sukses --}}
        @if (session('status'))
            <div class="status-msg">
                ✓ {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div style="margin-bottom:16px;">
                <label for="email">Alamat Email</label>
                <input id="email" type="email" name="email"
                    class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="email@sekolah.com"
                    required autofocus autocomplete="email">
                @error('email')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-button">
                Kirim Link Reset Password
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">← Kembali ke halaman masuk</a>
    </main>
</body>
</html>
