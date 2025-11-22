<?php

/**
 * Script export database dump - Command Line Version
 * 
 * Cách sử dụng:
 * php export_dump_cli.php
 * 
 * Hoặc từ PowerShell:
 * php export_dump_cli.php
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
    die("❌ Không thể tạo file dump. Kiểm tra quyền ghi thư mục docs/\n");
}

echo "📦 Bắt đầu export database dump...\n";
echo "=====================================\n\n";

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

    $total_tables = count($tables);
    echo "📊 Tìm thấy $total_tables bảng trong database\n\n";

    $exported_tables = 0;
    $total_rows = 0;

    foreach ($tables as $table) {
        $exported_tables++;
        echo "[$exported_tables/$total_tables] Đang export bảng: $table... ";

        // Export cấu trúc bảng
        fwrite($file, "\n-- --------------------------------------------------------\n");
        fwrite($file, "-- Cấu trúc bảng: `$table`\n");
        fwrite($file, "-- --------------------------------------------------------\n\n");

        $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($file, $create_table['Create Table'] . ";\n\n");

        // Export dữ liệu
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $row_count = count($rows);
        $total_rows += $row_count;

        if ($row_count > 0) {
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

            echo "✅ ($row_count dòng)\n";
        } else {
            echo "ℹ️  (không có dữ liệu)\n";
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

    echo "\n";
    echo "=====================================\n";
    echo "✅ Export thành công!\n";
    echo "=====================================\n";
    echo "📁 File: $output_file\n";
    echo "📊 Kích thước: $size_display\n";
    echo "📋 Số bảng: $total_tables\n";
    echo "📝 Tổng số dòng dữ liệu: $total_rows\n";
    echo "📅 Thời gian: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    echo "💡 Bạn có thể import file này vào MySQL bằng:\n";
    echo "   - phpMyAdmin: Import → Chọn file → Go\n";
    echo "   - MySQL Workbench: Data Import → Import từ file\n";
    echo "\n";
} catch (PDOException $e) {
    fclose($file);
    if (file_exists($output_file)) {
        unlink($output_file); // Xóa file nếu có lỗi
    }

    echo "\n";
    echo "=====================================\n";
    echo "❌ Lỗi khi export!\n";
    echo "=====================================\n";
    echo "Chi tiết: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Vui lòng kiểm tra:\n";
    echo "- Kết nối database có đúng không?\n";
    echo "- Quyền ghi file trong thư mục docs/\n";
    echo "- Database có tồn tại không?\n";
    echo "\n";
    exit(1);
}
