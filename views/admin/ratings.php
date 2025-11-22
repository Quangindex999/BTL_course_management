<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/ratings_functions.php';

requireAdmin();

$ratings = getAllRatings();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá khóa học - Admin</title>
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
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            color: white;
            padding: 2rem 0;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.9);
            display: block;
            padding: 0.85rem 1.5rem;
            text-decoration: none;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid white;
        }

        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-brand px-4 mb-4">
            <i class="fas fa-graduation-cap me-2"></i>EduLearn Admin
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
        <a href="manage_courses.php"><i class="fas fa-book me-2"></i>Quản lý khóa học</a>
        <a href="create_courses.php"><i class="fas fa-plus-circle me-2"></i>Thêm khóa học</a>
        <a href="manage_users.php"><i class="fas fa-users me-2"></i>Quản lý người dùng</a>
        <a href="create_users.php"><i class="fas fa-user-plus me-2"></i>Thêm người dùng</a>
        <a href="manage_enrollments.php"><i class="fas fa-file-invoice me-2"></i>Quản lý đăng ký</a>
        <a href="ratings.php" class="active"><i class="fas fa-star me-2"></i>Đánh giá</a>
        <hr class="mx-4" style="border-color: rgba(255,255,255,0.2);">
        <a href="../../index.php"><i class="fas fa-home me-2"></i>Về trang chủ</a>
        <a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="fas fa-star me-2 text-warning"></i>Đánh giá từ học viên</h2>
                <p class="text-muted mb-0">Theo dõi chất lượng khóa học qua phản hồi của học viên.</p>
            </div>
            <a href="manage_courses.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php showAlert(); ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($ratings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có đánh giá nào</h5>
                        <p class="text-muted mb-0">Danh sách đánh giá sẽ xuất hiện khi học viên gửi phản hồi.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Khóa học</th>
                                    <th>Giáo viên</th>
                                    <th>Học viên</th>
                                    <th>Số sao</th>
                                    <th>Nhận xét</th>
                                    <th>Ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ratings as $rating): ?>
                                    <tr>
                                        <td><?php echo escape($rating['course_name']); ?></td>
                                        <td><?php echo escape($rating['teacher_name'] ?? 'Chưa gán'); ?></td>
                                        <td>
                                            <strong><?php echo escape($rating['student_name']); ?></strong>
                                            <div class="text-muted small"><?php echo escape($rating['student_email']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark fw-bold">
                                                <?php echo $rating['rating']; ?>/5
                                            </span>
                                        </td>
                                        <td><?php echo $rating['review'] ? nl2br(escape($rating['review'])) : '<span class="text-muted">Không có nhận xét</span>'; ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($rating['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>