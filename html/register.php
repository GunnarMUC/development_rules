<?php
require_once 'includes/session.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
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

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .spinner-border {
            display: none;
            width: 1rem;
            height: 1rem;
            margin-right: 5px;
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
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo" id="logo-container">
            <i class="bi bi-box-seam"></i>
            <h2 class="mt-3">Create Account</h2>
            <p class="text-muted">Join us today</p>
        </div>

        <form id="registerForm" novalidate>
            <?php echo csrf_field(); ?>

            <div id="alert-container"></div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="invalid-feedback">
                        Please enter your first name.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="invalid-feedback">
                        Please enter your last name.
                    </div>
                </div>
            </div>

            <div class="mb-3" id="email-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>

            <div class="mb-3" id="password-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
                <div class="invalid-feedback">
                    Password must be at least 8 characters.
                </div>
                <small class="text-muted">Minimum 8 characters</small>
            </div>

            <div class="mb-3" id="confirm-password-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Passwords do not match.
                </div>
            </div>

            <div class="mb-3 form-check" id="terms-group">
                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the terms and conditions
                </label>
                <div class="invalid-feedback">
                    You must agree to the terms.
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="registerBtn">
                <span class="spinner-border" role="status"></span>
                Create Account
            </button>

            <div class="text-center mt-3" id="links-container">
                <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
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

        $('#toggleConfirmPassword').on('click', function() {
            const passwordField = $('#confirm_password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('bi-eye bi-eye-slash');
        });

        // Password strength indicator
        $('#password').on('keyup', function() {
            const password = $(this).val();
            const strengthBar = $('#passwordStrength');

            if (password.length === 0) {
                strengthBar.removeClass('strength-weak strength-medium strength-strong');
                return;
            }

            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;

            strengthBar.removeClass('strength-weak strength-medium strength-strong');

            if (strength <= 2) {
                strengthBar.addClass('strength-weak');
            } else if (strength === 3) {
                strengthBar.addClass('strength-medium');
            } else {
                strengthBar.addClass('strength-strong');
            }
        });

        // Custom validator for password match
        $.validator.addMethod("passwordMatch", function(value, element) {
            return value === $('#password').val();
        }, "Passwords do not match");

        // Form validation
        $('#registerForm').validate({
            rules: {
                first_name: {
                    required: true,
                    minlength: 2
                },
                last_name: {
                    required: true,
                    minlength: 2
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 8
                },
                confirm_password: {
                    required: true,
                    passwordMatch: true
                },
                terms: {
                    required: true
                }
            },
            messages: {
                first_name: {
                    required: "Please enter your first name",
                    minlength: "First name must be at least 2 characters"
                },
                last_name: {
                    required: "Please enter your last name",
                    minlength: "Last name must be at least 2 characters"
                },
                email: {
                    required: "Please enter your email address",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please create a password",
                    minlength: "Password must be at least 8 characters"
                },
                confirm_password: {
                    required: "Please confirm your password"
                },
                terms: {
                    required: "You must agree to the terms"
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
            errorPlacement: function(error, element) {
                if (element.attr('id') === 'terms') {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element.closest('.input-group'));
                }
            },
            submitHandler: function(form) {
                // Show loading state
                const $btn = $('#registerBtn');
                const $spinner = $btn.find('.spinner-border');
                $btn.prop('disabled', true);
                $spinner.show();

                // Submit form via AJAX
                $.ajax({
                    url: 'api/auth.php',
                    type: 'POST',
                    data: $(form).serialize() + '&action=register',
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

                            // Reset form
                            form.reset();

                            // Redirect to dashboard
                            setTimeout(function() {
                                window.location.href = 'dashboard.php';
                            }, 1500);
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