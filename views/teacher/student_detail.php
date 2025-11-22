<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/enrollments_functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$student_id = intval($_GET['id'] ?? 0);
$course_id = intval($_GET['course_id'] ?? 0);

if (!$student_id) {
    setAlert('Không tìm thấy học viên', 'error');
    redirect(SITE_URL . '/views/teacher/students.php');
}

// Lấy thông tin học viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    setAlert('Không tìm thấy học viên', 'error');
    redirect(SITE_URL . '/views/teacher/students.php');
}

// Lấy thông tin enrollment nếu có course_id
$enrollment = null;
if ($course_id) {
    $stmt = $pdo->prepare("
        SELECT e.*, c.course_name, c.course_id,
        (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.course_id) AS lesson_count,
        (SELECT COUNT(*) FROM progress pr WHERE pr.enrollment_id = e.enrollment_id AND pr.completed = 1) AS completed_count
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        WHERE e.user_id = ? AND e.course_id = ? AND c.teacher_id = ?
    ");
    $stmt->execute([$student_id, $course_id, $teacher_id]);
    $enrollment = $stmt->fetch();
}

// Lấy tất cả khóa học của học viên với giáo viên này
$stmt = $pdo->prepare("
    SELECT e.*, c.course_name, c.course_id,
    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.course_id) AS lesson_count,
    (SELECT COUNT(*) FROM progress pr WHERE pr.enrollment_id = e.enrollment_id AND pr.completed = 1) AS completed_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ? AND c.teacher_id = ?
    ORDER BY e.enrollment_date DESC
");
$stmt->execute([$student_id, $teacher_id]);
$student_courses = $stmt->fetchAll();

// Thống kê
$stats = [
    'total_courses' => count($student_courses),
    'completed_courses' => 0,
    'active_courses' => 0,
    'pending_courses' => 0
];

foreach ($student_courses as $sc) {
    if ($sc['status'] === 'completed') $stats['completed_courses']++;
    elseif ($sc['status'] === 'approved') $stats['active_courses']++;
    elseif ($sc['status'] === 'pending') $stats['pending_courses']++;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết học viên - Giáo viên</title>
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
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }

        .avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
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
            color: #f59e0b;
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

        .course-item {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .course-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
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
        <?php showAlert(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-graduate me-2"></i>Chi tiết học viên</h2>
            <div class="d-flex gap-2">
                <a href="students.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="card mb-4">
            <div class="profile-header">
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
                                <i class="fas fa-book me-2"></i><?php echo $stats['total_courses']; ?> Khóa học
                            </span>
                            <span class="stat-badge">
                                <i class="fas fa-check-circle me-2"></i><?php echo $stats['completed_courses']; ?> Hoàn thành
                            </span>
                            <span class="stat-badge">
                                <i class="fas fa-graduation-cap me-2"></i><?php echo $stats['active_courses']; ?> Đang học
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row">
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
                                    <a href="mailto:<?php echo escape($student['email']); ?>">
                                        <strong><?php echo escape($student['email']); ?></strong>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Điện thoại:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <strong><?php echo escape($student['phone']) ?: 'Chưa cập nhật'; ?></strong>
                                    <?php if ($student['phone']): ?>
                                        <a href="tel:<?php echo escape($student['phone']); ?>" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-phone me-1"></i>Gọi
                                        </a>
                                    <?php endif; ?>
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

                        <div class="info-row">
                            <div class="d-flex">
                                <div class="info-label">Ngày tham gia:</div>
                                <div class="flex-grow-1">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <strong><?php echo formatDate($student['created_at']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-4">
                            <i class="fas fa-book-open me-2"></i>Khóa học đã đăng ký
                        </div>

                        <?php if (empty($student_courses)): ?>
                            <p class="text-muted">Học viên chưa đăng ký khóa học nào của bạn.</p>
                        <?php else: ?>
                            <?php foreach ($student_courses as $sc): ?>
                                <?php
                                $totalLessons = (int)($sc['lesson_count'] ?? 0);
                                $completedLessons = (int)($sc['completed_count'] ?? 0);
                                $progressPercent = $totalLessons > 0 ? (int)round(($completedLessons / $totalLessons) * 100) : 0;
                                if ($progressPercent > 100) $progressPercent = 100;
                                if ($progressPercent < 0) $progressPercent = 0;
                                ?>
                                <div class="course-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?php echo escape($sc['course_name']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                Đăng ký: <?php echo formatDate($sc['enrollment_date']); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php echo getStatusBadge($sc['status'], 'enrollment'); ?>
                                            <?php echo getStatusBadge($sc['payment_status'], 'payment'); ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Tiến độ học tập</small>
                                            <small class="text-muted"><strong><?php echo $progressPercent; ?>%</strong></small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $progressPercent; ?>%"></div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $completedLessons; ?>/<?php echo $totalLessons; ?> bài học đã hoàn thành
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-4">
                        <div class="section-title">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê
                        </div>

                        <div class="card border-0 mb-4" style="background: #eff6ff;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tổng khóa học</span>
                                    <strong><?php echo $stats['total_courses']; ?></strong>
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
                                    <span class="text-muted">Chờ duyệt</span>
                                    <strong class="text-warning"><?php echo $stats['pending_courses']; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="section-title">
                            <i class="fas fa-comments me-2"></i>Liên hệ
                        </div>

                        <div class="card border-0" style="background: #f9fafb;">
                            <div class="card-body">
                                <button type="button"
                                    class="btn btn-primary w-100 mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#notificationModal"
                                    data-student-id="<?php echo $student['user_id']; ?>"
                                    data-student-name="<?php echo escape($student['full_name']); ?>"
                                    data-student-email="<?php echo escape($student['email']); ?>">
                                    <i class="fas fa-bell me-2"></i>Gửi thông báo
                                </button>
                                <?php if ($student['phone']): ?>
                                    <a href="tel:<?php echo escape($student['phone']); ?>" class="btn btn-success w-100">
                                        <i class="fas fa-phone me-2"></i>Gọi điện
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">
                        <i class="fas fa-bell me-2"></i>Gửi thông báo cho học viên
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="../../handle/send_notification.php">
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="modal_student_id">
                        <input type="hidden" name="student_email" id="modal_student_email">

                        <div class="mb-3">
                            <label class="form-label">Gửi đến</label>
                            <input type="text" class="form-control" id="modal_student_name_display" readonly>
                            <small class="text-muted" id="modal_student_email_display"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required
                                placeholder="Nhập tiêu đề thông báo">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="6" required
                                placeholder="Nhập nội dung thông báo..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Thông báo sẽ được gửi đến học viên và hiển thị trong phần thông báo của họ.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Gửi thông báo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý modal notification
        const notificationModal = document.getElementById('notificationModal');
        if (notificationModal) {
            notificationModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const studentId = button.getAttribute('data-student-id');
                const studentName = button.getAttribute('data-student-name');
                const studentEmail = button.getAttribute('data-student-email');

                document.getElementById('modal_student_id').value = studentId;
                document.getElementById('modal_student_email').value = studentEmail;
                document.getElementById('modal_student_name_display').value = studentName;
                document.getElementById('modal_student_email_display').textContent = studentEmail;
            });
        }
    </script>
</body>

</html>