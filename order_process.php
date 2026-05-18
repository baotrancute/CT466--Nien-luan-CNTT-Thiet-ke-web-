<?php


session_start();// Khởi động phiên làm việc (session) để sử dụng dữ liệu giỏ hàng, user, v.v.
include 'db_config.php'; // Kết nối đến file cấu hình cơ sở dữ liệu (tạo ra biến $pdo để truy vấn DB).


if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SESSION['cart'])) {// Kiểm tra xem request có phải là POST hay không (xác nhận đơn hàng).
    // Đồng thời kiểm tra giỏ hàng (session 'cart') có dữ liệu hay chưa.
    
    $pdo->beginTransaction();// Bắt đầu một giao dịch (transaction).//
    
   try {  // Bắt đầu khối try để xử lý và bắt lỗi
        $user_id = $_SESSION['user_id'] ?? 0;  // Lấy ID người dùng từ session, nếu không có thì dùng 0
            $total_amount = 0;  // Khởi tạo biến tổng tiền đơn hàng
            foreach ($_SESSION['cart'] as $item) {  // Lặp qua từng sản phẩm trong giỏ hàng
             $total_amount += $item['price'] * $item['quantity'];  // Tính tiền từng sản phẩm rồi cộng dồn vào tổng
}


        // BƯỚC 1: LƯU ĐƠN HÀNG VÀO BẢNG 'orders'
$sql_order = "INSERT INTO orders 
(user_id, customer_name, customer_phone, customer_address, total_amount, shipping_fee, status, order_date) 
VALUES 
(:user_id, :name, :phone, :address, :total, 0, 'Mới', NOW())";// Tạo câu SQL thêm đơn hàng mới, trạng thái mặc định Pending
        $stmt_order = $pdo->prepare($sql_order);// Chuẩn bị câu SQL để tránh lỗi và chống SQL Injection
$stmt_order->execute([
':user_id' => $user_id,
':name' => $_POST['name'],
':phone' => $_POST['phone'],
':address' => $_POST['address'],
':total' => $total_amount
]);
        
        $order_id = $pdo->lastInsertId();// Lấy ID của đơn hàng vừa được thêm vào bảng orders

        // BƯỚC 2: LƯU CHI TIẾT ĐƠN HÀNG
        $sql_detail = "INSERT INTO order_items (order_id, product_id, price, quantity) 
               VALUES (:order_id, :product_id, :price, :quantity)";
        $stmt_detail = $pdo->prepare($sql_detail);// Chuẩn bị câu SQL chi tiết đơn hàng//
        
        foreach ($_SESSION['cart'] as $product_id => $item) {// Lặp qua từng sản phẩm trong giỏ hàng
            $stmt_detail->execute([
                ':order_id' => $order_id,// Gắn ID đơn hàng
                ':product_id' => $product_id,   // ID sản phẩm
                ':price' => $item['price'],     // Giá của sản phẩm lúc đặt
                ':quantity' => $item['quantity'] // Số lượng mua
            ]);
        }

        $pdo->commit();// Xác nhận toàn bộ giao dịch, lưu tất cả vào DB
       $cart_items = [];

foreach ($_SESSION['cart'] as $item) {
    $cart_items[] = [
        'name' => $item['name'],
        'price' => $item['price'],
        'quantity' => $item['quantity']
    ];
}

$_SESSION['order_info'] = [
    'order_id' => $order_id,
    'name' => $_POST['name'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'address' => $_POST['address'] ?? '',
    'order_time' => date('Y-m-d H:i:s'),
    'delivery_time' => date('Y-m-d H:i:s', strtotime('+3 days')),
    'total' => $total_amount,
    'shipping' => 0,
    'cart' => $cart_items
];
        // BƯỚC 3: XÓA GIỎ HÀNG VÀ ĐẶT THÔNG BÁO THÀNH CÔNG
        unset($_SESSION['cart']);// Xóa toàn bộ giỏ hàng sau khi đặt hàng thành công
        $_SESSION['order_success_message'] = "Đơn hàng **#{$order_id}** của bạn đã được đặt thành công!";
        // Lưu thông báo thành công vào session để hiển thị ở trang khác

        header("Location: order_success.php?id=" . $order_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack(); // Hủy toàn bộ thay đổi trong DB nếu có lỗi
        $_SESSION['order_error'] = "Lỗi đặt hàng: " . $e->getMessage(); // Lưu thông báo lỗi vào session
        header("Location: cart.php");  // Quay lại giỏ hàng
          exit();  // Dừng chương trình
    }

} else {
    header("Location: index.php"); // Nếu giỏ hàng trống hoặc truy cập sai, về trang chủ
    exit();  // Dừng chương trình
}

?>