-- Cập nhật database schema để hỗ trợ Teacher role
-- Chạy file này sau khi đã chạy course_db_schema.sql

USE course_management;

-- 1. Cập nhật bảng users: Thêm role 'teacher' và các trường teacher
ALTER TABLE users 
MODIFY COLUMN role ENUM('student', 'teacher', 'admin') DEFAULT 'student';

-- Thêm các trường cho teacher
ALTER TABLE users 
ADD COLUMN bio TEXT AFTER address,
ADD COLUMN specialization VARCHAR(200) AFTER bio,
ADD COLUMN experience_years INT DEFAULT 0 AFTER specialization,
ADD COLUMN education VARCHAR(255) AFTER experience_years,
ADD COLUMN linkedin VARCHAR(255) AFTER education,
ADD COLUMN website VARCHAR(255) AFTER linkedin,
ADD COLUMN rating DECIMAL(3,2) DEFAULT 0.00 AFTER website;

-- 2. Thêm teacher_id vào bảng courses
ALTER TABLE courses 
ADD COLUMN teacher_id INT AFTER category_id,
ADD FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- 3. Tạo index để tối ưu query
CREATE INDEX idx_teacher_id ON courses(teacher_id);
CREATE INDEX idx_user_role ON users(role);

-- 4. Cập nhật dữ liệu mẫu (nếu cần)
-- INSERT INTO users (full_name, email, password, role, specialization, experience_years) VALUES
-- ('Giáo viên Mẫu', 'teacher@course.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Web Development', 5);

