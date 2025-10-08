<?php
require_once 'includes/session.php';

// Redirect if already logged in
if (is_logged_in()) {
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
    <title>Register - SaaS Template</title>

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
            padding: 20px 0;
        }

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }

        @media (min-width: 768px) {
            .register-container {
                max-width: 600px;
            }
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

        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }

        .strength-weak {
            background: #dc3545;
            width: 33%;
        }

        .strength-medium {
            background: #ffc107;
            width: 66%;
        }

        .strength-strong {
            background: #28a745;
            width: 100%;
        }

        .htmx-indicator {
            display: none;
        }

        .htmx-request .htmx-indicator {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="register-container" x-data="registerForm()">
        <div class="logo">
            <i class="bi bi-box-seam"></i>
            <h2 class="mt-3">Create Account</h2>
            <p class="text-muted">Join us today</p>
        </div>

        <form hx-post="api/auth.php?action=register"
              hx-target="#alert-container"
              hx-swap="innerHTML"
              hx-indicator="#registerBtn .spinner-border"
              @submit="handleSubmit"
              @htmx:after-request="handleResponse($event)">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div id="alert-container"></div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text"
                               class="form-control"
                               :class="{ 'is-invalid': errors.first_name }"
                               id="first_name"
                               name="first_name"
                               x-model="formData.first_name"
                               @blur="validateFirstName"
                               required>
                    </div>
                    <div class="invalid-feedback" x-show="errors.first_name" x-text="errors.first_name"></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text"
                               class="form-control"
                               :class="{ 'is-invalid': errors.last_name }"
                               id="last_name"
                               name="last_name"
                               x-model="formData.last_name"
                               @blur="validateLastName"
                               required>
                    </div>
                    <div class="invalid-feedback" x-show="errors.last_name" x-text="errors.last_name"></div>
                </div>
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
                           required>
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
                           @input="checkPasswordStrength"
                           @blur="validatePassword"
                           required>
                    <button class="btn btn-outline-secondary"
                            type="button"
                            @click="showPassword = !showPassword">
                        <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
                <div class="password-strength"
                     :class="{
                         'strength-weak': passwordStrength === 'weak',
                         'strength-medium': passwordStrength === 'medium',
                         'strength-strong': passwordStrength === 'strong'
                     }"
                     x-show="formData.password.length > 0"></div>
                <div class="invalid-feedback" x-show="errors.password" x-text="errors.password"></div>
                <small class="text-muted">Minimum 8 characters</small>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input :type="showConfirmPassword ? 'text' : 'password'"
                           class="form-control"
                           :class="{ 'is-invalid': errors.confirm_password }"
                           id="confirm_password"
                           name="confirm_password"
                           x-model="formData.confirm_password"
                           @blur="validateConfirmPassword"
                           required>
                    <button class="btn btn-outline-secondary"
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword">
                        <i class="bi" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
                <div class="invalid-feedback" x-show="errors.confirm_password" x-text="errors.confirm_password"></div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox"
                       class="form-check-input"
                       :class="{ 'is-invalid': errors.terms }"
                       id="terms"
                       name="terms"
                       x-model="formData.terms"
                       @change="validateTerms"
                       required>
                <label class="form-check-label" for="terms">
                    I agree to the terms and conditions
                </label>
                <div class="invalid-feedback" x-show="errors.terms" x-text="errors.terms"></div>
            </div>

            <button type="submit"
                    class="btn btn-primary w-100"
                    id="registerBtn"
                    :disabled="!isValid || loading">
                <span class="spinner-border spinner-border-sm htmx-indicator" role="status"></span>
                <span x-show="!loading">Create Account</span>
                <span x-show="loading">Creating account...</span>
            </button>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap Bundle (includes jQuery for Bootstrap components only) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function registerForm() {
        return {
            formData: {
                first_name: '',
                last_name: '',
                email: '',
                password: '',
                confirm_password: '',
                terms: false
            },
            errors: {
                first_name: '',
                last_name: '',
                email: '',
                password: '',
                confirm_password: '',
                terms: ''
            },
            showPassword: false,
            showConfirmPassword: false,
            passwordStrength: '',
            loading: false,

            validateFirstName() {
                if (!this.formData.first_name) {
                    this.errors.first_name = 'Please enter your first name';
                } else if (this.formData.first_name.length < 2) {
                    this.errors.first_name = 'First name must be at least 2 characters';
                } else {
                    this.errors.first_name = '';
                }
            },

            validateLastName() {
                if (!this.formData.last_name) {
                    this.errors.last_name = 'Please enter your last name';
                } else if (this.formData.last_name.length < 2) {
                    this.errors.last_name = 'Last name must be at least 2 characters';
                } else {
                    this.errors.last_name = '';
                }
            },

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
                    this.errors.password = 'Please create a password';
                } else if (this.formData.password.length < 8) {
                    this.errors.password = 'Password must be at least 8 characters';
                } else {
                    this.errors.password = '';
                }

                // Revalidate confirm password if it's been filled
                if (this.formData.confirm_password) {
                    this.validateConfirmPassword();
                }
            },

            validateConfirmPassword() {
                if (!this.formData.confirm_password) {
                    this.errors.confirm_password = 'Please confirm your password';
                } else if (this.formData.password !== this.formData.confirm_password) {
                    this.errors.confirm_password = 'Passwords do not match';
                } else {
                    this.errors.confirm_password = '';
                }
            },

            validateTerms() {
                if (!this.formData.terms) {
                    this.errors.terms = 'You must agree to the terms';
                } else {
                    this.errors.terms = '';
                }
            },

            checkPasswordStrength() {
                const password = this.formData.password;

                if (password.length === 0) {
                    this.passwordStrength = '';
                    return;
                }

                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!]+/)) strength++;

                if (strength <= 2) {
                    this.passwordStrength = 'weak';
                } else if (strength === 3) {
                    this.passwordStrength = 'medium';
                } else {
                    this.passwordStrength = 'strong';
                }
            },

            handleSubmit(event) {
                // Validate all fields before submission
                this.validateFirstName();
                this.validateLastName();
                this.validateEmail();
                this.validatePassword();
                this.validateConfirmPassword();
                this.validateTerms();

                // Check if any errors exist
                const hasErrors = Object.values(this.errors).some(error => error !== '');

                if (hasErrors) {
                    event.preventDefault();
                    return false;
                }

                this.loading = true;
            },

            handleResponse(event) {
                this.loading = false;

                try {
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

                            // Reset form
                            this.formData = {
                                first_name: '',
                                last_name: '',
                                email: '',
                                password: '',
                                confirm_password: '',
                                terms: false
                            };
                            this.passwordStrength = '';

                            // Redirect to login page with success message
                            setTimeout(() => {
                                window.location.href = 'login.php?registered=1';
                            }, 1500);
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
                return this.formData.first_name &&
                       this.formData.last_name &&
                       this.formData.email &&
                       this.formData.password &&
                       this.formData.password.length >= 8 &&
                       this.formData.confirm_password === this.formData.password &&
                       this.formData.terms &&
                       Object.values(this.errors).every(error => error === '');
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
