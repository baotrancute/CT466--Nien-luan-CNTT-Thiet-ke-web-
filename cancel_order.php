<?php
//file hủy đhang cho user, thêm chức năng dl 10-4. 
session_start(); // Khởi tạo session để sử dụng thông tin đăng nhập
include 'db_config.php'; // Nhúng file cấu hình để kết nối cơ sở dữ liệu

if (!isset($_SESSION['user_id'])) { // Kiểm tra nếu người dùng chưa đăng nhập
    die("Bạn chưa đăng nhập"); // Dừng chương trình và hiển thị thông báo
}

if (!isset($_GET['id'])) { // Kiểm tra nếu không có tham số id (ID đơn hàng) trên URL
    die("Thiếu ID đơn hàng"); // Dừng chương trình và báo lỗi
}

$order_id = $_GET['id']; // Lấy giá trị id từ URL và gán vào biến $order_id (ID đơn hàng cần hủy)

// 👉 Kiểm tra quyền (user chỉ hủy đơn của mình)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?"); // Chuẩn bị câu lệnh SQL để lấy thông tin đơn hàng theo id
$stmt->execute([$order_id]); // Thực thi câu lệnh, truyền vào id đơn hàng
$order = $stmt->fetch(); // Lấy dữ liệu đơn hàng (1 dòng) từ kết quả truy vấn

if (!$order) { // Kiểm tra nếu không tìm thấy đơn hàng
    die("Đơn không tồn tại"); // Dừng chương trình và báo lỗi
}

// 👤 USER
if (!isset($_SESSION['is_admin'])) { // Nếu không phải admin (tức là user thường)
    if ($order['user_id'] != $_SESSION['user_id']) { // Kiểm tra nếu đơn hàng này không thuộc về user đang đăng nhập
        die("Bạn không có quyền hủy đơn này"); // Không cho phép hủy và báo lỗi
    }
}

// ❌ Chỉ cho hủy khi còn trạng thái Mới
if ($order['status'] != 'Mới') { // Kiểm tra nếu trạng thái đơn hàng không phải "Mới"
    die("Không thể hủy đơn này"); // Không cho phép hủy nếu đơn đã xử lý
}

// ✅ Update trạng thái
$stmt = $pdo->prepare("UPDATE orders SET status = 'Đã hủy' WHERE id = ?");
$stmt->execute([$order_id]);

header("Location: my_orders.php");
exit();