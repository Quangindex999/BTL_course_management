<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/course_functions.php';
require_once '../../functions/enrollments_functions.php';
require_once '../../functions/notification_functions.php';
require_once '../../functions/ratings_functions.php';

requireStudent();

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';
$allowed_statuses = ['all', 'approved', 'pending', 'completed', 'rejected'];
if (!in_array($status_filter, $allowed_statuses, true)) {
    $status_filter = 'all';
}

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 6;
$stats = getUserEnrollmentStats($user_id);
$enrollment_result = getUserEnrollmentsPaginated(
    $user_id,
    $status_filter === 'all' ? null : $status_filter,
    $page,
    $per_page
);
$my_courses = $enrollment_result['enrollments'];
$pagination = $enrollment_result['pagination'];

$avatar = getAvatarUrl($_SESSION['avatar'] ?? '', $_SESSION['full_name'] ?? 'User', 36, 'https://i.pravatar.cc/100?img=12');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khóa học của tôi - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --accent: #f59e0b;
            --text: #111827;
            --muted: #6b7280;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --border: #e5e7eb;
        }

        /* Remove underline for links globally */
        a,
        a:hover,
        .nav-link,
        .dropdown-item,
        .filter-pill {
            text-decoration: none;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface-2);
            min-height: 100vh;
            color: var(--text);
        }

        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--text);
            font-size: 1.3rem;
        }

        .navbar-brand:hover {
            color: #374151 !important;
            background-color: #cfd2d7;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .navbar .nav-link,
        .navbar .dropdown-toggle {
            color: #374151;
            font-weight: 500;
        }

        .navbar .nav-link:hover,
        .navbar .dropdown-toggle:hover {
            color: var(--primary);
        }

        .hero-section {
            position: relative;
            padding: 3rem 0 2rem;
            color: var(--text);
        }

        .hero-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.04);
        }

        .hero-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1.25rem;
        }

        .stat-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            color: var(--text);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: var(--primary-dark);
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .content-wrapper {
            margin-top: 0;
            padding-bottom: 3rem;
        }

        .glass-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.04);
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .filter-pill {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 0.45rem 1rem;
            font-weight: 600;
            background: var(--surface);
            color: #374151;
            transition: all 0.2s ease;
        }

        .filter-pill.active,
        .filter-pill:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: var(--primary-dark);
            box-shadow: none;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .course-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
        }

        .course-thumb {
            position: relative;
            height: 170px;
            overflow: hidden;
        }

        .course-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .course-card:hover .course-thumb img {
            transform: scale(1.03);
        }

        .status-chip {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.78rem;
            color: #111827;
            background: #fff;
            border: 1px solid var(--border);
        }

        .status-pending {
            color: #92400e;
            background: #fef3c7;
            border-color: #fde68a;
        }

        .status-approved {
            color: #065f46;
            background: #d1fae5;
            border-color: #a7f3d0;
        }

        .status-completed {
            color: #1e40af;
            background: #dbeafe;
            border-color: #bfdbfe;
        }

        .status-rejected {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fecaca;
        }

        .course-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .course-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .course-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }

        .course-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            color: var(--text);
        }

        .course-desc {
            color: var(--muted);
            font-size: 0.92rem;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .progress-wrapper {
            margin-bottom: 0.75rem;
        }

        .progress {
            height: 8px;
            border-radius: 999px;
            background: #e5e7eb;
        }

        .progress-bar {
            background: var(--primary);
        }

        .badge-payment {
            font-size: 0.72rem;
            border-radius: 999px;
            padding: 0.3rem 0.6rem;
            font-weight: 600;
        }

        .course-actions {
            margin-top: auto;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .action-group .btn {
            white-space: nowrap;
        }

        .btn-outline-dark {
            border-width: 1.5px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 0;
            color: var(--text);
        }

        .empty-state-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #eef2ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 991px) {
            .hero-card {
                padding: 1.25rem;
            }

            .hero-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="studentNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <li class="nav-item"><a class="nav-link active" href="my_courses.php">Khóa học của tôi</a></li>
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo $avatar; ?>" alt="Avatar" class="rounded-circle" width="36" height="36">
                            <span><?php echo escape($_SESSION['full_name'] ?? 'Học viên'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="menu_student.php"><i class="fas fa-user-circle me-2"></i>Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container position-relative">
            <div class="hero-card">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <p class="mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.15rem; opacity: 0.85;">
                            Lộ trình học tập cá nhân hóa
                        </p>
                        <h1 class="hero-title mb-3">
                            Xin chào, <?php echo escape($_SESSION['full_name'] ?? 'Học viên'); ?>!
                        </h1>
                        <p class="mb-4 fs-5" style="opacity: 0.9;">
                            Theo dõi tiến độ học tập, quản lý khóa học đã đăng ký và tiếp tục chinh phục mục tiêu mới mỗi ngày.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="course_detail.php" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-plus-circle me-2"></i>Đăng ký khóa học mới
                            </a>
                            <a href="<?php echo SITE_URL; ?>/index.php#featured-courses" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-compass me-2"></i>Khám phá thêm
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5 mt-4 mt-lg-0">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <p class="stat-label">Tổng khóa học</p>
                                <p class="stat-value"><?php echo number_format($stats['total']); ?></p>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2);">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <p class="stat-label">Đang học</p>
                                <p class="stat-value"><?php echo number_format($stats['approved'] ?? 0); ?></p>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.2);">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <p class="stat-label">Chờ duyệt</p>
                                <p class="stat-value"><?php echo number_format($stats['pending'] ?? 0); ?></p>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background: rgba(236, 72, 153, 0.2);">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <p class="stat-label">Tổng học phí đã thanh toán</p>
                                <p class="stat-value"><?php echo formatCurrency($stats['total_paid']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-wrapper">
        <div class="container">
            <div class="glass-card">
                <?php echo showAlert(); ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <div>
                        <h3 class="fw-bold mb-1">Danh sách khóa học</h3>
                        <p class="mb-0 text-muted">Lọc theo trạng thái để xem nhanh các khóa học của bạn</p>
                    </div>
                    <div class="filters">
                        <?php
                        $statuses = [
                            'all' => 'Tất cả',
                            'approved' => 'Đang học',
                            'pending' => 'Chờ duyệt',
                            'completed' => 'Hoàn thành',
                            'rejected' => 'Từ chối'
                        ];
                        foreach ($statuses as $key => $label):
                        ?>
                            <a class="filter-pill <?php echo $status_filter === $key ? 'active' : ''; ?>"
                                href="?status=<?php echo $key; ?>">
                                <?php echo $label; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (empty($my_courses)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Bạn chưa đăng ký khóa học nào</h4>
                        <p class="mb-4" style="opacity: 0.8;">Khám phá hơn 100+ khóa học hấp dẫn và bắt đầu hành trình học tập của bạn.</p>
                        <a href="course_detail.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-compass me-2"></i>Khám phá khóa học
                        </a>
                    </div>
                <?php else: ?>
                    <div class="course-grid">
                        <?php foreach ($my_courses as $enrollment):
                            $progress = null;
                            if ($enrollment['status'] === 'approved') {
                                $progress = getCourseProgress($user_id, $enrollment['course_id']);
                            }
                        ?>
                            <article class="course-card">
                                <div class="course-thumb">
                                    <img src="<?php echo getImageUrl($enrollment['thumbnail'], 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&h=400&fit=crop'); ?>" alt="<?php echo escape($enrollment['course_name']); ?>">
                                    <span class="status-chip status-<?php echo $enrollment['status']; ?>">
                                        <?php echo match ($enrollment['status']) {
                                            'approved' => 'Đang học',
                                            'completed' => 'Hoàn thành',
                                            'pending' => 'Chờ duyệt',
                                            'rejected' => 'Bị từ chối',
                                            default => ucfirst($enrollment['status'])
                                        }; ?>
                                    </span>
                                </div>
                                <div class="course-body">
                                    <div class="course-meta">
                                        <span><i class="fas fa-user-tie"></i><?php echo escape($enrollment['instructor_name']); ?></span>
                                        <span><i class="fas fa-clock"></i><?php echo escape($enrollment['duration']); ?></span>
                                    </div>
                                    <h4 class="course-title"><?php echo escape($enrollment['course_name']); ?></h4>
                                    <p class="course-desc"><?php echo escape(substr($enrollment['category_name'] ?? 'Khóa học trực tuyến', 0, 80)); ?></p>

                                    <?php if ($progress): ?>
                                        <div class="progress-wrapper">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: .08rem;">
                                                    Tiến độ học tập
                                                </span>
                                                <span class="fw-semibold"><?php echo $progress['percentage']; ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress['percentage']; ?>%;"></div>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <?php echo $progress['completed_lessons']; ?> / <?php echo $progress['total_lessons']; ?> bài học đã hoàn thành
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <div class="course-actions">
                                        <div class="action-group">
                                            <a href="course_detail.php?id=<?php echo $enrollment['course_id']; ?>" class="btn btn-outline-dark btn-sm px-3">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </a>
                                            <?php if ($enrollment['status'] === 'approved'): ?>
                                                <a href="course_detail.php?id=<?php echo $enrollment['course_id']; ?>#lessons" class="btn btn-dark btn-sm px-3">
                                                    <i class="fas fa-play me-1"></i>Tiếp tục học
                                                </a>
                                                <?php if ($enrollment['payment_status'] === 'unpaid'): ?>
                                                    <a href="payment.php?id=<?php echo $enrollment['enrollment_id']; ?>" class="btn btn-warning btn-sm px-3">
                                                        <i class="fas fa-credit-card me-1"></i>Thanh toán
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Đánh giá bên dưới</span>
                                                <?php endif; ?>
                                            <?php elseif ($enrollment['status'] === 'pending'): ?>
                                                <a href="../../handle/cancel_process.php?id=<?php echo $enrollment['enrollment_id']; ?>"
                                                    class="btn btn-outline-danger btn-sm px-3"
                                                    onclick="return confirm('Bạn chắc chắn muốn hủy đăng ký khóa học này?');">
                                                    <i class="fas fa-times me-1"></i>Hủy đăng ký
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?php echo $enrollment['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark'; ?> badge-payment">
                                            <?php echo $enrollment['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                        </span>
                                    </div>

                                    <?php if ($enrollment['status'] === 'approved' && $enrollment['payment_status'] === 'paid'):
                                        $user_rating = getUserRating($user_id, $enrollment['course_id']);
                                    ?>
                                        <div class="mt-3 p-3 border rounded bg-light-subtle">
                                            <h6 class="mb-2"><i class="fas fa-star me-2 text-warning"></i>Đánh giá nhanh</h6>
                                            <form method="POST" action="../../handle/rating_process.php" class="small">
                                                <input type="hidden" name="action" value="submit_rating">
                                                <input type="hidden" name="course_id" value="<?php echo $enrollment['course_id']; ?>">
                                                <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['enrollment_id']; ?>">

                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Số sao</label>
                                                    <select name="rating" class="form-select form-select-sm" required>
                                                        <option value="">Chọn số sao</option>
                                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                                            <option value="<?php echo $i; ?>" <?php echo ($user_rating && $user_rating['rating'] == $i) ? 'selected' : ''; ?>>
                                                                <?php echo $i; ?> sao
                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label mb-1" for="review<?php echo $enrollment['course_id']; ?>">Nhận xét (tùy chọn)</label>
                                                    <textarea class="form-control form-control-sm" name="review" id="review<?php echo $enrollment['course_id']; ?>"
                                                        rows="3" placeholder="Cảm nhận của bạn..."><?php echo escape($user_rating['review'] ?? ''); ?></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <?php echo $user_rating ? 'Cập nhật' : 'Gửi'; ?> đánh giá
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
                        <?php
                        $query = $_GET;
                        unset($query['page']);
                        $buildPageUrl = function ($pageNumber) use ($query) {
                            $params = $query;
                            $params['page'] = $pageNumber;
                            return '?' . http_build_query($params);
                        };
                        ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $pagination['has_prev'] ? '' : 'disabled'; ?>">
                                    <a class="page-link"
                                        href="<?php echo $pagination['has_prev'] ? htmlspecialchars($buildPageUrl($pagination['prev_page'])) : '#'; ?>">
                                        <i class="fas fa-chevron-left me-1"></i>Trước
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($buildPageUrl($i)); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagination['has_next'] ? '' : 'disabled'; ?>">
                                    <a class="page-link"
                                        href="<?php echo $pagination['has_next'] ? htmlspecialchars($buildPageUrl($pagination['next_page'])) : '#'; ?>">
                                        Sau <i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>