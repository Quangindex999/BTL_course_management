<?php

/**
 * Report Functions - Tổng hợp dữ liệu thống kê nâng cao cho admin
 */

/**
 * Chuẩn hóa khoảng thời gian lấy dữ liệu theo tháng.
 */
function buildMonthlyBuckets($months = 6)
{
    $months = max(1, min(12, intval($months)));
    $start = new DateTime('first day of this month');
    $start->modify('-' . ($months - 1) . ' months');

    $buckets = [];
    $cursor = clone $start;

    for ($i = 0; $i < $months; $i++) {
        $key = $cursor->format('Y-m');
        $buckets[$key] = [
            'label' => $cursor->format('m/Y'),
            'revenue' => 0,
            'enrollments' => 0
        ];
        $cursor->modify('+1 month');
    }

    return [$buckets, $start->format('Y-m-01')];
}

/**
 * Lấy xu hướng doanh thu & lượt đăng ký theo tháng.
 */
function getMonthlyRevenueTrend($months = 6)
{
    global $pdo;

    try {
        [$buckets, $startDate] = buildMonthlyBuckets($months);

        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(e.enrollment_date, '%Y-%m') AS ym,
                   SUM(CASE 
                           WHEN e.payment_status = 'paid' 
                                AND e.status IN ('approved','completed') 
                           THEN c.price ELSE 0 END) AS total_revenue,
                   SUM(CASE 
                           WHEN e.status IN ('approved','completed') 
                           THEN 1 ELSE 0 END) AS total_enrollments
            FROM enrollments e
            JOIN courses c ON e.course_id = c.course_id
            WHERE e.enrollment_date >= ?
            GROUP BY ym
            ORDER BY ym ASC
        ");
        $stmt->execute([$startDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $key = $row['ym'];
            if (isset($buckets[$key])) {
                $buckets[$key]['revenue'] = (float)($row['total_revenue'] ?? 0);
                $buckets[$key]['enrollments'] = (int)($row['total_enrollments'] ?? 0);
            }
        }

        return [
            'labels' => array_column($buckets, 'label'),
            'revenue' => array_column($buckets, 'revenue'),
            'enrollments' => array_column($buckets, 'enrollments')
        ];
    } catch (PDOException $e) {
        return [
            'labels' => [],
            'revenue' => [],
            'enrollments' => []
        ];
    }
}

/**
 * Lấy tỷ trọng đăng ký theo danh mục.
 */
function getCategoryEnrollmentShare($limit = 6)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(cat.category_name, 'Khác') AS label,
                COUNT(*) AS total
            FROM enrollments e
            JOIN courses c ON e.course_id = c.course_id
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            WHERE e.status IN ('approved','completed')
            GROUP BY label
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'labels' => array_column($rows, 'label'),
            'values' => array_map('intval', array_column($rows, 'total'))
        ];
    } catch (PDOException $e) {
        return ['labels' => [], 'values' => []];
    }
}

/**
 * Lấy phân bố đánh giá sao toàn hệ thống.
 */
function getRatingDistribution()
{
    global $pdo;

    try {
        $stmt = $pdo->query("
            SELECT 
                rating, 
                COUNT(*) AS total
            FROM ratings
            GROUP BY rating
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $distribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $distribution[] = [
                'label' => $star . ' sao',
                'value' => (int)($rows[$star] ?? 0)
            ];
        }

        $summary = $pdo->query("
            SELECT 
                COALESCE(AVG(rating), 0) AS avg_rating,
                COUNT(*) AS total_reviews
            FROM ratings
        ")->fetch(PDO::FETCH_ASSOC);

        return [
            'distribution' => $distribution,
            'average' => round((float)($summary['avg_rating'] ?? 0), 1),
            'total_reviews' => (int)($summary['total_reviews'] ?? 0)
        ];
    } catch (PDOException $e) {
        return [
            'distribution' => [
                ['label' => '5 sao', 'value' => 0],
                ['label' => '4 sao', 'value' => 0],
                ['label' => '3 sao', 'value' => 0],
                ['label' => '2 sao', 'value' => 0],
                ['label' => '1 sao', 'value' => 0],
            ],
            'average' => 0,
            'total_reviews' => 0
        ];
    }
}

/**
 * Lấy top khóa học theo doanh thu.
 */
function getTopCoursesByRevenue($limit = 5)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.course_id,
                c.course_name,
                c.thumbnail,
                COALESCE(cat.category_name, 'Chưa phân loại') AS category_name,
                SUM(CASE WHEN e.payment_status = 'paid' AND e.status IN ('approved','completed') THEN c.price ELSE 0 END) AS revenue,
                SUM(CASE WHEN e.status IN ('approved','completed') THEN 1 ELSE 0 END) AS total_students
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.course_id
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            GROUP BY c.course_id, category_name, c.thumbnail
            ORDER BY revenue DESC, total_students DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
                'thumbnail' => $row['thumbnail'],
                'category_name' => $row['category_name'],
                'revenue' => (float)($row['revenue'] ?? 0),
                'total_students' => (int)($row['total_students'] ?? 0),
            ];
        }, $rows);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Lấy đánh giá gần nhất.
 */
function getRecentRatings($limit = 5)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.rating,
                r.review,
                r.created_at,
                u.full_name AS student_name,
                c.course_name
            FROM ratings r
            JOIN users u ON r.user_id = u.user_id
            JOIN courses c ON r.course_id = c.course_id
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
