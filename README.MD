# 🎓 Hệ Thống Quản Lý Đăng Ký Khóa Học

## 📋 Mô tả dự án

Hệ thống quản lý đăng ký khóa học trực tuyến được xây dựng bằng PHP, MySQL, Bootstrap 5. Dự án bài tập lớn môn Lập trình PHP.

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3.2
- **Icons**: Font Awesome 6.5.1
- **Server**: XAMPP (Apache + MySQL)
- **Database Tool**: MySQL Workbench

## 📂 Cấu trúc thư mục

```
course-management/
├── css/                          # Thư mục CSS
│   ├── admin.css                # Style cho admin
│   ├── footer.css               # Style footer
│   ├── login.css                # Style trang đăng nhập
│   ├── popular.css              # Style khóa học phổ biến
│   ├── register.css             # Style trang đăng ký
│   ├── student.css              # Style cho học viên
│   └── user.css                 # Style chung cho user
│
├── docs/                         # Tài liệu
│   └── database_schema.sql      # File SQL tạo database
│
├── functions/                    # Các function xử lý
│   ├── auth.php                 # Xác thực người dùng
│   ├── course_functions.php     # Xử lý khóa học
│   ├── db_connection.php        # Kết nối database
│   ├── enrollments_functions.php # Xử lý đăng ký
│   └── user_functions.php       # Xử lý người dùng
│
├── handle/                       # Xử lý form submit
│   ├── cancel_process.php       # Hủy đăng ký
│   ├── course_process.php       # Xử lý khóa học
│   ├── enroll_process.php       # Xử lý đăng ký khóa học
│   ├── login_process.php        # Xử lý đăng nhập
│   ├── logout_process.php       # Xử lý đăng xuất
│   ├── register_process.php     # Xử lý đăng ký tài khoản
│   └── user_process.php         # Xử lý cập nhật user
│
├── images/                       # Thư mục hình ảnh
│   ├── courses/                 # Ảnh khóa học
│   ├── avatars/                 # Ảnh đại diện
│   └── banners/                 # Ảnh banner
│
├── views/                        # Các view/trang
│   ├── admin/                   # Trang quản trị
│   │   ├── create_courses.php   # Tạo khóa học
│   │   ├── create_users.php     # Tạo người dùng
│   │   ├── dashboard.php        # Dashboard admin
│   │   ├── edit_courses.php     # Sửa khóa học
│   │   ├── edit_users.php       # Sửa người dùng
│   │   ├── manage_courses.php   # Quản lý khóa học
│   │   └── manage_users.php     # Quản lý người dùng
│   │
│   ├── student/                 # Trang học viên
│   │   ├── course_detail.php    # Chi tiết khóa học
│   │   ├── menu_student.php     # Menu học viên
│   │   └── my_courses.php       # Khóa học của tôi
│   │
│   ├── teacher/                 # Trang giảng viên (mở rộng)
│   │   ├── menu_teacher.php     # Menu giảng viên
│   │   └── menu.php             # Menu chung
│   │
│   ├── index.php                # Trang chủ
│   └── register.php             # Trang đăng ký
│
├── index.php                     # File chính trang chủ
└── README.md                     # File này
```

## ⚙️ Cài đặt

### 1. Yêu cầu hệ thống

- XAMPP 8.x trở lên
- PHP 8.0+
- MySQL 8.0+
- MySQL Workbench (khuyến nghị)
- Trình duyệt hiện đại (Chrome, Firefox, Edge)

### 2. Các bước cài đặt

**Bước 1: Clone hoặc tải project**

```bash
# Đặt vào thư mục htdocs của XAMPP
C:\xampp\htdocs\course-management
```

**Bước 2: Khởi động XAMPP**

- Mở XAMPP Control Panel
- Start Apache
- Start MySQL

**Bước 3: Tạo Database**

- Mở MySQL Workbench
- Kết nối đến localhost
- Chạy file `docs/database_schema.sql`
- Database `course_management` sẽ được tạo với dữ liệu mẫu

**Bước 4: Cấu hình kết nối**

- Mở file `functions/db_connection.php`
- Kiểm tra thông tin kết nối:

```php
$host = 'localhost';
$dbname = 'course_management';
$username = 'root';
$password = ''; // Mặc định XAMPP không có password
```

**Bước 5: Truy cập hệ thống**

```
http://localhost/course-management/
```

## 👥 Tài khoản mẫu

### Admin

- Email: `admin@course.com`
- Password: `password123`

### Học viên

- Email: `student1@gmail.com`
- Password: `password123`

- Email: `student2@gmail.com`
- Password: `password123`

## 🎯 Chức năng chính

### Dành cho học viên

- ✅ Đăng ký tài khoản
- ✅ Đăng nhập/Đăng xuất
- ✅ Xem danh sách khóa học
- ✅ Tìm kiếm và lọc khóa học
- ✅ Xem chi tiết khóa học
- ✅ Đăng ký khóa học
- ✅ Quản lý khóa học đã đăng ký
- ✅ Hủy đăng ký khóa học
- ✅ Cập nhật thông tin cá nhân

### Dành cho Admin

- ✅ Dashboard thống kê
- ✅ Quản lý người dùng (CRUD)
- ✅ Quản lý khóa học (CRUD)
- ✅ Quản lý danh mục
- ✅ Duyệt đăng ký khóa học
- ✅ Quản lý thanh toán
- ✅ Báo cáo thống kê
- ✅ Phân trang danh sách (khóa học/người dùng/đăng ký)

## 🎨 Tính năng nổi bật

- 🎯 Giao diện hiện đại, responsive
- 🔐 Bảo mật với password hashing
- 🔍 Tìm kiếm và lọc nâng cao
- 📊 Dashboard thống kê real-time
- 💳 Quản lý đăng ký và thanh toán
- 📱 Tương thích mọi thiết bị
- ⚡ Performance tối ưu
- 🎨 UI/UX thân thiện
- 📚 Danh sách hiển thị theo phân trang server-side, tránh tải quá nhiều dữ liệu
- 🧹 Xóa khóa học tự động dọn dữ liệu liên quan (bài học/đăng ký/đánh giá) để DB luôn đồng bộ

## 🗄️ Database Schema

### Bảng chính

1. **users** - Người dùng
2. **categories** - Danh mục khóa học
3. **courses** - Khóa học
4. **enrollments** - Đăng ký khóa học
5. **lessons** - Bài học
6. **progress** - Tiến độ học tập

## 📝 Ghi chú

- Dự án được phát triển cho mục đích học tập
- Code được comment đầy đủ bằng tiếng Việt
- Tuân thủ chuẩn PSR-12 coding standard
- Sử dụng PDO để bảo mật SQL injection

## 🐛 Bug Report

Nếu phát hiện lỗi, vui lòng tạo issue hoặc liên hệ:

- Email: info@edulearn.vn
- Phone: (+84) 123 456 789

## 📄 License

MIT License - Tự do sử dụng cho mục đích học tập

## 👨‍💻 Tác giả

- **Sinh viên**: [Tên của bạn]
- **Lớp**: [Lớp học]
- **Môn học**: Lập trình PHP
- **Năm học**: 2024-2025

---

### 🚀 Happy Coding! 💻
