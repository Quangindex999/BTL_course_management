<?php
// File: includes/config.php

// Cấu hình Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mặc định XAMPP không có password

// Cấu hình ứng dụng
define('SITE_URL', 'http://localhost/course_management');
define('SITE_NAME', 'EduLearn');
define('SITE_EMAIL', 'info@edulearn.vn');

// Thư mục upload
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Kết nối Database với PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

// Hàm helper
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Hàm escape để bảo mật output
function escape($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Hàm setAlert để lưu thông báo vào session
function setAlert($message, $type = 'success')
{
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Hàm getAlert để lấy và xóa thông báo
function getAlert()
{
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Hàm showAlert để hiển thị thông báo (cải tiến)
function showAlert($message = null, $type = 'success')
{
    // Nếu không có message, lấy từ session
    if ($message === null) {
        $alert = getAlert();
        if ($alert) {
            $message = $alert['message'];
            $type = $alert['type'];
        } else {
            return '';
        }
    }

    // Map type để tương thích với Bootstrap
    $bootstrap_type = match ($type) {
        'error', 'danger' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
        default => 'success'
    };

    return '<div class="alert alert-' . $bootstrap_type . ' alert-dismissible fade show" role="alert">
                ' . escape($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

function getEnrollmentStatus($status)
{
    $statuses = [
        'pending' => '<span class="badge bg-warning">Chờ duyệt</span>',
        'approved' => '<span class="badge bg-success">Đã duyệt</span>',
        'rejected' => '<span class="badge bg-danger">Từ chối</span>',
        'completed' => '<span class="badge bg-info">Hoàn thành</span>'
    ];
    return $statuses[$status] ?? '<span class="badge bg-secondary">Không xác định</span>';
}

function getPaymentStatus($status)
{
    $statuses = [
        'unpaid' => '<span class="badge bg-danger">Chưa thanh toán</span>',
        'paid' => '<span class="badge bg-success">Đã thanh toán</span>',
        'refunded' => '<span class="badge bg-warning">Đã hoàn tiền</span>'
    ];
    return $statuses[$status] ?? '<span class="badge bg-secondary">Không xác định</span>';
}

// Hàm getStatusBadge để hiển thị badge cho các loại status khác nhau
function getStatusBadge($status, $type = 'course')
{
    switch ($type) {
        case 'enrollment':
            return getEnrollmentStatus($status);
        case 'payment':
            return getPaymentStatus($status);
        case 'course':
        default:
            $statuses = [
                'active' => '<span class="badge bg-success">Hoạt động</span>',
                'inactive' => '<span class="badge bg-secondary">Không hoạt động</span>',
                'completed' => '<span class="badge bg-info">Hoàn thành</span>'
            ];
            return $statuses[$status] ?? '<span class="badge bg-secondary">Không xác định</span>';
    }
}

// Xử lý upload file
function uploadFile($file, $allowed_types_or_folder = ['image/jpeg', 'image/png', 'image/jpg'], $max_size = 5242880)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Lỗi upload file'];
    }

    // Nếu tham số thứ 2 là string (folder name), sử dụng allowed_types mặc định
    $allowed_types = is_array($allowed_types_or_folder)
        ? $allowed_types_or_folder
        : ['image/jpeg', 'image/png', 'image/jpg'];

    // Tạo subfolder nếu là string
    $subfolder = is_string($allowed_types_or_folder) ? $allowed_types_or_folder . '/' : '';
    $upload_dir = UPLOAD_DIR . $subfolder;

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB)'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_types)) {
        return ['success' => false, 'message' => 'Định dạng file không được phép'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $filepath = $subfolder . $filename;
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'url' => UPLOAD_URL . $filepath
        ];
    }

    return ['success' => false, 'message' => 'Không thể lưu file'];
}

// Xóa file
function deleteFile($filepath)
{
    if (empty($filepath) || $filepath === 'default-avatar.jpg') {
        return false;
    }

    $normalized = str_replace('\\', '/', $filepath);

    if (str_contains($normalized, '..')) {
        return false;
    }

    $normalized = ltrim($normalized, '/');
    $full_path = UPLOAD_DIR . $normalized;

    if (file_exists($full_path)) {
        return unlink($full_path);
    }

    return false;
}

