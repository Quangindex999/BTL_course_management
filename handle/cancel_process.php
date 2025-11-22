<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/enrollments_functions.php';

requireLogin();

if(isset($_GET['id'])) {
    $enrollment_id = intval($_GET['id']);
    $result = cancelEnrollment($enrollment_id, $_SESSION['user_id']);
    
    if($result['success']) {
        setAlert($result['message'], 'success');
    } else {
        setAlert($result['message'], 'error');
    }
}

redirect(SITE_URL . '/views/student/my_courses.php');
?>