<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/enrollments_functions.php';
require_once '../../functions/notification_functions.php';

requireStudent();

$user_id = $_SESSION['user_id'];

// Lấy thông tin học viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

$user_enrollment_stats = getUserEnrollmentStats($user_id);
$stats = [
    'total_enrollments' => $user_enrollment_stats['total'],
    'completed_courses' => $user_enrollment_stats['completed'],
    'active_courses' => $user_enrollment_stats['approved'],
    'pending_courses' => $user_enrollment_stats['pending'],
    'total_paid' => $user_enrollment_stats['total_paid']
];

// Khóa học gần đây
$recent_courses = getUserEnrollmentsPaginated($user_id, null, 1, 5)['enrollments'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - Học viên</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px 15px 0 0;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .profile-content {
            position: relative;
            z-index: 2;
        }

        .avatar-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
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

        .info-row {
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            min-width: 150px;
            font-weight: 600;
            color: #6b7280;
        }

        .course-mini {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .course-mini:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
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
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="fas fa-bell"></i>
                            <?php
                            $unread_count = getUnreadNotificationCount($_SESSION['user_id']);
                            if ($unread_count > 0):
                            ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link active" href="menu_student.php">Hồ sơ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../../handle/logout_process.php">Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <?php showAlert(); ?>

        <!-- Profile Header Card -->
        <div class="card mb-4">
            <div class="profile-header">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="<?php echo getAvatarUrl($student['avatar'], $student['full_name'], 200); ?>"
                                class="avatar-large" alt="">
                        </div>
                        <div class="col">
                            <h2 class="mb-2"><?php echo escape($student['full_name']); ?></h2>
                            <p class="mb-3 fs-5">Học viên</p>

                            <div class="d-flex gap-3 flex-wrap">
                                <span class="stat-badge">
                                    <i class="fas fa-book me-2"></i><?php echo $stats['total_enrollments']; ?> Khóa học đã đăng ký
                                </span>
                                <span class="stat-badge">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $stats['completed_courses']; ?> Khóa học đã hoàn thành
                                </span>
                                <span class="stat-badge">
                                    <i class="fas fa-graduation-cap me-2"></i><?php echo $stats['active_courses']; ?> Khóa học đang học
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="edit_profile.php" class="btn btn-light btn-lg">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa hồ sơ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row">
                    <!-- About -->
                    <div class="col-lg-8">
                        <div class="section-title">
                            <i class="fas fa-info-circle me-2"></i>Thông tin cá nhân
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Họ và tên:</div>
                                <div class="flex-grow-1">
                                    <strong><?php echo escape($student['full_name']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Email:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <strong><?php echo escape($student['email']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Điện thoại:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <strong><?php echo escape($student['phone']) ?: 'Chưa cập nhật'; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Địa chỉ:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <strong><?php echo escape($student['address']) ?: 'Chưa cập nhật'; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-4">
                            <i class="fas fa-book-open me-2"></i>Khóa học gần đây
                        </div>

                        <?php if (empty($recent_courses)): ?>
                            <p class="text-muted">Bạn chưa đăng ký khóa học nào.</p>
                            <a href="course_detail.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Khám phá khóa học
                            </a>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($recent_courses as $enrollment): ?>
                                    <div class="col-md-6">
                                        <div class="course-mini">
                                            <h6 class="mb-1"><?php echo escape($enrollment['course_name']); ?></h6>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo formatDate($enrollment['enrollment_date'] ?? $enrollment['enrolled_at'] ?? ''); ?>
                                                </small>
                                                <span class="badge <?php
                                                                    echo $enrollment['status'] === 'approved' ? 'bg-success' : ($enrollment['status'] === 'completed' ? 'bg-info' : 'bg-warning');
                                                                    ?>">
                                                    <?php
                                                    echo match ($enrollment['status']) {
                                                        'approved' => 'Đang học',
                                                        'completed' => 'Hoàn thành',
                                                        'pending' => 'Chờ duyệt',
                                                        default => ucfirst($enrollment['status'])
                                                    };
                                                    ?>
                                                </span>
                                            </div>
                                            <a href="course_detail.php?id=<?php echo $enrollment['course_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="my_courses.php" class="btn btn-primary mt-3">
                                <i class="fas fa-arrow-right me-2"></i>Xem tất cả khóa học
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="section-title">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê học tập
                        </div>

                        <div class="card border-0 mb-4" style="background: #eff6ff;">
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Tiến độ học tập</span>
                                        <strong><?php
                                                $progress = $stats['total_enrollments'] > 0
                                                    ? round(($stats['completed_courses'] / $stats['total_enrollments']) * 100)
                                                    : 0;
                                                echo $progress;
                                                ?>%</strong>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: <?php echo $progress; ?>%; background: #2563eb;"></div>
                                    </div>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tổng khóa học</span>
                                    <strong><?php echo $stats['total_enrollments']; ?></strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Đã hoàn thành</span>
                                    <strong class="text-success"><?php echo $stats['completed_courses']; ?></strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Đang học</span>
                                    <strong class="text-primary"><?php echo $stats['active_courses']; ?></strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Tổng đã thanh toán</span>
                                    <strong class="text-success"><?php echo formatCurrency($stats['total_paid']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="section-title">
                            <i class="fas fa-user-circle me-2"></i>Thông tin tài khoản
                        </div>

                        <div class="card border-0" style="background: #f9fafb;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Trạng thái</span>
                                    <?php echo getStatusBadge($student['status']); ?>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Ngày tham gia</span>
                                    <strong><?php echo formatDate($student['created_at']); ?></strong>
                                </div>

                                <hr>

                                <a href="edit_profile.php" class="btn btn-primary w-100">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa hồ sơ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>