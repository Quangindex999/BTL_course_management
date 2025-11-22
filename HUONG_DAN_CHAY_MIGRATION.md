# 📚 HƯỚNG DẪN CHẠY FILE MIGRATION SQL

## 🎯 File cần chạy: `docs/update_schema_teacher.sql`

File này sẽ cập nhật database để hỗ trợ Teacher role và các trường liên quan.

---

## ✅ CÁCH 1: Sử dụng phpMyAdmin (Khuyến nghị - Dễ nhất)

### Bước 1: Mở phpMyAdmin
1. Đảm bảo XAMPP đã khởi động (Apache và MySQL đang chạy)
2. Mở trình duyệt và truy cập: `http://localhost/phpmyadmin`
3. Đăng nhập (thường không cần password, hoặc password là rỗng)

### Bước 2: Chọn Database
1. Ở cột bên trái, click vào database `course_management`
2. Nếu chưa có database này, cần chạy file `docs/course_db_schema.sql` trước

### Bước 3: Chạy SQL
1. Click vào tab **SQL** ở phía trên cùng
2. Mở file `docs/update_schema_teacher.sql` bằng Notepad hoặc VS Code
3. **Copy toàn bộ nội dung** của file SQL
4. **Paste** vào khung SQL trong phpMyAdmin
5. Click nút **Go** (hoặc nhấn **Ctrl + Enter**)

### Bước 4: Kiểm tra kết quả
- ✅ **Thành công**: Sẽ hiển thị thông báo "Query OK" cho mỗi câu lệnh
- ❌ **Lỗi**: Nếu có lỗi, sẽ hiển thị thông báo lỗi cụ thể
  - Nếu lỗi "Duplicate column name" hoặc "Duplicate key name": Có thể đã chạy rồi, bỏ qua
  - Nếu lỗi khác: Kiểm tra lại database và cấu trúc bảng

---

## ✅ CÁCH 2: Sử dụng MySQL Command Line

### Bước 1: Mở Command Prompt hoặc PowerShell
- Nhấn `Win + R`, gõ `cmd` hoặc `powershell`, nhấn Enter

### Bước 2: Chuyển đến thư mục MySQL
```bash
cd C:\xampp\mysql\bin
```

### Bước 3: Chạy file SQL
```bash
mysql -u root -e "source C:/xampp/htdocs/course_management/docs/update_schema_teacher.sql"
```

**Hoặc nếu MySQL có password:**
```bash
mysql -u root -p -e "source C:/xampp/htdocs/course_management/docs/update_schema_teacher.sql"
```
(Sẽ yêu cầu nhập password)

---

## ✅ CÁCH 3: Import trực tiếp file SQL

### Trong phpMyAdmin:
1. Chọn database `course_management`
2. Click tab **Import** ở phía trên
3. Click **Choose File** và chọn file `docs/update_schema_teacher.sql`
4. Click **Go** ở cuối trang
5. Đợi import hoàn tất

---

## 🔍 KIỂM TRA SAU KHI CHẠY

### Kiểm tra bảng users:
```sql
DESCRIBE users;
```
**Kết quả mong đợi:**
- Cột `role` phải có: `ENUM('student', 'teacher', 'admin')`
- Có các cột mới: `bio`, `specialization`, `experience_years`, `education`, `linkedin`, `website`, `rating`

### Kiểm tra bảng courses:
```sql
DESCRIBE courses;
```
**Kết quả mong đợi:**
- Có cột mới: `teacher_id` (INT, có thể NULL)

### Kiểm tra indexes:
```sql
SHOW INDEXES FROM courses;
SHOW INDEXES FROM users;
```
**Kết quả mong đợi:**
- `courses` có index: `idx_teacher_id`
- `users` có index: `idx_user_role`

---

## ⚠️ LƯU Ý QUAN TRỌNG

1. **Backup database trước khi chạy:**
   - Trong phpMyAdmin: Chọn database → tab **Export** → **Go**
   - Hoặc export từng bảng quan trọng

2. **Chạy file `course_db_schema.sql` trước:**
   - File migration này chỉ cập nhật, không tạo database mới
   - Cần có database `course_management` và các bảng cơ bản trước

3. **Nếu đã chạy rồi:**
   - Có thể gặp lỗi "Duplicate column" hoặc "Duplicate key"
   - Đây là bình thường, có thể bỏ qua hoặc comment các dòng đã chạy

4. **Kiểm tra quyền:**
   - Đảm bảo user MySQL có quyền ALTER TABLE và CREATE INDEX

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi 1: "Unknown database 'course_management'"
**Nguyên nhân:** Database chưa được tạo
**Giải pháp:** Chạy file `docs/course_db_schema.sql` trước

### Lỗi 2: "Duplicate column name 'bio'"
**Nguyên nhân:** Đã chạy migration rồi
**Giải pháp:** Bỏ qua hoặc comment các dòng ALTER TABLE đã chạy

### Lỗi 3: "Access denied"
**Nguyên nhân:** Không có quyền
**Giải pháp:** Đăng nhập với user root hoặc user có quyền admin

### Lỗi 4: "Table doesn't exist"
**Nguyên nhân:** Chưa tạo các bảng cơ bản
**Giải pháp:** Chạy file `docs/course_db_schema.sql` trước

---

## ✅ SAU KHI CHẠY THÀNH CÔNG

1. ✅ Database đã hỗ trợ Teacher role
2. ✅ Có thể tạo user với role 'teacher'
3. ✅ Có thể gán teacher_id cho courses
4. ✅ Teacher dashboard sẽ hoạt động đúng

---

## 📝 GHI CHÚ

- File migration này an toàn, không xóa dữ liệu hiện có
- Chỉ thêm các cột và index mới
- Có thể chạy nhiều lần (sẽ báo lỗi duplicate nhưng không ảnh hưởng)

---

**Chúc bạn thành công! 🎉**

