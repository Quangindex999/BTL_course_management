<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/notification_functions.php';

// Kiểm tra đăng nhập
requireStudent();

$user_id = $_SESSION['user_id'];

// Xử lý đánh dấu đã đọc
if (isset($_GET['mark_read']) && $_GET['mark_read']) {
    $notification_id = intval($_GET['mark_read']);
    markNotificationAsRead($notification_id, $user_id);
    redirect(SITE_URL . '/views/student/notifications.php');
}

// Xử lý đánh dấu tất cả đã đọc
if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] == '1') {
    markAllNotificationsAsRead($user_id);
    redirect(SITE_URL . '/views/student/notifications.php');
}

// Xử lý xóa thông báo
if (isset($_GET['delete']) && $_GET['delete']) {
    $notification_id = intval($_GET['delete']);
    deleteNotification($notification_id, $user_id);
    redirect(SITE_URL . '/views/student/notifications.php');
}

// Lọc
$filter = $_GET['filter'] ?? 'all';
$filters = [];
if ($filter === 'unread') {
    $filters['is_read'] = false;
}

// Lấy thông báo
$page = max(1, intval($_GET['page'] ?? 1));
$filters['page'] = $page;
$result = getNotifications($user_id, $filters);
$notifications = $result['notifications'] ?? [];
$pagination = $result['pagination'] ?? [];

// Đếm số thông báo chưa đọc
$unread_count = getUnreadNotificationCount($user_id);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #1d5c7a, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .main-content {
            padding: 2rem 0;
        }

        .notification-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .notification-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .notification-card.unread {
            background: #f0f9ff;
            border-left-color: #3b82f6;
        }

        .notification-card.unread .notification-title {
            font-weight: 600;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .notification-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .notification-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .notification-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .notification-icon.error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .notification-icon.message {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .filter-tabs {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-tab {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            color: #6b7280;
            font-weight: 500;
            transition: all 0.3s;
            margin-right: 0.5rem;
        }

        .filter-tab:hover,
        .filter-tab.active {
            background: #1d5c7a;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="my_courses.php"><i class="fas fa-book-open me-1"></i>Khóa Học Của Tôi</a></li>
                    <li class="nav-item"><a class="nav-link active" href="notifications.php">
                            <i class="fas fa-bell me-1"></i>Thông Báo
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a></li>
                    <li class="nav-item"><a class="nav-link" href="menu_student.php"><i class="fas fa-user me-1"></i>Hồ Sơ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt me-1"></i>Đăng Xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-content">
        <?php showAlert(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-bell me-2"></i>Thông báo của tôi</h2>
                <p class="text-muted mb-0">Quản lý và xem tất cả thông báo từ hệ thống</p>
            </div>
            <?php if ($unread_count > 0): ?>
                <a href="?mark_all_read=1" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-2"></i>Đánh dấu tất cả đã đọc
                </a>
            <?php endif; ?>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list me-2"></i>Tất cả
            </a>
            <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                <i class="fas fa-envelope me-2"></i>Chưa đọc
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Notifications List -->
        <?php if (empty($notifications)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h4 class="text-muted">Chưa có thông báo nào</h4>
                        <p class="text-muted">Bạn sẽ nhận được thông báo khi có cập nhật mới</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification-card <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                    <div class="d-flex">
                        <div class="notification-icon <?php echo $notif['type']; ?>">
                            <i class="fas <?php echo getNotificationIcon($notif['type']); ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="notification-title mb-1">
                                        <?php echo escape($notif['title']); ?>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="badge bg-primary ms-2">Mới</span>
                                        <?php endif; ?>
                                    </h6>
                                    <?php if ($notif['sender_name']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Từ: <?php echo escape($notif['sender_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if (!$notif['is_read']): ?>
                                            <li>
                                                <a class="dropdown-item" href="?mark_read=<?php echo $notif['notification_id']; ?>">
                                                    <i class="fas fa-check me-2"></i>Đánh dấu đã đọc
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item text-danger" href="?delete=<?php echo $notif['notification_id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa thông báo này?')">
                                                <i class="fas fa-trash me-2"></i>Xóa
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-2"><?php echo nl2br(escape($notif['message'])); ?></p>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php
                                $created = strtotime($notif['created_at']);
                                $now = time();
                                $diff = $now - $created;

                                if ($diff < 60) {
                                    echo 'Vừa xong';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . ' phút trước';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' giờ trước';
                                } elseif ($diff < 604800) {
                                    echo floor($diff / 86400) . ' ngày trước';
                                } else {
                                    echo formatDate($notif['created_at']);
                                }
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&filter=<?php echo $filter; ?>">Trước</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&filter=<?php echo $filter; ?>">Sau</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>