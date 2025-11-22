# 📚 HƯỚNG DẪN CHẠY DỰ ÁN COURSE MANAGEMENT

## 🎯 Tổng quan dự án

Đây là hệ thống quản lý đăng ký khóa học trực tuyến được xây dựng bằng:

- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.3.2
- **Server**: XAMPP (Apache + MySQL)

---

## ✅ YÊU CẦU HỆ THỐNG

1. **XAMPP 8.x trở lên** (đã cài đặt Apache và MySQL)
2. **PHP 8.0+** (đã có trong XAMPP)
3. **MySQL 8.0+** (đã có trong XAMPP)
4. **Trình duyệt hiện đại** (Chrome, Firefox, Edge)

---

## 📋 CÁC BƯỚC CÀI ĐẶT VÀ CHẠY DỰ ÁN

### **Bước 1: Kiểm tra vị trí dự án**

Dự án phải được đặt trong thư mục:

```
C:\xampp\htdocs\course_management
```

Nếu bạn đang ở đúng vị trí này, bạn sẽ thấy các thư mục:

- `functions/`
- `views/`
- `handle/`
- `css/`
- `docs/`
- `index.php`

### **Bước 2: Khởi động XAMPP**

1. Mở **XAMPP Control Panel**
2. Click **Start** cho **Apache**
3. Click **Start** cho **MySQL**
4. Đảm bảo cả hai đều hiển thị màu xanh (running)

### **Bước 3: Tạo Database**

Có 2 cách để tạo database:

#### **Cách 1: Sử dụng phpMyAdmin (Khuyến nghị)**

1. Mở trình duyệt và truy cập: `http://localhost/phpmyadmin`
2. Click tab **SQL** ở phía trên
3. Mở file `docs/course_db_schema.sql` bằng Notepad hoặc editor
4. Copy toàn bộ nội dung file SQL
5. Paste vào khung SQL trong phpMyAdmin
6. Click **Go** hoặc nhấn **Ctrl + Enter**
7. Kiểm tra bên trái có database `course_management` xuất hiện

#### **Cách 2: Sử dụng MySQL Command Line**

1. Mở Command Prompt hoặc PowerShell
2. Chạy lệnh:

```bash
cd C:\xampp\mysql\bin
mysql -u root -e "source C:/xampp/htdocs/course_management/docs/course_db_schema.sql"
```

### **Bước 4: Kiểm tra cấu hình kết nối Database**

Mở file `functions/db_connection.php` và kiểm tra:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mặc định XAMPP không có password
```

**Lưu ý**:

- Nếu MySQL của bạn có password, thay `''` bằng password của bạn
- Đảm bảo `SITE_URL` là: `http://localhost/course_management` (có dấu gạch dưới)

### **Bước 5: Tạo thư mục uploads (nếu chưa có)**

Tạo thư mục `uploads` trong thư mục gốc của dự án:

```
C:\xampp\htdocs\course_management\uploads
```

Hoặc chạy lệnh trong PowerShell:

```powershell
New-Item -ItemType Directory -Path "C:\xampp\htdocs\course_management\uploads"
```

### **Bước 6: Truy cập hệ thống**

Mở trình duyệt và truy cập:

```
http://localhost/course_management/
```

Bạn sẽ thấy trang chủ của hệ thống!

---

## 👤 TÀI KHOẢN ĐĂNG NHẬP MẪU

Sau khi import database, bạn có thể đăng nhập với các tài khoản sau:

### **Tài khoản Admin:**

- **Email**: `admin@course.com`
- **Password**: `password123`

### **Tài khoản Học viên:**

- **Email**: `student1@gmail.com`
- **Password**: `password123`

- **Email**: `student2@gmail.com`
- **Password**: `password123`

---

## 🔧 XỬ LÝ LỖI THƯỜNG GẶP

### **Lỗi 1: "Lỗi kết nối database"**

**Nguyên nhân**: Database chưa được tạo hoặc thông tin kết nối sai

**Giải pháp**:

1. Kiểm tra MySQL đã chạy chưa trong XAMPP Control Panel
2. Kiểm tra database `course_management` đã tồn tại chưa trong phpMyAdmin
3. Kiểm tra lại thông tin trong `functions/db_connection.php`

### **Lỗi 2: "404 Not Found" hoặc "Page not found"**

**Nguyên nhân**: URL không đúng hoặc Apache chưa chạy

