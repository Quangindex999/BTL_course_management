<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

// Kiểm tra role teacher
requireTeacher();

$teacher_id = $_SESSION['user_id'];

// Lấy tham số
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Xây dựng query
$where = ["c.teacher_id = ?"];
$params = [$teacher_id];

if ($status) {
    $where[] = "c.status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(c.course_name LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where);

// Lấy khóa học
$sql = "
    SELECT c.*, cat.category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count,
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as lesson_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE $where_sql
    ORDER BY c.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Thống kê
$stats = [
    'active' => 0,
    'inactive' => 0,
    'total_students' => 0,
    'total_revenue' => 0
];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = 'active'");
$stmt->execute([$teacher_id]);
$stats['active'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = 'inactive'");
$stmt->execute([$teacher_id]);
$stats['inactive'] = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khóa học của tôi - Giáo viên</title>
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

        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .course-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .course-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .action-menu {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 0.5rem;
            opacity: 0;
            transition: all 0.3s;
        }

        .course-card:hover .action-menu {
            opacity: 1;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: white;
            color: #333;
            border: none;
            transition: all 0.3s;
            margin: 0 0.25rem;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-chalkboard-teacher me-2"></i>Giáo viên</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="my_courses.php" class="active"><i class="fas fa-book"></i>Khóa học của tôi</a></li>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-book me-2"></i>Khóa học của tôi</h2>
                <p class="text-muted mb-0">Quản lý và theo dõi các khóa học bạn đang giảng dạy</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="ratings.php" class="btn btn-outline-dark btn-lg">
                    <i class="fas fa-star me-2"></i>Xem đánh giá
                </a>
                <a href="create_course.php" class="btn btn-warning btn-lg">
                    <i class="fas fa-plus me-2"></i>Tạo khóa học mới
                </a>
            </div>
        </div>

        <?php showAlert(); ?>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="mb-1"><?php echo count($courses); ?></h3>
                    <p class="text-muted mb-0">Tổng khóa học</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['active']; ?></h3>
                    <p class="text-muted mb-0">Đang hoạt động</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $total = 0;
                        foreach ($courses as $c) $total += $c['enrolled_count'];
                        echo $total;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Tổng học viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $total_lessons = 0;
                        foreach ($courses as $c) $total_lessons += $c['lesson_count'];
                        echo $total_lessons;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Tổng bài giảng</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Tìm kiếm</label>
                    <input type="text" class="form-control" name="search"
                        placeholder="Tên khóa học..." value="<?php echo escape($search); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-search me-2"></i>Lọc
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courses)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book fa-4x text-muted mb-4"></i>
                    <h4>Chưa có khóa học nào</h4>
                    <p class="text-muted mb-4">Bắt đầu tạo khóa học đầu tiên của bạn ngay hôm nay!</p>
                    <a href="create_course.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-plus me-2"></i>Tạo khóa học mới
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card course-card">
                            <div class="position-relative">
                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop'); ?>"
                                    class="course-thumbnail" alt="">

                                <span class="course-badge <?php echo $course['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $course['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>

                                <div class="action-menu">
                                    <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="action-btn" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../../views/student/course_detail.php?id=<?php echo $course['course_id']; ?>" class="action-btn" title="Xem">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-warning text-dark"><?php echo escape($course['category_name']); ?></span>
                                    <?php echo getLevelBadge($course['level']); ?>
                                </div>

                                <h5 class="card-title mb-2"><?php echo escape($course['course_name']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo escape(substr($course['description'], 0, 80)); ?>...</p>

                                <div class="d-flex justify-content-between text-muted small mb-3">
                                    <span><i class="fas fa-users me-1"></i><?php echo $course['enrolled_count']; ?> học viên</span>
                                    <span><i class="fas fa-video me-1"></i><?php echo $course['lesson_count']; ?> bài</span>
                                    <span><i class="fas fa-clock me-1"></i><?php echo $course['duration']; ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-warning h5 mb-0"><?php echo formatCurrency($course['price']); ?></strong>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline-warning">
                                            <i class="fas fa-edit me-1"></i>Sửa
                                        </a>
                                        <a href="../../views/student/course_detail.php?id=<?php echo $course['course_id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>