<?php

/**
 * Enrollments Functions - Xử lý đăng ký khóa học
 */

// Đăng ký khóa học
function enrollCourse($user_id, $course_id, $payment_method = 'bank_transfer')
{
    global $pdo;

    try {
        // Kiểm tra đã đăng ký chưa
        $stmt = $pdo->prepare("
            SELECT * FROM enrollments 
            WHERE user_id = ? AND course_id = ?
        ");
        $stmt->execute([$user_id, $course_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['status'] === 'pending') {
                return ['success' => false, 'message' => 'Bạn đã đăng ký khóa học này và đang chờ duyệt'];
            } elseif ($existing['status'] === 'approved') {
                return ['success' => false, 'message' => 'Bạn đã được duyệt khóa học này'];
            } elseif ($existing['status'] === 'rejected') {
                return ['success' => false, 'message' => 'Đăng ký của bạn đã bị từ chối trước đó'];
            }
        }

        // Kiểm tra khóa học còn chỗ không
        $stmt = $pdo->prepare("
            SELECT max_students,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status = 'approved') as current_students
            FROM courses WHERE course_id = ?
        ");
        $stmt->execute([$course_id, $course_id]);
        $course = $stmt->fetch();

        if ($course && $course['current_students'] >= $course['max_students']) {
            return ['success' => false, 'message' => 'Khóa học đã đầy'];
        }

        // Tạo đăng ký mới
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (user_id, course_id, status, payment_status, payment_method)
            VALUES (?, ?, 'pending', 'unpaid', ?)
        ");
        $stmt->execute([$user_id, $course_id, $payment_method]);

        return [
            'success' => true,
            'message' => 'Đăng ký khóa học thành công! Vui lòng chờ admin duyệt.',
            'enrollment_id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi đăng ký: ' . $e->getMessage()];
    }
}

// Hủy đăng ký
function cancelEnrollment($enrollment_id, $user_id)
{
    global $pdo;

    try {
        // Kiểm tra quyền sở hữu
        $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE enrollment_id = ? AND user_id = ?");
        $stmt->execute([$enrollment_id, $user_id]);
        $enrollment = $stmt->fetch();

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Không tìm thấy đăng ký'];
        }

        if ($enrollment['status'] === 'completed') {
            return ['success' => false, 'message' => 'Không thể hủy khóa học đã hoàn thành'];
        }

        // Xóa đăng ký
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE enrollment_id = ?");
        $stmt->execute([$enrollment_id]);

        return ['success' => true, 'message' => 'Hủy đăng ký thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi hủy đăng ký: ' . $e->getMessage()];
    }
}

// Admin: Duyệt đăng ký
function approveEnrollment($enrollment_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE enrollments SET status = 'approved' WHERE enrollment_id = ?");
        $stmt->execute([$enrollment_id]);

        // TODO: Gửi email thông báo cho học viên

        return ['success' => true, 'message' => 'Duyệt đăng ký thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi duyệt đăng ký'];
    }
}

// Admin: Từ chối đăng ký
function rejectEnrollment($enrollment_id, $reason = '')
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE enrollments SET status = 'rejected', notes = ? WHERE enrollment_id = ?");
        $stmt->execute([$reason, $enrollment_id]);

        // TODO: Gửi email thông báo cho học viên

        return ['success' => true, 'message' => 'Từ chối đăng ký thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi từ chối đăng ký'];
    }
}

function buildUserEnrollmentQuery($user_id, $status = null)
{
    $where = ["e.user_id = ?"];
    $params = [$user_id];

    if ($status) {
        $where[] = "e.status = ?";
        $params[] = $status;
    }

    $base_sql = "
        SELECT e.*, c.course_name, c.thumbnail, c.instructor_name, c.duration, c.price,
               cat.category_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        LEFT JOIN categories cat ON c.category_id = cat.category_id
    ";

    return [$base_sql, implode(' AND ', $where), $params];
}

// Lấy danh sách đăng ký của user
function getUserEnrollments($user_id, $status = null)
{
    global $pdo;

    try {
        [$base_sql, $where_sql, $params] = buildUserEnrollmentQuery($user_id, $status);

        $sql = $base_sql . " WHERE $where_sql ORDER BY e.enrollment_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy danh sách đăng ký có phân trang cho user
function getUserEnrollmentsPaginated($user_id, $status = null, $page = 1, $per_page = 6)
{
    global $pdo;

    try {
        [$base_sql, $where_sql, $params] = buildUserEnrollmentQuery($user_id, $status);

        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e WHERE $where_sql");
        $count_stmt->execute($params);
        $total = (int)$count_stmt->fetchColumn();

        $pagination = paginate($total, $per_page, $page);

        $sql = $base_sql . " WHERE $where_sql ORDER BY e.enrollment_date DESC LIMIT ? OFFSET ?";
        $params_with_limits = array_merge($params, [$pagination['per_page'], $pagination['offset']]);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params_with_limits);

        return [
            'enrollments' => $stmt->fetchAll(),
            'pagination' => $pagination
        ];
    } catch (PDOException $e) {
        return [
            'enrollments' => [],
            'pagination' => paginate(0, $per_page, 1)
        ];
    }
}

// Lấy tất cả đăng ký (cho admin)
function getAllEnrollments($filters = [])
{
    global $pdo;

    try {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "e.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $where[] = "e.course_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "e.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        $where_sql = implode(' AND ', $where);

        // Phân trang
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        // Đếm tổng
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e WHERE $where_sql");
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();

        // Lấy dữ liệu
        $sql = "
            SELECT e.*, 
            u.full_name, u.email, u.phone,
            c.course_name, c.price
            FROM enrollments e
            JOIN users u ON e.user_id = u.user_id
            JOIN courses c ON e.course_id = c.course_id
            WHERE $where_sql
            ORDER BY e.enrollment_date DESC
            LIMIT $per_page OFFSET $offset
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'enrollments' => $stmt->fetchAll(),
            'pagination' => paginate($total, $per_page, $page)
        ];
    } catch (PDOException $e) {
        return ['enrollments' => [], 'pagination' => []];
    }
}

