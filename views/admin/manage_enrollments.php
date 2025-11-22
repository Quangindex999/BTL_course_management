<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/enrollments_functions.php';

requireAdmin();

// Lấy tham số
$status = $_GET['status'] ?? '';
$course_id = $_GET['course_id'] ?? '';
$payment_status = $_GET['payment_status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Lấy danh sách đăng ký
$result = getAllEnrollments([
    'status' => $status,
    'course_id' => $course_id,
    'payment_status' => $payment_status,
    'page' => $page
]);

$enrollments = $result['enrollments'] ?? [];
$pagination = $result['pagination'] ?? [];

// Lấy danh sách khóa học cho filter
$courses_stmt = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name");
$courses_list = $courses_stmt->fetchAll();

// Thống kê
$enrollment_stats = getEnrollmentStats();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đăng ký - Admin</title>
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

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .table thead {
            background: #f9fafb;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-graduation-cap me-2"></i>EduLearn Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i>Quản lý Khóa học</a></li>
            <li><a href="create_courses.php"><i class="fas fa-plus-circle"></i>Thêm Khóa học</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
            <li><a href="create_users.php"><i class="fas fa-user-plus"></i>Thêm Người dùng</a></li>
            <li><a href="manage_enrollments.php" class="active"><i class="fas fa-file-invoice"></i>Quản lý Đăng ký</a></li>
            <li>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 1.5rem;">
            </li>
            <li><a href="../../index.php"><i class="fas fa-home"></i>Về Trang chủ</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-file-invoice me-2"></i>Quản lý Đăng ký</h2>
                <p class="text-muted mb-0">Duyệt và quản lý đăng ký khóa học</p>
            </div>
        </div>

        <?php showAlert(); ?>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-list"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $enrollment_stats['total'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Tổng đăng ký</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $enrollment_stats['pending'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Chờ duyệt</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $enrollment_stats['approved'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Đã duyệt</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $enrollment_stats['rejected'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Từ chối</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Khóa học</label>
                    <select class="form-select" name="course_id">
                        <option value="">Tất cả khóa học</option>
                        <?php foreach ($courses_list as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php echo $course_id == $course['course_id'] ? 'selected' : ''; ?>>
                                <?php echo escape($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Thanh toán</label>
                    <select class="form-select" name="payment_status">
                        <option value="">Tất cả</option>
                        <option value="unpaid" <?php echo $payment_status === 'unpaid' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                        <option value="paid" <?php echo $payment_status === 'paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
                        <option value="refunded" <?php echo $payment_status === 'refunded' ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                        <a href="manage_enrollments.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Enrollments Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Danh sách đăng ký</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Học viên</th>
                                <th>Khóa học</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enrollments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                        Không có đăng ký nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($enrollments as $enroll): ?>
                                    <tr>
                                        <td>#<?php echo $enroll['enrollment_id']; ?></td>
                                        <td>
                                            <strong><?php echo escape($enroll['full_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo escape($enroll['email']); ?></small>
                                        </td>
                                        <td><?php echo escape($enroll['course_name']); ?></td>
                                        <td><strong class="text-primary"><?php echo formatCurrency($enroll['price']); ?></strong></td>
                                        <td><?php echo getStatusBadge($enroll['status'], 'enrollment'); ?></td>
                                        <td><?php echo getStatusBadge($enroll['payment_status'], 'payment'); ?></td>
                                        <td><?php echo formatDate($enroll['enrollment_date']); ?></td>
                                        <td>
                                            <?php if ($enroll['status'] === 'pending'): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-success" onclick="approveEnrollment(<?php echo $enroll['enrollment_id']; ?>)">
                                                        <i class="fas fa-check"></i> Duyệt
                                                    </button>
                                                    <button class="btn btn-danger" onclick="rejectEnrollment(<?php echo $enroll['enrollment_id']; ?>)">
                                                        <i class="fas fa-times"></i> Từ chối
                                                    </button>
                                                </div>
                                            <?php elseif ($enroll['status'] === 'approved' && $enroll['payment_status'] === 'unpaid'): ?>
                                                <button class="btn btn-sm btn-warning" onclick="updatePayment(<?php echo $enroll['enrollment_id']; ?>, 'paid')">
                                                    <i class="fas fa-money-bill me-1"></i>Đánh dấu đã thanh toán
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&status=<?php echo $status; ?>&course_id=<?php echo $course_id; ?>&payment_status=<?php echo $payment_status; ?>">Trước</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&course_id=<?php echo $course_id; ?>&payment_status=<?php echo $payment_status; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&status=<?php echo $status; ?>&course_id=<?php echo $course_id; ?>&payment_status=<?php echo $payment_status; ?>">Sau</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function approveEnrollment(id) {
            if (confirm('Xác nhận duyệt đăng ký này?')) {
                window.location.href = '../../handle/enroll_process.php?action=approve&id=' + id + '&from=manage';
            }
        }

        function rejectEnrollment(id) {
            if (confirm('Xác nhận từ chối đăng ký này?')) {
                const reason = prompt('Lý do từ chối (tùy chọn):');
                window.location.href = '../../handle/enroll_process.php?action=reject&id=' + id + '&reason=' + encodeURIComponent(reason || '') + '&from=manage';
            }
        }

        function updatePayment(id, status) {
            if (confirm('Xác nhận cập nhật trạng thái thanh toán?')) {
                window.location.href = '../../handle/enroll_process.php?action=update_payment&id=' + id + '&status=' + status;
            }
        }
    </script>
</body>

</html>