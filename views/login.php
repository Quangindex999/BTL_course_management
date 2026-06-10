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
    <title>Đăng nhập - EduLearn</title>
    <meta name="description" content="Đăng nhập vào EduLearn để tiếp tục hành trình học tập và quản lý khóa học.">
    <meta name="theme-color" content="#14324a">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #eef3f8;
            --surface: rgba(255, 255, 255, 0.92);
            --primary: #14324a;
            --accent: #0f766e;
            --text: #162231;
            --muted: #66758a;
            --border: rgba(20, 50, 74, 0.1);
            --shadow: 0 30px 80px rgba(16, 24, 40, 0.18);
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.16), transparent 30%),
                radial-gradient(circle at bottom right, rgba(20, 50, 74, 0.14), transparent 24%),
                linear-gradient(135deg, #f8fbfd 0%, var(--bg) 55%, #e7edf4 100%);
        }

        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .auth-card {
            max-width: 1080px;
            width: 100%;
            border-radius: 32px;
            overflow: hidden;
            background: var(--surface);
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(18px);
        }

        .auth-visual {
            position: relative;
            color: #fff;
            min-height: 100%;
            padding: 3rem;
            background:
                linear-gradient(160deg, rgba(20, 50, 74, 0.95), rgba(15, 118, 110, 0.88)),
                url('https://images.unsplash.com/photo-1513258496099-48168024aec0?w=1200&h=1600&fit=crop') center/cover;
        }

        .auth-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.12) 1px, transparent 0);
            background-size: 28px 28px;
            opacity: 0.22;
        }

        .auth-visual > * {
            position: relative;
            z-index: 1;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .auth-visual h2 {
            font-size: clamp(2.2rem, 4vw, 3.8rem);
            font-weight: 900;
            letter-spacing: -0.06em;
            line-height: 0.96;
            max-width: 11ch;
            margin-bottom: 1rem;
        }

        .auth-visual p {
            max-width: 32rem;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.8;
        }

        .auth-bullets {
            margin-top: 2rem;
            display: grid;
            gap: 0.85rem;
        }

        .auth-bullet {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .auth-form {
            padding: clamp(2rem, 4vw, 3.5rem);
        }

        .auth-form h3 {
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            margin-bottom: 0.5rem;
        }

        .auth-form .subtitle {
            color: var(--muted);
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 700;
            color: #39485a;
            margin-bottom: 0.5rem;
        }

        .input-group-text,
        .form-control {
            border-color: var(--border);
        }

        .input-group-text {
            background: #f7fafc;
            border-right: 0;
            color: var(--primary);
            border-radius: 14px 0 0 14px;
        }

        .form-control {
            padding: 0.9rem 1rem;
            border-radius: 0 14px 14px 0;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: rgba(20, 50, 74, 0.28);
            box-shadow: 0 0 0 0.2rem rgba(20, 50, 74, 0.12);
        }

        .btn-login {
            width: 100%;
            padding: 0.95rem 1rem;
            border: 0;
            border-radius: 16px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(15, 118, 110, 0.26);
        }

        .btn-social {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            color: #314154;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .btn-social:hover {
            transform: translateY(-1px);
            border-color: rgba(20, 50, 74, 0.18);
            background: #f8fbfd;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--muted);
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid var(--border);
        }

        .helper-link {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .helper-link:hover {
            text-decoration: underline;
        }

        .info-box {
            border-radius: 16px;
            border: 1px solid rgba(20, 50, 74, 0.1);
            background: linear-gradient(180deg, rgba(20, 50, 74, 0.06), rgba(15, 118, 110, 0.05));
        }

        @media (max-width: 991.98px) {
            .auth-visual {
                min-height: auto;
                padding: 2.25rem;
            }
        }
    </style>
</head>

<body>
    <main class="auth-shell">
        <div class="container">
            <div class="auth-card row g-0 mx-auto">
                <div class="col-lg-5 auth-visual">
                    <div class="brand-mark"><i class="fas fa-graduation-cap"></i>EduLearn</div>
                    <h2>Chào mừng trở lại với hành trình học tập</h2>
                    <p>Đăng nhập để tiếp tục các khóa học, theo dõi tiến độ và quản lý tài khoản trong một giao diện gọn gàng, hiện đại hơn.</p>

                    <div class="auth-bullets">
                        <div class="auth-bullet"><i class="fas fa-check-circle"></i><span>Truy cập nhanh vào các khóa học của bạn</span></div>
                        <div class="auth-bullet"><i class="fas fa-check-circle"></i><span>Theo dõi tiến độ học tập rõ ràng</span></div>
                        <div class="auth-bullet"><i class="fas fa-check-circle"></i><span>Giao diện tập trung và ít nhiễu</span></div>
                    </div>
                </div>

                <div class="col-lg-7 auth-form">
                    <div class="mb-4">
                        <h3>Đăng nhập</h3>
                        <p class="subtitle mb-0">Nhập thông tin để truy cập tài khoản của bạn</p>
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
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                            <a href="#" class="helper-link">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </button>
                    </form>

                    <div class="divider"><span>Hoặc đăng nhập với</span></div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <button class="btn-social" type="button"><i class="fab fa-google" style="color: #DB4437;"></i>Google</button>
                        </div>
                        <div class="col-6">
                            <button class="btn-social" type="button"><i class="fab fa-facebook" style="color: #4267B2;"></i>Facebook</button>
                        </div>
                    </div>

                    <p class="text-center mb-0">
                        Chưa có tài khoản? <a href="register.php" class="helper-link">Đăng ký ngay</a>
                    </p>

                    <div class="alert info-box mt-4 mb-0">
                        <strong><i class="fas fa-info-circle me-2"></i>Tài khoản demo</strong><br>
                        <small>
                            <strong>Học viên:</strong> student1@gmail.com / password123<br>
                            <strong>Giáo viên:</strong> teacher1@edulearn.vn / password123<br>
                            <strong>Admin:</strong> admin@course.com / password123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>