<?php
// FILE: logout.php

session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Nếu muốn hủy session, cũng cần xóa session cookie.
// Lưu ý: Việc này sẽ làm hỏng session, không chỉ đơn giản là session data.
if (ini_get("session.use_cookies")) { // Kiểm tra xem PHP có đang dùng cookie để lưu session hay không.
    $params = session_get_cookie_params(); // Lấy toàn bộ thông tin của cookie session hiện tại (path, domain, secure, httponly).
    setcookie(session_name(), '', time() - 42000,// Hết hạn trong quá khứ => trình duyệt sẽ tự xoá//
        $params["path"], $params["domain"],// Đúng đường dẫn cũ, Đúng domain cũ
        $params["secure"], $params["httponly"]//Có yêu cầu HTTPS hay không . Chế độ chỉ cho HTTP (bảo vệ khỏi XSS)//
    );
}

// Hủy session
session_destroy();

// Chuyển hướng về trang chủ
header("Location: index.php");
exit();
?>