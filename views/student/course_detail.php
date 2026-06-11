<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/course_functions.php';
require_once '../../functions/enrollments_functions.php';
require_once '../../functions/notification_functions.php';

function generatePlaceholderLessons($courseName)
{
    return [
        [
            'lesson_title' => 'Giới thiệu khóa học ' . $courseName,
            'duration' => '08 phút'
        ],
        [
            'lesson_title' => 'Các kiến thức nền tảng quan trọng',
            'duration' => '15 phút'
        ],
        [
            'lesson_title' => 'Thực hành với ví dụ thực tế',
            'duration' => '20 phút'
        ],
        [
            'lesson_title' => 'Ứng dụng kỹ năng vào dự án nhỏ',
            'duration' => '18 phút'
        ],
        [
            'lesson_title' => 'Tổng kết và định hướng tiếp theo',
            'duration' => '10 phút'
        ],
    ];
}

// Nếu có ID, hiển thị chi tiết, nếu không thì hiển thị danh sách
$course_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$is_placeholder_lessons = false;
$is_enrolled = false;
$related_courses = [];

if ($course_id) {
    // Chi tiết khóa học
    $result = getCourseById($course_id);
    if (!$result['success']) {
        setAlert('Không tìm thấy khóa học', 'error');
        redirect(SITE_URL . '/views/student/course_detail.php');
    }
    $course = $result['course'];
    $is_placeholder_lessons = false;

    if (empty($course['lessons'])) {
        $is_placeholder_lessons = true;
        $course['lessons'] = generatePlaceholderLessons($course['course_name']);
        $course['lesson_count'] = count($course['lessons']);
    }

    // Kiểm tra đã đăng ký chưa
    $is_enrolled = isLoggedIn() ? isEnrolled($_SESSION['user_id'], $course_id) : false;

    // Khóa học liên quan
    $related_courses = getRelatedCourses($course_id, $course['category_id'], 3);
} else {
    // Danh sách khóa học
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $level = $_GET['level'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));

    $courses_result = getCourses([
        'search' => $search,
        'category_id' => $category,
        'level' => $level,
        'page' => $page,
        'per_page' => 9
    ]);

    $courses = $courses_result['courses'] ?? [];
    $pagination = $courses_result['pagination'] ?? [];
    $categories = getCategories();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course_id ? escape($course['course_name']) : 'Danh sách khóa học'; ?> - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/student.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../../index.php"><i class="fas fa-home me-1"></i>Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link active" href="course_detail.php"><i class="fas fa-book me-1"></i>Khóa Học</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isStudent()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="my_courses.php">
                                    <i class="fas fa-book-open me-1"></i>Khóa Học Của Tôi
                                </a>
                            </li>
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
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt me-1"></i>Đăng Xuất</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-primary ms-2" href="../../views/login.php">Đăng Nhập</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($course_id) { ?>
        <!-- Chi tiết khóa học -->
        <div class="course-hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-3">
                                <li class="breadcrumb-item"><a href="../../index.php">Trang chủ</a></li>
                                <li class="breadcrumb-item"><a href="course_detail.php">Khóa học</a></li>
                                <li class="breadcrumb-item active"><?php echo escape($course['course_name']); ?></li>
                            </ol>
                        </nav>
                        <h1><?php echo escape($course['course_name']); ?></h1>
                        <p class="fs-5 mb-4"><?php echo escape($course['description']); ?></p>
                        <div class="course-hero-info">
                            <div>
                                <i class="fas fa-user-tie"></i>
                                <span><?php echo escape($course['instructor_name']); ?></span>
                            </div>
                            <div>
                                <i class="fas fa-users"></i>
                                <span><?php echo number_format($course['enrolled_count']); ?> học viên</span>
                            </div>
                            <div>
                                <i class="fas fa-clock"></i>
                                <span><?php echo $course['duration']; ?></span>
                            </div>
                            <div>
                                <?php echo getLevelBadge($course['level']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <?php showAlert(); ?>
            <div class="row">
                <div class="col-lg-8">
                    <!-- Course Content -->
                    <div class="course-content-card">
                        <h4><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin khóa học</h4>
                        <p><?php echo nl2br(escape($course['description'])); ?></p>

                        <hr class="my-4">

                        <h5><i class="fas fa-check-circle me-2 text-success"></i>Bạn sẽ học được gì?</h5>
                        <ul class="learning-points">
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Nắm vững kiến thức cơ bản và nâng cao</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Thực hành với các dự án thực tế</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Nhận chứng chỉ sau khi hoàn thành</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Hỗ trợ từ giảng viên 24/7</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Lessons -->
                    <?php if (!empty($course['lessons'])): ?>
                        <div class="lessons-card">
                            <div class="lessons-header">
                                <h5>
                                    <i class="fas fa-list me-2 text-primary"></i>
                                    Nội dung khóa học (<?php echo count($course['lessons']); ?> bài)
                                </h5>
                            </div>
                            <?php if ($is_placeholder_lessons): ?>
                                <div class="alert alert-info m-4 mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Nội dung chi tiết của khóa học đang được cập nhật, hiện tại đây là bản xem trước giao diện.
                                </div>
                            <?php endif; ?>
                            <div class="card-body p-0">
                                <?php foreach ($course['lessons'] as $index => $lesson): ?>
                                    <div class="lesson-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <span class="badge bg-primary rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                        <?php echo $index + 1; ?>
                                                    </span>
                                                    <h6 class="mb-0"><?php echo escape($lesson['lesson_title']); ?></h6>
                                                </div>
                                                <small class="ms-5">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo escape($lesson['duration']); ?>
                                                </small>
                                            </div>
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="course-info-box">
                        <img src="<?php echo getImageUrl($course['thumbnail'], 'https://via.placeholder.com/400x250'); ?>"
                            class="course-thumbnail" alt="<?php echo escape($course['course_name']); ?>">

                        <div class="course-price"><?php echo formatCurrency($course['price']); ?></div>

                        <?php if ($is_enrolled): ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Bạn đã đăng ký khóa học này</strong>
                            </div>
                            <a href="my_courses.php" class="btn btn-enroll w-100 text-white mb-3">
                                <i class="fas fa-book me-2"></i>Vào học ngay
                            </a>
                        <?php else: ?>
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="../../handle/enroll_process.php">
                                    <input type="hidden" name="action" value="enroll">
                                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                    <button type="submit" class="btn btn-enroll w-100 text-white mb-3">
                                        <i class="fas fa-shopping-cart me-2"></i>Đăng ký ngay
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="../../views/login.php" class="btn btn-enroll w-100 text-white mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để đăng ký
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <hr class="my-4">

                        <h6 class="mb-3 fw-bold">Khóa học bao gồm:</h6>
                        <ul class="course-features">
                            <li>
                                <i class="fas fa-video"></i>
                                <span><?php echo $course['lesson_count'] ?? count($course['lessons'] ?? []); ?> bài học</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span><?php echo $course['duration']; ?></span>
                            </li>
                            <li>
                                <i class="fas fa-certificate"></i>
                                <span>Chứng chỉ hoàn thành</span>
                            </li>
                            <li>
                                <i class="fas fa-infinity"></i>
                                <span>Truy cập trọn đời</span>
                            </li>
                            <li>
                                <i class="fas fa-mobile-alt"></i>
                                <span>Học trên mọi thiết bị</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Related Courses -->
            <?php if (!empty($related_courses)): ?>
                <div class="related-courses-section">
                    <h4><i class="fas fa-fire me-2 text-primary"></i>Khóa học liên quan</h4>
                    <div class="row g-4">
                        <?php foreach ($related_courses as $related): ?>
                            <div class="col-md-4">
                                <div class="card course-card">
                                    <img src="<?php echo getImageUrl($related['thumbnail'], 'https://via.placeholder.com/400x250'); ?>"
                                        class="card-img-top" alt="<?php echo escape($related['course_name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo escape($related['course_name']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo escape(substr($related['description'], 0, 80)); ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="text-primary fs-5"><?php echo formatCurrency($related['price']); ?></strong>
                                            <a href="?id=<?php echo $related['course_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>Xem
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php } else { ?>
        <!-- Danh sách khóa học -->
        <div class="course-list-hero">
            <div class="container">
                <h2>Khám phá khóa học</h2>
                <p>Tìm kiếm khóa học phù hợp với bạn</p>
            </div>
        </div>

        <div class="container py-5">
            <?php showAlert(); ?>
            <div class="row">
                <div class="col-lg-3">
                    <div class="filter-card">
                        <h6><i class="fas fa-search me-2 text-primary"></i>Tìm kiếm</h6>
                        <form method="GET">
                            <input type="text" class="form-control mb-3" name="search"
                                placeholder="Tìm khóa học..." value="<?php echo escape($search); ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Tìm kiếm
                            </button>
                        </form>
                    </div>

                    <div class="filter-card">
                        <h6><i class="fas fa-folder me-2 text-primary"></i>Danh mục</h6>
                        <a href="?" class="category-link <?php echo !$category ? 'active' : ''; ?>">
                            <i class="fas fa-th me-2"></i>Tất cả
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="?category=<?php echo $cat['category_id']; ?>"
                                class="category-link <?php echo $category == $cat['category_id'] ? 'active' : ''; ?>">
                                <i class="fas fa-folder-open me-2"></i>
                                <?php echo escape($cat['category_name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-9">
                    <?php if (empty($courses)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy khóa học nào</h5>
                            <p class="text-muted">Thử tìm kiếm với từ khóa khác</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($courses as $c): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card course-card h-100">
                                        <img src="<?php echo getImageUrl($c['thumbnail'], 'https://via.placeholder.com/400x250'); ?>"
                                            class="card-img-top" alt="<?php echo escape($c['course_name']); ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo escape($c['course_name']); ?></h5>
                                            <p class="text-muted small mb-3"><?php echo escape(substr($c['description'], 0, 80)); ?>...</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="text-primary fs-5"><?php echo formatCurrency($c['price']); ?></strong>
                                                <a href="?id=<?php echo $c['course_id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                            <nav class="mt-5">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['current_page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>