# ✅ CHECKLIST KIỂM TRA DỰ ÁN ĐÃ SẴN SÀNG CHẠY

## 🎯 KIỂM TRA TRƯỚC KHI CHẠY

### 1. ✅ Các File Quan Trọng Đã Được Sửa

- [x] `handle/enroll_process.php` - ✅ Đã thêm logic xử lý đăng ký
- [x] `functions/db_connection.php` - ✅ Đã thêm `deleteFile()`, `getLevelBadge()`, sửa `uploadFile()`
- [x] `views/register.php` - ✅ Đã sửa form và alert

### 2. ✅ Database Schema

- [ ] **Bước 1**: Chạy file `docs/course_db_schema.sql` trong MySQL Workbench

  - Tạo database `course_management`
  - Tạo các bảng: users, categories, courses, enrollments, lessons, progress
  - Chèn dữ liệu mẫu

- [ ] **Bước 2**: Chạy file `docs/update_schema_teacher_safe.sql` trong MySQL Workbench
  - Thêm role 'teacher' vào bảng users
  - Thêm các trường teacher: bio, specialization, experience_years, education, linkedin, website, rating
  - Thêm teacher_id vào bảng courses
  - Tạo các index

### 3. ✅ Cấu Hình Database

Kiểm tra file `functions/db_connection.php`:

```php
define('DB_HOST', 'localhost');        // ✅ Đúng
define('DB_NAME', 'course_management'); // ✅ Đúng
define('DB_USER', 'root');             // ✅ Đúng
define('DB_PASS', '');                 // ✅ Đúng (XAMPP mặc định)
define('SITE_URL', 'http://localhost/course_management'); // ✅ Đúng
```

### 4. ✅ Thư Mục Cần Thiết

- [ ] Thư mục `uploads/` đã tồn tại
  - Nếu chưa có, tạo thư mục: `C:\xampp\htdocs\course_management\uploads`
  - Tạo subfolder: `uploads/avatars/` và `uploads/courses/`

### 5. ✅ XAMPP Đang Chạy

- [ ] Apache đã Start (màu xanh)
- [ ] MySQL đã Start (màu xanh)

---

## 🚀 CÁC BƯỚC CHẠY DỰ ÁN

### Bước 1: Khởi động XAMPP

1. Mở XAMPP Control Panel
2. Start Apache
3. Start MySQL

### Bước 2: Tạo Database

1. Mở MySQL Workbench
2. Kết nối đến localhost
3. Chạy file `docs/course_db_schema.sql`
4. Chạy file `docs/update_schema_teacher_safe.sql`

### Bước 3: Tạo Thư Mục Uploads

```bash
# Tạo thư mục uploads
mkdir C:\xampp\htdocs\course_management\uploads
mkdir C:\xampp\htdocs\course_management\uploads\avatars
mkdir C:\xampp\htdocs\course_management\uploads\courses
```

Hoặc tạo thủ công trong Windows Explorer.

### Bước 4: Truy Cập Website

Mở trình duyệt và truy cập:

```
http://localhost/course_management/
```

---

## ✅ KIỂM TRA SAU KHI CHẠY

### 1. Trang Chủ

- [ ] Truy cập `http://localhost/course_management/` thấy trang chủ
- [ ] Hiển thị khóa học nổi bật
- [ ] Hiển thị thống kê (số khóa học, học viên, đăng ký)

### 2. Đăng Ký

- [ ] Truy cập `http://localhost/course_management/views/register.php`
- [ ] Điền form đăng ký
- [ ] Submit thành công, chuyển đến trang login

### 3. Đăng Nhập

- [ ] Truy cập `http://localhost/course_management/views/login.php`
- [ ] Đăng nhập với tài khoản:
  - **Admin**: `admin@course.com` / `password123`
  - **Student**: `student1@gmail.com` / `password123`
- [ ] Đăng nhập thành công, chuyển đến dashboard

### 4. Admin Dashboard

- [ ] Truy cập `http://localhost/course_management/views/admin/dashboard.php`
- [ ] Hiển thị thống kê
- [ ] Hiển thị đăng ký gần đây
- [ ] Hiển thị khóa học phổ biến

### 5. Quản Lý Users

- [ ] Truy cập `http://localhost/course_management/views/admin/manage_users.php`
- [ ] Hiển thị danh sách users
- [ ] Có thể tạo user mới
- [ ] Có thể sửa user
- [ ] Có thể xóa user

### 6. Quản Lý Courses

- [ ] Truy cập `http://localhost/course_management/views/admin/manage_courses.php`
- [ ] Hiển thị danh sách courses
- [ ] Có thể tạo course mới
- [ ] Có thể sửa course
- [ ] Có thể xóa course

### 7. Student - Khóa Học

- [ ] Đăng nhập với tài khoản student
- [ ] Truy cập `http://localhost/course_management/views/student/course_detail.php`
- [ ] Xem danh sách khóa học
- [ ] Xem chi tiết khóa học
- [ ] Đăng ký khóa học

### 8. Student - Khóa Học Của Tôi

- [ ] Truy cập `http://localhost/course_management/views/student/my_courses.php`
- [ ] Hiển thị khóa học đã đăng ký
- [ ] Có thể hủy đăng ký

---

## ⚠️ CÁC LỖI THƯỜNG GẶP VÀ CÁCH XỬ LÝ

### Lỗi 1: "Lỗi kết nối database"

**Nguyên nhân**: Database chưa được tạo hoặc MySQL chưa chạy
**Giải pháp**:

- Kiểm tra MySQL đã Start chưa
- Chạy file `docs/course_db_schema.sql` trong MySQL Workbench

### Lỗi 2: "404 Not Found"

**Nguyên nhân**: URL không đúng hoặc Apache chưa chạy
**Giải pháp**:

- Kiểm tra Apache đã Start chưa
- Kiểm tra URL: `http://localhost/course_management/` (có dấu gạch dưới)

### Lỗi 3: "Call to undefined function"

**Nguyên nhân**: Function chưa được định nghĩa
**Giải pháp**:

- Đã được sửa trong `functions/db_connection.php`
- Kiểm tra file `db_connection.php` đã được require đúng chưa

### Lỗi 4: "Cannot upload file"

**Nguyên nhân**: Thư mục uploads chưa tồn tại
**Giải pháp**:

- Tạo thư mục `uploads/` trong thư mục gốc
- Tạo subfolder `uploads/avatars/` và `uploads/courses/`

### Lỗi 5: "Table doesn't exist"

**Nguyên nhân**: Database chưa được tạo hoặc chưa chạy migration
**Giải pháp**:

- Chạy file `docs/course_db_schema.sql` trước
- Sau đó chạy `docs/update_schema_teacher_safe.sql`

---

## ✅ TÓM TẮT

Dự án đã được sửa các lỗi chính:

- ✅ `handle/enroll_process.php` - Đã có logic xử lý
- ✅ `functions/db_connection.php` - Đã thêm các function còn thiếu
- ✅ `views/register.php` - Đã sửa form

**Điều kiện để chạy:**

1. ✅ XAMPP đang chạy (Apache + MySQL)
2. ✅ Database đã được tạo (chạy `course_db_schema.sql`)
3. ✅ Migration đã chạy (chạy `update_schema_teacher_safe.sql`)
4. ✅ Thư mục `uploads/` đã tồn tại

**Sau khi hoàn thành các bước trên, dự án sẽ chạy được! 🎉**
