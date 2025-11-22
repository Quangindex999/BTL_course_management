# ✅ CHECKLIST NHANH - CHẠY DỰ ÁN

## ⚡ Các bước cần làm (5 phút)

- [ ] **Bước 1**: Mở XAMPP Control Panel → Start Apache và MySQL
- [ ] **Bước 2**: Mở phpMyAdmin (`http://localhost/phpmyadmin`)
- [ ] **Bước 3**: Import file `docs/course_db_schema.sql` vào phpMyAdmin
- [ ] **Bước 4**: Tạo thư mục `uploads` trong thư mục gốc (nếu chưa có)
- [ ] **Bước 5**: Truy cập `http://localhost/course_management/`

## 🔑 Thông tin đăng nhập

**Admin:**

- Email: `admin@course.com`
- Password: `password123`

**Student:**

- Email: `student1@gmail.com`
- Password: `password123`

## ⚠️ Lưu ý quan trọng

1. URL phải là: `http://localhost/course_management/` (có dấu gạch dưới `_`)
2. Database tên: `course_management` (có dấu gạch dưới)
3. MySQL user: `root`, password: `` (để trống mặc định)

## 🐛 Nếu gặp lỗi

1. **Lỗi kết nối database** → Kiểm tra MySQL đã chạy và database đã tạo chưa
2. **404 Not Found** → Kiểm tra Apache đã chạy và URL đúng chưa
3. **Lỗi function** → Đã được sửa trong `functions/db_connection.php`

---

Xem file `HUONG_DAN_CHAY_DU_AN.md` để biết chi tiết!
