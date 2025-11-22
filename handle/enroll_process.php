<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/enrollments_functions.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'enroll':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireLogin();

            $user_id = $_SESSION['user_id'];
            $course_id = intval($_POST['course_id'] ?? 0);
            $payment_method = $_POST['payment_method'] ?? 'bank_transfer';

            if ($course_id <= 0) {
                setAlert('Khóa học không hợp lệ', 'error');
                redirect(SITE_URL . '/views/student/course_detail.php');
            }

            $result = enrollCourse($user_id, $course_id, $payment_method);

            if ($result['success']) {
                setAlert($result['message'], 'success');
            } else {
                setAlert($result['message'], 'error');
            }

            redirect(SITE_URL . '/views/student/course_detail.php?id=' . $course_id);
        }
        break;

    case 'approve':
        requireAdmin();

        if (isset($_GET['id'])) {
            $enrollment_id = intval($_GET['id']);
            $result = approveEnrollment($enrollment_id);

            if ($result['success']) {
                setAlert($result['message'], 'success');
            } else {
                setAlert($result['message'], 'error');
            }
        }
        // Redirect về trang quản lý đăng ký nếu có, nếu không thì về dashboard
        $redirect_url = isset($_GET['from']) && $_GET['from'] === 'manage'
            ? SITE_URL . '/views/admin/manage_enrollments.php'
            : SITE_URL . '/views/admin/dashboard.php';
        redirect($redirect_url);
        break;

    case 'reject':
        requireAdmin();

        if (isset($_GET['id'])) {
            $enrollment_id = intval($_GET['id']);
            $reason = $_GET['reason'] ?? '';
            $result = rejectEnrollment($enrollment_id, $reason);

            if ($result['success']) {
                setAlert($result['message'], 'success');
            } else {
                setAlert($result['message'], 'error');
            }
        }
        // Redirect về trang quản lý đăng ký nếu có, nếu không thì về dashboard
        $redirect_url = isset($_GET['from']) && $_GET['from'] === 'manage'
            ? SITE_URL . '/views/admin/manage_enrollments.php'
            : SITE_URL . '/views/admin/dashboard.php';
        redirect($redirect_url);
        break;

    case 'update_payment':
        requireAdmin();

        if (isset($_GET['id']) && isset($_GET['status'])) {
            $enrollment_id = intval($_GET['id']);
            $payment_status = $_GET['status'];
            $allowed_statuses = ['unpaid', 'paid', 'refunded'];

            if (!in_array($payment_status, $allowed_statuses, true)) {
                setAlert('Trạng thái thanh toán không hợp lệ', 'error');
                redirect(SITE_URL . '/views/admin/manage_enrollments.php');
            }
            $result = updatePaymentStatus($enrollment_id, $payment_status);

            if ($result['success']) {
                setAlert($result['message'], 'success');
            } else {
                setAlert($result['message'], 'error');
            }
        }
        redirect(SITE_URL . '/views/admin/manage_enrollments.php');
        break;

    default:
        redirect(SITE_URL . '/index.php');
}
