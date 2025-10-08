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

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .error-message {
            display: none;
            margin-top: 10px;
        }

        .spinner-border {
            display: none;
            width: 1rem;
            height: 1rem;
            margin-right: 5px;
        }

        #togglePassword {
            border-left: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo" id="logo-container">
            <i class="bi bi-box-seam"></i>
            <h2 class="mt-3">Welcome Back</h2>
            <p class="text-muted">Login to your account</p>
        </div>

        <form id="loginForm" novalidate>
            <?php echo csrf_field(); ?>

            <div id="alert-container"></div>

            <div class="alert alert-info" id="example-credentials">
                <small>
                    <strong>Example:</strong><br>
                    Username: admin@localhost.com<br>
                    Password: #Admin123!
                </small>
            </div>

            <div class="mb-3" id="email-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="admin@localhost.com">
                </div>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>

            <div class="mb-3" id="password-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="#admin123!">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Password is required.
                </div>
            </div>

            <div class="mb-3 form-check" id="remember-group">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                <span class="spinner-border" role="status"></span>
                Login
            </button>

            <div class="text-center mt-3" id="links-container">
                <a href="register.php" class="text-decoration-none">Don't have an account? Register</a>
            </div>
        </form>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery Validation Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('bi-eye bi-eye-slash');
        });

        // Form validation
        $('#loginForm').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 8
                }
            },
            messages: {
                email: {
                    required: "Please enter your email address",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Password must be at least 8 characters"
                }
            },
            errorClass: 'invalid-feedback',
            errorElement: 'div',
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                // Show loading state
                const $btn = $('#loginBtn');
                const $spinner = $btn.find('.spinner-border');
                $btn.prop('disabled', true);
                $spinner.show();

                // Submit form via AJAX
                $.ajax({
                    url: 'api/auth.php',
                    type: 'POST',
                    data: $(form).serialize() + '&action=login',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('#alert-container').html(
                                '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-check-circle me-2"></i>' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                '</div>'
                            );

                            // Redirect to dashboard
                            setTimeout(function() {
                                window.location.href = 'dashboard.php';
                            }, 1000);
                        } else {
                            // Show error message
                            $('#alert-container').html(
                                '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-exclamation-triangle me-2"></i>' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                '</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#alert-container').html(
                            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-exclamation-triangle me-2"></i>An error occurred. Please try again.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                            '</div>'
                        );
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $spinner.hide();
                    }
                });
            }
        });

        // Check for timeout parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('timeout') === '1') {
            $('#alert-container').html(
                '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                '<i class="bi bi-clock-history me-2"></i>Your session has expired. Please login again.' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>'
            );
        }
    });
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
