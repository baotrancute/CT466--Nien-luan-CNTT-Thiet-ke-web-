<?php
//thêm vào ghang

session_start(); 
include 'db_config.php'; // Kết nối Database
//Nhúng file cấu hình kết nối cơ sở dữ liệu, để truy vấn sản phẩm //
// ❌ Nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// 1. Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || !is_numeric($_GET['id']))  { // Kiểm tra xem tham số 'id' có tồn tại và là số hợp lệ hay không//
    header("Location: index.php"); // Quay về trang chủ nếu thiếu ID
    exit();
}

$product_id = (int)$_GET['id']; // Lấy ID sản phẩm từ URL và ép kiểu sang số nguyên để đảm bảo an toàn //
$quantity = 1; // Mặc định là thêm 1 sản phẩm

// 2. Lấy thông tin sản phẩm từ Database
$sql = "SELECT id, name, price, image FROM products WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch();
// Thực hiện truy vấn lấy thông tin sản phẩm từ bảng products theo ID //
if (!$product) {
    // Sản phẩm không tồn tại
    header("Location: index.php"); // Nếu không tìm thấy sản phẩm, chuyển hướng về trang chủ //
    exit();
}

// 3. Khởi tạo Giỏ hàng (nếu chưa có)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Nếu chưa có giỏ hàng trong session, tạo mảng rỗng để lưu trữ sản phẩm//
}

// 4. Thêm sản phẩm vào Giỏ hàng
if (array_key_exists($product_id, $_SESSION['cart'])) {
    // Nếu sản phẩm đã có trong giỏ, tăng số lượng
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    // Nếu sản phẩm chưa có, thêm mới vào giỏ
    $_SESSION['cart'][$product_id] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'quantity' => $quantity
    ];
}

//5. Chuyển hướng đến trang Giỏ hàng
     
        header("Location: cart.php");
        exit();
?>