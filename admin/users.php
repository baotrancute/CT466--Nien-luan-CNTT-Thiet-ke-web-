<?php
// trang qli ng dùng (admin_)

session_start();

$currentPage = basename($_SERVER['PHP_SELF']);
//cho bic đang ở trang nào
// nhúng file kn db trc
// Đảm bảo đường dẫn đến db_config.php là chính xác
include '../db_config.php'; 

// 2. LOGIC BẢO VỆ (Kiểm tra quyền Admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) != 1) {
    header("Location: ../index.php"); 
    exit();
}
//phân `ss
$message = "";

// Biến lưu thông tin người dùng đang được sửa (Edit Mode)
$is_edit_mode = false;
$edit_user = [
    'id' => '', 
    'fullname' => '', 
    'email' => '', 
    'is_admin' => 0, // Mặc định là user thường
];

//xly xó ng dùng
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) { //Kiểm tra nếu có yêu cầu xóa qua tham số URL delete_id.
    $delete_id = $_GET['delete_id']; //Lấy Id người dùng cần xóa
    
    // Ngăn chặn xóa tài khoản Admin đang đăng nhập
    if ($delete_id == $_SESSION['user_id']) { // Kiểm tra nếu ID cần xóa trùng với ID Admin đang đăng nhập.
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không thể tự xóa tài khoản quản trị viên đang đăng nhập!</div>"; // hiện thông báo lỗi
        goto end_delete; // Chuyển đến nhãn end_delete để bỏ qua logic xóa
    }

    $sql_delete = "DELETE FROM users WHERE id = :id"; // Câu lệnh SQL xóa người dùng.
    
    try {
        $stmt_delete = $pdo->prepare($sql_delete);// Chuẩn bị câu lệnh SQL.
        $stmt_delete->execute([':id' => $delete_id]);  // Thực thi xóa với ID.
        
        if ($stmt_delete->rowCount() > 0) { // Kiểm tra xem có hàng nào bị xóa không.
            $message = "<div style='color: green; text-align: center; font-weight: bold;'>Xóa người dùng ID: {$delete_id} thành công!</div>";// Thông báo thành công.
        } else {
            $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không tìm thấy người dùng để xóa.</div>";// Thông báo lỗi không tìm thấy.
        }
    } catch (\PDOException $e) { // Bắt lỗi PDO (lỗi CSDL).
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi xóa người dùng: " . $e->getMessage() . "</div>";// Hiển thị lỗi CSDL.
    }
}
end_delete: // Nhãn để goto chuyển tới sau khi xử lý xóa.

// =================================================================
// 4. CHUYỂN SANG CHẾ ĐỘ SỬA (GET - Đọc dữ liệu người dùng cần sửa)
// =================================================================
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) { // Kiểm tra nếu có yêu cầu sửa qua tham số URL edit_id.
    $edit_id = $_GET['edit_id']; // Lấy ID người dùng cần sửa.
    $sql_edit = "SELECT id, fullname, email, is_admin FROM users WHERE id = :id"; // Câu lệnh SQL lấy thông tin người dùng.
    $stmt_edit = $pdo->prepare($sql_edit); // Chuẩn bị câu lệnh.
    $stmt_edit->execute([':id' => $edit_id]); // Thực thi với ID.
    
    if ($stmt_edit->rowCount() > 0) { // Nếu tìm thấy người dùng:
        $edit_user = $stmt_edit->fetch(PDO::FETCH_ASSOC); // Lấy dữ liệu người dùng dưới dạng mảng kết hợp.
        $is_edit_mode = true; // Chuyển sang chế độ Sửa.
    } else {
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không tìm thấy người dùng để sửa.</div>"; // Thông báo lỗi.
    }
}


