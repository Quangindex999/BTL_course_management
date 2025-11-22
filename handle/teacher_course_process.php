<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';
require_once '../functions/course_functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['full_name'] ?? '';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Tự động set teacher_id và instructor_name
            $_POST['teacher_id'] = $teacher_id;
            $_POST['instructor_name'] = $teacher_name;

            $thumbnail_file = $_FILES['thumbnail'] ?? null;
            $result = createCourse($_POST, $thumbnail_file);

            if ($result['success']) {
                setAlert('Tạo khóa học thành công!', 'success');
                redirect(SITE_URL . '/views/teacher/my_course.php');
            } else {
                setAlert($result['message'], 'error');
                redirect(SITE_URL . '/views/teacher/edit_course.php');
            }
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course_id = intval($_POST['course_id']);

            // Kiểm tra quyền sở hữu
            $stmt = $pdo->prepare("SELECT teacher_id FROM courses WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $course = $stmt->fetch();

            if (!$course || $course['teacher_id'] != $teacher_id) {
                setAlert('Bạn không có quyền chỉnh sửa khóa học này', 'error');
                redirect(SITE_URL . '/views/teacher/my_course.php');
            }

            // Tự động set instructor_name
            $_POST['instructor_name'] = $teacher_name;
            $_POST['teacher_id'] = $teacher_id;

            $thumbnail_file = $_FILES['thumbnail'] ?? null;
            $result = updateCourse($course_id, $_POST, $thumbnail_file);

            if ($result['success']) {
                setAlert('Cập nhật khóa học thành công!', 'success');
            } else {
                setAlert($result['message'], 'error');
            }
            redirect(SITE_URL . '/views/teacher/edit_course.php?id=' . $course_id);
        }
        break;

    default:
        redirect(SITE_URL . '/views/teacher/my_course.php');
}
