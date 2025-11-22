# Hướng dẫn thiết lập chức năng Đánh giá và Thanh toán

## 1. Tạo bảng Ratings trong Database

Chạy file SQL sau để tạo bảng lưu trữ đánh giá:

```sql
-- File: docs/create_ratings_table.sql
```

Hoặc chạy trực tiếp trong MySQL:

```sql
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE SET NULL,
    UNIQUE KEY unique_rating (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_course_rating ON ratings(course_id);
CREATE INDEX idx_user_rating ON ratings(user_id);
```

## 2. Các chức năng đã được thêm

### A. Đánh giá khóa học (Rating)

**Tính năng:**

- Học viên có thể đánh giá khóa học từ 1-5 sao
- Có thể thêm nhận xét (review) tùy chọn
- Chỉ học viên đã đăng ký và thanh toán mới được đánh giá
- Hiển thị thống kê đánh giá (số sao trung bình, phân bố số sao)
- Hiển thị danh sách đánh giá từ các học viên khác

**Vị trí:**

- Trang `course_detail.php`: Hiển thị đánh giá và form đánh giá
- Trang `my_courses.php`: Nút "Đánh giá" trong modal

**Files liên quan:**

- `functions/ratings_functions.php`: Các hàm xử lý đánh giá
- `handle/rating_process.php`: Xử lý submit đánh giá

### B. Thanh toán khóa học (Mock Payment)

**Tính năng:**

- Thanh toán giả (mock payment) - không có giao dịch thực tế
- Hỗ trợ nhiều phương thức thanh toán:
  - Chuyển khoản ngân hàng
  - MoMo
  - VNPay
  - ZaloPay
- Cập nhật trạng thái thanh toán trong bảng `enrollments`

**Vị trí:**

- Trang `payment.php`: Trang thanh toán
- Trang `my_courses.php`: Nút "Thanh toán" cho khóa học chưa thanh toán

**Files liên quan:**

- `views/student/payment.php`: Trang thanh toán
- `handle/payment_process.php`: Xử lý thanh toán

## 3. Luồng hoạt động

### Đánh giá khóa học:

1. Học viên đăng ký khóa học → status: `pending`
2. Admin duyệt → status: `approved`
3. Học viên thanh toán → payment_status: `paid`
4. Học viên có thể đánh giá khóa học (1-5 sao + review)

### Thanh toán:

1. Học viên đăng ký khóa học → payment_status: `unpaid`
2. Học viên click "Thanh toán" → Chuyển đến trang thanh toán
3. Chọn phương thức thanh toán → Submit
4. Hệ thống cập nhật payment_status: `paid` (mock - không có giao dịch thực)

## 4. Lưu ý

- **Thanh toán là giả (mock)**: Không có giao dịch thực tế nào được thực hiện
- **Đánh giá yêu cầu**: Học viên phải đăng ký, được duyệt và thanh toán trước khi đánh giá
- **Mỗi học viên chỉ đánh giá 1 lần**: Có thể cập nhật đánh giá đã gửi

## 5. Cấu trúc Database

### Bảng `ratings`:

- `rating_id`: ID đánh giá
- `user_id`: ID học viên
- `course_id`: ID khóa học
- `enrollment_id`: ID đăng ký (tùy chọn)
- `rating`: Số sao (1-5)
- `review`: Nhận xét
- `created_at`, `updated_at`: Thời gian tạo/cập nhật

### Bảng `enrollments` (đã có):

- `payment_status`: `unpaid`, `paid`, `refunded`
- `payment_method`: Phương thức thanh toán
