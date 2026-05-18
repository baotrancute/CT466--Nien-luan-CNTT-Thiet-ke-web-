<?php
//gửi chat cnang mới cần bổ sung (user
session_start(); // Khởi tạo session (có thể dùng để xác định người gửi nếu cần)
include "db_config.php"; // Nhúng file cấu hình để kết nối cơ sở dữ liệu

$message = trim($_POST['message'] ?? ''); // Lấy nội dung message từ POST, nếu không có thì gán rỗng và loại bỏ khoảng trắng đầu/cuối

if($message == ""){ // Kiểm tra nếu tin nhắn rỗng
    echo json_encode(["status"=>"error"]); // Trả về JSON báo lỗi
    exit; // Dừng chương trình
}

$sql = "INSERT INTO chat_messages (message, sender) VALUES (:message,'user')"; // Tạo câu lệnh SQL thêm tin nhắn với sender là user
$stmt = $pdo->prepare($sql); // Chuẩn bị câu lệnh để chống SQL Injection
$stmt->execute([
    ":message"=>$message
]); // Thực thi câu lệnh, truyền nội dung tin nhắn vào placeholder

echo json_encode(["status"=>"success"]); // Trả về JSON báo gửi tin nhắn thành công