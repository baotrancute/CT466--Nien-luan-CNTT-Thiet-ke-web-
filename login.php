<?php
session_start(); 
include 'db_config.php'; // Nhúng file cấu hình kết nối cơ sở dữ liệu


$message = ""; // Biến lưu thông báo hiển thị ra giao diện
$email = ""; // Biến lưu email (dùng khi cần giữ lại dữ liệu)
/* Thông báo đăng ký thành công */
if (isset($_SESSION['registration_success'])) { // Kiểm tra xem session có thông báo đăng ký thành công hay không
    $message = "<div style='color: green; text-align:center; font-weight:bold;'>" .
     $_SESSION['registration_success'] . "</div>"; // Gán nội dung thông báo thành công để hiển thị
    unset($_SESSION['registration_success']); // Xóa thông báo khỏi session sau khi đã hiển thị
}

/* Kiểm tra kết nối DB */
if (!isset($pdo)) { // Kiểm tra biến $pdo có tồn tại hay không (tức là có kết nối CSDL chưa)
    $message = "<div style='color:red; text-align:center;'>Lỗi hệ thống: Không kết nối được CSDL.</div>"; // Gán thông báo lỗi để hiển thị ra giao diện
}

/* Xử lý đăng nhập */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($pdo)) { // Kiểm tra có submit form bằng POST và đã có kết nối CSDL

    $email = trim($_POST['email']); // Lấy email từ form và loại bỏ khoảng trắng dư
    $password = $_POST['password']; // Lấy mật khẩu từ form

    if (empty($email) || empty($password)) { // Kiểm tra nếu email hoặc mật khẩu bị bỏ trống
        $message = "<div style='color:red; text-align:center;'>Vui lòng nhập đầy đủ Email và Mật khẩu.</div>"; // Thông báo lỗi cho người dùng
    } else { // Nếu đã nhập đầy đủ dữ liệu

       $sql = "SELECT id, fullname, password, is_admin FROM users WHERE email = :email LIMIT 1"; // Câu SQL lấy thông tin người dùng theo email
        $stmt = $pdo->prepare($sql); // Chuẩn bị câu lệnh SQL để chống SQL Injection
        $stmt->execute([':email' => $email]); // Gán giá trị email vào placeholder và thực thi truy vấn
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Lấy 1 bản ghi người dùng dưới dạng mảng associative

        if ($user && password_verify($password, $user['password'])) { // Kiểm tra có user và mật khẩu nhập vào khớp mật khẩu đã mã hóa

            $_SESSION['user_id']  = $user['id']; // Lưu ID người dùng vào session
            $_SESSION['fullname'] = $user['fullname']; // Lưu tên người dùng vào session
            $_SESSION['is_admin'] = $user['is_admin']; // Lưu quyền admin (0/1) vào session

            // PHÂN QUYỀN
            if ($user['is_admin'] == 1) { // kiểm tra nếu là admin//
                header("Location: admin/products.php"); // admin trả về trang qtng-qlsp-qlt
            } else {
                header("Location: index.php"); // user thường trả về trang chủ
            }
            exit();

        } else { // Trường hợp không tìm thấy user hoặc mật khẩu không đúng
             $message = "<div style='color:red; text-align:center;'>Email hoặc mật khẩu không chính xác.</div>"; // Gán thông báo lỗi đăng nhập sai
            }

    }
}
?>

<!DOCTYPE html><!-- Khai báo loại tài liệu, giúp trình duyệt hiểu đây là HTML5 -->
<html lang="vi"> <!-- Trang web sử dụng ngôn ngữ tiếng Việt -->
<head>
     <style>
      /* ===== LOGIN STYLE MATCH CART ===== */

body {
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);
    background-color: #fff7ec;
    background-repeat: no-repeat;
    background-attachment: fixed;

    min-height: 50vh;
    display: flex;
    flex-direction: column;

}

main {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
    padding-top: 30px;
    
}


.login-container {
    max-width: 450px;
    width: 200%;
    background: #ffffff;
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    text-align: center;
}
.footer {
    padding: 10px 0;
}

  </style>
    <meta charset="UTF-8"><!-- Đặt bảng mã UTF-8 để hiển thị tiếng Việt không bị lỗi -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- Giúp giao diện responsive trên mobile -->
    <title>Đăng Nhập Hệ Thống - CrystalSpace</title> <!-- Tiêu đề hiển thị trên tab trình duyệt -->
    
    <link rel="stylesheet" href="css/style.css"><!-- Import file CSS của bạn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">  <!-- Import thư viện icon FontAwesome để dùng các icon như user, khóa, vv. -->
    
</head>
<body>
<?php include "includes/header.php"; ?>
    <main><!-- Phần nội dung chính của trang -->
        <div class="login-container"><!-- Khối chứa form đăng nhập -->
            <h2>ĐĂNG NHẬP HỆ THỐNG</h2><!-- Tiêu đề lớn của trang đăng nhập -->
            
            <?php echo $message; ?><!-- Hiển thị thông báo lỗi/thành công từ PHP nếu có -->

            <form action="login.php" method="POST"><!-- Form gửi dữ liệu bằng POST về trang login.php -->
                
                <div class="form-group"><!-- Nhóm ô nhập Email -->
                    <label for="email"><i class="fas fa-envelope"></i> Địa chỉ Email</label><!-- Nhãn Email + icon -->
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"><!-- Input nhập email, tự động điền lại giá trị cũ nếu nhập sai -->
                </div>

                <div class="form-group"><!-- Nhóm ô nhập mật khẩu -->
                    <label for="password"><i class="fas fa-lock"></i> Mật Khẩu</label><!-- Nhãn mật khẩu + icon -->
                    <input type="password" id="password" name="password" required><!-- Ô nhập mật khẩu, không hiển thị ký tự -->
                </div>

                <button type="submit" class="btn-login"><!-- Nút gửi form để thực hiện đăng nhập -->
                    ĐĂNG NHẬP
                </button>
            
            </form>

            <div class="login-footer"> <!-- Phần liên kết phụ dưới form -->
                <p>Quên mật khẩu? <a href="#">Nhấp vào đây</a></p><!-- Link hỗ trợ quên mật khẩu -->
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p><!-- Link chuyển qua trang đăng ký -->
            </div>
        </div>
    </main>

<?php include "includes/footer.php"; ?>

    </body>
</html>