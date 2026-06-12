<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
            margin: 0;
        }
        .error-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 3.5rem 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            max-width: 500px;
            text-align: center;
            border-top: 5px solid #dc3545;
            transition: transform 0.3s ease;
        }
        .error-card:hover {
            transform: translateY(-2px);
        }
        .error-code {
            font-size: 6.5rem;
            font-weight: 700;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 1.5rem;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
        }
        .error-text {
            color: #6b7280;
            margin-bottom: 2.5rem;
            font-size: 1rem;
            line-height: 1.6;
        }
        .btn-back {
            background-color: #dc3545;
            color: #ffffff;
            font-weight: 600;
            padding: 0.8rem 2.2rem;
            border-radius: 6px;
            transition: all 0.2s ease-in-out;
            border: none;
            box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
        }
        .btn-back:hover {
            background-color: #c82333;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-back:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <h1 class="error-title">Akses Tidak Diizinkan</h1>
        <p class="error-text">
            Maaf, Anda tidak memiliki hak akses untuk membuka halaman ini. Silakan kembali ke halaman sebelumnya atau hubungi administrator jika Anda merasa ini adalah kesalahan.
        </p>
        <button onclick="window.history.length > 1 ? window.history.back() : window.location.href = '/dashboard'" class="btn btn-back">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Sebelumnya
        </button>
    </div>
</body>
</html>
