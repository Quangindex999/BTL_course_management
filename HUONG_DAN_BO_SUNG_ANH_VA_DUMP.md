# 📸 Hướng Dẫn Bổ Sung Ảnh và Dump Dữ Liệu

## 🖼️ PHẦN 1: BỔ SUNG ẢNH MINH HỌA

### Cách 1: Upload ảnh qua giao diện Admin (KHUYẾN NGHỊ) ✅

**Bước 1:** Đăng nhập với tài khoản Admin

- Email: `admin@course.com`
- Password: `password123`

**Bước 2:** Vào trang quản lý khóa học

- Truy cập: `http://localhost/course_management/views/admin/manage_courses.php`
- Click "Sửa" trên khóa học cần thêm ảnh

**Bước 3:** Upload ảnh

- Scroll xuống phần "Ảnh đại diện"
- Click "Chọn file" và chọn ảnh từ máy tính
- Click "Cập nhật khóa học"
- Ảnh sẽ tự động lưu vào `uploads/courses/` và đường dẫn được cập nhật vào database

**Lưu ý:**

- Ảnh sẽ được lưu vào thư mục `uploads/courses/` (KHÔNG phải `images/`)
- Đường dẫn ảnh được lưu tự động vào cột `thumbnail` trong bảng `courses`
- Kích thước ảnh tối đa: 5MB
- Định dạng: JPG, PNG

---

### Cách 2: Thêm ảnh thủ công (Nếu muốn tự tải ảnh về)

**Bước 1:** Tải ảnh về máy

- Tìm ảnh minh họa khóa học (ví dụ: từ Unsplash, Pexels)
- Lưu vào thư mục `uploads/courses/`
- Đặt tên file dễ nhớ, ví dụ: `php-course.jpg`, `react-course.jpg`

**Bước 2:** Cập nhật đường dẫn trong database

Có 2 cách:

**Cách A: Dùng phpMyAdmin**

1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `course_management`
3. Vào bảng `courses`
4. Click "Sửa" (Edit) trên khóa học cần thêm ảnh
5. Ở cột `thumbnail`, nhập: `uploads/courses/ten-file-anh.jpg`
6. Click "Go" để lưu

**Cách B: Dùng SQL**

```sql
UPDATE courses
SET thumbnail = 'uploads/courses/php-course.jpg'
WHERE course_id = 1;

UPDATE courses
SET thumbnail = 'uploads/courses/react-course.jpg'
WHERE course_id = 2;
```

---

## 💾 PHẦN 2: DUMP DỮ LIỆU (DATABASE BACKUP)

### Dump dữ liệu là gì?

**Dump dữ liệu** là file SQL chứa:

- ✅ Cấu trúc database (tạo bảng, khóa ngoại, v.v.)
- ✅ Toàn bộ dữ liệu trong database (users, courses, lessons, enrollments, v.v.)

**Tại sao cần dump?**

- Khi nộp bài, giảng viên cần file này để import và chạy dự án
- Đảm bảo giảng viên có đầy đủ dữ liệu để test các chức năng
- Không cần phải tạo dữ liệu thủ công

---

### Cách tạo dump dữ liệu từ MySQL

#### Cách 1: Dùng Script Tự Động (NHANH NHẤT) ⚡

**Bước 1:** Truy cập script

- Mở trình duyệt: `http://localhost/course_management/export_dump.php`
- Hoặc chạy từ terminal: `php export_dump_cli.php`

**Bước 2:** Chờ script chạy

- Script sẽ tự động export tất cả bảng và dữ liệu
- File dump sẽ được tạo tại: `docs/course_management_dump.sql`

**Bước 3:** Tải file về (nếu dùng trình duyệt)

- Click nút "Tải file dump" để tải về

**Ưu điểm:**

- ✅ Tự động, không cần thao tác thủ công
- ✅ Hiển thị tiến trình export
- ✅ Tự động tạo thư mục nếu chưa có
- ✅ Hiển thị thông tin chi tiết về file dump

---

#### Cách 2: Dùng phpMyAdmin (Dễ nhất) ✅

**Bước 1:** Mở phpMyAdmin

- Truy cập: `http://localhost/phpmyadmin`

**Bước 2:** Chọn database

- Click vào database `course_management` ở cột bên trái

**Bước 3:** Export database

