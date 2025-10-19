<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-hover: #2e59d9;
            --secondary-color: #858796;
            --light-bg: #f8f9fc;
            --gradient-primary: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        }

        body {
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1200px;
            width: 90%;
            margin: 20px;
        }

        .hero-content {
            display: flex;
            flex-wrap: wrap;
        }

        .hero-left {
            flex: 1 1 500px;
            padding: 60px 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-right {
            flex: 1 1 400px;
            padding: 60px 50px;
            background: white;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }

        .feature-list li {
            margin: 15px 0;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .feature-list i {
            margin-right: 15px;
            font-size: 1.3rem;
            color: #4caf50;
            background: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form-section h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .login-form-section p {
            color: #666;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary-custom {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 100%;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
            background: linear-gradient(180deg, #2e59d9 10%, #224abe 100%);
        }

        .divider-text {
            position: relative;
            text-align: center;
            margin: 25px 0;
            color: #999;
        }

        .divider-text:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider-text span {
            background: white;
            padding: 0 15px;
            position: relative;
        }

        .btn-outline-secondary-custom {
            border: 2px solid #e0e0e0;
            color: #666;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            background: white;
        }

        .btn-outline-secondary-custom:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(78, 115, 223, 0.05);
        }

        .stats-row {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
            }

            .hero-left, .hero-right {
                padding: 40px 30px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .stats-row {
                flex-wrap: wrap;
            }
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .form-check-label {
            color: #666;
            font-size: 0.95rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div id="hero-container" class="hero-section">
        <div id="hero-content" class="hero-content">
            <div id="hero-left" class="hero-left">
                <h1 id="hero-title" class="hero-title">Task Management System</h1>
                <p id="hero-subtitle" class="hero-subtitle">
                    Welcome to the starter template for the Linux, Apache, MariaDb, HTMX, Bootstrap 5, and Alpine.js SaaS appication.
                </p>

                <ul id="feature-list" class="feature-list">
                    <li id="feature-1"><i class="bi bi-check-circle-fill"></i> Organize tasks and projects efficiently</li>
                    <li id="feature-2"><i class="bi bi-check-circle-fill"></i> Real-time team collaboration</li>
                    <li id="feature-3"><i class="bi bi-check-circle-fill"></i> Calendar integration and scheduling</li>
                    <li id="feature-4"><i class="bi bi-check-circle-fill"></i> Smart notifications and reminders</li>
                    <li id="feature-5"><i class="bi bi-check-circle-fill"></i> Advanced analytics and reporting</li>
                </ul>

                <div id="stats-row" class="stats-row">
                    <div id="stat-1" class="stat-item">
                        <span class="stat-number">PHP</span>
                        <span class="stat-label">Backend Language</span>
                    </div>
                    <div id="stat-2" class="stat-item">
                        <span class="stat-number">HTMX</span>
                        <span class="stat-label">Javascript Framework</span>
                    </div>
                    <div id="stat-3" class="stat-item">
                        <span class="stat-number">Bootstrap 5.3</span>
                        <span class="stat-label">Responsive Framework</span>
                    </div>
                </div>
            </div>

            <div id="hero-right" class="hero-right">
                <div id="login-form-section" class="login-form-section">
                    <h2 id="welcome-title">Get Started Today!</h2>
                    <p id="welcome-subtitle">Use this template for the widest range of large applications.</p>

                    <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
                        <div id="success-alert" class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> Registration successful! Please log in.
                        </div>
                    <?php endif; ?>

                    <div id="action-buttons" class="d-grid gap-3 mt-4">
                        <a id="login-button" href="login.php" class="btn btn-primary-custom btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>

                        <a id="signup-button" href="register.php" class="btn btn-outline-secondary-custom btn-lg">
                            <i class="bi bi-person-plus"></i> Sign Up
                        </a>
                    </div>

                    <div id="features-summary" class="text-center mt-5">
                        <h5 id="features-title" class="text-muted mb-3">Why Choose Us?</h5>
                        <div id="features-grid" class="row g-3">
                            <div id="feature-item-1" class="col-6">
                                <i class="bi bi-shield-check text-success fs-3"></i>
                                <p class="small mb-0">Secure & Reliable</p>
                            </div>
                            <div id="feature-item-2" class="col-6">
                                <i class="bi bi-lightning-charge text-warning fs-3"></i>
                                <p class="small mb-0">Fast Performance</p>
                            </div>
                            <div id="feature-item-3" class="col-6">
                                <i class="bi bi-people text-info fs-3"></i>
                                <p class="small mb-0">Team Collaboration</p>
                            </div>
                            <div id="feature-item-4" class="col-6">
                                <i class="bi bi-graph-up text-primary fs-3"></i>
                                <p class="small mb-0">Analytics & Insights</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const heroSection = document.querySelector('.hero-section');
            heroSection.style.opacity = '0';
            heroSection.style.transform = 'translateY(20px)';

            setTimeout(() => {
                heroSection.style.transition = 'all 0.6s ease';
                heroSection.style.opacity = '1';
                heroSection.style.transform = 'translateY(0)';
            }, 100);

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>