<?php
//file mới, để rep ib cho admin, kco pbiet tên user
session_start(); // Khởi tạo session để sử dụng các biến phiên (lưu thông tin đăng nhập)
include '../db_config.php'; // Nhúng file cấu hình để kết nối cơ sở dữ liệu

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) != 1) { // Kiểm tra nếu chưa đăng nhập hoặc không phải admin
    header("Location: ../index.php"); // Chuyển hướng về trang chủ nếu không có quyền
    exit(); // Dừng chương trình ngay sau khi chuyển hướng
}

$reply = trim($_POST['reply'] ?? ''); // Lấy dữ liệu 'reply' từ form gửi lên bằng POST, nếu không có thì gán rỗng, đồng thời loại bỏ khoảng trắng đầu/cuối

if($reply == ""){ // Kiểm tra nếu nội dung trả lời rỗng
    header("Location: chat.php"); // Chuyển hướng lại trang chat
    exit(); // Dừng chương trình
}
$sql = "INSERT INTO chat_messages (user_name,message,sender)
        VALUES ('Admin', :message, 'admin')"; // Tạo câu lệnh SQL để thêm một tin nhắn mới vào bảng chat_messages với tên người gửi là Admin, nội dung message dùng placeholder :message, và sender là admin

$stmt = $pdo->prepare($sql); // Chuẩn bị (prepare) câu lệnh SQL để chống SQL Injection và tối ưu thực thi

$stmt->execute([
    ":message"=>$reply
]); // Thực thi câu lệnh SQL, truyền giá trị biến $reply vào placeholder :message

header("Location: chat.php"); // Sau khi thêm tin nhắn thành công, chuyển hướng về lại trang chat