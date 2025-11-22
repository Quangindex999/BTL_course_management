-- ============================================
-- Database Dump: course_management
-- Generated: 2025-11-11 18:05:05
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


-- --------------------------------------------------------
-- Cấu trúc bảng: `categories`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dữ liệu bảng: `categories`
-- --------------------------------------------------------

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `icon`, `created_at`) VALUES
('1', 'Lập trình Web', 'Các khóa học về phát triển web', 'fa-code', '2025-11-06 20:52:24'),
('2', 'Mobile App', 'Phát triển ứng dụng di động', 'fa-mobile-alt', '2025-11-06 20:52:24'),
('3', 'Data Science', 'Khoa học dữ liệu và AI', 'fa-chart-bar', '2025-11-06 20:52:24'),
('4', 'Design', 'Thiết kế đồ họa và UI/UX', 'fa-paint-brush', '2025-11-06 20:52:24');


-- --------------------------------------------------------
-- Cấu trúc bảng: `courses`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `instructor_name` varchar(100) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `level` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner',
  `price` decimal(10,2) DEFAULT 0.00,
  `thumbnail` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `max_students` int(11) DEFAULT 30,
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`course_id`),
  KEY `category_id` (`category_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dữ liệu bảng: `courses`
-- --------------------------------------------------------

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `category_id`, `instructor_name`, `teacher_id`, `duration`, `level`, `price`, `thumbnail`, `start_date`, `end_date`, `max_students`, `status`, `created_at`, `updated_at`) VALUES
('1', 'PHP & MySQL - Lập trình Web từ A-Z', 'Học lập trình web từ cơ bản đến nâng cao với PHP và MySQL', '1', 'Nguyễn Văn Hoàng', '5', '12 tuần', 'Beginner', '2600000.00', NULL, '2025-01-15', '2025-04-15', '30', 'active', '2025-11-06 20:52:24', '2025-11-11 17:08:48'),
('2', 'React Native - Xây dựng App Mobile', 'Phát triển ứng dụng di động đa nền tảng', '2', 'Trần Minh Tuấn', '6', '10 tuần', 'Intermediate', '3000000.00', NULL, '2025-02-01', '2025-04-15', '25', 'active', '2025-11-06 20:52:24', '2025-11-09 12:03:51'),
('3', 'Python cho Data Science', 'Phân tích dữ liệu và Machine Learning', '3', 'Lê Thị Mai', '7', '14 tuần', 'Intermediate', '3500000.00', NULL, '2025-01-20', '2025-05-01', '20', 'active', '2025-11-06 20:52:24', '2025-11-09 12:03:51'),
('4', 'UI/UX Design Masterclass', 'Thiết kế giao diện người dùng chuyên nghiệp', '4', 'Phạm Đức Anh', '8', '8 tuần', 'Beginner', '2000000.00', NULL, '2025-02-10', '2025-04-10', '35', 'active', '2025-11-06 20:52:24', '2025-11-09 12:03:51'),
('5', 'Machine Learning', 'Machine learning (ML) hay máy học là một nhánh của trí tuệ nhân tạo (AI), nó là một lĩnh vực nghiên cứu cho phép máy tính có khả năng cải thiện chính bản thân chúng dựa trên dữ liệu mẫu (training data) hoặc dựa vào kinh nghiệm (những gì đã được học). Machine learning có thể tự dự đoán hoặc đưa ra quyết định mà không cần được lập trình cụ thể.

Bài toán machine learning thường được chia làm hai loại là dự đoán (prediction) và phân loại (classification). Các bài toán dự đoán như dự đoán giá nhà, giá xe… Các bài toán phân loại như nhận diện chữ viết tay, nhận diện đồ vật…', '3', 'Thu Trang', NULL, '12 tuần', 'Intermediate', '1000000.00', NULL, '2025-11-08', '2025-12-31', '30', 'inactive', '2025-11-07 10:27:18', '2025-11-07 11:13:33'),
('6', 'nvvnv', 'nvvnc', '3', 'nnnnnnn', NULL, '12 tuần', 'Beginner', '10000000.00', NULL, '2025-11-08', '2025-12-05', '30', 'inactive', '2025-11-07 11:17:53', '2025-11-09 12:22:37'),
('7', 'nvvnv', 'ghjghjghj', '3', 'fghjfghj', NULL, '12 tuần', 'Beginner', '1000000.00', NULL, '2025-11-11', '2025-11-27', '30', 'inactive', '2025-11-11 17:24:49', '2025-11-11 17:24:52'),
('8', 'nvvnv', 'ftdhfghfgh', '4', 'fghghghf', NULL, '12 tuần', 'Beginner', '1000000.00', NULL, '2025-11-11', '2025-12-04', '30', 'inactive', '2025-11-11 17:52:37', '2025-11-11 17:52:47');


-- --------------------------------------------------------
-- Cấu trúc bảng: `enrollments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `unique_enrollment` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dữ liệu bảng: `enrollments`
-- --------------------------------------------------------

INSERT INTO `enrollments` (`enrollment_id`, `user_id`, `course_id`, `enrollment_date`, `status`, `payment_status`, `payment_method`, `notes`) VALUES
('4', '10', '3', '2025-11-09 23:19:51', 'approved', 'paid', 'bank_transfer', NULL),
('5', '10', '2', '2025-11-09 23:25:25', 'rejected', 'unpaid', 'bank_transfer', 'cút mm đi');


-- --------------------------------------------------------
-- Cấu trúc bảng: `lessons`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `lesson_title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `order_number` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`lesson_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dữ liệu bảng: `lessons`
-- --------------------------------------------------------

INSERT INTO `lessons` (`lesson_id`, `course_id`, `lesson_title`, `content`, `video_url`, `duration`, `order_number`, `created_at`) VALUES
('1', '1', 'Giới thiệu về PHP', 'Cài đặt môi trường và cú pháp cơ bản', NULL, '45 phút', '1', '2025-11-06 20:52:24'),
('2', '1', 'Biến và kiểu dữ liệu', 'Làm việc với biến và các kiểu dữ liệu trong PHP', NULL, '60 phút', '2', '2025-11-06 20:52:24'),
('3', '1', 'Kết nối MySQL', 'Kết nối và thao tác với cơ sở dữ liệu', NULL, '90 phút', '3', '2025-11-06 20:52:24');


-- --------------------------------------------------------
-- Cấu trúc bảng: `progress`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `progress`;
CREATE TABLE `progress` (
  `progress_id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `unique_progress` (`enrollment_id`,`lesson_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE,
  CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Cấu trúc bảng: `teacher_courses`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `teacher_courses`;
CREATE TABLE `teacher_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`teacher_id`,`course_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `teacher_courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dữ liệu bảng: `teacher_courses`
-- --------------------------------------------------------

INSERT INTO `teacher_courses` (`id`, `teacher_id`, `course_id`, `assigned_at`) VALUES
('1', '5', '1', '2025-11-09 12:03:51'),
('2', '6', '2', '2025-11-09 12:03:51'),
('3', '7', '3', '2025-11-09 12:03:51'),
('4', '8', '4', '2025-11-09 12:03:51');


-- --------------------------------------------------------
-- Cấu trúc bảng: `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `specialization` varchar(200) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `education` varchar(200) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_students` int(11) DEFAULT 0,
  `linkedin` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `avatar` varchar(255) DEFAULT 'default-avatar.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dữ liệu bảng: `users`
-- --------------------------------------------------------

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `bio`, `specialization`, `experience_years`, `education`, `rating`, `total_students`, `linkedin`, `website`, `role`, `avatar`, `created_at`, `updated_at`, `status`) VALUES
('1', 'Admin User', 'admin@course.com', '$2y$10$w2Y5J6iuysd2gcP0Y4796OTRU82qTwlM2RhRJztvWgIMe8HToe/gO', NULL, NULL, NULL, NULL, '0', NULL, '0.00', '0', NULL, NULL, 'admin', 'default-avatar.jpg', '2025-11-06 20:52:24', '2025-11-10 23:26:46', 'active'),
('5', 'Nguyễn Văn Hoàng', 'teacher1@edulearn.vn', '$2y$10$w2Y5J6iuysd2gcP0Y4796OTRU82qTwlM2RhRJztvWgIMe8HToe/gO', '0912345678', NULL, 'Giảng viên với 10 năm kinh nghiệm trong lĩnh vực lập trình web và phát triển ứng dụng. Đam mê giảng dạy và chia sẻ kiến thức.', 'Web Development, PHP, JavaScript', '10', 'Thạc sĩ Công nghệ thông tin - ĐH Bách Khoa Hà Nội', '4.80', '0', NULL, NULL, 'teacher', 'default-avatar.jpg', '2025-11-09 12:03:51', '2025-11-11 22:54:10', 'active'),
('6', 'Trần Minh Tuấn', 'teacher2@edulearn.vn', '$2y$10$w2Y5J6iuysd2gcP0Y4796OTRU82qTwlM2RhRJztvWgIMe8HToe/gO', '0923456789', NULL, 'Chuyên gia Mobile Development với nhiều năm kinh nghiệm làm việc tại các công ty công nghệ hàng đầu.', 'Mobile Development, React Native, Flutter', '8', 'Cử nhân Khoa học máy tính - ĐH Công nghệ', '4.70', '0', NULL, NULL, 'teacher', 'default-avatar.jpg', '2025-11-09 12:03:51', '2025-11-10 23:26:46', 'active'),
('7', 'Lê Thị Mai', 'teacher3@edulearn.vn', '$2y$10$w2Y5J6iuysd2gcP0Y4796OTRU82qTwlM2RhRJztvWgIMe8HToe/gO', '0934567890', NULL, 'Data Scientist với chứng chỉ quốc tế, đã đào tạo hơn 1000 học viên về Machine Learning và AI.', 'Data Science, Machine Learning, Python', '7', 'Tiến sĩ Khoa học dữ liệu - ĐH Quốc gia', '4.90', '0', NULL, NULL, 'teacher', 'default-avatar.jpg', '2025-11-09 12:03:51', '2025-11-10 23:26:46', 'active'),
('8', 'Phạm Đức Anh', 'teacher4@edulearn.vn', '$2y$10$w2Y5J6iuysd2gcP0Y4796OTRU82qTwlM2RhRJztvWgIMe8HToe/gO', '0945678901', NULL, 'UI/UX Designer với portfolio đa dạng từ startup đến enterprise. Passionate về thiết kế trải nghiệm người dùng.', 'UI/UX Design, Figma, Adobe XD', '6', 'Cử nhân Thiết kế đồ họa - ĐH Mỹ thuật', '4.60', '0', NULL, NULL, 'teacher', 'default-avatar.jpg', '2025-11-09 12:03:51', '2025-11-10 23:26:46', 'active'),
('10', 'Nguyễn Nhật Quang', 'quangit@dnu.edu.vn', '$2y$10$MuiFNGgd0H5VJEN93Z0W5.t6hs0pKo1iu02TjQtxMqueyc/xD9jfq', '09xxxxxxx5', 'số 10 đan phượng, hà nội', NULL, NULL, '0', NULL, '0.00', '0', NULL, NULL, 'student', 'avatars/69130acd4af52_1762855629.jpg', '2025-11-09 23:15:59', '2025-11-11 22:54:10', 'active');

COMMIT;
-- ============================================
-- End of Dump
-- ============================================
