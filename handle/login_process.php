<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $result = loginUser($email, $password);

    if ($result['success']) {
        setAlert('Đăng nhập thành công!', 'success');

        // Redirect theo role
        if (isAdmin()) {
            redirect(SITE_URL . '/views/admin/dashboard.php');
        } elseif (isTeacher()) {
            redirect(SITE_URL . '/views/teacher/dashboard.php');
        } else {
            // Student redirect về trang chủ
            redirect(SITE_URL . '/index.php');
        }
    } else {
        $message = $result['message'] ?? 'Email hoặc mật khẩu không chính xác';
        setAlert($message, 'error');
        redirect(SITE_URL . '/views/login.php');
    }
} else {
    redirect(SITE_URL . '/views/login.php');
}
