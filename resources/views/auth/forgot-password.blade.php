<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TPSR') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800&display=swap" rel="stylesheet">
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
            color: #1f2937;
        }
        .auth-card {
            width: 920px;
            max-width: 100%;
            min-height: 520px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.24), 0 10px 10px rgba(15, 23, 42, 0.18);
        }
        .auth-panel {
            padding: 54px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .brand-panel {
            background: linear-gradient(to right, #0b2f5b, #061a33);
            color: #ffffff;
        }
        h1 { margin: 0; font-size: 30px; font-weight: 800; }
        p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.4px;
            margin: 18px 0 26px;
        }
        .auth-button {
            border-radius: 20px;
            border: 1px solid #0b2f5b;
            background-color: #0b2f5b;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 12px 34px;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .auth-button.ghost {
            background: transparent;
            border-color: #ffffff;
            color: #ffffff;
        }
        .auth-note {
            background-color: #eef2f7;
            border-radius: 6px;
            padding: 16px;
            font-size: 13px;
            line-height: 20px;
            text-align: left;
            margin-top: 4px;
        }
        @media (max-width: 760px) {
            .auth-card { grid-template-columns: 1fr; }
            .brand-panel { min-height: 220px; }
        }
    </style>
</head>
<body>
    <main class="auth-card">
        <section class="auth-panel">
            <h1>Lupa Password</h1>
            <p>Untuk menjaga keamanan akun, penggantian password dilakukan melalui admin.</p>
            <div class="auth-note">
                Silakan hubungi admin untuk bantuan reset password akun Anda. Sertakan nama dan email akun agar proses pengecekan lebih mudah.
            </div>
            <a class="auth-button" href="{{ route('login') }}">Kembali Masuk</a>
        </section>

        <section class="auth-panel brand-panel">
            <h1>Butuh Bantuan?</h1>
            <p>Admin akan membantu memverifikasi akun dan mengatur ulang akses Anda.</p>
            <a class="auth-button ghost" href="{{ route('login') }}">Masuk</a>
        </section>
    </main>
</body>
</html>