// =================================================================
// 5. XỬ LÝ THÊM (CREATE) VÀ SỬA (UPDATE) NGƯỜI DÙNG KHI CÓ POST
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Kiểm tra nếu dữ liệu được gửi đi bằng phương thức POST (từ Form).

    // 5a. Lấy dữ liệu từ form
    $user_id = trim($_POST['user_id'] ?? ''); // Lấy ID nếu đang sửa (nếu không có thì là rỗng).
    $fullname = trim($_POST['fullname']); // Lấy Tên đầy đủ.
    $email = trim($_POST['email']); // Lấy Email.
    $password = trim($_POST['password'] ?? ''); // Lấy Mật khẩu (nếu có).
    $is_admin = isset($_POST['is_admin']) ? 1 : 0; // Lấy quyền Admin (1 nếu checkbox được check, 0 nếu không).
    $hash_password = ''; // Khởi tạo biến lưu mật khẩu đã hash.

    // Kiểm tra định dạng Email cơ bản
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Kiểm tra xem email có hợp lệ không.
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Định dạng email không hợp lệ.</div>"; // Thông báo lỗi email.
        goto end_post_processing; // Chuyển đến nhãn kết thúc xử lý POST.
    }

    // 5b. Xử lý Mật khẩu
    if (!empty($password)) { // Nếu mật khẩu được nhập (không rỗng):
        if (strlen($password) < 6) { // Kiểm tra độ dài mật khẩu tối thiểu 6 ký tự.
             $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Mật khẩu phải có tối thiểu 6 ký tự.</div>"; // Thông báo lỗi.
             goto end_post_processing; // Chuyển đến nhãn kết thúc xử lý POST.
        }
        // Hash mật khẩu
        $hash_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu (hash) bằng thuật toán mặc định.
    }
    // Ghi chú: Nếu đang sửa và $password rỗng, $hash_password vẫn rỗng.

    // 5c. Thực thi Query (UPDATE hoặc INSERT)
    if (!empty($user_id)) { // Nếu có user_id (đang trong chế độ SỬA):
        // CHẾ ĐỘ UPDATE
        $sql = "UPDATE users SET fullname = :fullname, email = :email, is_admin = :is_admin"; // Khởi tạo câu lệnh UPDATE.
        $params = [ // Thiết lập mảng tham số ban đầu.
            ':id' => $user_id, // ID người dùng cần cập nhật.
            ':fullname' => $fullname, // Tên đầy đủ.
            ':email' => $email, // Email.
            ':is_admin' => $is_admin // Quyền Admin.
        ];

        if (!empty($hash_password)) { // Nếu mật khẩu mới đã được nhập và hash:
            $sql .= ", password = :password"; // Thêm cột password vào câu lệnh UPDATE.
            $params[':password'] = $hash_password; // Thêm mật khẩu đã hash vào mảng tham số.
        }
        $sql .= " WHERE id = :id"; // Thêm điều kiện WHERE để cập nhật đúng người dùng.
        
        $success_message = "<div style='color: green; text-align: center; font-weight: bold;'>Cập nhật người dùng ID: {$user_id} thành công!</div>"; // Thiết lập thông báo thành công.
        $error_message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không thể cập nhật người dùng. Chi tiết: "; // Thiết lập thông báo lỗi.

    } else { // Nếu không có user_id (đang trong chế độ THÊM MỚI):
        // CHẾ ĐỘ CREATE (INSERT)
        if (empty($hash_password)) { // Kiểm tra mật khẩu (bắt buộc phải có khi thêm mới).
            $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Mật khẩu là bắt buộc khi thêm người dùng mới.</div>"; // Thông báo lỗi.
            goto end_post_processing; // Chuyển đến nhãn kết thúc xử lý POST.
        }

        $sql = "INSERT INTO users (fullname, email, password, is_admin) "; // Khởi tạo câu lệnh INSERT.
        $sql .= " VALUES (:fullname, :email, :password, :is_admin)"; // Định nghĩa giá trị INSERT.
        $params = [ // Thiết lập mảng tham số.
            ':fullname' => $fullname, // Tên đầy đủ.
            ':email' => $email, // Email.
            ':password' => $hash_password, // Mật khẩu đã hash.
            ':is_admin' => $is_admin // Quyền Admin.
        ];
        $success_message = "<div style='color: green; text-align: center; font-weight: bold;'>Thêm người dùng thành công!</div>"; // Thiết lập thông báo thành công.
        $error_message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không thể thêm người dùng. Chi tiết: "; // Thiết lập thông báo lỗi.
    }
   try {
        $stmt = $pdo->prepare($sql); // Chuẩn bị câu lệnh SQL.
        $stmt->execute($params); // Thực thi câu lệnh (INSERT/UPDATE).
        $message = $success_message; // Gán thông báo thành công.
        
        // Chuyển hướng sau khi cập nhật để làm mới trang và thoát chế độ sửa
        if (!empty($user_id)) { // Nếu đang ở chế độ Sửa (UPDATE):
             header("Location: users.php?message_update_success=1"); // Chuyển hướng để xóa tham số edit_id trên URL.
             exit(); // Dừng script.
        }

    } catch (\PDOException $e) { // Bắt lỗi PDO (lỗi CSDL).
        // Kiểm tra lỗi trùng email (MySQL code 23000)
        if ($e->getCode() == '23000') { // Nếu mã lỗi là 23000 (Lỗi khóa trùng lặp/email đã tồn tại).
             $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Email đã tồn tại trong hệ thống.</div>"; // Thông báo lỗi trùng email.
        } else {
             $message = $error_message . $e->getMessage() . "</div>"; // Thông báo lỗi CSDL chi tiết khác.
        }
    }
}

