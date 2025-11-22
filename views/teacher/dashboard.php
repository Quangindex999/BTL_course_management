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

// Lấy khóa học của giáo viên
$courses_stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count
    FROM courses c
    WHERE c.teacher_id = ?
    ORDER BY c.created_at DESC
");
$courses_stmt->execute([$teacher_id]);
$my_courses = $courses_stmt->fetchAll();

// Thống kê
$stats = [
    'total_courses' => count($my_courses),
    'total_students' => 0,
    'pending_enrollments' => 0,
    'total_revenue' => 0
];

// Tính tổng học viên
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.user_id) 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    WHERE c.teacher_id = ? AND e.status = 'approved'
");
$stmt->execute([$teacher_id]);
$stats['total_students'] = $stmt->fetchColumn();

// Đăng ký chờ duyệt
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    WHERE c.teacher_id = ? AND e.status = 'pending'
");
$stmt->execute([$teacher_id]);
$stats['pending_enrollments'] = $stmt->fetchColumn();

// Đăng ký gần đây
$stmt = $pdo->prepare("
    SELECT e.*, u.full_name, u.email, c.course_name
    FROM enrollments e
    JOIN users u ON e.user_id = u.user_id
    JOIN courses c ON e.course_id = c.course_id
    WHERE c.teacher_id = ?
    ORDER BY e.enrollment_date DESC
    LIMIT 10
");
$stmt->execute([$teacher_id]);
$recent_enrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Giáo viên - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --secondary: #10b981;
        }

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

        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 15px;
        }

        .teacher-profile {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .teacher-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f59e0b;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
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

        .course-card {
            transition: all 0.3s;
            cursor: pointer;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .course-thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-chalkboard-teacher me-2"></i>Giáo viên</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="my_course.php"><i class="fas fa-book"></i>Khóa học của tôi</a></li>
            <li><a href="students.php"><i class="fas fa-users"></i>Học viên</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i>Hồ sơ của tôi</a></li>
            <li>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 1.5rem;">
            </li>
            <li><a href="../../index.php"><i class="fas fa-home"></i>Về Trang chủ</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="teacher-profile">
                <img src="<?php echo getAvatarUrl($teacher['avatar'], $teacher['full_name'], 100); ?>"
                    class="teacher-avatar" alt="">
                <div class="flex-grow-1">
                    <h4 class="mb-1">Xin chào, <?php echo escape($teacher['full_name']); ?>! 👋</h4>
                    <p class="text-muted mb-2"><?php echo escape($teacher['specialization']) ?: 'Giáo viên'; ?></p>
                    <?php if ($teacher['rating']): ?>
                        <div>
                            <i class="fas fa-star text-warning"></i>
                            <strong><?php echo $teacher['rating']; ?></strong> / 5.0
                            <span class="text-muted ms-2">• <?php echo $teacher['experience_years']; ?> năm kinh nghiệm</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-end">
                    <small class="text-muted"><i class="far fa-calendar me-2"></i><?php echo date('d/m/Y'); ?></small><br>
                    <small class="text-muted"><i class="far fa-clock me-2"></i><?php echo date('H:i'); ?></small>
                </div>
            </div>
        </div>

        <?php showAlert(); ?>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['total_courses']; ?></h3>
                    <p class="text-muted mb-0">Khóa học</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #10b981;">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['total_students']; ?></h3>
                    <p class="text-muted mb-0">Học viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #3b82f6;">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $teacher['rating'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Đánh giá</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #8b5cf6;">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $teacher['total_students'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Chứng chỉ</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- My Courses -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Khóa học của tôi</h5>
                            <a href="my_course.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($my_courses)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Bạn chưa có khóa học nào</p>
                                <a href="#" class="btn btn-primary">Tạo khóa học mới</a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach (array_slice($my_courses, 0, 4) as $course): ?>
                                    <div class="col-md-6">
                                        <div class="card course-card">
                                            <div class="card-body">
                                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://via.placeholder.com/300x150'); ?>"
                                                    class="course-thumbnail mb-3" alt="">
                                                <h6 class="mb-2"><?php echo escape($course['course_name']); ?></h6>
                                                <div class="d-flex justify-content-between text-muted small mb-2">
                                                    <span><i class="fas fa-users me-1"></i><?php echo $course['enrolled_count']; ?> học viên</span>
                                                    <span><?php echo getLevelBadge($course['level']); ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <strong class="text-primary"><?php echo formatCurrency($course['price']); ?></strong>
                                                    <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-primary">Quản lý</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <!-- <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tạo khóa học mới
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Sửa hồ sơ
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-chart-line me-2"></i>Xem báo cáo
                            </a>
                        </div>
                    </div>
                </div> -->

                <!-- Profile Completeness -->
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Hồ sơ</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <small>Hoàn thiện hồ sơ</small>
                                <small class="fw-bold">85%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 85%"></div>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>Thông tin cơ bản
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>Chuyên môn
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-times-circle text-danger me-2"></i>Chứng chỉ
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>