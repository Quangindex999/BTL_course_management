<?php

/**
 * Course Functions - Xử lý các chức năng liên quan đến khóa học
 */

// Lấy tất cả khóa học với filter
function getCourses($filters = [])
{
    global $pdo;

    $where = ["c.status = 'active'"];
    $params = [];

    // Tìm kiếm
    if (!empty($filters['search'])) {
        $where[] = "(c.course_name LIKE ? OR c.description LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }

    // Lọc theo danh mục
    if (!empty($filters['category_id'])) {
        $where[] = "c.category_id = ?";
        $params[] = $filters['category_id'];
    }

    // Lọc theo level
    if (!empty($filters['level'])) {
        $where[] = "c.level = ?";
        $params[] = $filters['level'];
    }

    // Lọc theo giá
    if (isset($filters['min_price'])) {
        $where[] = "c.price >= ?";
        $params[] = $filters['min_price'];
    }
    if (isset($filters['max_price'])) {
        $where[] = "c.price <= ?";
        $params[] = $filters['max_price'];
    }

    $where_sql = implode(' AND ', $where);

    // Sắp xếp
    $order_by = match ($filters['sort'] ?? 'newest') {
        'price_asc' => 'c.price ASC',
        'price_desc' => 'c.price DESC',
        'name' => 'c.course_name ASC',
        'popular' => 'enrolled_count DESC',
        'id_asc' => 'c.course_id ASC',
        'id_desc' => 'c.course_id DESC',
        default => 'c.created_at DESC'
    };

    // Phân trang
    $page = max(1, intval($filters['page'] ?? 1));
    $per_page = intval($filters['per_page'] ?? 9);
    $offset = ($page - 1) * $per_page;

    try {
        // Đếm tổng số
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM courses c WHERE $where_sql");
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();

        // Lấy dữ liệu
        $sql = "SELECT c.*, cat.category_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.category_id
                WHERE $where_sql
                ORDER BY $order_by
                LIMIT $per_page OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $courses = $stmt->fetchAll();

        return [
            'success' => true,
            'courses' => $courses,
            'pagination' => paginate($total, $per_page, $page)
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi lấy dữ liệu: ' . $e->getMessage()];
    }
}

// Lấy chi tiết khóa học
function getCourseById($course_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.category_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count,
            (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as lesson_count
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            WHERE c.course_id = ?
        ");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();

        if (!$course) {
            return ['success' => false, 'message' => 'Không tìm thấy khóa học'];
        }

        // Lấy bài học
        $lesson_stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_number ASC");
        $lesson_stmt->execute([$course_id]);
        $course['lessons'] = $lesson_stmt->fetchAll();

        return ['success' => true, 'course' => $course];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi lấy chi tiết: ' . $e->getMessage()];
    }
}

// Tạo khóa học mới
function createCourse($data, $thumbnail_file = null)
{
    global $pdo;

    try {
        // Upload thumbnail
        $thumbnail = null;
        if ($thumbnail_file && $thumbnail_file['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($thumbnail_file, 'courses');
            if ($upload['success']) {
                $thumbnail = $upload['path'];
            }
        }

        // Nếu có teacher_id, thêm vào query
        $has_teacher_id = isset($data['teacher_id']) && !empty($data['teacher_id']);
        $fields = "course_name, description, category_id, instructor_name, duration, level, price, thumbnail, start_date, end_date, max_students, status";
        $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active'";
        $values = [
            $data['course_name'],
            $data['description'],
            $data['category_id'],
            $data['instructor_name'],
            $data['duration'],
            $data['level'],
            $data['price'],
            $thumbnail,
            $data['start_date'],
            $data['end_date'],
            $data['max_students'] ?? 30
        ];

        if ($has_teacher_id) {
            $fields .= ", teacher_id";
            $placeholders = str_replace("'active'", "?, 'active'", $placeholders);
            $values[] = $data['teacher_id'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO courses ($fields) VALUES ($placeholders)
        ");

        $stmt->execute($values);

        return [
            'success' => true,
            'message' => 'Tạo khóa học thành công',
            'course_id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi tạo khóa học: ' . $e->getMessage()];
    }
}

// Cập nhật khóa học
function updateCourse($course_id, $data, $thumbnail_file = null)
{
    global $pdo;

    try {
        // Upload thumbnail mới nếu có
        if ($thumbnail_file && $thumbnail_file['error'] === UPLOAD_ERR_OK) {
            // Lấy thumbnail cũ
            $stmt = $pdo->prepare("SELECT thumbnail FROM courses WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $old_course = $stmt->fetch();

            // Xóa file cũ
            if ($old_course && $old_course['thumbnail']) {
                deleteFile($old_course['thumbnail']);
            }

            // Upload file mới
            $upload = uploadFile($thumbnail_file, 'courses');
            if ($upload['success']) {
                $data['thumbnail'] = $upload['path'];
            }
        }

        $updates = [];
        $params = [];

        $allowed_fields = [
            'course_name',
            'description',
            'category_id',
            'instructor_name',
            'teacher_id',
            'duration',
            'level',
            'price',
            'thumbnail',
            'start_date',
            'end_date',
            'max_students',
            'status'
        ];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'Không có dữ liệu để cập nhật'];
        }

        $params[] = $course_id;
        $sql = "UPDATE courses SET " . implode(', ', $updates) . " WHERE course_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Cập nhật khóa học thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
    }
}

// Xóa khóa học (hard delete với các dữ liệu liên quan)
function deleteCourse($course_id)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Lấy thông tin khóa học để xóa file sau khi xóa dữ liệu
        $course_stmt = $pdo->prepare("SELECT thumbnail FROM courses WHERE course_id = ?");
        $course_stmt->execute([$course_id]);
        $course = $course_stmt->fetch();

        if (!$course) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Không tìm thấy khóa học'];
        }

        // Kiểm tra có học viên đang học hoặc chờ duyệt không
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status IN ('approved', 'pending')");
        $stmt->execute([$course_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Không thể xóa khóa học đang có học viên'];
        }

        // Xóa tiến độ học tập liên quan
        $progress_stmt = $pdo->prepare("
            DELETE p FROM progress p
            INNER JOIN enrollments e ON p.enrollment_id = e.enrollment_id
            WHERE e.course_id = ?
        ");
        $progress_stmt->execute([$course_id]);

        // Xóa đánh giá liên quan
        $ratings_stmt = $pdo->prepare("DELETE FROM ratings WHERE course_id = ?");
        $ratings_stmt->execute([$course_id]);

        // Xóa đăng ký (lúc này chỉ còn completed/rejected)
        $enroll_stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?");
        $enroll_stmt->execute([$course_id]);

        // Xóa bài học
        $lesson_stmt = $pdo->prepare("DELETE FROM lessons WHERE course_id = ?");
        $lesson_stmt->execute([$course_id]);

        // Xóa khóa học
        $course_delete_stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
        $course_delete_stmt->execute([$course_id]);

        $pdo->commit();

        if (!empty($course['thumbnail'])) {
            deleteFile($course['thumbnail']);
        }

        return ['success' => true, 'message' => 'Xóa khóa học thành công'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Lỗi xóa khóa học: ' . $e->getMessage()];
    }
}

// Lấy danh sách danh mục
function getCategories()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy khóa học phổ biến
function getPopularCourses($limit = 6)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.category_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            WHERE c.status = 'active'
            ORDER BY enrolled_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy khóa học mới nhất
function getLatestCourses($limit = 6)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.category_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            WHERE c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Lấy khóa học liên quan
function getRelatedCourses($course_id, $category_id, $limit = 3)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.category_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'approved') as enrolled_count
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            WHERE c.category_id = ? AND c.course_id != ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$category_id, $course_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Kiểm tra user đã đăng ký khóa học chưa
function isEnrolled($user_id, $course_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM enrollments 
            WHERE user_id = ? AND course_id = ? AND status IN ('pending', 'approved')
        ");
        $stmt->execute([$user_id, $course_id]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Lấy thống kê khóa học
function getCourseStats()
{
    global $pdo;

    try {
        $stats = [];

        // Tổng số khóa học
        $stats['total_courses'] = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();

        // Tổng số học viên (tất cả users có role = 'student')
        $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

        // Tổng số đăng ký
        $stats['total_enrollments'] = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'approved'")->fetchColumn();

        // Đăng ký chờ duyệt
        $stats['pending_enrollments'] = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'")->fetchColumn();

        // Doanh thu (nếu có field revenue)
        $stats['total_revenue'] = $pdo->query("
            SELECT SUM(c.price) 
            FROM enrollments e 
            JOIN courses c ON e.course_id = c.course_id 
            WHERE e.status = 'approved' AND e.payment_status = 'paid'
        ")->fetchColumn() ?? 0;

        return $stats;
    } catch (PDOException $e) {
        return [];
    }
}

// Thêm bài học vào khóa học
function addLesson($course_id, $data)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO lessons (course_id, lesson_title, content, video_url, duration, order_number)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $course_id,
            $data['lesson_title'],
            $data['content'],
            $data['video_url'] ?? null,
            $data['duration'],
            $data['order_number'] ?? 1
        ]);

        return ['success' => true, 'message' => 'Thêm bài học thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi thêm bài học: ' . $e->getMessage()];
    }
}

// Cập nhật bài học
function updateLesson($lesson_id, $data)
{
    global $pdo;

    try {
        $updates = [];
        $params = [];

        $allowed_fields = ['lesson_title', 'content', 'video_url', 'duration', 'order_number'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'Không có dữ liệu cập nhật'];
        }

        $params[] = $lesson_id;
        $sql = "UPDATE lessons SET " . implode(', ', $updates) . " WHERE lesson_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Cập nhật bài học thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
    }
}

// Xóa bài học
function deleteLesson($lesson_id)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$lesson_id]);

        return ['success' => true, 'message' => 'Xóa bài học thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa bài học'];
    }
}
