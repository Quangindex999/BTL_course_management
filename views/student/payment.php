<?php
session_start();
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';
require_once '../../functions/enrollments_functions.php';
require_once '../../functions/course_functions.php';

requireStudent();

$user_id = $_SESSION['user_id'];
$enrollment_id = intval($_GET['id'] ?? 0);

if (!$enrollment_id) {
    setAlert('Không tìm thấy thông tin đăng ký', 'error');
    redirect(SITE_URL . '/views/student/my_courses.php');
}

// Lấy thông tin enrollment
$stmt = $pdo->prepare("
    SELECT e.*, c.course_name, c.price, c.thumbnail, c.instructor_name,
    u.full_name, u.email, u.phone
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON e.user_id = u.user_id
    WHERE e.enrollment_id = ? AND e.user_id = ?
");
$stmt->execute([$enrollment_id, $user_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    setAlert('Không tìm thấy thông tin đăng ký', 'error');
    redirect(SITE_URL . '/views/student/my_courses.php');
}

if ($enrollment['payment_status'] === 'paid') {
    setAlert('Khóa học này đã được thanh toán', 'info');
    redirect(SITE_URL . '/views/student/my_courses.php');
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán khóa học - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }

        .payment-container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .payment-header h2 {
            margin: 0;
            font-weight: 800;
        }

        .payment-body {
            padding: 2rem;
        }

        .course-info {
            background: #f9fafb;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .course-thumbnail {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
        }

        .payment-methods {
            margin-bottom: 2rem;
        }

        .payment-method-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method-card:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .payment-method-card.selected {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .payment-summary {
            background: #f9fafb;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
        }

        .btn-pay {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 12px;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        .mock-notice {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #92400e;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>/index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <div class="ms-auto">
                <a href="my_courses.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>
    </nav>

    <div class="container payment-container">
        <?php showAlert(); ?>

        <div class="payment-card">
            <div class="payment-header">
                <h2><i class="fas fa-credit-card me-2"></i>Thanh toán khóa học</h2>
                <p class="mb-0 mt-2">Hoàn tất thanh toán để bắt đầu học ngay</p>
            </div>

            <div class="payment-body">
                <!-- Course Info -->
                <div class="course-info">
                    <div class="d-flex gap-3">
                        <img src="<?php echo getImageUrl($enrollment['thumbnail'], 'https://via.placeholder.com/100'); ?>"
                            class="course-thumbnail" alt="">
                        <div class="flex-grow-1">
                            <h5 class="mb-2"><?php echo escape($enrollment['course_name']); ?></h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-user-tie me-2"></i>
                                <?php echo escape($enrollment['instructor_name']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                Đăng ký: <?php echo date('d/m/Y', strtotime($enrollment['enrollment_date'])); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mock Payment Notice -->
                <div class="mock-notice">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Lưu ý:</strong> Đây là hệ thống thanh toán giả (mock payment) để demo.
                    Không có giao dịch thực tế nào được thực hiện.
                </div>

                <!-- Payment Methods -->
                <div class="payment-methods">
                    <h5 class="mb-3"><i class="fas fa-wallet me-2"></i>Chọn phương thức thanh toán</h5>
                    <form id="paymentForm" method="POST" action="../../handle/payment_process.php">
                        <input type="hidden" name="action" value="process_payment">
                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment_id; ?>">
                        <input type="hidden" name="payment_method" id="payment_method" value="bank_transfer">

                        <div class="payment-method-card selected" data-method="bank_transfer">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method_radio"
                                    id="bank_transfer" value="bank_transfer" checked>
                                <label class="form-check-label w-100" for="bank_transfer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-university me-2 text-primary"></i>
                                            <strong>Chuyển khoản ngân hàng</strong>
                                        </div>
                                        <i class="fas fa-check-circle text-success"></i>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="payment-method-card" data-method="momo">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method_radio"
                                    id="momo" value="momo">
                                <label class="form-check-label w-100" for="momo">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-mobile-alt me-2" style="color: #d82d8b;"></i>
                                            <strong>Ví điện tử MoMo</strong>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="payment-method-card" data-method="vnpay">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method_radio"
                                    id="vnpay" value="vnpay">
                                <label class="form-check-label w-100" for="vnpay">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-credit-card me-2" style="color: #0052a5;"></i>
                                            <strong>VNPay</strong>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="payment-method-card" data-method="zalopay">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method_radio"
                                    id="zalopay" value="zalopay">
                                <label class="form-check-label w-100" for="zalopay">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-wallet me-2" style="color: #0068ff;"></i>
                                            <strong>ZaloPay</strong>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Payment Summary -->
                <div class="payment-summary">
                    <h5 class="mb-3"><i class="fas fa-receipt me-2"></i>Tóm tắt thanh toán</h5>
                    <div class="summary-row">
                        <span>Học phí khóa học:</span>
                        <span><?php echo formatCurrency($enrollment['price']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Phí giao dịch:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <div class="summary-row">
                        <span>Tổng cộng:</span>
                        <span><?php echo formatCurrency($enrollment['price']); ?></span>
                    </div>
                </div>

                <!-- Pay Button -->
                <button type="submit" form="paymentForm" class="btn btn-pay">
                    <i class="fas fa-lock me-2"></i>Xác nhận thanh toán
                </button>

                <p class="text-center text-muted mt-3 small">
                    <i class="fas fa-shield-alt me-1"></i>
                    Thanh toán an toàn và bảo mật
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý chọn phương thức thanh toán
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.payment-method-card').forEach(c => {
                    c.classList.remove('selected');
                    c.querySelector('.fa-check-circle')?.remove();
                });

                // Add selected class to clicked card
                this.classList.add('selected');

                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;

                // Update hidden input
                document.getElementById('payment_method').value = radio.value;

                // Add check icon
                const label = this.querySelector('label > div');
                if (!label.querySelector('.fa-check-circle')) {
                    const checkIcon = document.createElement('i');
                    checkIcon.className = 'fas fa-check-circle text-success';
                    label.appendChild(checkIcon);
                }
            });
        });

        // Handle radio change
        document.querySelectorAll('input[name="payment_method_radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('payment_method').value = this.value;
            });
        });
    </script>
</body>

</html>