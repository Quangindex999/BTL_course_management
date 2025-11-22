<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/enrollments_functions.php';

requireStudent();

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'process_payment') {
    $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'bank_transfer';

    if (!$enrollment_id) {
        setAlert('Thông tin thanh toán không hợp lệ', 'error');
        redirect(SITE_URL . '/views/student/my_courses.php');
    }

    // Kiểm tra quyền sở hữu
    $stmt = $pdo->prepare("
        SELECT e.*, c.price 
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        WHERE e.enrollment_id = ? AND e.user_id = ?
    ");
    $stmt->execute([$enrollment_id, $user_id]);
    $enrollment = $stmt->fetch();

    if (!$enrollment) {
        setAlert('Không tìm thấy thông tin đăng ký', 'error');
        redirect(SITE_URL . '/views/student/my_courses.php');
    }

    if ($enrollment['payment_status'] === 'paid') {
        setAlert('Khóa học này đã được thanh toán', 'info');
        redirect(SITE_URL . '/views/student/my_courses.php');
    }

    // Mô phỏng thanh toán thành công (mock payment)
    // Trong thực tế, đây sẽ là nơi gọi API thanh toán thật

    try {
        // Cập nhật trạng thái thanh toán
        $stmt = $pdo->prepare("
            UPDATE enrollments 
            SET payment_status = 'paid', 
                payment_method = ?,
                notes = CONCAT(IFNULL(notes, ''), '\nĐã thanh toán qua ', ?, ' vào ', NOW())
            WHERE enrollment_id = ?
        ");
        $stmt->execute([
            $payment_method,
            $payment_method === 'bank_transfer' ? 'Chuyển khoản ngân hàng' : ($payment_method === 'momo' ? 'MoMo' : ($payment_method === 'vnpay' ? 'VNPay' : 'ZaloPay')),
            $enrollment_id
        ]);

        // Tạo transaction log (nếu có bảng transactions)
        // TODO: Có thể tạo bảng transactions để lưu lịch sử giao dịch

        setAlert('Thanh toán thành công! Bạn có thể bắt đầu học ngay.', 'success');
        redirect(SITE_URL . '/views/student/my_courses.php');
    } catch (PDOException $e) {
        setAlert('Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.', 'error');
        redirect(SITE_URL . '/views/student/payment.php?id=' . $enrollment_id);
    }
} else {
    setAlert('Hành động không hợp lệ', 'error');
    redirect(SITE_URL . '/views/student/my_courses.php');
}
