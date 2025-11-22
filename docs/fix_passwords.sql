-- Script để cập nhật password hash đúng cho các tài khoản demo
-- Password: password123
-- Chạy file này trong phpMyAdmin để fix password

-- Password hash mới cho "password123"
-- Lưu ý: Mỗi lần chạy password_hash() sẽ tạo hash khác nhau, nhưng đều verify được với cùng password

UPDATE users 
SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' 
WHERE email = 'admin@course.com';

UPDATE users 
SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' 
WHERE email = 'student1@gmail.com';

UPDATE users 
SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' 
WHERE email = 'student2@gmail.com';

-- Hoặc sử dụng cách này để tạo hash mới (chạy trong PHP):
-- password_hash('password123', PASSWORD_DEFAULT)

