<!-- resources/views/reset-password.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .success-message {
            color: #28a745;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .submit-button {
            width: 100%;
            padding: 12px;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .submit-button:hover {
            background-color: #357ABD;
        }

        .submit-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #4A90E2;
            text-decoration: none;
            font-size: 14px;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Password</h1>
            <p>Masukkan password baru Anda</p>
        </div>

        <form id="resetPasswordForm">
            <input type="hidden" id="token" value="{{ $token }}">
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    minlength="8"
                    autocomplete="new-password"
                >
                <div class="error-message" id="passwordError"></div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required
                    autocomplete="new-password"
                >
                <div class="error-message" id="confirmError"></div>
            </div>

            <div class="success-message" id="successMessage"></div>
            <div class="error-message" id="apiErrorMessage"></div>

            <button type="submit" class="submit-button" id="submitButton">
                Reset Password
            </button>
        </form>

        <div class="back-to-login">
            <a href="/login">Kembali ke Login</a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('resetPasswordForm');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const passwordError = document.getElementById('passwordError');
        const confirmError = document.getElementById('confirmError');
        const successMessage = document.getElementById('successMessage');
        const apiErrorMessage = document.getElementById('apiErrorMessage');
        const submitButton = document.getElementById('submitButton');
        const token = document.getElementById('token').value;

        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            // Reset semua pesan error
            [passwordError, confirmError, apiErrorMessage, successMessage].forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });

            // Validasi
            const validations = {
                password: {
                    minLength: password.value.length >= 8,
                    hasUpperCase: /[A-Z]/.test(password.value),
                    hasLowerCase: /[a-z]/.test(password.value),
                    hasNumber: /\d/.test(password.value)
                },
                confirmation: password.value === passwordConfirmation.value
            };

            let isValid = true;
            const errors = [];

            // Validasi password
            if (!validations.password.minLength) {
                errors.push('Password minimal 8 karakter');
                isValid = false;
            }
            if (!validations.password.hasUpperCase || !validations.password.hasLowerCase) {
                errors.push('Password harus mengandung huruf besar dan kecil');
                isValid = false;
            }
            if (!validations.password.hasNumber) {
                errors.push('Password harus mengandung angka');
                isValid = false;
            }

            // Tampilkan error password jika ada
            if (errors.length > 0) {
                passwordError.textContent = errors.join(', ');
                passwordError.style.display = 'block';
            }

            // Validasi konfirmasi password
            if (!validations.confirmation) {
                confirmError.textContent = 'Konfirmasi password tidak sesuai';
                confirmError.style.display = 'block';
                isValid = false;
            }

            if (!isValid) return;

            try {
                submitButton.disabled = true;
                submitButton.textContent = 'Memproses...';

                const response = await fetch(`/api/password/reset/${token}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        token,
                        password: password.value,
                        password_confirmation: passwordConfirmation.value
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    successMessage.textContent = 'Password berhasil direset! Mengalihkan ke halaman login...';
                    successMessage.style.display = 'block';
                    form.reset();
                    
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat mereset password');
                }
            } catch (error) {
                apiErrorMessage.textContent = error.message;
                apiErrorMessage.style.display = 'block';
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Reset Password';
            }
        });

        // Validasi real-time untuk konfirmasi password
        passwordConfirmation.addEventListener('input', function() {
            if (this.value && this.value !== password.value) {
                confirmError.textContent = 'Password tidak cocok';
                confirmError.style.display = 'block';
            } else {
                confirmError.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>