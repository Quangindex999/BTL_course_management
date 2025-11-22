<?php

/**
 * Script để cập nhật password hash đúng cho các tài khoản demo
 * Chạy file này một lần để fix password trong database
 */

require_once 'functions/db_connection.php';

// Password mặc định cho tất cả tài khoản demo
$default_password = 'password123';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

echo "Đang cập nhật password hash...\n";
echo "Password hash mới: " . $hashed_password . "\n\n";

try {
    // Cập nhật password cho tất cả user demo (admin, student, teacher)
    $emails = [
        'admin@course.com',
        'student1@gmail.com',
        'student2@gmail.com',
        'teacher1@edulearn.vn',
        'teacher2@edulearn.vn',
        'teacher3@edulearn.vn',
        'teacher4@edulearn.vn'
    ];

    $placeholders = implode(',', array_fill(0, count($emails), '?'));
    $sql = "UPDATE users SET password = ? WHERE email IN ($placeholders)";

    $params = array_merge([$hashed_password], $emails);
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
        $updated = $stmt->rowCount();
        echo "✅ Đã cập nhật password thành công cho $updated tài khoản!\n";
        echo "\nBây giờ bạn có thể đăng nhập với:\n";
        echo "- Admin: admin@course.com / password123\n";
        echo "- Học viên: student1@gmail.com / password123\n";
        echo "- Học viên: student2@gmail.com / password123\n";
        echo "- Giáo viên: teacher1@edulearn.vn / password123\n";
        echo "- Giáo viên: teacher2@edulearn.vn / password123\n";
        echo "- Giáo viên: teacher3@edulearn.vn / password123\n";
        echo "- Giáo viên: teacher4@edulearn.vn / password123\n";
    } else {
        echo "❌ Có lỗi xảy ra khi cập nhật password\n";
    }
} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
