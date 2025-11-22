<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

requireAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            // Validate
            if (empty($_POST['full_name'])) $errors[] = 'Vui lòng nhập họ tên';
            if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            }
            if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
                $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $errors[] = 'Mật khẩu xác nhận không khớp';
            }

            // Check email exists
            if (empty($errors)) {
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email đã tồn tại';
                }
            }

            if (!empty($errors)) {
                setAlert(implode('<br>', $errors), 'error');
                redirect(SITE_URL . '/views/admin/create_users.php');
            }

            try {
                // Upload avatar if provided
                $avatar = 'default-avatar.jpg';
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['avatar'], 'avatars');
                    if ($upload['success']) {
                        $avatar = $upload['path'];
                    }
                }

                // Insert user
                $sql = "INSERT INTO users (full_name, email, phone, address, password, role, avatar, status";
                $values = "?, ?, ?, ?, ?, ?, ?, ?";
                $params = [
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['phone'] ?? null,
                    $_POST['address'] ?? null,
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['role'],
                    $avatar,
                    $_POST['status'] ?? 'active'
                ];

                // Add teacher fields if role is teacher
                if ($_POST['role'] === 'teacher') {
                    $sql .= ", bio, specialization, experience_years, education, linkedin, website";
                    $values .= ", ?, ?, ?, ?, ?, ?";
                    $params = array_merge($params, [
                        $_POST['bio'] ?? null,
                        $_POST['specialization'] ?? null,
                        intval($_POST['experience_years'] ?? 0),
                        $_POST['education'] ?? null,
                        $_POST['linkedin'] ?? null,
                        $_POST['website'] ?? null
                    ]);
                }

                $sql .= ") VALUES ($values)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                setAlert('Tạo người dùng thành công!', 'success');
                redirect(SITE_URL . '/views/admin/manage_users.php');
            } catch (PDOException $e) {
                setAlert('Lỗi: ' . $e->getMessage(), 'error');
                redirect(SITE_URL . '/views/admin/create_users.php');
            }
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = intval($_POST['user_id']);

            try {
                // Upload avatar if provided
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    // Get old avatar
                    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $old_user = $stmt->fetch();

                    // Delete old avatar if not default
                    if ($old_user && $old_user['avatar'] !== 'default-avatar.jpg') {
                        deleteFile($old_user['avatar']);
                    }

                    // Upload new avatar
                    $upload = uploadFile($_FILES['avatar'], 'avatars');
                    if ($upload['success']) {
                        $_POST['avatar'] = $upload['path'];
                    }
                }

                // Build update query
                $updates = [];
                $params = [];

                $allowed_fields = ['full_name', 'email', 'phone', 'address', 'role', 'status', 'avatar'];

                foreach ($allowed_fields as $field) {
                    if (isset($_POST[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $_POST[$field];
                    }
                }

                // Add teacher fields if role is teacher
                if ($_POST['role'] === 'teacher') {
                    $teacher_fields = ['bio', 'specialization', 'experience_years', 'education', 'linkedin', 'website'];
                    foreach ($teacher_fields as $field) {
                        if (isset($_POST[$field])) {
                            $updates[] = "$field = ?";
                            $params[] = $field === 'experience_years' ? intval($_POST[$field]) : $_POST[$field];
                        }
                    }
                }

                $params[] = $user_id;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                setAlert('Cập nhật thành công!', 'success');
                redirect(SITE_URL . '/views/admin/edit_users.php?id=' . $user_id);
            } catch (PDOException $e) {
                setAlert('Lỗi: ' . $e->getMessage(), 'error');
                redirect(SITE_URL . '/views/admin/edit_users.php?id=' . $user_id);
            }
        }
        break;

    case 'delete':
        if (isset($_GET['id'])) {
            $user_id = intval($_GET['id']);

            // Cannot delete self
            if ($user_id == $_SESSION['user_id']) {
                setAlert('Không thể xóa chính mình!', 'error');
                redirect(SITE_URL . '/views/admin/manage_users.php');
            }

            try {
                // Get avatar to delete
                $stmt = $pdo->prepare("SELECT avatar FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                // Delete user
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);

                // Delete avatar if not default
                if ($user && $user['avatar'] !== 'default-avatar.jpg') {
                    deleteFile($user['avatar']);
                }

                setAlert('Xóa người dùng thành công!', 'success');
            } catch (PDOException $e) {
                setAlert('Lỗi: Không thể xóa người dùng có dữ liệu liên quan', 'error');
            }
        }
        redirect(SITE_URL . '/views/admin/manage_users.php');
        break;

    case 'bulk_delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
            $ids = array_map('intval', $_POST['ids']);
            // Prevent self-delete
            $ids = array_filter($ids, function ($id) {
                return $id != ($_SESSION['user_id'] ?? 0);
            });

            if (!empty($ids)) {
                try {
                    // Get avatars
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("SELECT user_id, avatar FROM users WHERE user_id IN ($in)");
                    $stmt->execute($ids);
                    $rows = $stmt->fetchAll();

                    // Delete users
                    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id IN ($in)");
                    $stmt->execute($ids);

                    // Delete avatars
                    foreach ($rows as $row) {
                        if ($row['avatar'] && $row['avatar'] !== 'default-avatar.jpg') {
                            deleteFile($row['avatar']);
                        }
                    }

                    setAlert('Đã xóa ' . count($ids) . ' người dùng.', 'success');
                } catch (PDOException $e) {
                    setAlert('Lỗi: Không thể xóa một số người dùng vì ràng buộc dữ liệu.', 'error');
                }
            } else {
                setAlert('Không có người dùng hợp lệ để xóa.', 'warning');
            }
        } else {
            setAlert('Vui lòng chọn người dùng để xóa.', 'warning');
        }
        redirect(SITE_URL . '/views/admin/manage_users.php');
        break;

    case 'bulk_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
            $ids = array_map('intval', $_POST['ids']);
            $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

            if (!empty($ids)) {
                try {
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $params = array_merge([$status], $ids);
                    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id IN ($in)");
                    $stmt->execute($params);
                    setAlert('Cập nhật trạng thái thành công cho ' . count($ids) . ' người dùng.', 'success');
                } catch (PDOException $e) {
                    setAlert('Lỗi: Không thể cập nhật trạng thái.', 'error');
                }
            } else {
                setAlert('Không có người dùng hợp lệ.', 'warning');
            }
        } else {
            setAlert('Vui lòng chọn người dùng.', 'warning');
        }
        redirect(SITE_URL . '/views/admin/manage_users.php');
        break;

    default:
        redirect(SITE_URL . '/views/admin/manage_users.php');
}
