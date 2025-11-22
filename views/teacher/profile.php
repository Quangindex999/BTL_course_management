<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

// Kiểm tra role teacher
requireTeacher();

$teacher_id = $_SESSION['user_id'];

// Lấy thông tin giáo viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

// Lấy thống kê
$stats = [];

// Tổng khóa học
$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$stats['total_courses'] = $stmt->fetchColumn();

// Tổng học viên
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.user_id) 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    WHERE c.teacher_id = ? AND e.status = 'approved'
");
$stmt->execute([$teacher_id]);
$stats['total_students'] = $stmt->fetchColumn();

// Khóa học gần đây
$stmt = $pdo->prepare("
    SELECT * FROM courses 
    WHERE teacher_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$teacher_id]);
$recent_courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - Giáo viên</title>
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

        .profile-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

        .rating-stars {
            color: #fbbf24;
            font-size: 1.5rem;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
            width: 150px;
        }

        .info-row {
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .stat-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-block;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f59e0b;
        }

        .course-mini {
            background: #fef3c7;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            transition: all 0.3s;
        }

        .course-mini:hover {
            background: #fde68a;
            transform: translateX(5px);
        }

        .social-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
        }

        .social-btn:hover {
            transform: translateY(-2px);
        }

        .social-linkedin {
            background: #0077b5;
            color: white;
        }

        .social-website {
            background: #1d5c7a;
            color: white;
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

        <!-- Profile Header Card -->
        <div class="card mb-4">
            <div class="profile-header">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="<?php echo getAvatarUrl($teacher['avatar'], $teacher['full_name'], 200); ?>"
                                class="avatar-large" alt="">
                        </div>
                        <div class="col">
                            <h2 class="mb-2"><?php echo escape($teacher['full_name']); ?></h2>
                            <p class="mb-3 fs-5"><?php echo escape($teacher['specialization']) ?: 'Giáo viên'; ?></p>

                            <?php if ($teacher['rating']): ?>
                                <div class="rating-stars mb-3">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i < floor($teacher['rating']) ? '' : '-half-alt'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2"><?php echo $teacher['rating']; ?>/5.0</span>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-3">
                                <span class="stat-badge">
                                    <i class="fas fa-book me-2"></i><?php echo $stats['total_courses']; ?> Khóa học
                                </span>
                                <span class="stat-badge">
                                    <i class="fas fa-users me-2"></i><?php echo $stats['total_students']; ?> Học viên
                                </span>
                                <span class="stat-badge">
                                    <i class="fas fa-clock me-2"></i><?php echo $teacher['experience_years'] ?: 0; ?> Năm kinh nghiệm
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
                            <i class="fas fa-info-circle me-2"></i>Giới thiệu
                        </div>
                        <?php if ($teacher['bio']): ?>
                            <p class="text-muted" style="line-height: 1.8;">
                                <?php echo nl2br(escape($teacher['bio'])); ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">Chưa có thông tin giới thiệu.</p>
                        <?php endif; ?>

                        <div class="section-title mt-4">
                            <i class="fas fa-graduation-cap me-2"></i>Thông tin chuyên môn
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Chuyên môn:</div>
                                <div class="flex-grow-1">
                                    <?php if ($teacher['specialization']): ?>
                                        <?php foreach (explode(',', $teacher['specialization']) as $skill): ?>
                                            <span class="badge badge-custom" style="background: #fef3c7; color: #92400e;">
                                                <?php echo escape(trim($skill)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa cập nhật</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Học vấn:</div>
                                <div class="flex-grow-1">
                                    <strong><?php echo escape($teacher['education']) ?: 'Chưa cập nhật'; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Kinh nghiệm:</div>
                                <div class="flex-grow-1">
                                    <strong><?php echo $teacher['experience_years'] ?: 0; ?> năm</strong>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-4">
                            <i class="fas fa-envelope me-2"></i>Liên hệ
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Email:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <strong><?php echo escape($teacher['email']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Điện thoại:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <strong><?php echo escape($teacher['phone']) ?: 'Chưa cập nhật'; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Địa chỉ:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <strong><?php echo escape($teacher['address']) ?: 'Chưa cập nhật'; ?></strong>
                                </div>
                            </div>
                        </div>

                        <?php if ($teacher['linkedin'] || $teacher['website']): ?>
                            <div class="mt-4">
                                <h6 class="mb-3"><i class="fas fa-link me-2"></i>Liên kết mạng xã hội</h6>
                                <div class="d-flex gap-3">
                                    <?php if ($teacher['linkedin']): ?>
                                        <a href="<?php echo escape($teacher['linkedin']); ?>" target="_blank" class="social-btn social-linkedin">
                                            <i class="fab fa-linkedin"></i> LinkedIn
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($teacher['website']): ?>
                                        <a href="<?php echo escape($teacher['website']); ?>" target="_blank" class="social-btn social-website">
                                            <i class="fas fa-globe"></i> Website
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="section-title">
                            <i class="fas fa-book me-2"></i>Khóa học gần đây
                        </div>

                        <?php if (empty($recent_courses)): ?>
                            <p class="text-muted">Chưa có khóa học nào</p>
                        <?php else: ?>
                            <?php foreach ($recent_courses as $course): ?>
                                <div class="course-mini">
                                    <h6 class="mb-1"><?php echo escape($course['course_name']); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo formatDate($course['created_at']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <a href="my_course.php" class="btn btn-warning w-100 mt-3">
                            <i class="fas fa-arrow-right me-2"></i>Xem tất cả khóa học
                        </a>

                        <div class="section-title mt-4">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê
                        </div>

                        <div class="card border-0" style="background: #fef3c7;">
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Hồ sơ hoàn thiện</span>
                                        <strong>85%</strong>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: 85%; background: #f59e0b;"></div>
                                    </div>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Đánh giá trung bình</span>
                                    <strong class="text-warning">
                                        <i class="fas fa-star"></i> <?php echo $teacher['rating'] ?? 0; ?>/5
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Tài khoản</span>
                                    <?php echo getStatusBadge($teacher['status']); ?>
                                </div>
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