# 📚 HƯỚNG DẪN CHẠY FILE MIGRATION SQL TRONG MYSQL WORKBENCH

## 🎯 File cần chạy: `docs/update_schema_teacher.sql`

---

## ✅ CÁC BƯỚC THỰC HIỆN

### Bước 1: Mở MySQL Workbench

1. Khởi động **MySQL Workbench**
2. Kết nối đến server MySQL (thường là `localhost` hoặc `127.0.0.1`)
3. Nhập password nếu được yêu cầu (mặc định XAMPP thường không có password)

### Bước 2: Chọn Database

1. Trong phần **Navigator** (bên trái), tìm database `course_management`
2. **Click chuột phải** vào database `course_management`
3. Chọn **Set as Default Schema** (hoặc click đúp vào database)
4. Database sẽ được highlight/bold để xác nhận đã chọn

**Lưu ý:** Nếu chưa có database `course_management`, cần chạy file `docs/course_db_schema.sql` trước.

### Bước 3: Mở File SQL

**Cách 1: Mở file trực tiếp**

1. Trong MySQL Workbench, click menu **File** → **Open SQL Script...**
2. Điều hướng đến thư mục: `C:\xampp\htdocs\course_management\docs\`
3. Chọn file: `update_schema_teacher.sql`
4. Click **Open**

**Cách 2: Copy và Paste**

1. Mở file `docs/update_schema_teacher.sql` bằng Notepad hoặc VS Code
2. **Copy toàn bộ nội dung** (Ctrl + A, Ctrl + C)
3. Trong MySQL Workbench, tạo tab SQL mới (nếu chưa có)
4. **Paste** nội dung vào (Ctrl + V)

### Bước 4: Chạy SQL

1. Đảm bảo đã chọn đúng database `course_management` (xem ở thanh toolbar phía trên)
2. Click nút **Execute** (⚡) trên thanh toolbar
   - Hoặc nhấn **Ctrl + Shift + Enter**
   - Hoặc nhấn **F5**

### Bước 5: Kiểm tra kết quả

1. Xem kết quả ở tab **Output** phía dưới
2. ✅ **Thành công**: Sẽ hiển thị "Query OK" cho mỗi câu lệnh
3. ❌ **Lỗi**: Sẽ hiển thị thông báo lỗi màu đỏ
   - Nếu lỗi "Duplicate column name" → Có thể đã chạy rồi, bỏ qua
   - Nếu lỗi khác → Kiểm tra lại database và cấu trúc

---

## 🔍 KIỂM TRA SAU KHI CHẠY

### Kiểm tra trong MySQL Workbench:

1. **Kiểm tra bảng users:**

   - Trong Navigator, mở rộng `course_management` → `Tables`
   - Click chuột phải vào bảng `users` → **Table Inspector** hoặc **Alter Table**
   - Kiểm tra các cột mới: `bio`, `specialization`, `experience_years`, `education`, `linkedin`, `website`, `rating`
   - Kiểm tra cột `role` có giá trị: `student`, `teacher`, `admin`

2. **Kiểm tra bảng courses:**

   - Click chuột phải vào bảng `courses` → **Table Inspector**
   - Kiểm tra có cột `teacher_id` chưa

3. **Chạy query kiểm tra:**

   ```sql
   -- Kiểm tra cấu trúc bảng users
   DESCRIBE users;

   -- Kiểm tra cấu trúc bảng courses
   DESCRIBE courses;

   -- Kiểm tra indexes
   SHOW INDEXES FROM courses;
   SHOW INDEXES FROM users;
   ```

---

## ⚠️ LƯU Ý QUAN TRỌNG

1. **Backup database trước khi chạy:**

   - Trong MySQL Workbench: **Server** → **Data Export**
   - Chọn database `course_management` → **Start Export**

2. **Chạy file `course_db_schema.sql` trước:**

   - File migration này chỉ cập nhật, không tạo database mới
   - Cần có database `course_management` và các bảng cơ bản trước

3. **Nếu đã chạy rồi:**

   - Có thể gặp lỗi "Duplicate column" hoặc "Duplicate key"
   - Đây là bình thường, có thể bỏ qua hoặc comment các dòng đã chạy

4. **Kiểm tra kết nối:**
   - Đảm bảo MySQL server đang chạy (trong XAMPP Control Panel)

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi 1: "Unknown database 'course_management'"

**Nguyên nhân:** Database chưa được tạo
**Giải pháp:**

- Chạy file `docs/course_db_schema.sql` trước
- Hoặc tạo database thủ công:
  ```sql
  CREATE DATABASE course_management;
  ```

### Lỗi 2: "Duplicate column name 'bio'"

**Nguyên nhân:** Đã chạy migration rồi
**Giải pháp:**

- Bỏ qua lỗi này (không ảnh hưởng)
- Hoặc comment các dòng ALTER TABLE đã chạy trong file SQL

### Lỗi 3: "Access denied for user"

**Nguyên nhân:** Không có quyền
**Giải pháp:**

- Đăng nhập với user `root`
- Hoặc user có quyền ALTER TABLE và CREATE INDEX

### Lỗi 4: "Table doesn't exist"

**Nguyên nhân:** Chưa tạo các bảng cơ bản
**Giải pháp:**

- Chạy file `docs/course_db_schema.sql` trước để tạo các bảng

### Lỗi 5: "Error Code: 1064 - Syntax error"

**Nguyên nhân:** File SQL có lỗi cú pháp
**Giải pháp:**

- Kiểm tra lại file SQL
- Đảm bảo đã copy đầy đủ nội dung

---

## 📸 HÌNH ẢNH MÔ TẢ (Tùy chọn)

### Vị trí các nút trong MySQL Workbench:

- **Execute (⚡)**: Nút hình tia sét, ở thanh toolbar phía trên
- **Navigator**: Panel bên trái, hiển thị databases và tables
- **Output**: Tab phía dưới, hiển thị kết quả query

---

## ✅ SAU KHI CHẠY THÀNH CÔNG

1. ✅ Database đã hỗ trợ Teacher role
2. ✅ Có thể tạo user với role 'teacher' trong admin panel
3. ✅ Có thể gán teacher_id cho courses
4. ✅ Teacher dashboard sẽ hoạt động đúng

---

## 🎯 TÓM TẮT NHANH

1. Mở MySQL Workbench → Kết nối
2. Chọn database `course_management`
3. File → Open SQL Script → Chọn `update_schema_teacher.sql`
4. Click **Execute** (⚡) hoặc nhấn **Ctrl + Shift + Enter**
5. Kiểm tra kết quả ở tab Output

---

**Chúc bạn thành công! 🎉**
