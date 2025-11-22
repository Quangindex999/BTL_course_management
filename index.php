<?php
session_start();
require_once 'functions/db_connection.php';
require_once 'functions/auth.php';
require_once 'functions/course_functions.php';

// Lấy khóa học nổi bật
$featured_courses = getLatestCourses(6);

// Lấy thống kê
$stats = getCourseStats();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Hệ thống Quản lý Khóa học</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1d5c7a;
            --primary-dark: #134152;
            --secondary: #f97316;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --light: #f9fafb;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: var(--dark);
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            min-height: 90vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-image {
            position: relative;
            z-index: 2;
        }

        .hero-image img {
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .btn-primary-custom {
            background: white;
            color: var(--primary);
            border: none;
            padding: 0.875rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: var(--primary-dark);
        }

        /* Stats Section */
        .stats-section {
            background: white;
            padding: 4rem 0;
            margin-top: -60px;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
            color: white;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(22, 143, 112, 0.35);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        /* Course Cards */
        .course-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .course-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .course-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i>Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="views/student/course_detail.php"><i class="fas fa-book me-1"></i>Khóa Học</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-newspaper me-1"></i>Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-envelope me-1"></i>Liên Hệ</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isStudent()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="views/student/my_courses.php">
                                    <i class="fas fa-book-open me-1"></i>Khóa Học Của Tôi
                                </a>
                            </li>
                        <?php elseif (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="views/admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="handle/logout_process.php"><i class="fas fa-sign-out-alt me-1"></i>Đăng Xuất</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-primary-custom ms-3" href="views/login.php">Đăng Nhập</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>Học tập không giới hạn với EduLearn</h1>
                    <p class="fs-5">Khám phá hàng trăm khóa học chất lượng cao, được thiết kế bởi các chuyên gia hàng đầu. Bắt đầu hành trình học tập của bạn ngay hôm nay!</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#featured-courses" class="btn btn-primary-custom">
                            <i class="fas fa-search me-2"></i>Khám Phá Khóa Học
                        </a>
                        <?php if (!isLoggedIn()): ?>
                            <a href="views/register.php" class="btn btn-outline-light btn-lg" style="border-radius: 50px;">
                                <i class="fas fa-user-plus me-2"></i>Đăng Ký Ngay
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 hero-image d-none d-lg-block">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=400&fit=crop" alt="Students Learning" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <i class="fas fa-book-open"></i>
                        <h3><?php echo $stats['total_courses'] ?? 0; ?>+</h3>
                        <p>Khóa Học</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $stats['total_students'] ?? 0; ?>+</h3>
                        <p>Học Viên</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-certificate"></i>
                        <h3><?php echo $stats['total_enrollments'] ?? 0; ?>+</h3>
                        <p>Đăng Ký</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses -->
    <section id="featured-courses" class="py-5">
        <div class="container">
            <h2 class="section-title">Khóa Học Nổi Bật</h2>
            <div class="row g-4">
                <?php foreach ($featured_courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card course-card">
                            <div class="position-relative">
                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop'); ?>" class="card-img-top" alt="<?php echo escape($course['course_name']); ?>">
                                <span class="course-badge"><?php echo $course['level']; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-3 mb-2 text-muted small">
                                    <span><i class="fas fa-clock"></i> <?php echo $course['duration']; ?></span>
                                    <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> học viên</span>
                                </div>
                                <h5 class="card-title"><?php echo escape($course['course_name']); ?></h5>
                                <p class="text-muted"><?php echo escape(substr($course['description'], 0, 80)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="h4 text-primary mb-0"><?php echo formatCurrency($course['price']); ?></div>
                                    <a href="views/student/course_detail.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary">
                                        Xem Chi Tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="views/student/course_detail.php" class="btn btn-primary-custom btn-lg">
                    Xem Tất Cả Khóa Học <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-graduation-cap me-2"></i>EduLearn</h5>
                    <p class="mt-3">Nền tảng học trực tuyến hàng đầu Việt Nam. Cung cấp các khóa học chất lượng cao với giảng viên giàu kinh nghiệm.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên Kết Nhanh</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2"></i>Về Chúng Tôi</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2"></i>Khóa Học</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2"></i>Giảng Viên</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2"></i>Liên Hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên Hệ</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>info@edulearn.vn</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>(+84) 123 456 789</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Hà Nội, Việt Nam</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1)">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 EduLearn. All rights reserved. | Bài tập lớn môn Lập trình PHP</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll cho link "Khám Phá Khóa Học" với animation mượt mà
        document.addEventListener('DOMContentLoaded', function() {
            const exploreLink = document.querySelector('a[href="#featured-courses"]');
            if (exploreLink) {
                exploreLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetSection = document.getElementById('featured-courses');
                    if (targetSection) {
                        // Tính toán vị trí với offset cho navbar sticky
                        const navbarHeight = document.querySelector('.navbar').offsetHeight || 80;
                        const targetPosition = targetSection.offsetTop - navbarHeight;
                        const startPosition = window.pageYOffset;
                        const distance = targetPosition - startPosition;
                        const duration = 800; // Thời gian scroll (ms)
                        let start = null;

                        // Hàm animation smooth scroll
                        function smoothScroll(timestamp) {
                            if (!start) start = timestamp;
                            const progress = timestamp - start;
                            const percentage = Math.min(progress / duration, 1);

                            // Easing function (ease-in-out)
                            const ease = percentage < 0.5 ?
                                2 * percentage * percentage :
                                1 - Math.pow(-2 * percentage + 2, 2) / 2;

                            window.scrollTo(0, startPosition + distance * ease);

                            if (progress < duration) {
                                requestAnimationFrame(smoothScroll);
                            }
                        }

                        requestAnimationFrame(smoothScroll);
                    }
                });
            }
        });
    </script>
</body>

</html>