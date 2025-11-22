-- Cập nhật bảng users để hỗ trợ role teacher
ALTER TABLE users 
MODIFY COLUMN role ENUM('student', 'teacher', 'admin') DEFAULT 'student';

-- Thêm các trường cho giáo viên
ALTER TABLE users
ADD COLUMN bio TEXT AFTER address,
ADD COLUMN specialization VARCHAR(200) AFTER bio,
ADD COLUMN experience_years INT DEFAULT 0 AFTER specialization,
ADD COLUMN education VARCHAR(200) AFTER experience_years,
ADD COLUMN rating DECIMAL(3,2) DEFAULT 0.00 AFTER education,
ADD COLUMN total_students INT DEFAULT 0 AFTER rating,
ADD COLUMN linkedin VARCHAR(255) AFTER total_students,
ADD COLUMN website VARCHAR(255) AFTER linkedin;

-- Tạo bảng teacher_courses để liên kết giáo viên với khóa học
CREATE TABLE IF NOT EXISTS teacher_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    course_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (teacher_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cập nhật bảng courses để có teacher_id
ALTER TABLE courses
ADD COLUMN teacher_id INT AFTER instructor_name,
ADD FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- Thêm dữ liệu mẫu giáo viên
INSERT INTO users (full_name, email, password, role, phone, bio, specialization, experience_years, education, rating) VALUES
('Nguyễn Văn Hoàng', 'teacher1@edulearn.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '0912345678', 
'Giảng viên với 10 năm kinh nghiệm trong lĩnh vực lập trình web và phát triển ứng dụng. Đam mê giảng dạy và chia sẻ kiến thức.', 
'Web Development, PHP, JavaScript', 10, 'Thạc sĩ Công nghệ thông tin - ĐH Bách Khoa Hà Nội', 4.8),

('Trần Minh Tuấn', 'teacher2@edulearn.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '0923456789',
'Chuyên gia Mobile Development với nhiều năm kinh nghiệm làm việc tại các công ty công nghệ hàng đầu.', 
'Mobile Development, React Native, Flutter', 8, 'Cử nhân Khoa học máy tính - ĐH Công nghệ', 4.7),

('Lê Thị Mai', 'teacher3@edulearn.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '0934567890',
'Data Scientist với chứng chỉ quốc tế, đã đào tạo hơn 1000 học viên về Machine Learning và AI.', 
'Data Science, Machine Learning, Python', 7, 'Tiến sĩ Khoa học dữ liệu - ĐH Quốc gia', 4.9),

('Phạm Đức Anh', 'teacher4@edulearn.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '0945678901',
'UI/UX Designer với portfolio đa dạng từ startup đến enterprise. Passionate về thiết kế trải nghiệm người dùng.', 
'UI/UX Design, Figma, Adobe XD', 6, 'Cử nhân Thiết kế đồ họa - ĐH Mỹ thuật', 4.6);

-- Gán giáo viên cho các khóa học hiện có
UPDATE courses SET teacher_id = (SELECT user_id FROM users WHERE email = 'teacher1@edulearn.vn') WHERE course_id = 1;
UPDATE courses SET teacher_id = (SELECT user_id FROM users WHERE email = 'teacher2@edulearn.vn') WHERE course_id = 2;
UPDATE courses SET teacher_id = (SELECT user_id FROM users WHERE email = 'teacher3@edulearn.vn') WHERE course_id = 3;
UPDATE courses SET teacher_id = (SELECT user_id FROM users WHERE email = 'teacher4@edulearn.vn') WHERE course_id = 4;

-- Thêm vào teacher_courses
INSERT INTO teacher_courses (teacher_id, course_id) 
SELECT u.user_id, c.course_id 
FROM courses c 
JOIN users u ON c.teacher_id = u.user_id 
WHERE c.teacher_id IS NOT NULL;

-- Cập nhật instructor_name từ teacher_id
UPDATE courses c 
JOIN users u ON c.teacher_id = u.user_id 
SET c.instructor_name = u.full_name
WHERE c.teacher_id IS NOT NULL;

-- Password cho tất cả teacher demo: password123