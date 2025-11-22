<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/notification_functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    setAlert('Vui lòng đăng nhập', 'error');
    redirect(SITE_URL . '/views/login.php');
}

// Chỉ teacher và admin mới được gửi thông báo
if (!isTeacher() && !isAdmin()) {
    setAlert('Bạn không có quyền gửi thông báo', 'error');
    redirect(SITE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $title = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate
    if (!$student_id) {
        setAlert('Không tìm thấy học viên', 'error');
        redirect(SITE_URL . '/views/teacher/students.php');
    }

    if (empty($title) || empty($message)) {
        setAlert('Vui lòng điền đầy đủ thông tin', 'error');
        redirect(SITE_URL . '/views/teacher/students.php');
    }

    // Lấy thông tin người gửi
    $sender_id = $_SESSION['user_id'];
    $sender_name = $_SESSION['full_name'] ?? 'Giáo viên';

    // Tạo thông báo
    $result = createNotification($student_id, [
        'sender_id' => $sender_id,
        'title' => $title,
        'message' => $message,
        'type' => 'message',
        'related_type' => 'teacher_message'
    ]);

    if ($result['success']) {
        setAlert('Thông báo đã được gửi thành công đến học viên', 'success');
    } else {
        setAlert('Lỗi gửi thông báo: ' . $result['message'], 'error');
    }

    // Redirect về trang trước
    $referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/views/teacher/students.php';
    redirect($referer);
} else {
    redirect(SITE_URL . '/views/teacher/students.php');
}
