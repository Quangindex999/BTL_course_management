-- Tạo database
CREATE DATABASE IF NOT EXISTS course_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE course_management;

-- Bảng người dùng
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('student', 'admin') DEFAULT 'student',
    avatar VARCHAR(255) DEFAULT 'default-avatar.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng danh mục khóa học
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng khóa học
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    instructor_name VARCHAR(100),
    duration VARCHAR(50),
    level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    price DECIMAL(10,2) DEFAULT 0.00,
    thumbnail VARCHAR(255),
    start_date DATE,
    end_date DATE,
    max_students INT DEFAULT 30,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng đăng ký khóa học
CREATE TABLE enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng bài học
CREATE TABLE lessons (
    lesson_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_title VARCHAR(200) NOT NULL,
    content TEXT,
    video_url VARCHAR(255),
    duration VARCHAR(20),
    order_number INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng tiến độ học tập
CREATE TABLE progress (
    progress_id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (enrollment_id, lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chèn dữ liệu mẫu
INSERT INTO users (full_name, email, password, role) VALUES
('Admin User', 'admin@course.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Nguyễn Văn A', 'student1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Trần Thị B', 'student2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO categories (category_name, description, icon) VALUES
('Lập trình Web', 'Các khóa học về phát triển web', 'fa-code'),
('Mobile App', 'Phát triển ứng dụng di động', 'fa-mobile-alt'),
('Data Science', 'Khoa học dữ liệu và AI', 'fa-chart-bar'),
('Design', 'Thiết kế đồ họa và UI/UX', 'fa-paint-brush');

INSERT INTO courses (course_name, description, category_id, instructor_name, duration, level, price, start_date, end_date, max_students) VALUES
('PHP & MySQL - Lập trình Web từ A-Z', 'Học lập trình web từ cơ bản đến nâng cao với PHP và MySQL', 1, 'Nguyễn Văn Hoàng', '12 tuần', 'Beginner', 2500000, '2025-01-15', '2025-04-15', 30),
('React Native - Xây dựng App Mobile', 'Phát triển ứng dụng di động đa nền tảng', 2, 'Trần Minh Tuấn', '10 tuần', 'Intermediate', 3000000, '2025-02-01', '2025-04-15', 25),
('Python cho Data Science', 'Phân tích dữ liệu và Machine Learning', 3, 'Lê Thị Mai', '14 tuần', 'Intermediate', 3500000, '2025-01-20', '2025-05-01', 20),
('UI/UX Design Masterclass', 'Thiết kế giao diện người dùng chuyên nghiệp', 4, 'Phạm Đức Anh', '8 tuần', 'Beginner', 2000000, '2025-02-10', '2025-04-10', 35);

INSERT INTO lessons (course_id, lesson_title, content, duration, order_number) VALUES
(1, 'Giới thiệu về PHP', 'Cài đặt môi trường và cú pháp cơ bản', '45 phút', 1),
(1, 'Biến và kiểu dữ liệu', 'Làm việc với biến và các kiểu dữ liệu trong PHP', '60 phút', 2),
(1, 'Kết nối MySQL', 'Kết nối và thao tác với cơ sở dữ liệu', '90 phút', 3);

-- Password mặc định cho tất cả user demo là: password123