<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/course_functions.php';

requireAdmin();

// Lấy tham số
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Lấy danh sách khóa học
$result = getCourses([
    'search' => $search,
    'category_id' => $category,
    'page' => $page,
    'per_page' => 10,
    'sort' => 'id_asc'
]);

$courses = $result['courses'] ?? [];
$pagination = $result['pagination'] ?? [];

// Lấy categories
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khóa học - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/admin.css">
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
    <div class="admin-sidebar">
        <div class="admin-sidebar-brand"><i class="fas fa-graduation-cap me-2"></i>EduLearn Admin</div>
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="manage_courses.php" class="active"><i class="fas fa-book"></i>Quản lý Khóa học</a></li>
            <li><a href="create_courses.php"><i class="fas fa-plus-circle"></i>Thêm Khóa học</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
            <li><a href="create_users.php"><i class="fas fa-user-plus"></i>Thêm Người dùng</a></li>
            <li><a href="manage_enrollments.php"><i class="fas fa-file-invoice"></i>Quản lý Đăng ký</a></li>
            <li><hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 1.5rem;"></li>
            <li><a href="../../index.php"><i class="fas fa-home"></i>Về Trang chủ</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <div class="admin-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1 admin-section-title"><i class="fas fa-book me-2"></i>Quản lý Khóa học</h2>
                <p class="admin-section-subtitle mb-0">Quản lý danh sách khóa học, trạng thái và thao tác nhanh</p>
            </div>
            <div class="d-flex gap-2">
                <a href="ratings.php" class="btn btn-outline-primary"><i class="fas fa-star me-2"></i>Xem đánh giá</a>
                <a href="create_courses.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm Khóa học Mới</a>
            </div>
        </div>

        <?php /* inline alert removed in favor of top banner */ ?>

        <div class="card admin-filter-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm khóa học..." value="<?php echo escape($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Danh mục</label>
                        <select class="form-select" name="category">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>><?php echo escape($cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Tìm kiếm</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card admin-card">
            <div class="card-body">
                <div class="admin-table-wrap table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th><th>Khóa học</th><th>Danh mục</th><th>Giảng viên</th><th>Giá</th><th>Học viên</th><th>Trạng thái</th><th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr><td colspan="8" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><p class="text-muted mb-0">Không tìm thấy khóa học nào</p></td></tr>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?php echo $course['course_id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://via.placeholder.com/80'); ?>" alt="" class="admin-thumbnail me-3">
                                                <div><strong><?php echo escape($course['course_name']); ?></strong><br><small class="text-muted"><?php echo escape(substr($course['description'], 0, 50)); ?>...</small></div>
                                            </div>
                                        </td>
                                        <td><?php echo escape($course['category_name']); ?></td>
                                        <td><?php echo escape($course['instructor_name']); ?></td>
                                        <td><strong class="text-primary"><?php echo formatCurrency($course['price']); ?></strong></td>
                                        <td><span class="badge bg-info"><?php echo $course['enrolled_count']; ?></span></td>
                                        <td><?php echo getStatusBadge($course['status']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_courses.php?id=<?php echo $course['course_id']; ?>" class="btn btn-warning btn-action" title="Sửa"><i class="fas fa-edit"></i></a>
                                                <button onclick="deleteCourse(<?php echo $course['course_id']; ?>)" class="btn btn-danger btn-action" title="Xóa"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($pagination['total_pages'] ?? 0) > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_prev']): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>"><i class="fas fa-chevron-left"></i></a></li><?php endif; ?>
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?><li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                            <?php if ($pagination['has_next']): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>"><i class="fas fa-chevron-right"></i></a></li><?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
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

        function deleteCourse(id) {
            if (confirm('Bạn có chắc chắn muốn xóa khóa học này?\n\nLưu ý: Khóa học có học viên sẽ không thể xóa.')) {
                window.location.href = '../../handle/course_process.php?action=delete&id=' + id;
            }
        }
    </script>
</body>

</html>