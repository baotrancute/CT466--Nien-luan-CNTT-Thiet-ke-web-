<?php
//file cấu hình db, lưu tt k nthiet ph knoi nhưng làm 2 cái luôn
// Thiết lập thông tin kết nối Database 
$host = 'localhost'; 
$db   = 'flowershop'; // Tên database 
$user = 'root'; //ten
$pass = ''; 

// Đã thêm port=3306 vào chuỗi DSN
$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4";// Chuỗi DSN chứa thông tin kết nối MySQL: host, port, tên database và mã hóa utf8mb4//


$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,// Bật chế độ báo lỗi dạng Exception → giúp dễ debug hơn//
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Khi lấy dữ liệu sẽ trả về dạng mảng associative (['column' => value])//
    PDO::ATTR_EMULATE_PREPARES   => false,  // Tắt mô phỏng prepared statement → dùng prepared statement thật của MySQL (an toàn hơn)//
];

try {
    // Tạo đối tượng kết nối CSDL (PDO)
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Dừng và thông báo lỗi nếu kết nối Database thất bại
    die("Lỗi kết nối Database: " . $e->getMessage());
}
?>