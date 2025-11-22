-- File kiểm tra trạng thái migration
-- Chạy file này để xem các cột và index đã được thêm chưa
-- Lưu ý: File này dùng MySQL syntax, có thể bị linter báo lỗi nếu dùng SQL Server syntax checker

USE course_management;

-- Kiểm tra các cột trong bảng users
SELECT 
    'Checking users table columns' AS Check_Type,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'course_management'
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME IN ('role', 'bio', 'specialization', 'experience_years', 'education', 'linkedin', 'website', 'rating')
ORDER BY ORDINAL_POSITION;

-- Kiểm tra các cột trong bảng courses
SELECT 
    'Checking courses table columns' AS Check_Type,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'course_management'
  AND TABLE_NAME = 'courses'
  AND COLUMN_NAME = 'teacher_id';

-- Kiểm tra các index (MySQL syntax)
-- Lưu ý: Linter có thể báo lỗi nhưng cú pháp này đúng trong MySQL
SELECT 
    'Checking indexes' AS Check_Type,
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'course_management'
  AND (
    (TABLE_NAME = 'courses' AND INDEX_NAME = 'idx_teacher_id')
    OR (TABLE_NAME = 'users' AND INDEX_NAME = 'idx_user_role')
  );

-- Tóm tắt trạng thái
SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = 'course_management' 
            AND TABLE_NAME = 'users' 
            AND COLUMN_NAME = 'bio'
        ) THEN '✓ bio column exists'
        ELSE '✗ bio column missing'
    END AS bio_status,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = 'course_management' 
            AND TABLE_NAME = 'courses' 
            AND COLUMN_NAME = 'teacher_id'
        ) THEN '✓ teacher_id column exists'
        ELSE '✗ teacher_id column missing'
    END AS teacher_id_status,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = 'course_management' 
            AND TABLE_NAME = 'courses' 
            AND INDEX_NAME = 'idx_teacher_id'
        ) THEN '✓ idx_teacher_id index exists'
        ELSE '✗ idx_teacher_id index missing'
    END AS index_status;
