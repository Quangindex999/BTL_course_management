<?php
// File: views/admin/create_courses.php (hoặc edit_courses.php nếu có ?id)
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/course_functions.php';

requireAdmin();

$is_edit = isset($_GET['id']);
$course = null;

if ($is_edit) {
    $course_id = intval($_GET['id']);
    $result = getCourseById($course_id);
    if ($result['success']) {
        $course = $result['course'];
    } else {
        setAlert('Không tìm thấy khóa học', 'error');
        redirect(SITE_URL . '/views/admin/manage_courses.php');
    }
}

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Sửa' : 'Thêm'; ?> Khóa học - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
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
            color: rgba(255, 255, 255, 0.8);
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

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .preview-image {
            max-width: 200px;
            border-radius: 10px;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <?php $__alert = getAlert(); ?>
    <?php if ($__alert): ?>
        <?php
        $__type = match ($__alert['type']) {
            'error', 'danger' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success'
        };
        $__icon = match ($__type) {
            'danger' => 'fa-circle-xmark',
            'warning' => 'fa-triangle-exclamation',
            'info' => 'fa-circle-info',
            default => 'fa-circle-check'
        };
        ?>
        <div class="position-fixed top-0 start-0 end-0" style="z-index: 2000;">
            <div class="alert alert-<?php echo $__type; ?> rounded-0 mb-0 d-flex align-items-center justify-content-between">
                <div><i class="fas <?php echo $__icon; ?> me-2"></i><?php echo escape($__alert['message']); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <div id="top-alert-spacer" style="height: 56px;"></div>
    <?php endif; ?>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-graduation-cap me-2"></i>EduLearn Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i>Quản lý Khóa học</a></li>
            <li><a href="create_courses.php" class="active"><i class="fas fa-plus-circle"></i><?php echo $is_edit ? 'Sửa' : 'Thêm'; ?> Khóa học</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas <?php echo $is_edit ? 'fa-edit' : 'fa-plus-circle'; ?> me-2"></i><?php echo $is_edit ? 'Sửa' : 'Thêm'; ?> Khóa học</h2>
            <a href="manage_courses.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php /* inline alert removed in favor of top banner */ ?>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="../../handle/course_process.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                    <?php endif; ?>

                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Tên khóa học <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="course_name"
                                    value="<?php echo escape($course['course_name'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="5" required><?php echo escape($course['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['category_id']; ?>"
                                                <?php echo isset($course) && $course['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo escape($cat['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giảng viên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="instructor_name"
                                        value="<?php echo escape($course['instructor_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Thời lượng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="duration"
                                        placeholder="VD: 12 tuần" value="<?php echo escape($course['duration'] ?? ''); ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Trình độ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="level" required>
                                        <option value="Beginner" <?php echo isset($course) && $course['level'] == 'Beginner' ? 'selected' : ''; ?>>Cơ bản</option>
                                        <option value="Intermediate" <?php echo isset($course) && $course['level'] == 'Intermediate' ? 'selected' : ''; ?>>Trung cấp</option>
                                        <option value="Advanced" <?php echo isset($course) && $course['level'] == 'Advanced' ? 'selected' : ''; ?>>Nâng cao</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price"
                                        value="<?php echo $course['price'] ?? 0; ?>" required min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" name="start_date"
                                        value="<?php echo $course['start_date'] ?? ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc</label>
                                    <input type="date" class="form-control" name="end_date"
                                        value="<?php echo $course['end_date'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số lượng học viên tối đa</label>
                                <input type="number" class="form-control" name="max_students"
                                    value="<?php echo $course['max_students'] ?? 30; ?>" min="1">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" class="form-control" name="thumbnail" accept="image/*"
                                    onchange="previewImage(this)">
                                <small class="text-muted">Khuyến nghị: 800x500px, JPG/PNG, tối đa 5MB</small>

                                <?php if ($is_edit && $course['thumbnail']): ?>
                                    <img src="<?php echo getImageUrl($course['thumbnail']); ?>"
                                        class="preview-image img-fluid" id="imagePreview">
                                <?php else: ?>
                                    <img src="" class="preview-image img-fluid d-none" id="imagePreview">
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo !isset($course) || $course['status'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="inactive" <?php echo isset($course) && $course['status'] == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                                </select>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Các trường có dấu (<span class="text-danger">*</span>) là bắt buộc
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="manage_courses.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo $is_edit ? 'Cập nhật' : 'Tạo khóa học'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto dismiss top alert after a few seconds
        document.addEventListener('DOMContentLoaded', function() {
            const banner = document.querySelector('.position-fixed.top-0.start-0.end-0 .alert');
            if (banner) {
                setTimeout(function() {
                    try {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(banner);
                        bsAlert.close();
                    } catch (e) {
                        banner.remove();
                    }
                    const spacer = document.getElementById('top-alert-spacer');
                    if (spacer) spacer.remove();
                }, 3500);
            }
        });

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>

<?php
// Note: Copy file này thành edit_courses.php (giống hệt nhau, chỉ khác logic xử lý ở đầu file)
?>