- Click tab "Export" ở trên cùng
- Chọn phương thức: **Quick** (nhanh) hoặc **Custom** (tùy chỉnh)
- Format: **SQL**
- Click nút "Go" (Đi)

**Bước 4:** Lưu file

- File sẽ được tải về với tên: `course_management.sql`
- Đổi tên thành: `course_management_dump.sql` (để dễ nhận biết)
- Đặt vào thư mục `docs/`

---

#### Cách 3: Dùng MySQL Workbench

**Bước 1:** Mở MySQL Workbench

- Kết nối đến localhost

**Bước 2:** Chọn database

- Click vào database `course_management` trong Navigator

**Bước 3:** Export

- Click menu: **Server** → **Data Export**
- Chọn database `course_management`
- Chọn tất cả các bảng
- Export to: Chọn thư mục `docs/`
- Click "Start Export"

---

#### Cách 4: Dùng Command Line (MySQL)

Mở Command Prompt hoặc PowerShell và chạy:

```bash
# Vào thư mục XAMPP MySQL
cd C:\xampp\mysql\bin

# Export database
mysqldump -u root -p course_management > C:\xampp\htdocs\course_management\docs\course_management_dump.sql
```

(Nhấn Enter khi hỏi password, nếu XAMPP không có password thì để trống)

---

### Kiểm tra file dump

Sau khi tạo xong, file dump nên có:

- ✅ Kích thước: Ít nhất vài KB (nếu có dữ liệu)
- ✅ Định dạng: `.sql`
- ✅ Nội dung: Có các câu lệnh `CREATE TABLE`, `INSERT INTO`, v.v.

**Mở file bằng Notepad++ hoặc VS Code để kiểm tra:**

- Phải có: `CREATE TABLE users`
- Phải có: `CREATE TABLE courses`
- Phải có: `INSERT INTO users`
- Phải có: `INSERT INTO courses`
- Phải có: `INSERT INTO lessons`

---

## 📋 CHECKLIST TRƯỚC KHI NỘP BÀI

### Về ảnh:

- [ ] Tất cả khóa học đã có ảnh thumbnail (hoặc dùng placeholder)
- [ ] Ảnh được lưu đúng trong `uploads/courses/`
- [ ] Đường dẫn ảnh trong database đúng định dạng: `uploads/courses/ten-file.jpg`

### Về dump dữ liệu:

- [ ] Đã tạo file dump SQL đầy đủ
- [ ] File dump có tên rõ ràng: `course_management_dump.sql`
- [ ] File dump được đặt trong thư mục `docs/`
- [ ] File dump chứa đầy đủ:
  - [ ] Cấu trúc database (CREATE TABLE)
  - [ ] Dữ liệu mẫu (INSERT INTO)
  - [ ] Ít nhất 3-4 khóa học
  - [ ] Ít nhất 1 khóa học có bài học (lessons)
  - [ ] Ít nhất 1-2 đăng ký (enrollments) để test

### File cần nộp:

- [ ] File dump: `docs/course_management_dump.sql`
- [ ] File schema: `docs/course_db_schema.sql` (đã có sẵn)
- [ ] Thư mục `uploads/` với các ảnh đã upload

---

## 🎯 TÓM TẮT NHANH

### Ảnh:

1. **Cách dễ nhất:** Upload qua Admin → Tự động lưu vào `uploads/courses/`
2. **Cách thủ công:** Tải ảnh về → Đặt vào `uploads/courses/` → Cập nhật DB

### Dump:

1. **Cách dễ nhất:** Dùng phpMyAdmin → Export → Lưu vào `docs/`
2. File dump = Backup toàn bộ database để người khác import và chạy được

---

## ❓ CÂU HỎI THƯỜNG GẶP

**Q: Tôi có cần tải ảnh về không?**
A: Không bắt buộc. Bạn có thể upload trực tiếp qua Admin hoặc để code dùng placeholder từ internet.

**Q: File dump có cần dữ liệu thật không?**
A: Không cần dữ liệu thật, nhưng cần dữ liệu mẫu đầy đủ để test các chức năng.

**Q: Nếu tôi không có ảnh thì sao?**
A: Code đã có xử lý placeholder, nếu không có ảnh sẽ hiển thị ảnh mặc định từ internet.

**Q: File dump có cần password không?**
A: Không, file dump chỉ chứa cấu trúc và dữ liệu, không chứa thông tin đăng nhập MySQL.

---

**Chúc bạn hoàn thành tốt bài tập! 🎉**
