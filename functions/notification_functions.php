<?php

/**
 * Notification Functions - Xử lý các chức năng liên quan đến thông báo
 */

// Tạo thông báo mới
function createNotification($user_id, $data)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, sender_id, title, message, type, related_type, related_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $data['sender_id'] ?? null,
            $data['title'],
            $data['message'],
            $data['type'] ?? 'info',
            $data['related_type'] ?? null,
            $data['related_id'] ?? null
        ]);

        return [
            'success' => true,
            'notification_id' => $pdo->lastInsertId(),
            'message' => 'Thông báo đã được gửi thành công'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Lỗi tạo thông báo: ' . $e->getMessage()
        ];
    }
}

// Gửi thông báo cho nhiều người dùng
function sendNotificationToUsers($user_ids, $data)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, sender_id, title, message, type, related_type, related_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($user_ids as $user_id) {
            $stmt->execute([
                $user_id,
                $data['sender_id'] ?? null,
                $data['title'],
                $data['message'],
                $data['type'] ?? 'info',
                $data['related_type'] ?? null,
                $data['related_id'] ?? null
            ]);
        }

        $pdo->commit();
        return ['success' => true, 'message' => 'Thông báo đã được gửi thành công'];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Lỗi gửi thông báo: ' . $e->getMessage()];
    }
}

// Lấy danh sách thông báo của user
function getNotifications($user_id, $filters = [])
{
    global $pdo;

    $where = ["n.user_id = ?"];
    $params = [$user_id];

    // Lọc theo trạng thái đọc
    if (isset($filters['is_read'])) {
        $where[] = "n.is_read = ?";
        $params[] = $filters['is_read'] ? 1 : 0;
    }

    // Lọc theo loại
    if (!empty($filters['type'])) {
        $where[] = "n.type = ?";
        $params[] = $filters['type'];
    }

    $where_sql = implode(' AND ', $where);

    // Phân trang
    $page = max(1, intval($filters['page'] ?? 1));
    $per_page = intval($filters['per_page'] ?? 20);
    $offset = ($page - 1) * $per_page;

    try {
        // Đếm tổng số
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications n WHERE $where_sql");
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();

        // Lấy dữ liệu
        $sql = "SELECT n.*, 
                u.full_name as sender_name, u.avatar as sender_avatar
                FROM notifications n
                LEFT JOIN users u ON n.sender_id = u.user_id
                WHERE $where_sql
                ORDER BY n.created_at DESC
                LIMIT $per_page OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll();

        return [
            'success' => true,
            'notifications' => $notifications,
            'pagination' => paginate($total, $per_page, $page)
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi lấy thông báo: ' . $e->getMessage()];
    }
}

// Đếm số thông báo chưa đọc
function getUnreadNotificationCount($user_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// Đánh dấu thông báo là đã đọc
function markNotificationAsRead($notification_id, $user_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = TRUE, read_at = NOW() 
            WHERE notification_id = ? AND user_id = ?
        ");
        $stmt->execute([$notification_id, $user_id]);

        return ['success' => true, 'message' => 'Đã đánh dấu đã đọc'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
    }
}

// Đánh dấu tất cả thông báo là đã đọc
function markAllNotificationsAsRead($user_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = TRUE, read_at = NOW() 
            WHERE user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$user_id]);

        return ['success' => true, 'message' => 'Đã đánh dấu tất cả là đã đọc'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
    }
}

// Xóa thông báo
function deleteNotification($notification_id, $user_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);

        return ['success' => true, 'message' => 'Đã xóa thông báo'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa thông báo: ' . $e->getMessage()];
    }
}

// Lấy thông báo mới nhất (cho dropdown)
function getRecentNotifications($user_id, $limit = 5)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT n.*, 
            u.full_name as sender_name, u.avatar as sender_avatar
            FROM notifications n
            LEFT JOIN users u ON n.sender_id = u.user_id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy icon cho loại thông báo
function getNotificationIcon($type)
{
    $icons = [
        'info' => 'fa-info-circle',
        'success' => 'fa-check-circle',
        'warning' => 'fa-exclamation-triangle',
        'error' => 'fa-times-circle',
        'message' => 'fa-envelope'
    ];
    return $icons[$type] ?? 'fa-bell';
}

// Lấy class màu cho loại thông báo
function getNotificationColorClass($type)
{
    $colors = [
        'info' => 'text-primary',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-danger',
        'message' => 'text-info'
    ];
    return $colors[$type] ?? 'text-secondary';
}

// Lấy badge class cho loại thông báo
function getNotificationBadgeClass($type)
{
    $badges = [
        'info' => 'bg-primary',
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'error' => 'bg-danger',
        'message' => 'bg-info'
    ];
    return $badges[$type] ?? 'bg-secondary';
}