**Giải pháp**:

1. Đảm bảo Apache đã Start trong XAMPP
2. Kiểm tra URL: `http://localhost/course_management/` (có dấu gạch dưới)
3. Kiểm tra file `index.php` có tồn tại trong thư mục gốc

### **Lỗi 3: "Warning: session_start()"**

**Nguyên nhân**: Session chưa được khởi tạo

**Giải pháp**:

- Đây là lỗi cảnh báo, không ảnh hưởng chức năng
- Đảm bảo các file PHP đều có `session_start()` ở đầu file

### **Lỗi 4: "Call to undefined function escape()" hoặc "Call to undefined function setAlert()"**

**Nguyên nhân**: Các hàm helper chưa được định nghĩa

**Giải pháp**:

- Đã được sửa trong file `functions/db_connection.php`
- Đảm bảo file này được require đúng cách

### **Lỗi 5: Không upload được file**

**Nguyên nhân**: Thư mục uploads chưa tồn tại hoặc không có quyền ghi

**Giải pháp**:

1. Tạo thư mục `uploads` trong thư mục gốc
2. Đảm bảo thư mục có quyền ghi (Windows thường tự động có quyền)

---

## 📁 CẤU TRÚC THƯ MỤC QUAN TRỌNG

```
course_management/
├── index.php              # Trang chủ
├── courses.php            # Trang danh sách khóa học
├── functions/             # Các hàm xử lý
│   ├── db_connection.php  # Kết nối database (QUAN TRỌNG)
│   ├── auth.php           # Xác thực người dùng
│   └── ...
├── views/                 # Các trang view
│   ├── login.php          # Trang đăng nhập
│   ├── register.php       # Trang đăng ký
│   ├── admin/            # Trang admin
│   └── student/          # Trang học viên
├── handle/               # Xử lý form submit
├── docs/                 # Tài liệu
│   └── course_db_schema.sql  # File SQL tạo database
└── uploads/             # Thư mục upload file (cần tạo)
```

---

## 🎯 CÁC TRANG CHÍNH

Sau khi chạy thành công, bạn có thể truy cập:

1. **Trang chủ**: `http://localhost/course_management/`
2. **Đăng nhập**: `http://localhost/course_management/views/login.php`
3. **Đăng ký**: `http://localhost/course_management/views/register.php`
4. **Danh sách khóa học**: `http://localhost/course_management/courses.php`
5. **Dashboard Admin**: `http://localhost/course_management/views/admin/dashboard.php` (cần đăng nhập admin)
6. **Khóa học của tôi**: `http://localhost/course_management/views/student/my_courses.php` (cần đăng nhập)

---

## ✅ KIỂM TRA DỰ ÁN ĐÃ CHẠY ĐÚNG

1. ✅ Truy cập `http://localhost/course_management/` thấy trang chủ
2. ✅ Click "Đăng Nhập" chuyển đến trang login
3. ✅ Đăng nhập với tài khoản admin hoặc student thành công
4. ✅ Xem được danh sách khóa học
5. ✅ Admin có thể quản lý users và courses
6. ✅ Student có thể đăng ký khóa học

---

## 📝 GHI CHÚ QUAN TRỌNG

1. **Database**: Đảm bảo database `course_management` đã được tạo và có dữ liệu mẫu
2. **Session**: Hệ thống sử dụng session để quản lý đăng nhập
3. **Password**: Tất cả password mẫu đều là `password123`
4. **Upload**: Thư mục `uploads` cần có quyền ghi để upload ảnh
5. **URL**: Luôn sử dụng `course_management` (có dấu gạch dưới) trong URL

---

## 🆘 HỖ TRỢ

Nếu gặp vấn đề, kiểm tra:

1. **XAMPP Control Panel**: Apache và MySQL đều đang chạy (màu xanh)
2. **phpMyAdmin**: Database `course_management` đã tồn tại và có dữ liệu
3. **File cấu hình**: `functions/db_connection.php` có thông tin đúng
4. **Thư mục**: File và thư mục đều ở đúng vị trí
5. **Browser Console**: Mở F12 để xem lỗi JavaScript (nếu có)

---

## 🎉 CHÚC BẠN THÀNH CÔNG!

Sau khi hoàn thành các bước trên, dự án sẽ chạy được. Nếu còn vấn đề, hãy kiểm tra lại từng bước một cách cẩn thận.

**Happy Coding! 💻**
