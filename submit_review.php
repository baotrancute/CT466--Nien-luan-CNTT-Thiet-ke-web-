<?php
//trang mới, đgia cment dành cho khách
session_start(); // Khởi tạo session để sử dụng thông tin người dùng nếu cần
include "db_config.php"; // Nhúng file cấu hình để kết nối cơ sở dữ liệu

if($_SERVER["REQUEST_METHOD"] == "POST"){ // Kiểm tra nếu request gửi lên bằng phương thức POST

    $product_id = $_POST['product_id']; // Lấy ID sản phẩm từ form gửi lên
    $rating = $_POST['rating']; // Lấy số sao đánh giá từ form
    $comment = $_POST['comment']; // Lấy nội dung bình luận từ form

    $sql = "INSERT INTO reviews (product_id,rating,comment) 
            VALUES (:pid,:rating,:comment)"; // Câu lệnh SQL thêm đánh giá vào bảng reviews

    $stmt = $pdo->prepare($sql); // Chuẩn bị câu lệnh để chống SQL Injection

    $stmt->execute([
        ":pid"=>$product_id, // Gán giá trị product_id vào placeholder :pid
        ":rating"=>$rating, // Gán giá trị rating vào placeholder :rating
        ":comment"=>$comment // Gán nội dung comment vào placeholder :comment
    ]); // Thực thi câu lệnh SQL

    header("Location: product_detail.php?id=".$product_id); // Sau khi thêm xong, chuyển hướng về trang chi tiết sản phẩm
}
?>