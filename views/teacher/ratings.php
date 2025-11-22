<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/ratings_functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$ratings = getTeacherRatings($teacher_id);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá khóa học - Giáo viên</title>
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
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .rating-badge {
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-brand px-4 mb-4">
            <i class="fas fa-chalkboard-teacher me-2"></i>Giáo viên
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
        <a href="my_course.php"><i class="fas fa-book me-2"></i>Khóa học của tôi</a>
        <a href="students.php"><i class="fas fa-users me-2"></i>Học viên</a>
        <a href="profile.php"><i class="fas fa-user me-2"></i>Hồ sơ</a>
        <a href="ratings.php" class="active"><i class="fas fa-star me-2"></i>Đánh giá</a>
        <hr class="mx-4" style="border-color: rgba(255,255,255,0.2);">
        <a href="../../index.php"><i class="fas fa-home me-2"></i>Về trang chủ</a>
        <a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="fas fa-star me-2 text-warning"></i>Đánh giá khóa học</h2>
                <p class="text-muted mb-0">Xem phản hồi của học viên về các khóa học bạn giảng dạy</p>
            </div>
        </div>

        <?php showAlert(); ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($ratings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có đánh giá nào</h5>
                        <p class="text-muted mb-0">Khi học viên gửi đánh giá, bạn sẽ thấy chúng tại đây.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Khóa học</th>
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
                                        <td>
                                            <strong><?php echo escape($rating['student_name']); ?></strong>
                                            <div class="text-muted small"><?php echo escape($rating['student_email']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark rating-badge">
                                                <?php echo $rating['rating']; ?> / 5
                                            </span>
                                        </td>
                                        <td><?php echo $rating['review'] ? nl2br(escape($rating['review'])) : '<span class="text-muted">Không có nhận xét</span>'; ?></td>
                                        <td class="text-muted small">
                                            <?php echo date('d/m/Y H:i', strtotime($rating['created_at'])); ?>
                                        </td>
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