// Cập nhật trạng thái thanh toán
function updatePaymentStatus($enrollment_id, $status)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE enrollments SET payment_status = ? WHERE enrollment_id = ?");
        $stmt->execute([$status, $enrollment_id]);

        return ['success' => true, 'message' => 'Cập nhật trạng thái thanh toán thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật'];
    }
}

// Lấy thống kê đăng ký
function getEnrollmentStats()
{
    global $pdo;

    try {
        $stats = [];

        // Tổng đăng ký
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

        // Chờ duyệt
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'")->fetchColumn();

        // Đã duyệt
        $stats['approved'] = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'approved'")->fetchColumn();

        // Từ chối
        $stats['rejected'] = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'rejected'")->fetchColumn();

        // Hoàn thành
        $stats['completed'] = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'")->fetchColumn();

        return $stats;
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy thống kê đăng ký của một user
function getUserEnrollmentStats($user_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM enrollments
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $counts = $stmt->fetch() ?: [];

        $revenue_stmt = $pdo->prepare("
            SELECT COALESCE(SUM(c.price), 0) 
            FROM enrollments e
            JOIN courses c ON e.course_id = c.course_id
            WHERE e.user_id = ? AND e.payment_status = 'paid'
        ");
        $revenue_stmt->execute([$user_id]);
        $total_paid = $revenue_stmt->fetchColumn() ?: 0;

        return [
            'total' => (int)($counts['total'] ?? 0),
            'approved' => (int)($counts['approved'] ?? 0),
            'pending' => (int)($counts['pending'] ?? 0),
            'completed' => (int)($counts['completed'] ?? 0),
            'rejected' => (int)($counts['rejected'] ?? 0),
            'total_paid' => (float)$total_paid
        ];
    } catch (PDOException $e) {
        return [
            'total' => 0,
            'approved' => 0,
            'pending' => 0,
            'completed' => 0,
            'rejected' => 0,
            'total_paid' => 0
        ];
    }
}

// Kiểm tra tiến độ khóa học
function getCourseProgress($user_id, $course_id)
{
    global $pdo;

    try {
        // Lấy enrollment
        $stmt = $pdo->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'approved'");
        $stmt->execute([$user_id, $course_id]);
        $enrollment = $stmt->fetch();

        if (!$enrollment) {
            return null;
        }

        // Đếm tổng bài học
        $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
        $total_stmt->execute([$course_id]);
        $total_lessons = $total_stmt->fetchColumn();

        // Đếm bài đã hoàn thành
        $completed_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM progress 
            WHERE enrollment_id = ? AND completed = TRUE
        ");
        $completed_stmt->execute([$enrollment['enrollment_id']]);
        $completed_lessons = $completed_stmt->fetchColumn();

        $percentage = $total_lessons > 0 ? ($completed_lessons / $total_lessons) * 100 : 0;

        return [
            'total_lessons' => $total_lessons,
            'completed_lessons' => $completed_lessons,
            'percentage' => round($percentage, 2)
        ];
    } catch (PDOException $e) {
        return null;
    }
}

// Đánh dấu bài học hoàn thành
function markLessonComplete($user_id, $course_id, $lesson_id)
{
    global $pdo;

    try {
        // Lấy enrollment
        $stmt = $pdo->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'approved'");
        $stmt->execute([$user_id, $course_id]);
        $enrollment = $stmt->fetch();

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Bạn chưa được duyệt khóa học này'];
        }

        // Thêm hoặc cập nhật progress
        $stmt = $pdo->prepare("
            INSERT INTO progress (enrollment_id, lesson_id, completed, completed_at)
            VALUES (?, ?, TRUE, NOW())
            ON DUPLICATE KEY UPDATE completed = TRUE, completed_at = NOW()
        ");
        $stmt->execute([$enrollment['enrollment_id'], $lesson_id]);

        return ['success' => true, 'message' => 'Đã đánh dấu hoàn thành'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật tiến độ'];
    }
}