end_post_processing: // Nhãn để goto chuyển tới sau khi xử lý POST.
// Xử lý thông báo sau khi chuyển hướng
if (isset($_GET['message_update_success'])) { // Nếu có tham số báo hiệu cập nhật thành công sau khi chuyển hướng:
    $message = "<div style='color: green; text-align: center; font-weight: bold;'>Cập nhật người dùng thành công!</div>"; // Hiển thị thông báo.
}

// =================================================================
// 6. LẤY DỮ LIỆU NGƯỜI DÙNG HIỆN CÓ (READ)
// =================================================================
// Sắp xếp ID giảm dần (người dùng mới nhất lên trên)
$users_result = $pdo->query("SELECT id, fullname, email, is_admin, created_at FROM users ORDER BY id DESC"); // Thực thi query lấy tất cả người dùng, sắp xếp giảm dần theo ID.
$user_count = $users_result->rowCount(); // Đếm tổng số người dùng.
?>


<!DOCTYPE html> <!-- Khai báo loại tài liệu HTML5, giúp trình duyệt hiểu đây là trang HTML5 -->

<html lang="vi"> <!-- Thẻ gốc của trang HTML, thuộc tính lang="vi" chỉ ngôn ngữ là tiếng Việt -->
<head> <!-- Phần đầu của trang HTML, chứa meta, title, CSS, script... -->
    <meta charset="UTF-8"> <!-- Thiết lập bảng mã ký tự UTF-8, hỗ trợ tiếng Việt và ký tự đặc biệt -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Thiết lập hiển thị trên thiết bị di động, chiều rộng viewport = chiều rộng màn hình, tỷ lệ phóng đại 1 -->
    <title>QUẢN TRỊ NGƯỜI DÙNG - ADMIN</title> <!-- Tiêu đề của trang hiển thị trên tab trình duyệt -->
    <link rel="stylesheet" href="../css/style.css"> <!-- Liên kết file CSS nội bộ để định dạng giao diện -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Liên kết thư viện Font Awesome trên CDN để sử dụng icon -->
    <style> 
/* ===== BACKGROUND  ===== */
body {
    margin: 0;
    padding: 30px;
    font-family: 'Segoe UI', sans-serif;

    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);

    background-color: #fff7ec;
    background-attachment: fixed;
}

/* ===== CONTAINER CHÍNH ===== */
.admin-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 35px;
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
}

/* ===== HEADER ===== */
.admin-header-info h2 {
    font-size: 26px;
    margin: 0;
}

.admin-header-info p {
    font-size: 14px;
}

/* ===== MENU ===== */
.admin-menu {
    margin: 20px 0;
}

.admin-menu a {
    background: #1a1a1a;
    color: white;
    padding: 10px 18px;
    margin-right: 10px;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.3s ease;
}

.admin-menu a:hover,
.admin-menu .active {
    background: #ffcc00;
    color: #1a1a1a;
    transform: translateY(-2px);
}

/* ===== FORM ===== */
.form-user-management {
    border: 2px solid #ffcc00;
    padding: 25px;
    margin-top: 30px;
    margin-bottom: 40px;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
}

.form-user-management input[type="text"],
.form-user-management input[type="email"],
.form-user-management input[type="password"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: 0.2s;
}

.form-user-management input:focus {
    border-color: #ffcc00;
    outline: none;
}

/* ===== BUTTON ===== */
.btn-capnhat {
    background: linear-gradient(135deg, #ffcc00, #ffb700);
    color: #1a1a1a;
    padding: 12px;
    border: none;
    cursor: pointer;
    border-radius: 10px;
    font-weight: bold;
    font-size: 16px;
    transition: 0.3s;
    width: 100%;
}

.btn-capnhat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 183, 0, 0.4);
}

.btn-huybo {
    background-color: #dc3545;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    margin-top: 15px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 12px;
    overflow: hidden;
}

th {
    background: #1a1a1a;
    color: white;
    padding: 12px;
}

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background: #fff3d9;
}

/* ===== ACTION BUTTON ===== */
.action-btn {
    padding: 6px 12px;
    margin: 2px;
    text-decoration: none;
    color: white;
    border-radius: 6px;
    font-size: 14px;
    transition: 0.2s;
    display: inline-block;
margin-right: 8px;

}

