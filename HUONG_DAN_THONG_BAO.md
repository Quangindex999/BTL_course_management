# 📢 HƯỚNG DẪN SỬ DỤNG HỆ THỐNG THÔNG BÁO

## ✅ Đã chuyển từ Email sang Thông báo trong App

Hệ thống đã được cập nhật từ gửi email sang hệ thống thông báo trong app. Giáo viên có thể gửi thông báo cho học viên và học viên sẽ nhận được thông báo trong phần "Thông báo" của họ.

---

## 📋 CÁC BƯỚC CÀI ĐẶT

### **Bước 1: Tạo bảng notifications trong database**

Có 2 cách để tạo bảng:

#### **Cách 1: Sử dụng phpMyAdmin (Khuyến nghị)**

1. Mở trình duyệt và truy cập: `http://localhost/phpmyadmin`
2. Chọn database `course_management` ở bên trái
3. Click tab **SQL** ở phía trên
4. Mở file `docs/create_notifications_table.sql` bằng Notepad
5. Copy toàn bộ nội dung file SQL
6. Paste vào khung SQL trong phpMyAdmin
7. Click **Go** hoặc nhấn **Ctrl + Enter**
8. Kiểm tra bảng `notifications` đã được tạo thành công

#### **Cách 2: Sử dụng MySQL Command Line**

```bash
cd C:\xampp\mysql\bin
mysql -u root course_management < C:/xampp/htdocs/course_management/docs/create_notifications_table.sql
```

---

## 🎯 CHỨC NĂNG MỚI

### **Dành cho Giáo viên:**

1. **Gửi thông báo cho học viên:**
   - Vào trang "Học viên" (`views/teacher/students.php`)
   - Click nút **🔔 Gửi thông báo** bên cạnh tên học viên
   - Điền tiêu đề và nội dung thông báo
   - Click "Gửi thông báo"

2. **Gửi thông báo từ trang chi tiết học viên:**
   - Vào trang chi tiết học viên
   - Click nút **"Gửi thông báo"** trong phần "Liên hệ"
   - Điền thông tin và gửi

### **Dành cho Học viên:**

1. **Xem thông báo:**
   - Click icon **🔔** trên navbar (có badge đỏ hiển thị số thông báo chưa đọc)
   - Hoặc truy cập: `views/student/notifications.php`

2. **Quản lý thông báo:**
   - Xem tất cả thông báo hoặc chỉ xem chưa đọc
   - Đánh dấu đã đọc từng thông báo
   - Đánh dấu tất cả đã đọc
   - Xóa thông báo không cần thiết

---

## 📁 CÁC FILE MỚI ĐÃ TẠO

1. **`docs/create_notifications_table.sql`** - File SQL tạo bảng notifications
2. **`functions/notification_functions.php`** - Các hàm xử lý thông báo
3. **`handle/send_notification.php`** - Xử lý gửi thông báo (thay thế send_email.php)
4. **`views/student/notifications.php`** - Trang xem thông báo của học viên

---

## 🔄 CÁC FILE ĐÃ CẬP NHẬT

1. **`views/teacher/students.php`** - Đổi từ "Gửi email" sang "Gửi thông báo"
2. **`views/teacher/student_detail.php`** - Đổi từ "Gửi email" sang "Gửi thông báo"
3. **`views/student/my_courses.php`** - Thêm icon thông báo vào navbar
4. **`views/student/course_detail.php`** - Thêm icon thông báo vào navbar
5. **`views/student/menu_student.php`** - Thêm icon thông báo vào navbar

---

## 🎨 TÍNH NĂNG

- ✅ Gửi thông báo từ giáo viên đến học viên
- ✅ Hiển thị số thông báo chưa đọc trên navbar
- ✅ Xem danh sách thông báo (tất cả / chưa đọc)
- ✅ Đánh dấu đã đọc / chưa đọc
- ✅ Xóa thông báo
- ✅ Phân trang thông báo
- ✅ Hiển thị thời gian tương đối (ví dụ: "5 phút trước")
- ✅ Hiển thị người gửi thông báo

---

## 🧪 KIỂM TRA

Sau khi cài đặt, kiểm tra:

1. ✅ Bảng `notifications` đã được tạo trong database
2. ✅ Giáo viên có thể gửi thông báo cho học viên
3. ✅ Học viên thấy icon thông báo trên navbar với badge số lượng
4. ✅ Học viên có thể xem danh sách thông báo
5. ✅ Học viên có thể đánh dấu đã đọc và xóa thông báo

---

## 📝 LƯU Ý

- Hệ thống thông báo hoạt động hoàn toàn trong app, không cần cấu hình email
- Thông báo được lưu trong database và hiển thị ngay lập tức
- Học viên sẽ thấy badge đỏ trên icon thông báo khi có thông báo mới
- Thông báo được sắp xếp theo thời gian mới nhất

---

## 🆘 XỬ LÝ LỖI

### **Lỗi: "Table 'notifications' doesn't exist"**

**Giải pháp:** Chạy lại file SQL `docs/create_notifications_table.sql` trong phpMyAdmin

### **Lỗi: "Call to undefined function getUnreadNotificationCount()"**

**Giải pháp:** Đảm bảo file `functions/notification_functions.php` đã được require trong các trang student

### **Không thấy icon thông báo trên navbar**

**Giải pháp:** 
- Kiểm tra đã đăng nhập với tài khoản student chưa
- Kiểm tra file `functions/notification_functions.php` đã được require chưa
- Clear cache trình duyệt (Ctrl + F5)

---

## 🎉 HOÀN TẤT!

Hệ thống thông báo đã sẵn sàng sử dụng. Giáo viên có thể gửi thông báo cho học viên và học viên sẽ nhận được ngay trong app mà không cần cấu hình email!

