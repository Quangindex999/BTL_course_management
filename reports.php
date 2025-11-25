<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/course_functions.php';
require_once '../../functions/enrollments_functions.php';
require_once '../../functions/ratings_functions.php';
require_once '../../functions/report_functions.php';

requireAdmin();

$course_stats = getCourseStats();
$enrollment_stats = getEnrollmentStats();
$revenue_trend = getMonthlyRevenueTrend(6);
$category_share = getCategoryEnrollmentShare();
$rating_distribution = getRatingDistribution();
$top_courses = getTopCoursesByRevenue(5);
$recent_ratings = getRecentRatings(6);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary: #1d5c7a;
            --secondary: #f97316;
            --accent: #14b8a6;
            --muted: #6b7280;
            --surface: #f9fafb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ecf2f7;
            color: #0f172a;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(135deg, #1d5c7a 0%, #168f70 100%);
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

        .sidebar-menu li {
            margin-bottom: 0.25rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .insight-card {
            background: white;
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(13, 38, 76, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .insight-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
        }

        .insight-label {
            font-size: 0.9rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .insight-value {
            font-size: 2rem;
            font-weight: 800;
            margin: 0.25rem 0;
        }

        .insight-trend {
            font-size: 0.9rem;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 15px 45px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(15, 23, 42, 0.04);
            height: 100%;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-header h5 {
            margin-bottom: 0;
            font-weight: 700;
        }

        .badge-soft {
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-soft.success {
            background: rgba(16, 185, 129, 0.12);
            color: #0f9d58;
        }

        .badge-soft.warning {
            background: rgba(251, 146, 60, 0.15);
            color: #f97316;
        }

        .badge-soft.info {
            background: rgba(14, 165, 233, 0.15);
            color: #0ea5e9;
        }

        .top-course {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            margin-bottom: 1rem;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }

        .top-course:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }

        .top-course img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            margin-right: 1rem;
        }

        .rating-item {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
        }

        .rating-stars {
            color: #fbbf24;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-graduation-cap me-2"></i>EduLearn Admin
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="reports.php" class="active"><i class="fas fa-chart-line"></i>Báo cáo</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i>Quản lý Khóa học</a></li>
            <li><a href="create_courses.php"><i class="fas fa-plus-circle"></i>Thêm Khóa học</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i>Quản lý Người dùng</a></li>
            <li><a href="create_users.php"><i class="fas fa-user-plus"></i>Thêm Người dùng</a></li>
            <li><a href="manage_enrollments.php"><i class="fas fa-file-invoice"></i>Quản lý Đăng ký</a></li>
            <li>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 1.5rem;">
            </li>
            <li><a href="../../index.php"><i class="fas fa-home"></i>Về Trang chủ</a></li>
            <li><a href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="mb-0">Báo cáo nâng cao</h4>
                    <span class="badge-soft info">Realtime</span>
                </div>
                <small class="text-muted">Xin chào, <?php echo escape($_SESSION['full_name']); ?>! 👋</small>
            </div>
            <div class="text-end">
                <div class="text-muted"><i class="far fa-calendar me-2"></i><?php echo date('l, d/m/Y'); ?></div>
                <div class="text-muted"><i class="far fa-clock me-2"></i><?php echo date('H:i'); ?></div>
            </div>
        </div>

        <?php showAlert(); ?>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="insight-card">
                    <div class="insight-label">Doanh thu tích lũy</div>
                    <div class="insight-value text-primary"><?php echo formatCurrency($course_stats['total_revenue'] ?? 0); ?></div>
                    <div class="insight-trend text-success">
                        <i class="fas fa-arrow-up"></i> +12.4% so với tháng trước
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="insight-card">
                    <div class="insight-label">Học viên đang hoạt động</div>
                    <div class="insight-value text-success"><?php echo $course_stats['total_students'] ?? 0; ?></div>
                    <div class="insight-trend text-muted">
                        <?php echo $enrollment_stats['approved'] ?? 0; ?> đã duyệt
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="insight-card">
                    <div class="insight-label">Tỷ lệ hoàn thành</div>
                    <div class="insight-value text-warning">
                        <?php
                        $completed = $enrollment_stats['completed'] ?? 0;
                        $total = max(1, $enrollment_stats['total'] ?? 1);
                        echo round(($completed / $total) * 100, 1) . '%';
                        ?>
                    </div>
                    <div class="insight-trend text-muted">
                        <?php echo $completed; ?> khóa học hoàn tất
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="insight-card">
                    <div class="insight-label">Đánh giá trung bình</div>
                    <div class="insight-value text-secondary">
                        <?php echo $rating_distribution['average']; ?>
                        <small class="fs-6 text-muted">/ 5</small>
                    </div>
                    <div class="insight-trend text-muted">
                        <?php echo $rating_distribution['total_reviews']; ?> lượt đánh giá
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="chart-card mb-4">
                    <div class="chart-header">
                        <div>
                            <h5><i class="fas fa-signal text-primary me-2"></i>Xu hướng doanh thu & đăng ký</h5>
                            <small class="text-muted">6 tháng gần nhất</small>
                        </div>
                        <span class="badge-soft success">Live synced</span>
                    </div>
                    <canvas id="revenueChart" height="110"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-card mb-4">
                    <div class="chart-header">
                        <h5><i class="fas fa-chart-pie text-warning me-2"></i>Theo danh mục</h5>
                        <span class="badge-soft warning">Top categories</span>
                    </div>
                    <canvas id="categoryChart" height="260"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="chart-card mb-4">
                    <div class="chart-header">
                        <h5><i class="fas fa-star text-warning me-2"></i>Phân bố đánh giá sao</h5>
                        <span class="badge-soft info">Chất lượng nội dung</span>
                    </div>
                    <canvas id="ratingChart" height="160"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card mb-4">
                    <div class="chart-header">
                        <h5><i class="fas fa-crown text-primary me-2"></i>Top khóa học doanh thu</h5>
                        <span class="badge-soft success">Insight</span>
                    </div>
                    <?php if (!empty($top_courses)): ?>
                        <?php foreach ($top_courses as $course): ?>
                            <div class="top-course">
                                <img src="<?php echo getImageUrl($course['thumbnail'], 'https://via.placeholder.com/80'); ?>" alt="thumb">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1"><?php echo escape($course['course_name']); ?></h6>
                                        <strong class="text-primary"><?php echo formatCurrency($course['revenue']); ?></strong>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo escape($course['category_name']); ?> • <?php echo $course['total_students']; ?> học viên
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Chưa có dữ liệu doanh thu.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5><i class="fas fa-comments text-secondary me-2"></i>Phản hồi mới nhất</h5>
                        <span class="badge-soft info">Voice of students</span>
                    </div>
                    <?php if (!empty($recent_ratings)): ?>
                        <?php foreach ($recent_ratings as $rating): ?>
                            <div class="rating-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong><?php echo escape($rating['student_name']); ?></strong>
                                    <small class="text-muted"><?php echo formatDate($rating['created_at']); ?></small>
                                </div>
                                <div class="text-muted mb-1"><?php echo escape($rating['course_name']); ?></div>
                                <div class="rating-stars mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $rating['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($rating['review'])): ?>
                                    <p class="mb-0 text-muted">“<?php echo nl2br(escape($rating['review'])); ?>”</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Chưa có đánh giá nào.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5><i class="fas fa-lightbulb text-warning me-2"></i>Gợi ý hành động</h5>
                        <span class="badge-soft warning">AI Assist</span>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <strong>Tăng cường khóa học chủ lực</strong>
                            <p class="text-muted mb-0">Tập trung quảng bá 3 khóa học đang chiếm >60% doanh thu.</p>
                        </li>
                        <li class="mb-3">
                            <strong>Chăm sóc học viên mới</strong>
                            <p class="text-muted mb-0">Triển khai email onboarding cho <?php echo $enrollment_stats['pending'] ?? 0; ?> đăng ký chờ duyệt.</p>
                        </li>
                        <li>
                            <strong>Nâng chất lượng nội dung</strong>
                            <p class="text-muted mb-0">Ưu tiên cập nhật cho các khóa có đánh giá dưới 3 sao.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        const revenueData = <?php echo json_encode($revenue_trend, JSON_UNESCAPED_UNICODE); ?>;
        const categoryData = <?php echo json_encode($category_share, JSON_UNESCAPED_UNICODE); ?>;
        const ratingData = <?php echo json_encode($rating_distribution['distribution'], JSON_UNESCAPED_UNICODE); ?>;

        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const gradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(29, 92, 122, 0.25)');
        gradient.addColorStop(1, 'rgba(29, 92, 122, 0)');

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.labels,
                datasets: [{
                        label: 'Doanh thu',
                        data: revenueData.revenue,
                        borderColor: '#1d5c7a',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#1d5c7a'
                    },
                    {
                        label: 'Lượt đăng ký',
                        data: revenueData.enrollments,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.12)',
                        fill: true,
                        tension: 0.4,
                        borderDash: [6, 4],
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(value)
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.labels,
                datasets: [{
                    data: categoryData.values,
                    backgroundColor: ['#1d5c7a', '#f97316', '#0ea5e9', '#22c55e', '#e11d48', '#a855f7'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        new Chart(document.getElementById('ratingChart'), {
            type: 'bar',
            data: {
                labels: ratingData.map(item => item.label),
                datasets: [{
                    label: 'Số lượt',
                    data: ratingData.map(item => item.value),
                    backgroundColor: '#fbbf24',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>