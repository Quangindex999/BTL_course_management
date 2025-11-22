<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    setAlert('Vui lòng đăng nhập', 'error');
    redirect(SITE_URL . '/views/login.php');
}

// Chỉ teacher và admin mới được gửi email
if (!isTeacher() && !isAdmin()) {
    setAlert('Bạn không có quyền gửi email', 'error');
    redirect(SITE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $student_email = $_POST['student_email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validate
    if (empty($student_email) || !filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        setAlert('Email không hợp lệ', 'error');
        redirect(SITE_URL . '/views/teacher/students.php');
    }

    if (empty($subject) || empty($message)) {
        setAlert('Vui lòng điền đầy đủ thông tin', 'error');
        redirect(SITE_URL . '/views/teacher/students.php');
    }

    // Lấy thông tin người gửi
    $sender_id = $_SESSION['user_id'];
    $sender_name = $_SESSION['full_name'] ?? 'Giáo viên';
    $sender_email = $_SESSION['email'] ?? SITE_EMAIL;

    // Tạo nội dung email HTML
    $email_content = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 20px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>EduLearn - Thông báo từ giáo viên</h2>
            </div>
            <div class='content'>
                <p>Xin chào,</p>
                <p>" . nl2br(escape($message)) . "</p>
                <p>Trân trọng,<br><strong>" . escape($sender_name) . "</strong></p>
            </div>
            <div class='footer'>
                <p>Email này được gửi từ hệ thống EduLearn</p>
                <p>Vui lòng không trả lời email này</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Gửi email
    $result = sendEmail($student_email, $subject, $email_content);

    if ($result) {
        setAlert('Email đã được gửi thành công đến ' . escape($student_email), 'success');
    } else {
        // Trên localhost, mail() có thể không hoạt động, nhưng vẫn hiển thị thông báo
        setAlert('Email đã được xử lý. Lưu ý: Trên môi trường localhost, email có thể không được gửi thực sự. Vui lòng kiểm tra cấu hình SMTP.', 'info');
    }

    // Redirect về trang trước
    $referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/views/teacher/students.php';
    redirect($referer);
} else {
    redirect(SITE_URL . '/views/teacher/students.php');
}
