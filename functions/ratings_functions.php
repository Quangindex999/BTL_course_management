<?php

/**
 * Ratings Functions - Xử lý đánh giá khóa học
 */

// Thêm hoặc cập nhật đánh giá
function addRating($user_id, $course_id, $rating, $review = '', $enrollment_id = null)
{
    global $pdo;

    try {
        // Kiểm tra user đã đăng ký khóa học chưa
        $stmt = $pdo->prepare("
            SELECT enrollment_id, status 
            FROM enrollments 
            WHERE user_id = ? AND course_id = ? AND status IN ('approved', 'completed')
            ORDER BY enrollment_date DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id, $course_id]);
        $enrollment = $stmt->fetch();

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Bạn cần đăng ký và được duyệt khóa học trước khi đánh giá'];
        }

        // Kiểm tra đã đánh giá chưa
        $stmt = $pdo->prepare("SELECT rating_id FROM ratings WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $course_id]);
        $existing = $stmt->fetch();

        $enrollment_id = $enrollment_id ?? $enrollment['enrollment_id'];

        if ($existing) {
            // Cập nhật đánh giá
            $stmt = $pdo->prepare("
                UPDATE ratings 
                SET rating = ?, review = ?, enrollment_id = ?, updated_at = NOW()
                WHERE rating_id = ?
            ");
            $stmt->execute([$rating, $review, $enrollment_id, $existing['rating_id']]);
            $rating_id = $existing['rating_id'];
        } else {
            // Thêm đánh giá mới
            $stmt = $pdo->prepare("
                INSERT INTO ratings (user_id, course_id, enrollment_id, rating, review)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $course_id, $enrollment_id, $rating, $review]);
            $rating_id = $pdo->lastInsertId();
        }

        // Cập nhật rating trung bình của khóa học
        updateCourseRating($course_id);

        return [
            'success' => true,
            'message' => $existing ? 'Cập nhật đánh giá thành công' : 'Đánh giá thành công',
            'rating_id' => $rating_id
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Lấy đánh giá của user cho một khóa học
function getUserRating($user_id, $course_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM ratings 
            WHERE user_id = ? AND course_id = ?
        ");
        $stmt->execute([$user_id, $course_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Lấy tất cả đánh giá của một khóa học
function getCourseRatings($course_id, $limit = 10, $offset = 0)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT r.*, u.full_name, u.avatar
            FROM ratings r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.course_id = ?
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$course_id, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy thống kê đánh giá của khóa học
function getCourseRatingStats($course_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_ratings,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM ratings
            WHERE course_id = ?
        ");
        $stmt->execute([$course_id]);
        $stats = $stmt->fetch();

        if ($stats && $stats['total_ratings'] > 0) {
            $stats['average_rating'] = round($stats['average_rating'], 1);
        } else {
            $stats = [
                'total_ratings' => 0,
                'average_rating' => 0,
                'five_star' => 0,
                'four_star' => 0,
                'three_star' => 0,
                'two_star' => 0,
                'one_star' => 0
            ];
        }

        return $stats;
    } catch (PDOException $e) {
        return [
            'total_ratings' => 0,
            'average_rating' => 0,
            'five_star' => 0,
            'four_star' => 0,
            'three_star' => 0,
            'two_star' => 0,
            'one_star' => 0
        ];
    }
}

// Cập nhật rating trung bình của khóa học (có thể lưu vào bảng courses nếu cần)
function updateCourseRating($course_id)
{
    global $pdo;

    try {
        $stats = getCourseRatingStats($course_id);

        // Có thể cập nhật vào bảng courses nếu có cột average_rating
        // Hiện tại chỉ tính toán động khi cần
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Xóa đánh giá
function deleteRating($rating_id, $user_id)
{
    global $pdo;

    try {
        // Kiểm tra quyền sở hữu
        $stmt = $pdo->prepare("SELECT course_id FROM ratings WHERE rating_id = ? AND user_id = ?");
        $stmt->execute([$rating_id, $user_id]);
        $rating = $stmt->fetch();

        if (!$rating) {
            return ['success' => false, 'message' => 'Không tìm thấy đánh giá hoặc bạn không có quyền xóa'];
        }

        $stmt = $pdo->prepare("DELETE FROM ratings WHERE rating_id = ?");
        $stmt->execute([$rating_id]);

        // Cập nhật lại rating trung bình
        updateCourseRating($rating['course_id']);

        return ['success' => true, 'message' => 'Xóa đánh giá thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa đánh giá'];
    }
}

// Kiểm tra user có thể đánh giá khóa học không
function canRateCourse($user_id, $course_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT enrollment_id 
            FROM enrollments 
            WHERE user_id = ? AND course_id = ? AND status IN ('approved', 'completed')
            LIMIT 1
        ");
        $stmt->execute([$user_id, $course_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// Lấy danh sách đánh giá cho giáo viên
function getTeacherRatings($teacher_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   u.full_name AS student_name,
                   u.email AS student_email,
                   c.course_name
            FROM ratings r
            JOIN users u ON r.user_id = u.user_id
            JOIN courses c ON r.course_id = c.course_id
            WHERE c.teacher_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy toàn bộ đánh giá (cho admin)
function getAllRatings()
{
    global $pdo;

    try {
        $stmt = $pdo->query("
            SELECT r.*, 
                   u.full_name AS student_name,
                   u.email AS student_email,
                   c.course_name,
                   t.full_name AS teacher_name
            FROM ratings r
            JOIN users u ON r.user_id = u.user_id
            JOIN courses c ON r.course_id = c.course_id
            LEFT JOIN users t ON c.teacher_id = t.user_id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
