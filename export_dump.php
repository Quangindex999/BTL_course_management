<?php

/**
 * Script tự động export database thành file dump SQL
 * 
 * Cách sử dụng:
 * 1. Truy cập: http://localhost/course_management/export_dump.php
 * 2. File dump sẽ được tạo tự động trong thư mục docs/
 * 
 * Lưu ý: Chỉ chạy trên môi trường localhost, KHÔNG deploy lên server thật!
 */

require_once 'functions/db_connection.php';

// Tên database
$db_name = DB_NAME;
$output_dir = __DIR__ . '/docs/';
$output_file = $output_dir . 'course_management_dump.sql';

// Tạo thư mục nếu chưa có
if (!is_dir($output_dir)) {
    mkdir($output_dir, 0755, true);
}

// Mở file để ghi
$file = fopen($output_file, 'w');

if (!$file) {
    die("❌ Không thể tạo file dump. Kiểm tra quyền ghi thư mục docs/");
}

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Export Database Dump</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1d5c7a;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #bee5eb;
        }
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #1d5c7a;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1d5c7a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #134152;
        }
        .progress {
            margin: 20px 0;
        }
        .progress-bar {
            background: #1d5c7a;
            height: 30px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📦 Export Database Dump</h1>";

try {
    // Ghi header
    fwrite($file, "-- ============================================\n");
    fwrite($file, "-- Database Dump: course_management\n");
    fwrite($file, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
    fwrite($file, "-- ============================================\n\n");

    fwrite($file, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
    fwrite($file, "SET AUTOCOMMIT = 0;\n");
    fwrite($file, "START TRANSACTION;\n");
    fwrite($file, "SET time_zone = \"+00:00\";\n\n");

    // Lấy danh sách bảng
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "<div class='progress'>";
    echo "<div class='progress-bar'>Đang export " . count($tables) . " bảng...</div>";
    echo "</div>";

    $total_tables = count($tables);
    $exported_tables = 0;

    foreach ($tables as $table) {
        $exported_tables++;
        $progress = round(($exported_tables / $total_tables) * 100);

        echo "<div class='info'>📊 Đang export bảng: <code>$table</code> ($exported_tables/$total_tables)</div>";

        // Export cấu trúc bảng
        fwrite($file, "\n-- --------------------------------------------------------\n");
        fwrite($file, "-- Cấu trúc bảng: `$table`\n");
        fwrite($file, "-- --------------------------------------------------------\n\n");

        $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($file, $create_table['Create Table'] . ";\n\n");

        // Export dữ liệu
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            fwrite($file, "-- --------------------------------------------------------\n");
            fwrite($file, "-- Dữ liệu bảng: `$table`\n");
            fwrite($file, "-- --------------------------------------------------------\n\n");

            // Lấy tên cột
            $columns = array_keys($rows[0]);
            $column_names = '`' . implode('`, `', $columns) . '`';

            fwrite($file, "INSERT INTO `$table` ($column_names) VALUES\n");

            $values = [];
            foreach ($rows as $row) {
                $row_values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $row_values[] = 'NULL';
                    } else {
                        // Escape giá trị
                        $escaped = addslashes($value);
                        $row_values[] = "'$escaped'";
                    }
                }
                $values[] = "(" . implode(", ", $row_values) . ")";
            }

            fwrite($file, implode(",\n", $values) . ";\n\n");

            echo "<div class='info'>✅ Đã export " . count($rows) . " dòng dữ liệu từ bảng <code>$table</code></div>";
        } else {
            echo "<div class='info'>ℹ️ Bảng <code>$table</code> không có dữ liệu</div>";
        }
    }

    // Ghi footer
    fwrite($file, "COMMIT;\n");
    fwrite($file, "-- ============================================\n");
    fwrite($file, "-- End of Dump\n");
    fwrite($file, "-- ============================================\n");

    fclose($file);

    // Thông tin file
    $file_size = filesize($output_file);
    $file_size_mb = round($file_size / 1024 / 1024, 2);
    $file_size_kb = round($file_size / 1024, 2);
    $size_display = $file_size_mb >= 1 ? "$file_size_mb MB" : "$file_size_kb KB";

    echo "<div class='success'>
            <h3>✅ Export thành công!</h3>
            <p>File dump đã được tạo tại:</p>
            <div class='file-info'>
                <strong>📁 Đường dẫn:</strong> <code>$output_file</code><br>
                <strong>📊 Kích thước:</strong> $size_display<br>
                <strong>📅 Thời gian:</strong> " . date('d/m/Y H:i:s') . "<br>
                <strong>📋 Số bảng:</strong> $total_tables bảng
            </div>
          </div>";

    echo "<div class='info'>
            <h4>📝 Hướng dẫn sử dụng file dump:</h4>
            <ol>
                <li>File dump này có thể import vào MySQL bằng phpMyAdmin hoặc MySQL Workbench</li>
                <li>Trong phpMyAdmin: Chọn database → Import → Chọn file → Go</li>
                <li>Trong MySQL Workbench: Server → Data Import → Import từ file</li>
                <li>File này chứa đầy đủ cấu trúc và dữ liệu của database</li>
            </ol>
          </div>";

    echo "<a href='docs/course_management_dump.sql' class='btn' download>⬇️ Tải file dump</a>";
    echo "<a href='views/admin/dashboard.php' class='btn' style='background: #6c757d; margin-left: 10px;'>🏠 Về Dashboard</a>";
} catch (PDOException $e) {
    fclose($file);
    unlink($output_file); // Xóa file nếu có lỗi

    echo "<div class='error'>
            <h3>❌ Lỗi khi export!</h3>
            <p><strong>Chi tiết lỗi:</strong> " . escape($e->getMessage()) . "</p>
            <p>Vui lòng kiểm tra:</p>
            <ul>
                <li>Kết nối database có đúng không?</li>
                <li>Quyền ghi file trong thư mục docs/</li>
                <li>Database có tồn tại không?</li>
            </ul>
          </div>";
}

echo "</div></body></html>";
