<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

if (isLoggedIn()) {
    redirect(SITE_URL . (isAdmin() ? '/views/admin/dashboard.php' : '/views/student/my_courses.php'));
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 2rem;
        }

        .login-left {
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            padding: 3rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: moveBackground 20s linear infinite;
        }

        @keyframes moveBackground {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(30px, 30px);
            }
        }

        .login-left-content {
            position: relative;
            z-index: 2;
        }

        .login-left h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .login-left p {
            opacity: 0.9;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .login-right {
            padding: 3rem;
        }

        .form-control {
            padding: 0.875rem 1rem;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #1d5c7a;
            box-shadow: 0 0 0 3px rgba(29, 92, 122, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(22, 143, 112, 0.35);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e5e7eb;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: #1d5c7a;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider span {
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .btn-social {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            color: #374151;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-social:hover {
            border-color: #1d5c7a;
            background: #f9fafb;
        }

        .illustration {
            width: 100%;
            max-width: 300px;
            margin: 2rem auto;
        }

        .form-check-input:checked {
            background-color: #1d5c7a;
            border-color: #1d5c7a;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="row g-0">
            <div class="col-lg-5 login-left">
                <div class="login-left-content">
                    <h2><i class="fas fa-graduation-cap me-2"></i>EduLearn</h2>
                    <p class="mb-4">Chào mừng trở lại! Đăng nhập để tiếp tục hành trình học tập của bạn.</p>

                    <div class="illustration">
                        <svg viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="250" cy="200" r="150" fill="rgba(255,255,255,0.1)" />
                            <rect x="150" y="150" width="200" height="150" rx="10" fill="rgba(255,255,255,0.2)" />
                            <circle cx="250" cy="120" r="40" fill="rgba(255,255,255,0.3)" />
                            <path d="M 200 250 Q 250 280 300 250" stroke="rgba(255,255,255,0.4)" stroke-width="4" fill="none" />
                        </svg>
                    </div>

                    <div class="mt-4">
                        <p class="small mb-2"><i class="fas fa-check-circle me-2"></i>Truy cập không giới hạn khóa học</p>
                        <p class="small mb-2"><i class="fas fa-check-circle me-2"></i>Học mọi lúc, mọi nơi</p>
                        <p class="small mb-2"><i class="fas fa-check-circle me-2"></i>Chứng chỉ hoàn thành</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 login-right">
                <div class="mb-4">
                    <h3 class="mb-2">Đăng Nhập</h3>
                    <p class="text-muted">Nhập thông tin để truy cập tài khoản</p>
                </div>

                <?php echo showAlert(); ?>

                <form method="POST" action="../handle/login_process.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="example@email.com" value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật Khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>
                        <a href="#" style="color: #1d5c7a; font-weight: 600; text-decoration: none;">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
                    </button>
                </form>

                <div class="divider">
                    <span>Hoặc đăng nhập với</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <button class="btn-social" type="button">
                            <i class="fab fa-google" style="color: #DB4437;"></i> Google
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn-social" type="button">
                            <i class="fab fa-facebook" style="color: #4267B2;"></i> Facebook
                        </button>
                    </div>
                </div>

                <p class="text-center mb-0">
                    Chưa có tài khoản? <a href="register.php" style="color: #1d5c7a; font-weight: 600;">Đăng ký ngay</a>
                </p>

                <div class="alert alert-info mt-4" style="border-radius: 10px;">
                    <strong><i class="fas fa-info-circle me-2"></i>Tài khoản Demo:</strong><br>
                    <small>
                        <strong>Học viên:</strong> student1@gmail.com / password123<br>
                        <strong>Giáo viên:</strong> teacher1@edulearn.vn / password123<br>
                        <strong>Admin:</strong> admin@course.com / password123
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>