<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-sm-5">
                        <!-- Logo Section -->
                        <div class="text-center mb-4">
                            <div class="app-brand-logo mb-3">
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-user-shield text-white fs-3"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">Welcome Back!</h3>
                            <p class="text-muted mb-0">Sign into your account</p>
                        </div>

                        <!-- Login Form -->
                        <form id="formAuthentication" class="form-submit-event" action="{{ route('users.authenticate') }}" method="POST">
                            @method('POST')
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-1 text-primary"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control border-start-0 ps-0" 
                                           id="email" 
                                           name="email"
                                           placeholder="Enter your email address"
                                           value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? 'admin@gmail.com' : '' ?>"
                                           autofocus 
                                           required />
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="password">
                                    <i class="fas fa-lock me-1 text-primary"></i>
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-key text-muted"></i>
                                    </span>
                                    <input type="password" 
                                           id="password" 
                                           class="form-control border-start-0 border-end-0 ps-0" 
                                           name="password"
                                           placeholder="Enter your password"
                                           value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? '12345678' : '' ?>"
                                           required />
                                    <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <div class="text-danger small mt-1 error-message"></div>
                                @error('password')
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <button class="btn btn-primary btn-lg w-100 fw-semibold rounded-3 shadow-sm" 
                                        id="submit_btn" 
                                        type="submit">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login to Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

 
        document.getElementById('formAuthentication').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            if (!email.value || !password.value) {
                e.preventDefault();
                if (!email.value) email.classList.add('is-invalid');
                if (!password.value) password.classList.add('is-invalid');
            }
        });

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });

        document.getElementById('submit_btn').addEventListener('submit', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        });
    </script>
</body>
</html>