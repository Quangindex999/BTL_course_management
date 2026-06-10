<?php
session_start();
require_once 'functions/db_connection.php';
require_once 'functions/auth.php';
require_once 'functions/course_functions.php';

$featured_courses = getLatestCourses(6);
$stats = getCourseStats();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Hệ thống Quản lý Khóa học</title>
    <meta name="description" content="EduLearn là nền tảng quản lý khóa học trực tuyến với giao diện hiện đại, rõ ràng và dễ dùng.">
    <meta name="theme-color" content="#14324a">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #f4f7fb;
            --bg-soft: #eef3f8;
            --surface: rgba(255, 255, 255, 0.78);
            --surface-strong: #ffffff;
            --primary: #14324a;
            --primary-soft: rgba(20, 50, 74, 0.08);
            --accent: #0f766e;
            --accent-soft: rgba(15, 118, 110, 0.14);
            --text: #162231;
            --muted: #66758a;
            --border: rgba(20, 50, 74, 0.1);
            --shadow: 0 24px 60px rgba(16, 24, 40, 0.12);
            --shadow-soft: 0 12px 30px rgba(16, 24, 40, 0.08);
            --radius-xl: 28px;
            --radius-lg: 22px;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 10%, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at 85% 0%, rgba(20, 50, 74, 0.14), transparent 24%),
                linear-gradient(180deg, #fbfdff 0%, var(--bg) 45%, var(--bg-soft) 100%);
            overflow-x: hidden;
            text-rendering: optimizeLegibility;
        }

        .container {
            max-width: 1240px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(20, 50, 74, 0.08);
            box-shadow: 0 8px 30px rgba(16, 24, 40, 0.05);
            padding: 0.9rem 0;
        }

        .navbar-brand {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--primary) !important;
        }

        .nav-link {
            color: var(--text) !important;
            font-weight: 600;
            margin: 0 0.2rem;
            border-radius: 999px;
            padding: 0.65rem 0.95rem !important;
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary) !important;
            background: rgba(20, 50, 74, 0.06);
        }

        .hero-section {
            min-height: 92dvh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 4.5rem 0 5rem;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(12, 22, 33, 0.9) 0%, rgba(20, 50, 74, 0.82) 52%, rgba(15, 118, 110, 0.8) 100%),
                url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1600&h=900&fit=crop') center/cover;
            transform: scale(1.03);
        }

        .hero-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.15) 1px, transparent 0);
            background-size: 30px 30px;
            opacity: 0.12;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            backdrop-filter: blur(10px);
        }

        .hero-content h1 {
            font-size: clamp(2.9rem, 6vw, 5.6rem);
            font-weight: 900;
            letter-spacing: -0.06em;
            line-height: 0.92;
            margin-bottom: 1.25rem;
            text-wrap: balance;
        }

        .hero-content p {
            max-width: 36rem;
            color: rgba(255, 255, 255, 0.84);
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .hero-image {
            position: relative;
            z-index: 2;
        }

        .hero-image img {
            border-radius: 30px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transform: rotate(1.25deg);
            animation: float 7s ease-in-out infinite;
        }

        @keyframes float {
            0%,
            100% {
                transform: translateY(0) rotate(1.25deg);
            }

            50% {
                transform: translateY(-14px) rotate(0.5deg);
            }
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #ffffff 0%, #edf4fb 100%);
            color: var(--primary);
            border: 1px solid rgba(255, 255, 255, 0.55);
            padding: 0.92rem 1.45rem;
            font-weight: 800;
            border-radius: 999px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            color: var(--primary);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.16);
        }

        .btn-primary-custom:active {
            transform: translateY(0);
        }

        .btn-outline-light {
            border-radius: 999px;
            padding: 0.92rem 1.45rem;
            border-width: 1px;
        }

        .stats-section {
            padding: 0 0 4rem;
            margin-top: -2.25rem;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            text-align: left;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            background: var(--surface);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(16px);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: var(--primary-soft);
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            margin-bottom: 0.2rem;
        }

        .stat-card p {
            margin: 0;
            color: var(--muted);
            font-weight: 500;
        }

        .section-title {
            font-size: clamp(1.8rem, 3vw, 3rem);
            font-weight: 900;
            text-align: left;
            letter-spacing: -0.05em;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: var(--muted);
            max-width: 52rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .course-card {
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: var(--shadow-soft);
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
            height: 100%;
        }

        .course-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow);
            border-color: rgba(20, 50, 74, 0.18);
        }

        .course-card img {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .course-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(255, 255, 255, 0.92);
            color: var(--primary);
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            border: 1px solid rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(10px);
        }

        .course-card .card-body {
            padding: 1.25rem;
        }

        .course-card .card-title {
            font-size: 1.12rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
        }

        .course-meta {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .course-price {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--primary);
            white-space: nowrap;
        }

        .section-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 800;
        }

        .section-link:hover {
            text-decoration: underline;
        }

        footer {
            background: linear-gradient(180deg, #10263a 0%, #0c1d2c 100%);
            position: relative;
        }

        footer::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.08) 1px, transparent 0);
            background-size: 24px 24px;
            opacity: 0.35;
            pointer-events: none;
        }

        footer .container {
            position: relative;
            z-index: 1;
        }

        footer a {
            color: rgba(255, 255, 255, 0.86) !important;
        }

        .footer-note {
            color: rgba(255, 255, 255, 0.68);
        }

        .navbar-toggler {
            border: 1px solid rgba(20, 50, 74, 0.12);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(20, 50, 74, 0.15);
        }

        @media (max-width: 991.98px) {
            .hero-section {
                min-height: auto;
                padding: 3.5rem 0 4rem;
            }

            .hero-content h1 {
                line-height: 1.02;
            }

            .section-title {
                text-align: center;
            }

            .section-subtitle {
                margin-left: auto;
                margin-right: auto;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Mở menu điều hướng">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i>Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="views/student/course_detail.php"><i class="fas fa-book me-1"></i>Khóa học</a></li>
                    <li class="nav-item"><a class="nav-link" href="#featured-courses"><i class="fas fa-grid-2 me-1"></i>Nổi bật</a></li>
                    <li class="nav-item"><a class="nav-link" href="#footer-contact"><i class="fas fa-envelope me-1"></i>Liên hệ</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isStudent()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="views/student/my_courses.php"><i class="fas fa-book-open me-1"></i>Khóa học của tôi</a>
                            </li>
                        <?php elseif (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="views/admin/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="handle/logout_process.php"><i class="fas fa-sign-out-alt me-1"></i>Đăng xuất</a></li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary-custom" href="views/login.php">Đăng nhập</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 hero-content">
                    <div class="eyebrow"><i class="fas fa-sparkles"></i> Học tập có chiều sâu, giao diện có gu</div>
                    <h1>Học tập không giới hạn với EduLearn</h1>
                    <p class="fs-5">Khám phá các khóa học được chọn lọc kỹ, trình bày rõ ràng và dễ theo dõi. Trải nghiệm học tập mạch lạc hơn, đẹp hơn, và tập trung hơn từ trang đầu tiên.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="#featured-courses" class="btn btn-primary-custom"><i class="fas fa-search me-2"></i>Khám phá khóa học</a>
                        <?php if (!isLoggedIn()): ?>
                            <a href="views/register.php" class="btn btn-outline-light btn-lg"><i class="fas fa-user-plus me-2"></i>Đăng ký ngay</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 hero-image d-none d-lg-block">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=400&fit=crop" alt="Sinh viên đang học tập" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                        <h3><?php echo $stats['total_courses'] ?? 0; ?>+</h3>
                        <p>Khóa học</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <h3><?php echo $stats['total_students'] ?? 0; ?>+</h3>
                        <p>Học viên</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-certificate"></i></div>
                        <h3><?php echo $stats['total_enrollments'] ?? 0; ?>+</h3>
                        <p>Đăng ký</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="featured-courses" class="py-5">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
                <div>
                    <h2 class="section-title mb-2">Khóa học nổi bật</h2>
                    <p class="section-subtitle mb-0">Bố cục thoáng hơn, tương phản tốt hơn và khoảng trắng được cân chỉnh để nội dung dễ đọc, dễ chọn hơn.</p>
                </div>
                <a href="views/student/course_detail.php" class="section-link">Xem toàn bộ danh mục <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="row g-4">
                <?php foreach ($featured_courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card course-card">
                            <div class="position-relative">
                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop'); ?>" class="card-img-top" alt="<?php echo escape($course['course_name']); ?>">
                                <span class="course-badge"><?php echo escape($course['level']); ?></span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex flex-wrap gap-3 mb-3 course-meta">
                                    <span><i class="fas fa-clock me-1"></i> <?php echo escape($course['duration']); ?></span>
                                    <span><i class="fas fa-users me-1"></i> <?php echo (int) $course['enrolled_count']; ?> học viên</span>
                                </div>
                                <h5 class="card-title"><?php echo escape($course['course_name']); ?></h5>
                                <p class="text-muted mb-4" style="line-height: 1.7;">
                                    <?php echo escape(substr($course['description'], 0, 80)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center gap-3 mt-auto">
                                    <div class="course-price"><?php echo formatCurrency($course['price']); ?></div>
                                    <a href="views/student/course_detail.php?id=<?php echo (int) $course['course_id']; ?>" class="btn btn-primary-custom">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="views/student/course_detail.php" class="btn btn-primary-custom btn-lg">Xem tất cả khóa học <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        </div>
    </section>

    <footer id="footer-contact" class="text-white py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h5 class="fw-bold"><i class="fas fa-graduation-cap me-2"></i>EduLearn</h5>
                    <p class="mt-3 footer-note">Nền tảng học trực tuyến với giao diện gọn, sang và dễ dùng hơn. Tập trung vào trải nghiệm học tập rõ ràng, ít nhiễu, nhiều giá trị.</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold">Liên kết nhanh</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><a href="#featured-courses" class="text-decoration-none"><i class="fas fa-angle-right me-2"></i>Khóa học nổi bật</a></li>
                        <li class="mb-2"><a href="views/student/course_detail.php" class="text-decoration-none"><i class="fas fa-angle-right me-2"></i>Tất cả khóa học</a></li>
                        <li class="mb-2"><a href="views/login.php" class="text-decoration-none"><i class="fas fa-angle-right me-2"></i>Đăng nhập</a></li>
                        <li class="mb-2"><a href="views/register.php" class="text-decoration-none"><i class="fas fa-angle-right me-2"></i>Đăng ký</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold">Liên hệ</h5>
                    <ul class="list-unstyled mt-3 footer-note">
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>info@edulearn.vn</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>(+84) 123 456 789</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Hà Nội, Việt Nam</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1)">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <p class="mb-0 footer-note">&copy; 2026 EduLearn. All rights reserved. | Bài tập lớn môn Lập trình PHP</p>
                <a href="#top" class="text-decoration-none footer-note">Trở lên đầu trang</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>