// Hiển thị badge cho level
function getLevelBadge($level)
{
    $badges = [
        'Beginner' => '<span class="badge bg-success">Cơ bản</span>',
        'Intermediate' => '<span class="badge bg-warning">Trung cấp</span>',
        'Advanced' => '<span class="badge bg-danger">Nâng cao</span>'
    ];

    return $badges[$level] ?? '<span class="badge bg-secondary">' . escape($level) . '</span>';
}

// Pagination helper
function paginate($total_records, $per_page = 10, $page = 1)
{
    $total_records = max(0, (int)$total_records);
    $per_page = max(1, (int)$per_page);

    $calculated_pages = $per_page > 0 ? (int)ceil($total_records / $per_page) : 1;
    $total_pages = max(1, $calculated_pages);
    $page = max(1, min((int)$page, $total_pages));
    $offset = ($page - 1) * $per_page;

    $start_record = $total_records > 0 ? $offset + 1 : 0;
    $end_record = $total_records > 0 ? min($offset + $per_page, $total_records) : 0;
    $has_prev = $page > 1;
    $has_next = $page < $total_pages;

    return [
        'total_pages' => $total_pages,
        'current_page' => $page,
        'per_page' => $per_page,
        'offset' => $offset,
        'total_records' => $total_records,
        'has_prev' => $has_prev,
        'has_next' => $has_next,
        'prev_page' => $has_prev ? $page - 1 : null,
        'next_page' => $has_next ? $page + 1 : null,
        'start_record' => $start_record,
        'end_record' => $end_record
    ];
}

// Email helper (cơ bản)
function sendEmail($to, $subject, $message)
{
    $headers = "From: " . SITE_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}

// Helper function để lấy URL ảnh đúng cách
function getImageUrl($thumbnail, $default = null)
{
    // Nếu thumbnail rỗng hoặc null, trả về default
    if (empty($thumbnail)) {
        return $default ?: 'https://via.placeholder.com/400x250';
    }

    // Nếu đã là URL đầy đủ (http/https), trả về nguyên vẹn
    if (strpos($thumbnail, 'http://') === 0 || strpos($thumbnail, 'https://') === 0) {
        return $thumbnail;
    }

    // Chuẩn hóa đường dẫn: loại bỏ 'uploads/' ở đầu nếu có
    $normalized = $thumbnail;
    if (strpos($normalized, 'uploads/') === 0) {
        $normalized = substr($normalized, 8); // Bỏ 'uploads/' (8 ký tự)
    }

    // Loại bỏ dấu '/' ở đầu nếu có
    $normalized = ltrim($normalized, '/');

    // Trả về URL đầy đủ
    return UPLOAD_URL . $normalized;
}

// Helper function để lấy URL avatar đúng cách
function getAvatarUrl($avatar, $name = '', $size = 100, $default = null)
{
    // Nếu avatar rỗng hoặc là default-avatar.jpg, tạo avatar từ tên
    if (empty($avatar) || $avatar === 'default-avatar.jpg') {
        if (!empty($name)) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=' . $size . '&background=random';
        }
        return $default ?: 'https://ui-avatars.com/api/?name=User&size=' . $size;
    }

    // Nếu đã là URL đầy đủ (http/https), trả về nguyên vẹn
    if (strpos($avatar, 'http://') === 0 || strpos($avatar, 'https://') === 0) {
        return $avatar;
    }

    // Chuẩn hóa đường dẫn: loại bỏ 'uploads/' ở đầu nếu có
    $normalized = $avatar;
    if (strpos($normalized, 'uploads/') === 0) {
        $normalized = substr($normalized, 8); // Bỏ 'uploads/' (8 ký tự)
    }

    // Loại bỏ dấu '/' ở đầu nếu có
    $normalized = ltrim($normalized, '/');

    // Trả về URL đầy đủ
    return UPLOAD_URL . $normalized;
}
