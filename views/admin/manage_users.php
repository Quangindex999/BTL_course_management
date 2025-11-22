<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

requireAdmin();

// Lấy tham số
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;

// Xây dựng query
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $where[] = "role = ?";
    $params[] = $role;
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

$where_sql = implode(' AND ', $where);

// Đếm tổng
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $where_sql");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();

// Lấy dữ liệu
$offset = ($page - 1) * $per_page;
$sql = "SELECT * FROM users WHERE $where_sql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pagination = paginate($total, $per_page, $page);

// Thống kê
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'students' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'teachers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng - Admin</title>
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

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f3f4f6;
        }

        .role-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-student {
            background: #dbeafe;
            color: #1e40af;
        }

        .role-teacher {
            background: #fef3c7;
            color: #92400e;
        }

        .role-admin {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s;
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
            transform: scale(1.01);
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
            <li><a href="create_courses.php"><i class="fas fa-plus-circle"></i>Thêm Khóa học</a></li>
            <li><a href="manage_users.php" class="active"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
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
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-users me-2"></i>Quản lý Người dùng</h2>
                <p class="text-muted mb-0">Quản lý học viên, giáo viên và quản trị viên</p>
            </div>
            <a href="create_users.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Thêm Người dùng
            </a>
        </div>

        <?php showAlert(); ?>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                    <p class="text-muted mb-0">Tổng người dùng</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['students']; ?></h3>
                    <p class="text-muted mb-0">Học viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['teachers']; ?></h3>
                    <p class="text-muted mb-0">Giáo viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['admins']; ?></h3>
                    <p class="text-muted mb-0">Quản trị viên</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tìm kiếm</label>
                    <input type="text" class="form-control" name="search"
                        placeholder="Tên, email, số điện thoại..." value="<?php echo escape($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Vai trò</label>
                    <select class="form-select" name="role">
                        <option value="">Tất cả vai trò</option>
                        <option value="student" <?php echo $role === 'student' ? 'selected' : ''; ?>>Học viên</option>
                        <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Giáo viên</option>
                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                        <a href="manage_users.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk actions -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" action="../../handle/user_process.php" id="bulkForm" class="row g-2 align-items-center">
                    <input type="hidden" name="action" id="bulkAction" value="">
                    <div class="col-auto">
                        <select class="form-select" id="bulkStatusValue" name="status">
                            <option value="active">Đặt Hoạt động</option>
                            <option value="inactive">Đặt Không hoạt động</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-primary" onclick="submitBulkStatus()">Cập nhật trạng thái</button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger" onclick="submitBulkDelete()">
                            <i class="fas fa-trash me-1"></i>Xóa đã chọn
                        </button>
                    </div>
                    <div class="col text-muted small">
                        Chọn người dùng bằng checkbox ở bảng bên dưới để thực hiện thao tác hàng loạt.
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card mb-4">
            <div class="card-body p-0">
                <form method="POST" action="../../handle/user_process.php" id="usersTableForm">
                    <input type="hidden" name="action" value="">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 42px;">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Người dùng</th>
                                    <th>Vai trò</th>
                                    <th>Liên hệ</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-user-slash fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">Không có người dùng phù hợp</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input row-check" name="ids[]" value="<?php echo $u['user_id']; ?>">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo getAvatarUrl($u['avatar'], $u['full_name'], 50); ?>"
                                                        class="user-avatar me-3" alt="">
                                                    <div>
                                                        <strong class="d-block"><?php echo escape($u['full_name']); ?></strong>
                                                        <small class="text-muted">ID: <?php echo $u['user_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $roleClass = match ($u['role']) {
                                                    'student' => 'role-student',
                                                    'teacher' => 'role-teacher',
                                                    'admin' => 'role-admin',
                                                    default => 'role-student'
                                                };
                                                $roleLabel = ['student' => 'Học viên', 'teacher' => 'Giáo viên', 'admin' => 'Quản trị viên'][$u['role']] ?? ucfirst($u['role']);
                                                ?>
                                                <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleLabel; ?></span>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-envelope text-muted me-1"></i><?php echo escape($u['email']); ?><br>
                                                    <?php if (!empty($u['phone'])): ?>
                                                        <i class="fas fa-phone text-muted me-1"></i><?php echo escape($u['phone']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo formatDate($u['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo getStatusBadge($u['status']); ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit_users.php?id=<?php echo $u['user_id']; ?>&view=1" class="btn btn-outline-secondary action-btn" title="Xem">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_users.php?id=<?php echo $u['user_id']; ?>" class="btn btn-outline-primary action-btn" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../../handle/user_process.php?action=delete&id=<?php echo $u['user_id']; ?>"
                                                        class="btn btn-outline-danger action-btn"
                                                        title="Xóa"
                                                        onclick="return confirm('Bạn chắc chắn muốn xóa người dùng này?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <?php
            $current = $pagination['current_page'];
            $total_pages = $pagination['total_pages'];
            // Build base URL without page param
            $query = $_GET;
            unset($query['page']);
            $base = 'manage_users.php' . (empty($query) ? '' : ('?' . http_build_query($query)));
            ?>
            <nav aria-label="User pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $current <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $base . (empty($query) ? '?page=' : '&page=') . ($current - 1); ?>" tabindex="-1">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $current ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $base . (empty($query) ? '?page=' : '&page=') . $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $current >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $base . (empty($query) ? '?page=' : '&page=') . ($current + 1); ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

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

            // Select all toggle
            const selectAll = document.getElementById('selectAll');
            const rowChecks = () => Array.from(document.querySelectorAll('.row-check'));
            selectAll?.addEventListener('change', function() {
                rowChecks().forEach(cb => cb.checked = this.checked);
            });

            function getSelectedIds() {
                return rowChecks().filter(cb => cb.checked).map(cb => cb.value);
            }

            function ensureSelection() {
                const ids = getSelectedIds();
                if (ids.length === 0) {
                    alert('Vui lòng chọn ít nhất một người dùng.');
                    return false;
                }
                return true;
            }

            function submitBulkDelete() {
                if (!ensureSelection()) return;
                if (!confirm('Bạn chắc chắn muốn xóa các người dùng đã chọn?')) return;

                const form = document.getElementById('bulkForm');
                // remove old ids if any
                Array.from(form.querySelectorAll('input[name="ids[]"]')).forEach(el => el.remove());
                getSelectedIds().forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.getElementById('bulkAction').value = 'bulk_delete';
                form.submit();
            }

            function submitBulkStatus() {
                if (!ensureSelection()) return;
                const status = document.getElementById('bulkStatusValue').value;

                const form = document.getElementById('bulkForm');
                document.getElementById('bulkAction').value = 'bulk_status';

                // Append selected ids as hidden inputs
                // First remove old ones
                Array.from(form.querySelectorAll('input[name="ids[]"]')).forEach(el => el.remove());
                getSelectedIds().forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                form.submit();
            }
        </script>