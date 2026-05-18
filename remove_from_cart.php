<?php
// xóa ghang
session_start();

// 1. Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {  // Kiểm tra xem tham số id có tồn tại và là số hay không
    header("Location: cart.php");  // Nếu không hợp lệ, chuyển hướng về trang giỏ hàng
    exit();  // Dừng thực thi tiếp

}

$product_id = (int)$_GET['id'];  // Ép kiểu id từ URL sang số nguyên để an toàn

// 2. Kiểm tra và xóa sản phẩm khỏi Session Giỏ hàng
if (isset($_SESSION['cart'])) {  // Nếu giỏ hàng tồn tại
    if (array_key_exists($product_id, $_SESSION['cart'])) {  // Nếu sản phẩm tồn tại trong giỏ
        unset($_SESSION['cart'][$product_id]);  // Xóa sản phẩm khỏi mảng giỏ hàng
    }
}

// 3. Chuyển hướng trở lại trang Giỏ hàng
header("Location: cart.php"); 
exit();
?>