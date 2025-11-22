<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

requireStudent();

$user_id = $_SESSION['user_id'];

// Lấy thông tin học viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

if (!$student) {
    setAlert('Không tìm thấy thông tin người dùng', 'error');
    redirect(SITE_URL . '/views/student/menu_student.php');
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cập nhật thông tin cơ bản
    if (isset($_POST['update_profile'])) {
        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        $result = updateProfile($user_id, $data);

        if ($result['success']) {
            setAlert($result['message'], 'success');
        } else {
            setAlert($result['message'], 'error');
        }

        redirect(SITE_URL . '/views/student/menu_student.php');
    }

    // Cập nhật avatar
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar'])) {
        $result = updateAvatar($user_id, $_FILES['avatar']);

        if ($result['success']) {
            setAlert($result['message'], 'success');
        } else {
            setAlert($result['message'], 'error');
        }

        redirect(SITE_URL . '/views/student/menu_student.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa hồ sơ - Học viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .main-content {
            padding: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1f2937;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 0.5rem;
            color: #2563eb;
        }

        .avatar-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #e5e7eb;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item"><a class="nav-link" href="my_courses.php">Khóa học của tôi</a></li>
                    <li class="nav-item"><a class="nav-link" href="course_detail.php">Khám phá khóa học</a></li>
                    <li class="nav-item"><a class="nav-link active" href="menu_student.php">Hồ sơ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../../handle/logout_process.php">Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <?php showAlert(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit me-2"></i>Chỉnh sửa hồ sơ</h2>
            <a href="menu_student.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <div class="row g-4">
            <!-- Thông tin cơ bản -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="section-title">
                            <i class="fas fa-user me-2"></i>Thông tin cơ bản
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="update_profile" value="1">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name"
                                        value="<?php echo escape($student['full_name']); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control"
                                        value="<?php echo escape($student['email']); ?>" disabled>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <input type="tel" class="form-control" name="phone"
                                        value="<?php echo escape($student['phone'] ?? ''); ?>"
                                        placeholder="Nhập số điện thoại">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address"
                                        value="<?php echo escape($student['address'] ?? ''); ?>"
                                        placeholder="Nhập địa chỉ">
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                                <a href="menu_student.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Avatar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="section-title">
                            <i class="fas fa-image me-2"></i>Ảnh đại diện
                        </div>

                        <div class="text-center mb-3">
                            <img src="<?php echo getAvatarUrl($student['avatar'], $student['full_name'], 200); ?>"
                                class="avatar-preview mb-3" id="avatarPreview" alt="Avatar">

                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="update_avatar" value="1">
                                <input type="file" class="form-control mb-2" name="avatar" accept="image/*"
                                    onchange="previewAvatar(this)" required>
                                <small class="text-muted d-block mb-3">JPG, PNG tối đa 2MB</small>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-upload me-2"></i>Cập nhật ảnh
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>