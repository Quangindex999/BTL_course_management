<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

requireTeacher();

$user_id = $_SESSION['user_id'];

// Lấy thông tin giáo viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    setAlert('Không tìm thấy thông tin người dùng', 'error');
    redirect(SITE_URL . '/views/teacher/profile.php');
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cập nhật thông tin cơ bản
    if (isset($_POST['update_profile'])) {
        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'specialization' => $_POST['specialization'] ?? '',
            'experience_years' => isset($_POST['experience_years']) ? intval($_POST['experience_years']) : 0,
            'education' => $_POST['education'] ?? '',
            'linkedin' => $_POST['linkedin'] ?? '',
            'website' => $_POST['website'] ?? ''
        ];

        try {
            $updates = [];
            $params = [];

            $allowed_fields = ['full_name', 'phone', 'address', 'bio', 'specialization', 'experience_years', 'education', 'linkedin', 'website'];

            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (!empty($updates)) {
                $params[] = $user_id;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                // Cập nhật session
                $_SESSION['full_name'] = $data['full_name'];

                setAlert('Cập nhật thông tin thành công!', 'success');
            } else {
                setAlert('Không có dữ liệu để cập nhật', 'error');
            }
        } catch (PDOException $e) {
            setAlert('Lỗi cập nhật: ' . $e->getMessage(), 'error');
        }

        redirect(SITE_URL . '/views/teacher/profile.php');
    }

    // Cập nhật avatar
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar'])) {
        $result = updateAvatar($user_id, $_FILES['avatar']);

        if ($result['success']) {
            setAlert($result['message'], 'success');
        } else {
            setAlert($result['message'], 'error');
        }

        redirect(SITE_URL . '/views/teacher/profile.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa hồ sơ - Giáo viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 2rem 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid white;
        }

        .sidebar-menu i {
            width: 24px;
            margin-right: 0.75rem;
        }

        .main-content {
            margin-left: 260px;
            padding: 2rem;
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
            color: #f59e0b;
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
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-chalkboard-teacher me-2"></i>Giáo viên</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="my_course.php"><i class="fas fa-book"></i>Khóa học của tôi</a></li>
            <li><a href="students.php"><i class="fas fa-users"></i>Học viên</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user"></i>Hồ sơ của tôi</a></li>
            <li>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 1.5rem;">
            </li>
            <li><a href="../../index.php"><i class="fas fa-home"></i>Về Trang chủ</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php showAlert(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit me-2"></i>Chỉnh sửa hồ sơ</h2>
            <a href="profile.php" class="btn btn-outline-secondary">
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
                                        value="<?php echo escape($teacher['full_name']); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control"
                                        value="<?php echo escape($teacher['email']); ?>" disabled>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <input type="tel" class="form-control" name="phone"
                                        value="<?php echo escape($teacher['phone'] ?? ''); ?>"
                                        placeholder="Nhập số điện thoại">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address"
                                        value="<?php echo escape($teacher['address'] ?? ''); ?>"
                                        placeholder="Nhập địa chỉ">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Giới thiệu</label>
                                <textarea class="form-control" name="bio" rows="4"
                                    placeholder="Viết giới thiệu về bản thân..."><?php echo escape($teacher['bio'] ?? ''); ?></textarea>
                            </div>

                            <div class="section-title mt-4">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Thông tin chuyên môn
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chuyên môn</label>
                                    <input type="text" class="form-control" name="specialization"
                                        value="<?php echo escape($teacher['specialization'] ?? ''); ?>"
                                        placeholder="VD: Web Development, PHP, JavaScript">
                                    <small class="text-muted">Phân cách bằng dấu phẩy</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số năm kinh nghiệm</label>
                                    <input type="number" class="form-control" name="experience_years"
                                        value="<?php echo $teacher['experience_years'] ?? 0; ?>" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Học vấn</label>
                                <input type="text" class="form-control" name="education"
                                    value="<?php echo escape($teacher['education'] ?? ''); ?>"
                                    placeholder="VD: Thạc sĩ Công nghệ thông tin - ĐH Bách Khoa">
                            </div>

                            <div class="section-title mt-4">
                                <i class="fas fa-link me-2"></i>Liên kết mạng xã hội
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">LinkedIn</label>
                                    <input type="url" class="form-control" name="linkedin"
                                        value="<?php echo escape($teacher['linkedin'] ?? ''); ?>"
                                        placeholder="https://linkedin.com/in/...">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="url" class="form-control" name="website"
                                        value="<?php echo escape($teacher['website'] ?? ''); ?>"
                                        placeholder="https://...">
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                                <a href="profile.php" class="btn btn-outline-secondary">
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
                            <img src="<?php echo getAvatarUrl($teacher['avatar'], $teacher['full_name'], 200); ?>"
                                class="avatar-preview mb-3" id="avatarPreview" alt="Avatar">

                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="update_avatar" value="1">
                                <input type="file" class="form-control mb-2" name="avatar" accept="image/*"
                                    onchange="previewAvatar(this)" required>
                                <small class="text-muted d-block mb-3">JPG, PNG tối đa 2MB</small>
                                <button type="submit" class="btn btn-warning w-100">
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