<?php
//file hủy đơn hàng cho admin
session_start(); // Khởi động session để truy cập các biến phiên như user_id và quyền admin
include '../db_config.php'; // Nhúng file cấu hình kết nối database để sử dụng biến $pdo

// CHỈ ADMIN ĐƯỢC HỦY
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) != 1) { // Kiểm tra nếu chưa đăng nhập hoặc không phải admin thì không cho phép thực hiện
    die("Không có quyền!"); // Dừng chương trình và hiển thị thông báo không có quyền truy cập
}

$id = $_GET['id'] ?? 0; // Lấy id đơn hàng từ tham số trên URL, nếu không có thì mặc định bằng 0
//dùng get để lấy dữ liệu và nếu url k có id thì gán bằng 0//
// Update trạng thái
$stmt = $pdo->prepare("UPDATE orders SET status='Đã hủy' WHERE id=?"); // Chuẩn bị câu lệnh SQL để cập nhật trạng thái đơn hàng thành "Đã hủy" theo id
//pdo: php data objects
//prepare chống sql 
//stmt biến ddee lưu câu lẹnh sql
$stmt->execute([$id]); // Thực thi câu lệnh SQL và truyền id đơn hàng vào dấu hỏi trong câu lệnh

header("Location: orders.php"); // Sau khi cập nhật xong thì chuyển hướng trình duyệt về trang danh sách đơn hàng
exit; // Kết thúc script để đảm bảo không có code nào chạy tiếp