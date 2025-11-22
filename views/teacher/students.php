<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

// Kiểm tra role teacher
requireTeacher();

$teacher_id = $_SESSION['user_id'];

// Lấy tham số
$search = $_GET['search'] ?? '';
$course_id = $_GET['course_id'] ?? '';
$status = $_GET['status'] ?? '';

// Lấy danh sách khóa học của giáo viên
$courses_stmt = $pdo->prepare("SELECT course_id, course_name FROM courses WHERE teacher_id = ? ORDER BY course_name");
$courses_stmt->execute([$teacher_id]);
$my_courses = $courses_stmt->fetchAll();

// Xây dựng query
$where = ["c.teacher_id = ?"];
$params = [$teacher_id];

if ($search) {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($course_id) {
    $where[] = "e.course_id = ?";
    $params[] = $course_id;
}

if ($status) {
    $where[] = "e.status = ?";
    $params[] = $status;
}

$where_sql = implode(' AND ', $where);

// Lấy danh sách học viên
$sql = "
    SELECT 
        u.*, 
        e.enrollment_id, 
        e.enrollment_date, 
        e.status as enrollment_status,
        c.course_name, 
        c.course_id, 
        e.payment_status,
        (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.course_id) AS lesson_count,
        (SELECT COUNT(*) FROM progress pr WHERE pr.enrollment_id = e.enrollment_id AND pr.completed = 1) AS completed_count
    FROM enrollments e
    JOIN users u ON e.user_id = u.user_id
    JOIN courses c ON e.course_id = c.course_id
    WHERE $where_sql
    ORDER BY e.enrollment_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Thống kê
$total_stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.user_id) 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    WHERE c.teacher_id = ? AND e.status = 'approved'
");
$total_stmt->execute([$teacher_id]);
$total_students = $total_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học viên - Giáo viên</title>
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

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f3f4f6;
        }

        .table thead {
            background: #fef3c7;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: #fef9e7;
            transform: scale(1.01);
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .student-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
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
            <li><a href="students.php" class="active"><i class="fas fa-users"></i>Học viên</a></li>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-users me-2"></i>Học viên của tôi</h2>
                <p class="text-muted mb-0">Quản lý và theo dõi học viên đang học các khóa của bạn</p>
            </div>
        </div>

        <?php showAlert(); ?>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $total_students; ?></h3>
                    <p class="text-muted mb-0">Tổng học viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $pending = array_filter($students, fn($s) => $s['enrollment_status'] === 'pending');
                        echo count($pending);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Chờ duyệt</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $approved = array_filter($students, fn($s) => $s['enrollment_status'] === 'approved');
                        echo count($approved);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Đã duyệt</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $completed = array_filter($students, fn($s) => $s['enrollment_status'] === 'completed');
                        echo count($completed);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Hoàn thành</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tìm kiếm học viên</label>
                    <input type="text" class="form-control" name="search"
                        placeholder="Tên hoặc email..." value="<?php echo escape($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Khóa học</label>
                    <select class="form-select" name="course_id">
                        <option value="">Tất cả khóa học</option>
                        <?php foreach ($my_courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>"
                                <?php echo $course_id == $course['course_id'] ? 'selected' : ''; ?>>
                                <?php echo escape($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-search me-2"></i>Lọc
                    </button>
                </div>
            </form>
        </div>

        <!-- Students Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Khóa học</th>
                                <th>Liên hệ</th>
                                <th>Ngày đăng ký</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th class="text-center">Tiến độ</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-user-slash fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Chưa có học viên nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo getAvatarUrl($student['avatar'], $student['full_name'], 50); ?>"
                                                    class="student-avatar me-3" alt="">
                                                <div>
                                                    <strong class="d-block"><?php echo escape($student['full_name']); ?></strong>
                                                    <small class="text-muted">ID: <?php echo $student['user_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <strong class="d-block"><?php echo escape($student['course_name']); ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <small>
                                                <i class="fas fa-envelope text-muted me-1"></i><?php echo escape($student['email']); ?><br>
                                                <?php if ($student['phone']): ?>
                                                    <i class="fas fa-phone text-muted me-1"></i><?php echo escape($student['phone']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted"><?php echo formatDate($student['enrollment_date']); ?></small>
                                        </td>
                                        <td class="align-middle">
                                            <?php echo getStatusBadge($student['enrollment_status'], 'enrollment'); ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php echo getStatusBadge($student['payment_status'], 'payment'); ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <?php
                                            $totalLessons = (int)($student['lesson_count'] ?? 0);
                                            $completedLessons = (int)($student['completed_count'] ?? 0);
                                            $progressPercent = $totalLessons > 0 ? (int)round(($completedLessons / $totalLessons) * 100) : 0;
                                            if ($progressPercent > 100) $progressPercent = 100;
                                            if ($progressPercent < 0) $progressPercent = 0;
                                            ?>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: <?php echo $progressPercent; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo $progressPercent; ?>%</small>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="student_detail.php?id=<?php echo $student['user_id']; ?>&course_id=<?php echo $student['course_id']; ?>"
                                                    class="btn btn-outline-warning" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-outline-primary"
                                                    title="Gửi thông báo"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#notificationModal"
                                                    data-student-id="<?php echo $student['user_id']; ?>"
                                                    data-student-name="<?php echo escape($student['full_name']); ?>"
                                                    data-student-email="<?php echo escape($student['email']); ?>">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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