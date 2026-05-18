<?php
session_start(); // Khởi động session để lưu thông tin người dùng
include 'db_config.php'; // Nhúng file cấu hình kết nối CSDL

$message = ""; // Biến lưu thông báo lỗi hoặc thành công
$fullname = ""; // Biến lưu họ tên người dùng để giữ lại khi form lỗi
$email = ""; // Biến lưu email người dùng để giữ lại khi form lỗi

if (!isset($pdo)) { // Kiểm tra biến $pdo (kết nối CSDL) có tồn tại không
    $message = "<div style='color:red; text-align:center;'>Lỗi kết nối CSDL.</div>"; // Hiển thị thông báo lỗi kết nối
}

else if ($_SERVER["REQUEST_METHOD"] === "POST") { // Kiểm tra nếu form được gửi bằng phương thức POST

    $fullname = trim($_POST['fullname']); // Lấy họ tên từ form và loại bỏ khoảng trắng dư
    $email = trim($_POST['email']); // Lấy email từ form và loại bỏ khoảng trắng dư
    $password = $_POST['password']; // Lấy mật khẩu người dùng nhập
    $confirm_password = $_POST['confirm_password']; // Lấy mật khẩu xác nhận
    $is_admin = 0; // Gán mặc định tài khoản là người dùng thường (không phải admin)

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Kiểm tra định dạng email có hợp lệ không
        $message = "<div style='color:red; text-align:center;'>Email không hợp lệ.</div>"; // Thông báo lỗi email
    }
    else if ($password !== $confirm_password) { // So sánh mật khẩu và mật khẩu xác nhận
        $message = "<div style='color:red; text-align:center;'>Mật khẩu xác nhận không khớp.</div>"; // Báo lỗi nếu không trùng
    }
    else if (strlen($password) < 6) { // Kiểm tra độ dài mật khẩu
        $message = "<div style='color:red; text-align:center;'>Mật khẩu tối thiểu 6 ký tự.</div>"; // Báo lỗi mật khẩu quá ngắn
    }
    else { // Nếu tất cả điều kiện đều hợp lệ

        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu bằng thuật toán an toàn mặc định của PHP

            $sql = "INSERT INTO users (fullname, email, password, is_admin)
                    VALUES (:fullname, :email, :password_hash, :is_admin)"; // Câu lệnh SQL thêm tài khoản mới vào bảng users
            $stmt = $pdo->prepare($sql); // Chuẩn bị câu lệnh SQL để thực thi an toàn (chống SQL Injection)

            try { // Bắt đầu khối xử lý có thể phát sinh lỗi CSDL
                $stmt->execute([ // Thực thi câu lệnh SQL với dữ liệu truyền vào
                    ':fullname' => $fullname, // Gán họ tên vào placeholder :fullname
                    ':email' => $email, // Gán email vào placeholder :email
                    ':password_hash' => $hashed_password, // Gán mật khẩu đã mã hóa
                    ':is_admin' => $is_admin // Gán quyền người dùng (0 = user)
                ]);
                $_SESSION['registration_success'] = "Đăng ký thành công! Vui lòng đăng nhập."; // Lưu thông báo thành công vào session
                header("Location: login.php"); // Chuyển hướng sang trang đăng nhập
                exit(); // Dừng chương trình sau khi chuyển trang

                 } catch (PDOException $e) { // Bắt lỗi xảy ra khi thao tác với CSDL
                      if ($e->getCode() == 23000) { // Mã lỗi 23000 là lỗi trùng khóa (email đã tồn tại)
                    $message = "<div style='color:red; text-align:center;'>Email đã tồn tại.</div>"; // Thông báo email bị trùng
                } else { // Các lỗi CSDL khác
                    $message = "<div style='color:red; text-align:center;'>Lỗi hệ thống.</div>"; // Thông báo lỗi chung
                }
            }
       }
}
?>

<!DOCTYPE html> <!-- Khai báo loại tài liệu HTML5 -->
<html lang="vi"> <!-- Thiết lập ngôn ngữ là tiếng Việt -->
<head>
     <style>
       
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
    width: 100%; /* sửa từ 200% -> 100% */
    background: #ffffff;
    border-radius: 14px;
    padding: 20px; /* tăng padding cho đẹp */
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    text-align: center;
}

.footer {
    padding: 10px 0;
}
    </style>
    <meta charset="UTF-8"> <!-- Đặt bộ mã ký tự UTF-8 cho trang -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Đảm bảo hiển thị responsive trên thiết bị di động -->
    <title>Đăng Ký Tài Khoản - CrystalSpace</title> <!-- Tiêu đề trang hiển thị trên trình duyệt -->
    
    <link rel="stylesheet" href="css/style.css"> <!-- Liên kết file CSS chính của trang -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Liên kết thư viện Font Awesome để dùng icon -->

</head>
<body>
<?php include "includes/header.php"; ?>
   
    <main>
        <div class="login-container" style="max-width: 500px;"> <!-- Khối chính chứa form đăng ký, giới hạn chiều rộng 500px -->
                <h2>TẠO TÀI KHOẢN MỚI</h2> <!-- Tiêu đề của form -->
                
                <?php echo $message; ?> <!-- Hiển thị thông báo lỗi hoặc thành công nếu có -->
                
                <form action="register.php" method="POST"> <!-- Form gửi dữ liệu về trang register.php bằng phương thức POST -->

                <div class="form-group"> <!-- Nhóm input họ và tên -->
                    <label for="fullname"><i class="fas fa-signature"></i> Họ và Tên</label> <!-- Label với icon Font Awesome -->
                    <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($fullname); ?>"> <!-- Input nhập họ tên, giữ lại giá trị cũ khi lỗi -->
                </div>

                <div class="form-group"> <!-- Nhóm input email -->
                    <label for="email"><i class="fas fa-envelope"></i> Địa chỉ Email</label> <!-- Label với icon email -->
                    <input type="text" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"> <!-- Input email, giữ lại giá trị cũ khi lỗi -->
                </div>

                <div class="form-group"> <!-- Nhóm input mật khẩu -->
                    <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label> <!-- Label với icon khóa -->
                    <input type="password" id="password" name="password" required> <!-- Input mật khẩu, không hiển thị giá trị cũ để bảo mật -->
                </div>

                
              <div class="form-group"> <!-- Nhóm input xác nhận mật khẩu -->
                <label for="confirm_password"><i class="fas fa-lock"></i> Xác nhận Mật khẩu</label> <!-- Label với icon khóa -->
                <input type="password" id="confirm_password" name="confirm_password" required> <!-- Input xác nhận mật khẩu, bắt buộc nhập -->
            </div>

            <button type="submit" class="btn-login"> <!-- Nút gửi form đăng ký -->
                ĐĂNG KÝ
            </button>

           </form> <!-- Kết thúc form đăng ký -->

            <div class="login-footer"> <!-- Khối thông tin phía dưới form -->
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p> <!-- Link dẫn tới trang đăng nhập nếu đã có tài khoản -->
            </div>

            </div> <!-- Kết thúc login-container -->
      </main>

<?php include "includes/footer.php"; ?>

</body>

</html>