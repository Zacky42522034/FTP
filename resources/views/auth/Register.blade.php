<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Modern Auth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .card-shadow {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.2);
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Register Card -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Buat Akun Baru</h1>
                    <p class="text-gray-600 mt-2">Isi data diri Anda</p>
                </div>

                <form id="register-form" class="space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div>
                        <label for="register-username"
                            class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="register-username" name="username" required
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl input-focus focus:outline-none focus:border-pink-500 transition duration-200"
                                placeholder="Masukkan username">
                        </div>
                    </div>

                    <div>
                        <label for="register-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" id="register-email" name="email" required
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl input-focus focus:outline-none focus:border-pink-500 transition duration-200"
                                placeholder="Masukkan email">
                        </div>
                    </div>

                    <div>
                        <label for="register-password"
                            class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="register-password" name="password" required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl input-focus focus:outline-none focus:border-pink-500 transition duration-200"
                                placeholder="Masukkan password">
                            <button type="button" id="toggle-register-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="register-confirm-password"
                            class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="register-confirm-password" name="password_confirmation" required
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl input-focus focus:outline-none focus:border-pink-500 transition duration-200"
                                placeholder="Masukkan ulang password">
                        </div>
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" id="agree-terms" required
                            class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded mt-1">
                        <label for="agree-terms" class="ml-2 block text-sm text-gray-700">
                            Saya setuju dengan <a href="#" class="text-pink-600 hover:text-pink-500 font-medium">syarat
                                dan ketentuan</a>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-pink-500 to-red-500 text-white py-3 px-4 rounded-xl hover:from-pink-600 hover:to-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition duration-200 font-medium">
                        <i class="fas fa-user-plus mr-2"></i>Daftar
                    </button>

                    <div class="text-center mt-6">
                        <p class="text-gray-600">Sudah punya akun?
                            <a href="{{ route('login') }}" class="text-pink-600 hover:text-pink-500 font-medium">Masuk
                                di sini</a>
                        </p>
                    </div>
                </form>

                <div class="mt-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Atau daftar dengan</span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fab fa-google text-red-500 mr-2"></i> Google
                        </button>
                        <button
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fab fa-facebook text-blue-600 mr-2"></i> Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; 2023 Modern Auth. All rights reserved.</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle password visibility
        const toggleRegisterPassword = document.getElementById('toggle-register-password');
        const registerPasswordInput = document.getElementById('register-password');

        toggleRegisterPassword.addEventListener('click', function () {
            const type = registerPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            registerPasswordInput.setAttribute('type', type);
            this.innerHTML = type === 'password'
                ? '<i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>'
                : '<i class="fas fa-eye-slash text-gray-400 hover:text-gray-600"></i>';
        });

        // Password strength indicator
        const passwordInput = document.getElementById('register-password');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');

        passwordInput.addEventListener('input', function () {
            const password = this.value;
            let strength = 0;

            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 20;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 20;
            if (password.match(/([0-9])/)) strength += 20;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 20;

            strengthBar.style.width = strength + '%';
            if (strength <= 40) {
                strengthBar.className = 'password-strength bg-red-500 h-1.5 rounded-full';
                strengthText.textContent = 'Lemah';
                strengthText.className = 'text-xs text-red-500';
            } else if (strength <= 80) {
                strengthBar.className = 'password-strength bg-yellow-500 h-1.5 rounded-full';
                strengthText.textContent = 'Sedang';
                strengthText.className = 'text-xs text-yellow-500';
            } else {
                strengthBar.className = 'password-strength bg-green-500 h-1.5 rounded-full';
                strengthText.textContent = 'Kuat';
                strengthText.className = 'text-xs text-green-500';
            }
        });

        
    </script>

</body>

</html>