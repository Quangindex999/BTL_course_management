<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/course_functions.php';
require_once '../../functions/enrollments_functions.php';

requireAdmin();

// Lấy thống kê
$course_stats = getCourseStats();
$enrollment_stats = getEnrollmentStats();

// Lấy đăng ký gần đây
$recent_enrollments = getAllEnrollments(['page' => 1])['enrollments'];
$recent_enrollments = array_slice($recent_enrollments, 0, 10);

// Lấy khóa học phổ biến
$popular_courses = getPopularCourses(5);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/admin.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <i class="fas fa-graduation-cap me-2"></i>EduLearn Admin
        </div>
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-line"></i>Báo cáo</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i>Quản lý Khóa học</a></li>
            <li><a href="create_courses.php"><i class="fas fa-plus-circle"></i>Thêm Khóa học</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
            <li><a href="create_users.php"><i class="fas fa-user-plus"></i>Thêm Người dùng</a></li>
            <li><a href="manage_enrollments.php"><i class="fas fa-file-invoice"></i>Quản lý Đăng ký</a></li>
            <li>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 1.5rem;">
            </li>
            <li><a href="../../index.php"><i class="fas fa-home"></i>Về Trang chủ</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <div class="admin-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge text-bg-light text-dark border">Admin Panel</span>
                    <span class="admin-soft-note small">Cập nhật theo phong cách đồng bộ</span>
                </div>
                <h4 class="mb-1 admin-section-title">Dashboard</h4>
                <small class="admin-soft-note">Xin chào, <?php echo escape($_SESSION['full_name']); ?>!</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="px-3 py-2 rounded-4 bg-white border">
                    <i class="far fa-calendar me-2 text-muted"></i><?php echo date('d/m/Y'); ?>
                </div>
                <div class="px-3 py-2 rounded-4 bg-white border">
                    <i class="far fa-clock me-2 text-muted"></i><?php echo date('H:i'); ?>
                </div>
            </div>
        </div>

        <?php showAlert(); ?>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background: rgba(22,57,85,0.12); color: var(--admin-primary);"><i class="fas fa-book-open"></i></div>
                    <div class="admin-stat-value"><?php echo $course_stats['total_courses'] ?? 0; ?></div>
                    <div class="admin-stat-label">Tổng Khóa học</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background: rgba(15,118,110,0.12); color: var(--admin-secondary);"><i class="fas fa-users"></i></div>
                    <div class="admin-stat-value"><?php echo $course_stats['total_students'] ?? 0; ?></div>
                    <div class="admin-stat-label">Tổng Học viên</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background: rgba(249,115,22,0.12); color: var(--admin-accent);"><i class="fas fa-clock"></i></div>
                    <div class="admin-stat-value"><?php echo $enrollment_stats['pending'] ?? 0; ?></div>
                    <div class="admin-stat-label">Chờ Duyệt</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background: rgba(239,68,68,0.12); color: #ef4444;"><i class="fas fa-dollar-sign"></i></div>
                    <div class="admin-stat-value"><?php echo number_format(($course_stats['total_revenue'] ?? 0) / 1000000, 1); ?>M</div>
                    <div class="admin-stat-label">Doanh thu</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card admin-card">
                    <div class="card-header py-3">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Đăng ký gần đây</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="admin-table-wrap table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Học viên</th>
                                        <th>Khóa học</th>
                                        <th>Giá</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đăng ký</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_enrollments as $enroll): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo escape($enroll['full_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo escape($enroll['email']); ?></small>
                                            </td>
                                            <td><?php echo escape($enroll['course_name']); ?></td>
                                            <td><strong class="text-primary"><?php echo formatCurrency($enroll['price']); ?></strong></td>
                                            <td><?php echo getStatusBadge($enroll['status'], 'enrollment'); ?></td>
                                            <td><?php echo formatDate($enroll['enrollment_date']); ?></td>
                                            <td>
                                                <?php if ($enroll['status'] === 'pending'): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-success" onclick="approveEnrollment(<?php echo $enroll['enrollment_id']; ?>)"><i class="fas fa-check"></i></button>
                                                        <button class="btn btn-danger" onclick="rejectEnrollment(<?php echo $enroll['enrollment_id']; ?>)"><i class="fas fa-times"></i></button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card admin-card">
                    <div class="card-header py-3">
                        <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Khóa học phổ biến</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($popular_courses as $course): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://via.placeholder.com/80'); ?>" alt="" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo escape($course['course_name']); ?></h6>
                                    <small class="text-muted"><i class="fas fa-users me-1"></i><?php echo $course['enrolled_count']; ?> học viên</small>
                                </div>
                                <div class="text-end"><div class="text-primary fw-bold"><?php echo formatCurrency($course['price']); ?></div></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card admin-card mt-4">
                    <div class="card-header py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Thống kê nhanh</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3"><span>Đã duyệt</span><strong class="text-success"><?php echo $enrollment_stats['approved'] ?? 0; ?></strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Chờ duyệt</span><strong class="text-warning"><?php echo $enrollment_stats['pending'] ?? 0; ?></strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Từ chối</span><strong class="text-danger"><?php echo $enrollment_stats['rejected'] ?? 0; ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Hoàn thành</span><strong class="text-info"><?php echo $enrollment_stats['completed'] ?? 0; ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function approveEnrollment(id) {
            if (confirm('Xác nhận duyệt đăng ký này?')) {
                window.location.href = '../../handle/enroll_process.php?action=approve&id=' + id;
            }
        }

        function rejectEnrollment(id) {
            if (confirm('Xác nhận từ chối đăng ký này?')) {
                const reason = prompt('Lý do từ chối (tùy chọn):');
                window.location.href = '../../handle/enroll_process.php?action=reject&id=' + id + '&reason=' + encodeURIComponent(reason || '');
            }
        }
    </script>
</body>

</html>