.btn-edit {
    background-color: #ffcc00;
    color: #1a1a1a !important;
}

.btn-delete {
    background-color: #dc3545;
}

.action-btn:hover {
    opacity: 0.85;
}
 
th, td {
    text-align: center;
}

    </style>
</head>
<body>
    <main>
        <div class="admin-container"><!-- Container tổng cho trang quản trị -->
            <div class="admin-header-info"><!-- Phần tiêu đề và thông tin Admin -->
                <h2><i class="fas fa-users-cog"></i> TRANG QUẢN TRỊ NGƯỜI DÙNG</h2><!-- Tiêu đề trang với icon Font Awesome -->
                <p>CHÀO MỪNG ADMIN: 
                    <span style="font-weight: bold; color: #1a1a1a;font-size: 15px;"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Quản trị viên'); ?></span>. 
                    <a href="../logout.php" style="color: red;">ĐĂNG XUẤT</a><!-- Hiển thị tên Admin và liên kết đăng xuất -->
                </p>
            </div>
            
            
                <div class="admin-menu">

                    <a href="users.php"
                    class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i> QUẢN TRỊ NGƯỜI DÙNG
                    </a>

                    <a href="products.php"
                    class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">
                        <i class="fas fa-box"></i> QUẢN LÝ SẢN PHẨM
                    </a>

                <a href="orders.php"
                class="<?= $currentPage == 'orders.php' ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i> QUẢN LÝ ĐƠN HÀNG
                </a>

                    <a href="theme.php"
                    class="<?= $currentPage == 'theme.php' ? 'active' : '' ?>">
                        <i class="fas fa-paint-brush"></i> QUẢN LÝ THEME
                    </a>
                    <a href="chat.php"
                    class="<?= $currentPage == 'chat.php' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i> QUẢN LÝ CHAT
                    </a>
                    <a href="/flower_shop/index.php">
                        <i class="fas fa-home"></i> HOME
                    </a>

                </div>

                            
            <hr>

            <?php echo $message; ?><!-- Hiển thị thông báo từ PHP -->

          <div class="form-user-management"><!-- Phần form thêm/chỉnh sửa người dùng -->
    <h3><?php echo $is_edit_mode ? '📝 Chỉnh Sửa Người Dùng #' . htmlspecialchars($edit_user['id']) : '➕ THÊM NGƯỜI DÙNG MỚI'; ?></h3><!-- Tiêu đề form thay đổi theo chế độ chỉnh sửa hoặc thêm mới -->
    
    <form action="users.php" method="POST"><!-- Form này gửi dữ liệu đến file users.php sử dụng phương thức POST -->
        <?php if ($is_edit_mode): ?><!-- Kiểm tra xem có đang ở chế độ chỉnh sửa không -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_user['id']); ?>"> <!-- Nếu là chế độ chỉnh sửa, tạo input ẩn chứa ID của người dùng để xác định user cần cập nhật -->
        <?php endif; ?>
        
        <input type="text" name="fullname" placeholder="Tên đầy đủ" required 
               value="<?php echo htmlspecialchars($edit_user['fullname']); ?>"><!-- Input cho tên đầy đủ của người dùng, bắt buộc phải nhập. Nếu là chế độ chỉnh sửa, sẽ hiển thị tên hiện có -->
               
        <input type="email" name="email" placeholder="Email" required 
               value="<?php echo htmlspecialchars($edit_user['email']); ?>"><!-- Input cho email người dùng, bắt buộc phải nhập. Hiển thị email hiện tại nếu đang chỉnh sửa -->
