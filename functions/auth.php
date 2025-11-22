<?php

/**
 * Auth Functions - Xử lý xác thực người dùng
 */

// Kiểm tra đã đăng nhập chưa
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Kiểm tra có phải admin không
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Kiểm tra có phải student không
function isStudent()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Kiểm tra có phải teacher không
function isTeacher()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

// Yêu cầu đăng nhập
function requireLogin()
{
    if (!isLoggedIn()) {
        setAlert('Vui lòng đăng nhập để tiếp tục', 'warning');
        redirect(SITE_URL . '/views/login.php');
    }
}

// Yêu cầu quyền admin
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        setAlert('Bạn không có quyền truy cập trang này', 'error');
        redirect(SITE_URL . '/views/login.php');
    }
}

// Yêu cầu quyền student
function requireStudent()
{
    requireLogin();
    if (!isStudent()) {
        setAlert('Trang này chỉ dành cho học viên', 'error');
        redirect(SITE_URL . '/views/login.php');
    }
}

// Yêu cầu quyền teacher
function requireTeacher()
{
    requireLogin();
    if (!isTeacher()) {
        setAlert('Trang này chỉ dành cho giáo viên', 'error');
        redirect(SITE_URL . '/index.php');
    }
}

// Lấy thông tin user hiện tại
function getCurrentUser()
{
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Đăng nhập user
function loginUser($email, $password)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Lưu session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar'];

            // Cập nhật last login (nếu có field này)
            // $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")->execute([$user['user_id']]);

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
    }
}

// Đăng ký user mới
function registerUser($data)
{
    global $pdo;

    // Validate dữ liệu
    $errors = [];

    if (empty($data['full_name'])) {
        $errors[] = 'Vui lòng nhập họ tên';
    }

    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }

    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    }

    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }

    // Kiểm tra email đã tồn tại
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email đã được đăng ký';
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi kiểm tra email';
        }
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Tạo tài khoản
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password, role, status) 
            VALUES (?, ?, ?, ?, 'student', 'active')
        ");

        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'] ?? null,
            $hashed_password
        ]);

        return [
            'success' => true,
            'message' => 'Đăng ký thành công! Vui lòng đăng nhập.',
            'user_id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'errors' => ['Lỗi tạo tài khoản: ' . $e->getMessage()]];
    }
}

// Đăng xuất
function logoutUser()
{
    // Xóa tất cả session
    $_SESSION = [];

    // Xóa cookie session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Hủy session
    session_destroy();

    // Khởi tạo session mới
    session_start();
}

// Đổi mật khẩu
function changePassword($user_id, $old_password, $new_password)
{
    global $pdo;

    try {
        // Lấy mật khẩu hiện tại
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        // Kiểm tra mật khẩu cũ
        if (!password_verify($old_password, $user['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu cũ không chính xác'];
        }

        // Validate mật khẩu mới
        if (strlen($new_password) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'];
        }

        // Cập nhật mật khẩu
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->execute([$hashed_password, $user_id]);

        return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
    }
}

// Reset mật khẩu (gửi email)
function requestPasswordReset($email)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Email không tồn tại trong hệ thống'];
        }

        // Tạo token reset
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Lưu token vào database (cần tạo bảng password_resets)
        // $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
        // $stmt->execute([$user['user_id'], $token, $expiry]);

        // Gửi email (tích hợp PHPMailer hoặc API)
        $reset_link = SITE_URL . "/reset-password.php?token=" . $token;

        // Tạm thời return link
        return [
            'success' => true,
            'message' => 'Link reset mật khẩu đã được gửi đến email của bạn',
            'reset_link' => $reset_link // Trong production nên gửi qua email
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi hệ thống'];
    }
}

// Cập nhật thông tin profile
function updateProfile($user_id, $data)
{
    global $pdo;

    try {
        $allowed_fields = ['full_name', 'phone', 'address'];
        $updates = [];
        $params = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'Không có dữ liệu để cập nhật'];
        }

        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Cập nhật session
        if (isset($data['full_name'])) {
            $_SESSION['full_name'] = $data['full_name'];
        }

        return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
    }
}

// Cập nhật avatar
function updateAvatar($user_id, $file)
{
    global $pdo;

    $upload = uploadFile($file, 'avatars');

    if (!$upload['success']) {
        return $upload;
    }

    try {
        // Lấy avatar cũ để xóa
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Xóa avatar cũ nếu không phải default
        if ($user && $user['avatar'] !== 'default-avatar.jpg') {
            deleteFile($user['avatar']);
        }

        // Cập nhật avatar mới
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $stmt->execute([$upload['path'], $user_id]);

        // Cập nhật session
        $_SESSION['avatar'] = $upload['path'];

        return ['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công', 'avatar' => $upload['path']];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật avatar'];
    }
}
