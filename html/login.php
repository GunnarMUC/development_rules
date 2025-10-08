<?php
require_once 'includes/session.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

// Check remember token
require_once 'includes/auth.php';
if (check_remember_token()) {
    header('Location: dashboard.php');
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SaaS Template</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 48px;
            color: #667eea;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }

        .btn-primary {
            background: #667eea;
            border: none;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover:not(:disabled) {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #togglePassword {
            border-left: 0;
        }

        .htmx-indicator {
            display: none;
        }

        .htmx-request .htmx-indicator {
            display: inline-block;
        }

        .htmx-request.htmx-indicator {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="login-container" x-data="loginForm()">
        <div class="logo">
            <i class="bi bi-box-seam"></i>
            <h2 class="mt-3">Welcome Back</h2>
            <p class="text-muted">Login to your account</p>
        </div>

        <form hx-post="api/auth.php?action=login"
              hx-target="#alert-container"
              hx-swap="innerHTML"
              hx-indicator="#loginBtn .spinner-border"
              @submit="handleSubmit"
              @htmx:after-request="handleResponse($event)">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div id="alert-container">
                <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-clock-history me-2"></i>Your session has expired. Please login again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Registration successful! Please login.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info">
                <small>
                    <strong>Example:</strong><br>
                    Username: admin@localhost.com<br>
                    Password: #Admin123!
                </small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email"
                           class="form-control"
                           :class="{ 'is-invalid': errors.email }"
                           id="email"
                           name="email"
                           x-model="formData.email"
                           @blur="validateEmail"
                           required
                           placeholder="admin@localhost.com">
                </div>
                <div class="invalid-feedback" x-show="errors.email" x-text="errors.email"></div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input :type="showPassword ? 'text' : 'password'"
                           class="form-control"
                           :class="{ 'is-invalid': errors.password }"
                           id="password"
                           name="password"
                           x-model="formData.password"
                           @blur="validatePassword"
                           required
                           placeholder="#Admin123!">
                    <button class="btn btn-outline-secondary"
                            type="button"
                            @click="showPassword = !showPassword">
                        <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
                <div class="invalid-feedback" x-show="errors.password" x-text="errors.password"></div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox"
                       class="form-check-input"
                       id="remember"
                       name="remember"
                       x-model="formData.remember">
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>

            <button type="submit"
                    class="btn btn-primary w-100"
                    id="loginBtn"
                    :disabled="!isValid || loading">
                <span class="spinner-border spinner-border-sm htmx-indicator" role="status"></span>
                <span x-show="!loading">Login</span>
                <span x-show="loading">Logging in...</span>
            </button>

            <div class="text-center mt-3">
                <a href="register.php" class="text-decoration-none">Don't have an account? Register</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap Bundle (includes jQuery for Bootstrap components only) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function loginForm() {
        return {
            formData: {
                email: '',
                password: '',
                remember: false
            },
            errors: {
                email: '',
                password: ''
            },
            showPassword: false,
            loading: false,

            validateEmail() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!this.formData.email) {
                    this.errors.email = 'Please enter your email address';
                } else if (!emailRegex.test(this.formData.email)) {
                    this.errors.email = 'Please enter a valid email address';
                } else {
                    this.errors.email = '';
                }
            },

            validatePassword() {
                if (!this.formData.password) {
                    this.errors.password = 'Please enter your password';
                } else if (this.formData.password.length < 8) {
                    this.errors.password = 'Password must be at least 8 characters';
                } else {
                    this.errors.password = '';
                }
            },

            handleSubmit(event) {
                // Validate all fields before submission
                this.validateEmail();
                this.validatePassword();

                // If there are errors, prevent HTMX submission
                if (this.errors.email || this.errors.password) {
                    event.preventDefault();
                    return false;
                }

                this.loading = true;
            },

            handleResponse(event) {
                this.loading = false;

                try {
                    // Try to parse response as JSON (from api/auth.php)
                    const xhr = event.detail.xhr;
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            // Show success message
                            document.getElementById('alert-container').innerHTML =
                                '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-check-circle me-2"></i>' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                '</div>';

                            // Redirect to dashboard
                            setTimeout(() => {
                                window.location.href = 'dashboard.php';
                            }, 1000);
                        } else {
                            // Show error message
                            document.getElementById('alert-container').innerHTML =
                                '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-exclamation-triangle me-2"></i>' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                '</div>';
                        }
                    }
                } catch (e) {
                    console.error('Response parsing error:', e);
                }
            },

            get isValid() {
                return this.formData.email &&
                       this.formData.password &&
                       this.formData.password.length >= 8 &&
                       !this.errors.email &&
                       !this.errors.password;
            }
        }
    }
    </script>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light" style="position: fixed; bottom: 0; width: 100%;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col text-center">
                    <span class="text-muted">Copyright <?php echo date('Y'); ?>, Kinetic Seas Incorporated - All Rights Reserved</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
