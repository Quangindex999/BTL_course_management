<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

requireAdmin();

$is_edit = isset($_GET['id']);
$is_view = isset($_GET['view']);
$user = null;

if ($is_edit) {
    $user_id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        setAlert('Không tìm thấy người dùng', 'error');
        redirect(SITE_URL . '/views/admin/manage_users.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? ($is_view ? 'Chi tiết' : 'Sửa') : 'Thêm'; ?> Người dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f3f4f6;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-row {
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
        }

        .teacher-fields {
            display: none;
        }

        .teacher-fields.show {
            display: block;
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
            <li><a href="manage_users.php"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
            <li><a href="create_users.php" class="active"><i class="fas fa-user-plus"></i><?php echo $is_edit ? 'Sửa' : 'Thêm'; ?> Người dùng</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas <?php echo $is_edit ? ($is_view ? 'fa-eye' : 'fa-user-edit') : 'fa-user-plus'; ?> me-2"></i>
                    <?php echo $is_edit ? ($is_view ? 'Chi tiết' : 'Sửa') : 'Thêm'; ?> Người dùng
                </h2>
                <?php if ($is_edit): ?>
                    <p class="text-muted mb-0">ID: <?php echo $user['user_id']; ?> | Email: <?php echo escape($user['email']); ?></p>
                <?php endif; ?>
            </div>
            <a href="manage_users.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php /* inline alert removed in favor of top banner */ ?>

        <?php if ($is_view): ?>
            <!-- View Mode -->
            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?php echo getAvatarUrl($user['avatar'], $user['full_name'], 200); ?>"
                                class="avatar-preview mb-3" alt="">
                            <h4 class="mb-1"><?php echo escape($user['full_name']); ?></h4>
                            <p class="text-muted mb-3">
                                <?php
                                $roles = ['student' => 'Học viên', 'teacher' => 'Giáo viên', 'admin' => 'Quản trị viên'];
                                echo $roles[$user['role']];
                                ?>
                            </p>
                            <?php if ($user['role'] === 'teacher' && $user['rating']): ?>
                                <div class="mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <strong><?php echo $user['rating']; ?></strong> / 5.0
                                </div>
                            <?php endif; ?>
                            <a href="edit_users.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Email:</div>
                                    <div class="col-md-8"><?php echo escape($user['email']); ?></div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Số điện thoại:</div>
                                    <div class="col-md-8"><?php echo escape($user['phone']) ?: '-'; ?></div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Địa chỉ:</div>
                                    <div class="col-md-8"><?php echo escape($user['address']) ?: '-'; ?></div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Trạng thái:</div>
                                    <div class="col-md-8"><?php echo getStatusBadge($user['status']); ?></div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Ngày tạo:</div>
                                    <div class="col-md-8"><?php echo formatDate($user['created_at']); ?></div>
                                </div>
                            </div>

                            <?php if ($user['role'] === 'teacher'): ?>
                                <div class="section-title mt-4"><i class="fas fa-chalkboard-teacher me-2"></i>Thông tin giáo viên</div>
                                <?php if ($user['bio']): ?>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-md-4 info-label">Giới thiệu:</div>
                                            <div class="col-md-8"><?php echo nl2br(escape($user['bio'])); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-md-4 info-label">Chuyên môn:</div>
                                        <div class="col-md-8"><?php echo escape($user['specialization']) ?: '-'; ?></div>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-md-4 info-label">Kinh nghiệm:</div>
                                        <div class="col-md-8"><?php echo $user['experience_years'] ?: 0; ?> năm</div>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-md-4 info-label">Học vấn:</div>
                                        <div class="col-md-8"><?php echo escape($user['education']) ?: '-'; ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Edit/Create Mode -->
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="../../handle/user_process.php" enctype="multipart/form-data" id="userForm">
                        <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                        <?php if ($is_edit): ?>
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <?php endif; ?>

                        <div class="row g-4">
                            <!-- Left Column -->
                            <div class="col-md-8">
                                <div class="section-title"><i class="fas fa-user me-2"></i>Thông tin cơ bản</div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="full_name"
                                            value="<?php echo escape($user['full_name'] ?? ''); ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email"
                                            value="<?php echo escape($user['email'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="tel" class="form-control" name="phone"
                                            value="<?php echo escape($user['phone'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                        <select class="form-select" name="role" id="roleSelect" required>
                                            <option value="student" <?php echo isset($user) && $user['role'] == 'student' ? 'selected' : ''; ?>>Học viên</option>
                                            <option value="teacher" <?php echo isset($user) && $user['role'] == 'teacher' ? 'selected' : ''; ?>>Giáo viên</option>
                                            <option value="admin" <?php echo isset($user) && $user['role'] == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                        </select>
                                    </div>
                                </div>

                                <?php if (!$is_edit): ?>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password" required minlength="6">
                                            <small class="text-muted">Tối thiểu 6 ký tự</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="confirm_password" required>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo escape($user['address'] ?? ''); ?></textarea>
                                </div>

                                <!-- Teacher Fields -->
                                <div class="teacher-fields <?php echo isset($user) && $user['role'] === 'teacher' ? 'show' : ''; ?>" id="teacherFields">
                                    <div class="section-title mt-4"><i class="fas fa-chalkboard-teacher me-2"></i>Thông tin giáo viên</div>

                                    <div class="mb-3">
                                        <label class="form-label">Giới thiệu</label>
                                        <textarea class="form-control" name="bio" rows="4" placeholder="Mô tả về bản thân, kinh nghiệm và đam mê..."><?php echo escape($user['bio'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Chuyên môn</label>
                                        <input type="text" class="form-control" name="specialization"
                                            placeholder="VD: Web Development, PHP, JavaScript"
                                            value="<?php echo escape($user['specialization'] ?? ''); ?>">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Số năm kinh nghiệm</label>
                                            <input type="number" class="form-control" name="experience_years"
                                                value="<?php echo $user['experience_years'] ?? 0; ?>" min="0">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Học vấn</label>
                                            <input type="text" class="form-control" name="education"
                                                placeholder="VD: Thạc sĩ CNTT - ĐH Bách Khoa"
                                                value="<?php echo escape($user['education'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">LinkedIn</label>
                                            <input type="url" class="form-control" name="linkedin"
                                                placeholder="https://linkedin.com/in/..."
                                                value="<?php echo escape($user['linkedin'] ?? ''); ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Website</label>
                                            <input type="url" class="form-control" name="website"
                                                placeholder="https://..."
                                                value="<?php echo escape($user['website'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="section-title"><i class="fas fa-image me-2"></i>Ảnh đại diện</div>

                                <div class="text-center mb-3">
                                    <img src="<?php echo getAvatarUrl($user['avatar'] ?? '', $user['full_name'] ?? 'User', 200); ?>"
                                        class="avatar-preview mb-3" id="avatarPreview" alt="">
                                    <input type="file" class="form-control" name="avatar" accept="image/*"
                                        onchange="previewAvatar(this)">
                                    <small class="text-muted">JPG, PNG tối đa 2MB</small>
                                </div>

                                <hr class="my-4">

                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?php echo !isset($user) || $user['status'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                        <option value="inactive" <?php echo isset($user) && $user['status'] == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                                    </select>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small><strong>Lưu ý:</strong> Các trường có dấu (<span class="text-danger">*</span>) là bắt buộc</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="manage_users.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo $is_edit ? 'Cập nhật' : 'Tạo người dùng'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
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

        // Preview avatar
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Toggle teacher fields
        document.getElementById('roleSelect')?.addEventListener('change', function() {
            const teacherFields = document.getElementById('teacherFields');
            if (this.value === 'teacher') {
                teacherFields.classList.add('show');
            } else {
                teacherFields.classList.remove('show');
            }
        });
    </script>
</body>

</html>