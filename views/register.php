<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            min-height: 120vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 2rem;
        }

        .register-left {
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-left h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .register-left p {
            opacity: 0.9;
            line-height: 1.6;
        }

        .register-right {
            padding: 3rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #1d5c7a;
            box-shadow: 0 0 0 3px rgba(29, 92, 122, 0.15);
        }

        .btn-register {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-register:hover {
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

        .icon-feature {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="row g-0">
            <div class="col-lg-5 register-left">
                <div>
                    <h2><i class="fas fa-graduation-cap me-2"></i>EduLearn</h2>
                    <p class="mb-4">Tham gia cùng hàng nghìn học viên đang học tập trên nền tảng của chúng tôi</p>

                    <div class="mb-4">
                        <div class="icon-feature">
                            <i class="fas fa-book"></i>
                        </div>
                        <h6>Hơn 100+ Khóa Học</h6>
                        <p class="small opacity-75">Đa dạng lĩnh vực từ cơ bản đến nâng cao</p>
                    </div>

                    <div class="mb-4">
                        <div class="icon-feature">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h6>Chứng Chỉ Uy Tín</h6>
                        <p class="small opacity-75">Được công nhận bởi các doanh nghiệp</p>
                    </div>

                    <div>
                        <div class="icon-feature">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6>Giảng Viên Chuyên Nghiệp</h6>
                        <p class="small opacity-75">Đội ngũ giảng viên giàu kinh nghiệm</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 register-right">
                <h3 class="mb-4 text-center">Đăng Ký Tài Khoản</h3>

                <?php echo showAlert(); ?>

                <form method="POST" action="../handle/register_process.php">
                    <div class="mb-3">
                        <label class="form-label">Họ và Tên</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="full_name" placeholder="Nguyễn Văn A" value="<?php echo escape($_POST['full_name'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="example@email.com" value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số Điện Thoại</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control" name="phone" placeholder="0123456789" value="<?php echo escape($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật Khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Tối thiểu 6 ký tự" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Xác Nhận Mật Khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Đăng Ký
                    </button>

                    <p class="text-center mt-3 mb-0">
                        Đã có tài khoản? <a href="login.php" style="color: #1d5c7a; font-weight: 600;">Đăng nhập ngay</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>