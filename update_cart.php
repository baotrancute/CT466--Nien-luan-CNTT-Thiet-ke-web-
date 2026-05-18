<?php
// cập nhật gh + gtien sp
session_start();

header('Content-Type: application/json');  // Thiết lập kiểu trả về là JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['quantity'])) {  // Kiểm tra xem có POST hợp lệ và đủ dữ liệu
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);  // Trả về JSON thông báo lỗi
    exit();  // Dừng thực thi

}

$productId = (int)$_POST['id'];       // Ép kiểu id sang số nguyên để an toàn
$newQuantity = (int)$_POST['quantity']; // Ép kiểu số lượng sang số nguyên

if ($newQuantity <= 0) {  // Kiểm tra số lượng hợp lệ
    echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0.']);  // Trả về JSON thông báo lỗi
    exit();  // Dừng thực thi

}

if (!isset($_SESSION['cart'][$productId])) {  // Kiểm tra sản phẩm có tồn tại trong giỏ hàng hay không
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.']);  // Trả về JSON thông báo lỗi
    exit();  // Dừng thực thi

}

// Cập nhật số lượng
$_SESSION['cart'][$productId]['quantity'] = $newQuantity;

// Tính lại tổng tiền giỏ hàng và thành tiền sản phẩm
$total_price = 0;      // Khởi tạo tổng tiền giỏ hàng
$item_subtotal = 0;    // Khởi tạo thành tiền của sản phẩm đang cập nhật

foreach ($_SESSION['cart'] as $item) {  // Duyệt từng sản phẩm trong giỏ hàng
    $subtotal = $item['price'] * $item['quantity'];  // Tính thành tiền của sản phẩm
    $total_price += $subtotal;                       // Cộng vào tổng tiền giỏ hàng
    if ($item['id'] == $productId) {                // Nếu đây là sản phẩm đang cập nhật
        $item_subtotal = $subtotal;                 // Lưu thành tiền của sản phẩm đó
    }

}

// Trả về kết quả JSON
echo json_encode([
    'success' => true,  // Trả về thành công
    'total_price_formatted' => number_format($total_price, 0, ',', '.'),  // Tổng tiền giỏ hàng định dạng VND
    'item_subtotal_formatted' => number_format($item_subtotal, 0, ',', '.'),  // Thành tiền sản phẩm định dạng VND
    'message' => 'Cập nhật số lượng thành công.'  // Thông báo thành công
]);


?>