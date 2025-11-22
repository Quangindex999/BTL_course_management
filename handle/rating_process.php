<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/ratings_functions.php';

requireStudent();

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'submit_rating') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');
    $enrollment_id = intval($_POST['enrollment_id'] ?? 0);

    if (!$course_id || !$rating || $rating < 1 || $rating > 5) {
        setAlert('Vui lòng chọn số sao đánh giá', 'error');
        redirect(SITE_URL . '/views/student/my_courses.php');
    }

    // Kiểm tra user có quyền đánh giá không
    if (!canRateCourse($user_id, $course_id)) {
        setAlert('Bạn cần đăng ký và được duyệt khóa học trước khi đánh giá', 'error');
        redirect(SITE_URL . '/views/student/my_courses.php');
    }

    $result = addRating($user_id, $course_id, $rating, $review, $enrollment_id);

    if ($result['success']) {
        setAlert($result['message'], 'success');
    } else {
        setAlert($result['message'], 'error');
    }

    redirect(SITE_URL . '/views/student/my_courses.php');
} else {
    setAlert('Hành động không hợp lệ', 'error');
    redirect(SITE_URL . '/views/student/my_courses.php');
}
