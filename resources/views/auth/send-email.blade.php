<!-- resources/views/emails/reset-password.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .content {
            padding: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            background-color: #000 !important; /* Tambahkan !important */
            color: #ffffff !important; /* Tambahkan !important */
            text-align: center;
        }
        .button:hover {
            background-color: #333 !important; /* Tambahkan !important */
        }
        .button:visited,
        .button:focus,
        .button:active {
            background-color: #000 !important; /* Pastikan warna latar tetap hitam */
            color: #ffffff !important; /* Pastikan teks tetap putih */
            text-decoration: none !important; /* Hapus underline default */
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Reset Password</h2>
        </div>
        
        <div class="content">
            <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
            
            <p>Silakan klik tombol di bawah ini untuk melakukan reset password:</p>
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </div>
            
            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            
            <p>Link reset password ini akan kadaluarsa dalam 1 jam.</p>
            
            <p>Jika Anda mengalami masalah saat mengklik tombol "Reset Password", salin dan tempel URL berikut ke browser Anda:</p>
            <p>{{ $resetUrl }}</p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>