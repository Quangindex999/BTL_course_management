<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/course_functions.php';

requireAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'create':
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $thumbnail_file = $_FILES['thumbnail'] ?? null;
            $result = createCourse($_POST, $thumbnail_file);
            
            if($result['success']) {
                setAlert('Tạo khóa học thành công!', 'success');
                redirect(SITE_URL . '/views/admin/manage_courses.php');
            } else {
                setAlert($result['message'], 'error');
                redirect(SITE_URL . '/views/admin/create_courses.php');
            }
        }
        break;
        
    case 'update':
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course_id = intval($_POST['course_id']);
            $thumbnail_file = $_FILES['thumbnail'] ?? null;
            
            $result = updateCourse($course_id, $_POST, $thumbnail_file);
            
            if($result['success']) {
                setAlert('Cập nhật khóa học thành công!', 'success');
            } else {
                setAlert($result['message'], 'error');
            }
            redirect(SITE_URL . '/views/admin/edit_courses.php?id=' . $course_id);
        }
        break;
        
    case 'delete':
        if(isset($_GET['id'])) {
            $course_id = intval($_GET['id']);
            $result = deleteCourse($course_id);
            
            if($result['success']) {
                setAlert($result['message'], 'success');
            } else {
                setAlert($result['message'], 'error');
            }
        }
        redirect(SITE_URL . '/views/admin/manage_courses.php');
        break;
        
    default:
        redirect(SITE_URL . '/views/admin/manage_courses.php');
}
?>