<label style="display: block; margin-bottom: 15px;">
            <input type="checkbox" name="is_admin" value="1" 
                   <?php echo ($edit_user['is_admin'] == 1) ? 'checked' : ''; ?>> <!-- Checkbox để cấp quyền Admin cho người dùng. Nếu user hiện tại là admin, checkbox sẽ được đánh dấu -->
            CẤP QUYỀN ADMIN (Quản trị viên)
        </label><!-- Nhãn hiển thị bên cạnh checkbox, giải thích chức năng cấp quyền Admin -->
        
        <hr>

        <p style="font-weight: bold; margin-bottom: 5px;">
            <?php echo $is_edit_mode ? 'Đặt lại Mật khẩu (Để trống nếu không muốn thay đổi)' : 'Mật khẩu'; ?><!-- Hiển thị tiêu đề cho trường mật khẩu. Nếu đang chỉnh sửa, hiển thị hướng dẫn đặt lại mật khẩu. Nếu thêm mới, hiển thị 'Mật khẩu' -->
        </p>
        <input type="password" name="password" placeholder="Mật khẩu mới (Tối thiểu 6 ký tự)"
               <?php echo $is_edit_mode ? '' : 'required'; ?>> <!-- Input nhập mật khẩu. Nếu đang thêm mới thì bắt buộc phải nhập (required). Nếu chỉnh sửa thì có thể để trống để giữ mật khẩu cũ -->
        
        <button type="submit" class="btn-capnhat">
            <?php echo $is_edit_mode ? 'CẬP NHẬT TÀI KHOẢN' : 'THÊM NGƯỜI DÙNG'; ?> <!-- Nút submit của form. Nếu đang chỉnh sửa, nút ghi là 'CẬP NHẬT TÀI KHOẢN'. Nếu thêm mới, nút ghi là 'THÊM NGƯỜI DÙNG' -->
        </button>
        
        <?php if ($is_edit_mode): ?><!-- Kiểm tra nếu đang ở chế độ chỉnh sửa -->
            <a href="users.php" class="btn-huybo"><!-- Link hủy bỏ chỉnh sửa và quay về trang danh sách người dùng -->
                Hoàn Tác Chỉnh Sửa
            </a>
        <?php endif; ?>
    </form>
</div>

            <h3>Danh Sách Người Dùng (Tổng cộng: <?php echo $user_count; ?>)</h3><!-- Hiển thị tiêu đề danh sách người dùng và tổng số người dùng hiện có -->
            <table> <!-- Bắt đầu bảng hiển thị danh sách người dùng -->
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đầy đủ</th>
                        <th>Email</th>
                        <th>Quyền</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                         <!-- Tiêu đề các cột trong bảng -->
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($user_count > 0):   // Kiểm tra nếu có ít nhất 1 người dùng //
                        while($row = $users_result->fetch()): ?> <!-- Lặp qua từng người dùng trong kết quả truy vấn -->
                            <tr>
                                <td><?php echo $row['id']; ?></td> <!-- Hiển thị ID của người dùng -->
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td> <!-- Hiển thị tên đầy đủ, sử dụng htmlspecialchars để tránh lỗi XSS -->
                                <td><?php echo htmlspecialchars($row['email']); ?></td><!-- Hiển thị email, dùng htmlspecialchars để bảo mật -->
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 4px; font-size: 14px; color: white; background-color: <?php echo ($row['is_admin'] == 1) ? '#1a1a1a' : '#007bff'; ?>;">
                                        <?php echo ($row['is_admin'] == 1) ? 'Admin' : 'User'; ?><!-- Hiển thị vai trò người dùng: 'Admin' nếu is_admin = 1, 'User' nếu không. Đồng thời đổi màu nền: đen cho Admin, xanh cho User -->
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'] ?? '')); ?></td><!-- Hiển thị ngày tạo tài khoản của người dùng theo định dạng YYYY-MM-DD HH:MM:SS. Nếu created_at không tồn tại, sử dụng chuỗi rỗng -->
                                <td>
                                    <a href="users.php?edit_id=<?php echo $row['id']; ?>" class="action-btn btn-edit"> <!-- Link để chỉnh sửa người dùng, truyền ID người dùng -->
                                        Sửa
                                    </a>
                                    
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?> <!-- Kiểm tra nếu người dùng hiện tại không phải là chính mình -->
                                        <a href="users.php?delete_id=<?php echo $row['id']; ?>"
                                        class="action-btn btn-delete" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng ID: <?php echo $row['id']; ?> này không?');">
                                           Xóa
                                            <!-- Link để xóa người dùng, yêu cầu xác nhận trước khi xóa -->
                                        </a>
                                    <?php else: ?>
                                        <span class="action-btn btn-delete" style="opacity: 0.5; cursor: not-allowed;">Xóa</span> <!-- Nếu là chính người dùng hiện tại, không cho phép xóa, hiển thị nút Xóa mờ và không click được -->
                                    <?php endif; ?>
                                </td>
                            </tr><!-- Đóng thẻ dòng của bảng cho từng người dùng -->
                        <?php endwhile; ?><!-- Kết thúc vòng lặp while lặp qua tất cả người dùng -->
                    <?php else: ?>
                        <tr><td colspan="6">Chưa có người dùng nào được đăng ký.</td></tr> <!-- Nếu không có người dùng nào, hiển thị thông báo trong bảng -->
                    <?php endif; ?><!-- Kết thúc điều kiện kiểm tra có người dùng hay không -->
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>