<?php
//file này là trang sp riêng cho admin thim sửa xó sp trong đây
//  Khởi tạo Session, kếtt nối DB
session_start(); 

$currentPage = basename($_SERVER['PHP_SELF']);// Lấy tên file PHP hiện tại đang được truy cập, ví dụ
// cu.. là biến dùng để lưu tên trang htai, base.. lấy tên file cúi từ ddan//

include '../db_config.php'; // Nhúng file cấu hình kết nối cơ sở dữ liệu (Database).


if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) != 1) { // Kiểm tra user_id đã tồn tại VÀ is_admin phải bằng 1 (là Admin).
    header("Location: ../index.php"); 
    exit(); // Dừng script lại
}

$message = ""; // Khởi tạo biến lưu trữ thông báo (thành công/lỗi).


$is_edit_mode = false; // Biến cờ xác định chế độ hiện tại là Sửa (true) hay Thêm mới (false).
$edit_product = [ // Mảng lưu trữ dữ liệu sản phẩm khi ở chế độ Sửa.
    'id' => '', // Khởi tạo ID.
    'name' => '', // Khởi tạo Tên sản phẩm.
    'price' => '', // Khởi tạo Giá.
    'quantity' => '', // Khởi tạo Số lượng.
    'description' => '', // Khởi tạo Mô tả.
    'image' => '', // Khởi tạo Đường dẫn ảnh.
    'image2' => '',
    'image3' => '',
    'image4' => ''
    ];

// =================================================================
//  XỬ LÝ XÓA SẢN PHẨM 
// =================================================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) { // Kiểm tra nếu có tham số 'delete_id' trên URL và là số.
    $delete_id = $_GET['delete_id']; // Lấy ID cần xóa.
    
    $pdo->beginTransaction(); // Bắt đầu một giao dịch cơ sở dữ liệu (để đảm bảo xóa ảnh và DB thành công hoặc thất bại cùng nhau).
    
    try { 
        // Bắt đầu khối xử lý lỗi.
        $sql_select_image = "SELECT image, image2, image3, image4 FROM products WHERE id = :id"; // Câu truy vấn lấy đường dẫn ảnh.
        $stmt_select = $pdo->prepare($sql_select_image); // Chuẩn bị truy vấn.
        $stmt_select->execute([':id' => $delete_id]); // Thực thi truy vấn.
        $product = $stmt_select->fetch(PDO::FETCH_ASSOC); // Lấy kết quả dưới dạng mảng kết hợp.
        
        if ($product) { // Kiểm tra nếu biến $product tồn tại và có dữ liệu (sản phẩm hợp lệ)
    $images = ['image', 'image2', 'image3', 'image4']; // Tạo mảng chứa tên các cột ảnh của sản phẩm trong database

    foreach ($images as $img) { // Duyệt qua từng tên cột ảnh (image, image2, image3, image4)
        if (!empty($product[$img])) { // Kiểm tra nếu trường ảnh đó có dữ liệu (không rỗng)
            $path = realpath(__DIR__ . '/../' . $product[$img]); // Lấy đường dẫn tuyệt đối của file ảnh (ghép đường dẫn thư mục + tên file)
            if ($path && file_exists($path) && is_file($path)) { // Kiểm tra đường dẫn hợp lệ, file tồn tại và đúng là file (không phải thư mục)
                unlink($path); // Xóa file ảnh khỏi server
            }
        }
    }
} // Kết thúc xử lý xóa tất cả ảnh liên quan đến sản phẩm

        //  Xóa sản phẩm khỏi db
        $sql_delete = "DELETE FROM products WHERE id = :id"; // Câu truy vấn xóa sản phẩm.
        $stmt_delete = $pdo->prepare($sql_delete); // Chuẩn bị truy vấn.
        $stmt_delete->execute([':id' => $delete_id]); // Thực thi xóa.
        
        if ($stmt_delete->rowCount() > 0) { // Kiểm tra nếu có dòng nào bị ảnh hưởng (xóa thành công).
            $pdo->commit(); // Xác nhận giao dịch (lưu thay đổi).
            $message = "<div style='color: green; text-align: center; font-weight: bold;'>Xóa sản phẩm thành công!</div>"; // Thông báo thành công.
        } else {
            $pdo->rollBack(); // Hoàn tác giao dịch nếu không tìm thấy sản phẩm để xóa.
            $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không tìm thấy sản phẩm để xóa.</div>"; // Thông báo lỗi.
        }
    } catch (\PDOException $e) { // Bắt lỗi PDO (lỗi liên quan đến Database).
        $pdo->rollBack(); // Hoàn tác giao dịch.
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi xóa sản phẩm: " . $e->getMessage() . "</div>"; // Thông báo lỗi chi tiết.
    }
}

// =================================================================
// 4. CHUYỂN SANG CHẾ ĐỘ SỬA (GET - Đọc dữ liệu sản phẩm cần sửa)
// =================================================================
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) { // Kiểm tra nếu có tham số 'edit_id' trên URL và là số.
    $edit_id = $_GET['edit_id']; // Lấy ID sản phẩm cần sửa.
    $sql_edit = "SELECT * FROM products WHERE id = :id"; // Câu truy vấn lấy thông tin sản phẩm.
    $stmt_edit = $pdo->prepare($sql_edit); // Chuẩn bị truy vấn.
$stmt_edit->execute([':id' => $edit_id]); // Thực thi truy vấn.
    
    if ($stmt_edit->rowCount() > 0) { // Kiểm tra nếu tìm thấy sản phẩm.
        $edit_product = $stmt_edit->fetch(PDO::FETCH_ASSOC); // Lấy thông tin sản phẩm để điền vào form.
        $is_edit_mode = true; // Đặt cờ sang chế độ Sửa.
    } else {
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không tìm thấy sản phẩm để sửa.</div>"; // Thông báo lỗi.
    }
}


// =================================================================
// 5. XỬ LÝ THÊM (CREATE) VÀ SỬA (UPDATE) SẢN PHẨM KHI CÓ POST
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Kiểm tra nếu form được gửi đi bằng phương thức POST.

    // 5a. Lấy dữ liệu từ form
    $product_id = trim($_POST['product_id'] ?? ''); // Lấy ID (nếu có, ở chế độ Sửa).
    $name = trim($_POST['name']); // Lấy và làm sạch Tên sản phẩm.
    $price = $_POST['price']; // Lấy Giá.
    $description = trim($_POST['description']); // Lấy và làm sạch Mô tả.
    $quantity = $_POST['quantity']; // Lấy Số lượng.
    
    // Đường dẫn ảnh hiện tại (hoặc rỗng nếu thêm mới)
    $image_path_for_db = trim($_POST['current_image'] ?? ''); // Lấy đường dẫn ảnh cũ từ hidden input (chỉ có khi Sửa).
    $image2 = trim($_POST['current_image2'] ?? '');
    $image3 = trim($_POST['current_image3'] ?? '');
    $image4 = trim($_POST['current_image4'] ?? '');


    // 5b. Xử lý Upload Tệp (chỉ xử lý nếu CÓ tệp mới được chọn)
    $is_file_uploaded = false; // Biến cờ theo dõi trạng thái upload file mới.
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) { // Kiểm tra nếu file được upload và không có lỗi.
        $file_tmp_name = $_FILES['product_image']['tmp_name']; // Đường dẫn tạm của file.
        $file_name = basename($_FILES['product_image']['name']); // Tên file gốc.
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Đuôi file (mở rộng).

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif']; // Các đuôi file cho phép.
        if (!in_array($file_ext, $allowed_exts)) { // Kiểm tra đuôi file có hợp lệ không.
            $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Chỉ cho phép các định dạng JPG, JPEG, PNG, GIF.</div>"; // Thông báo lỗi định dạng.
            goto end_post_processing; // Dùng goto để nhảy qua phần xử lý DB.
        }

        $unique_file_name = time() . '_' . uniqid() . '.' . $file_ext; // Tạo tên file độc nhất.
      $upload_dir = __DIR__ . '/../images/products/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

        $dest_path = $upload_dir . '/' . $unique_file_name; // Đường dẫn đầy đủ để lưu file.

        if (move_uploaded_file($file_tmp_name, $dest_path)) { // Di chuyển file từ thư mục tạm đến thư mục đích.
            // Xóa ảnh cũ nếu đang sửa và ảnh mới được upload thành công
            if (!empty($product_id) && !empty($_POST['current_image'])) { // Nếu đang sửa VÀ có ảnh cũ.
                $old_image_to_delete = realpath(__DIR__ . '/../' . $_POST['current_image']); // Lấy đường dẫn file ảnh cũ.
                if (file_exists($old_image_to_delete) && is_file($old_image_to_delete)) { // Kiểm tra và xóa file ảnh cũ.
                    unlink($old_image_to_delete);
                }
            }
            // Cập nhật đường dẫn ảnh mới cho DB
            $image_path_for_db = "images/products/" . $unique_file_name; // Cập nhật biến đường dẫn ảnh cho DB.
            $is_file_uploaded = true; // Đánh dấu đã upload file.
        } else {
            $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không thể di chuyển tệp đã tải lên.</div>"; // Thông báo lỗi di chuyển file.
            goto end_post_processing; // Dùng goto để nhảy qua phần xử lý DB.
        }
  // Nếu là thêm mới mà KHÔNG có file → báo lỗi
} else if (empty($product_id) && $_FILES['product_image']['error'] == UPLOAD_ERR_NO_FILE) {
    // TRƯỜNG HỢP THÊM MỚI → ảnh bắt buộc
    $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Vui lòng chọn ảnh khi thêm sản phẩm mới.</div>";
    goto end_post_processing;
// Trường hợp SỬA mà không upload ảnh → KHÔNG làm gì, giữ nguyên ảnh cũ

    } else if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
        // Xử lý các lỗi upload khác
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi upload ảnh: Mã lỗi " . $_FILES['product_image']['error'] . "</div>"; // Thông báo lỗi upload chung.
        goto end_post_processing; // Dùng goto để nhảy qua phần xử lý DB.
    } 
    $upload_dir = __DIR__ . '/../images/products/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


 function uploadExtraImage($fileKey, $upload_dir) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg','jpeg','png','gif'];

        if (!in_array($file_ext, $allowed_exts)) return '';

        $new_name = time() . '_' . uniqid() . '.' . $file_ext;
        $dest = $upload_dir . '/' . $new_name;

        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
            return "images/products/" . $new_name;
        }
    }
    return '';
}

$newImage2 = uploadExtraImage('product_image2', $upload_dir);
$newImage3 = uploadExtraImage('product_image3', $upload_dir);
$newImage4 = uploadExtraImage('product_image4', $upload_dir);

// Ảnh phụ 1
if (!empty($newImage2)) {
    if (!empty($image2)) {
        $old = realpath(__DIR__ . '/../' . $image2);
        if ($old && file_exists($old)) unlink($old);
    }
    $image2 = $newImage2;
}

// Ảnh phụ 2
if (!empty($newImage3)) {
    if (!empty($image3)) {
        $old = realpath(__DIR__ . '/../' . $image3);
        if ($old && file_exists($old)) unlink($old);
    }
    $image3 = $newImage3;
}

// Ảnh phụ 3
if (!empty($newImage4)) {
    if (!empty($image4)) {
        $old = realpath(__DIR__ . '/../' . $image4);
        if ($old && file_exists($old)) unlink($old);
    }
    $image4 = $newImage4;
}

    //  Thực thi Query (UPDATE hoặc INSERT)
    if (!empty($product_id)) { // Nếu product_id có giá trị -> Chế độ UPDATE.
        // CHẾ ĐỘ UPDATE
        
        $sql = "UPDATE products SET name = :name, price = :price, quantity = :quantity, description = :description, image = :image_path, image2 = :image2,
            image3 = :image3,
            image4 = :image4 WHERE id = :id"; // Câu truy vấn UPDATE.
        $params = [ // Mảng các tham số cho UPDATE.
            ':id' => $product_id, // ID để xác định sản phẩm cần cập nhật.
            ':name' => $name, // Tên mới.
            ':price' => $price, // Giá mới.
            ':quantity' => $quantity, // Số lượng mới.
            ':description' => $description, // Mô tả mới.
            ':image_path' => $image_path_for_db, // Đường dẫn ảnh mới (hoặc cũ).
            ':image2' => $image2,
            ':image3' => $image3,
            ':image4' => $image4,

            ];
        $success_message = "<div style='color: green; text-align: center; font-weight: bold;'>Cập nhật sản phẩm ID: {$product_id} thành công!</div>"; // Thông báo thành công UPDATE.
        $error_message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không thể cập nhật sản phẩm. Chi tiết: "; // Thông báo lỗi UPDATE.
    } else { // Nếu product_id rỗng -> Chế độ CREATE (INSERT).
        // CHẾ ĐỘ CREATE (INSERT)
        $sql = "INSERT INTO products (name, price, quantity, description, image,image2, image3, image4) 
        VALUES (:name, :price, :quantity, :description, :image_path, :image2, :image3, :image4)"; // Câu truy vấn INSERT.
        $params = [ // Mảng các tham số cho INSERT.
            ':name' => $name, // Tên.
            ':price' => $price, // Giá.
            ':quantity' => $quantity, // Số lượng.
            ':description' => $description, // Mô tả.
            ':image_path' => $image_path_for_db, // Đường dẫn ảnh.
           ':image2' => $image2 ?: null,
':image3' => $image3 ?: null,
':image4' => $image4 ?: null,

        ];
        $success_message = "<div style='color: green; text-align: center; font-weight: bold;'>Thêm sản phẩm thành công!</div>"; // Thông báo thành công INSERT.
        $error_message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi: Không thể thêm sản phẩm. Chi tiết: "; // Thông báo lỗi INSERT.
    }

    try { // Thực thi truy vấn.
        $stmt = $pdo->prepare($sql); // Chuẩn bị câu truy vấn.
        $stmt->execute($params); // Thực thi.
        $message = $success_message; // Gán thông báo thành công.
        
        // Sau khi INSERT/UPDATE thành công, thoát khỏi chế độ sửa nếu đang sửa
        if (!empty($product_id)) { // Nếu là UPDATE.
              header("Location: products.php?message_update_success=1"); // Chuyển hướng để xóa tham số edit_id và tránh resubmit form.
              exit(); // Dừng script.
        }

    } catch (\PDOException $e) { // Bắt lỗi khi thực thi DB (nếu có).
        $message = $error_message . $e->getMessage() . "</div>"; // Hiển thị lỗi DB chi tiết.
        
        // Nếu Query lỗi, xóa file đã upload (để dọn dẹp)
        if ($is_file_uploaded) { // Nếu đã upload file thành công ở bước 5b.
            $uploaded_file = realpath(__DIR__ . '/../' . $image_path_for_db); // Lấy đường dẫn file đã upload.
            if (file_exists($uploaded_file)) { // Kiểm tra file.
        unlink($uploaded_file); // Xóa file để dọn dẹp server.
            }
        }
    }
}

end_post_processing: // Nhãn GOTO cho các trường hợp lỗi upload (để bỏ qua phần xử lý DB).
// Xử lý thông báo sau khi chuyển hướng
if (isset($_GET['message_update_success'])) { // Kiểm tra nếu có thông báo thành công từ chuyển hướng (sau UPDATE).
    $message = "<div style='color: green; text-align: center; font-weight: bold;'>Cập nhật sản phẩm thành công!</div>"; // Hiển thị thông báo thành công.
}

// =================================================================
// 6. LẤY DỮ LIỆU SẢN PHẨM HIỆN CÓ (READ)
// =================================================================
$products_result = $pdo->query("SELECT * FROM products ORDER BY id DESC"); // Truy vấn lấy tất cả sản phẩm, sắp xếp theo ID mới nhất.
$product_count = $products_result->rowCount(); // Đếm số lượng sản phẩm.
?>


<!DOCTYPE html> 
<html lang="vi">  <!--Bắt đầu thẻ HTML, ngôn ngữ tiếng Việt-->
<head> <!-- Bắt đầu phần đầu tài liệu.-->
    <meta charset="UTF-8"> <!--  Mã hóa ký tự UTF-8.-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!--  Cấu hình hiển thị responsive.-->
    <title>Quản Lý Sản Phẩm - ADMIN</title> <!--  Tiêu đề trang.-->
    <link rel="stylesheet" href="../css/style.css"> <!--  Nhúng CSS cơ bản.-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!--  Nhúng Font Awesome cho icon.-->
<style> /* Bắt đầu khối CSS nội tuyến.
      /* ===== BACKGROUND PASTEL  ===== */
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

/* ===== CONTAINER ===== */
.admin-container {
    max-width: 1250px;
    margin: 40px auto;
    padding: 35px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.08);
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
    margin: 25px 0;
}

.admin-menu a {
    background: #1a1a1a;
    color: white;
    padding: 10px 18px;
    margin-right: 10px;
    text-decoration: none;
    border-radius: 10px;
    transition: 0.3s ease;
    font-weight: 500;
}

.admin-menu a:hover,
.admin-menu a.active {
    background: linear-gradient(135deg, #ffcc00, #ffb700);
    color: #1a1a1a;
    transform: translateY(-2px);
}

/* ===== FORM ADD PRODUCT ===== */
.form-add-product {
    border: 2px solid #ffcc00;
    padding: 30px;
    margin-top: 30px;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.form-add-product h3 {
    margin-bottom: 20px;
}

.form-add-product input:not([type="file"]),
.form-add-product textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 14px;
    border-radius: 10px;
    border: 1px solid #ddd;
    transition: 0.2s;
}

.form-add-product input:focus,
.form-add-product textarea:focus {
    border-color: #ffcc00;
    outline: none;
}

/* ===== FILE INPUT ===== */
.form-add-product input[type="file"] {
    margin-bottom: 15px;
}

/* ===== BUTTON ===== */
.btn-login {
    background: linear-gradient(135deg, #1a1a1a, #333);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 12px;
    font-weight: bold;
    transition: 0.3s;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* ===== CANCEL BUTTON ===== */
.action-btn {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 14px;
    text-decoration: none;
    transition: 0.2s;
     display: inline-block;
margin-right: 8px;
}

.btn-edit {
    background: linear-gradient(135deg, #ffcc00, #ffb700);
    color: #1a1a1a !important;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.action-btn:hover {
    opacity: 0.85;
}
a {
    text-decoration: none;
}

/* ===== IMAGE PREVIEW ===== */
.current-image-preview {
    margin-top: 10px;
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 12px;
    background: #fff3d9;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

th {
    background: #1a1a1a;
    color: white;
    padding: 12px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.btn-submit {
    width: 100%;
    padding: 14px 0;
    background: linear-gradient(135deg, #ffcc00, #ffb700);
    border: none;
    border-radius: 14px;
    font-weight: 600;
    font-size: 16px;
    color: #1a1a1a;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
  
th, td {
    text-align: center;
}
tbody tr:hover {
    background-color: #f3e6c6;   /* màu vàng nhạt giống bảng trên */
    transition: 0.2s ease;
}


 </style> <!-- Kết thúc khối CSS nội tuyến.-->
</head>  <!--Kết thúc phần đầu.-->

<body> <!-- Bắt đầu phần thân.-->
    <main> <!--Thẻ chứa nội dung chính.-->
        <div class="admin-container"> <!--Khối chứa nội dung Admin.-->
            <div class="admin-header-info"> <!--Khối tiêu đề và thông tin Admin.-->
                <h2><i class="fas fa-tools"></i> TRANG QUẢN TRỊ SẢN PHẨM</h2> <!--Tiêu đề trang.-->
                <p>CHÀO MỪNG ADMIN: 
                    <span style="font-weight: bold; color: #1a1a1a;"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Quản trị viên'); ?></span>. <!-- Hiển thị tên Admin.-->
                    <a href="../logout.php" style="color: red;">ĐĂNG XUẤT</a> <!--Liên kết Đăng xuất.-->
                </p>
            </div> <!-- Kết thúc khối admin-header-info.-->
            
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
                        <i class="fas fa-home"></i> HOME </a>

            </div>
            
            
            <hr>  <!--Đường kẻ ngang.-->

            <?php echo $message; ?> <!--Hiển thị thông báo (từ quá trình xử lý POST/GET).-->

          <div class="form-add-product"> <!--Khối Form thêm/sửa sản phẩm.-->
    <h3><?php echo $is_edit_mode ? 'SỬA SẢN PHẨM ID: ' . htmlspecialchars($edit_product['id']) : 'Thêm Sản Phẩm Mới'; ?></h3> <!--Tiêu đề Form động.-->
    
    <form action="products.php" method="POST" enctype="multipart/form-data"> <!-- Form submit đến chính trang này, cần enctype cho upload file.-->
        <?php if ($is_edit_mode): ?> <!-- Nếu đang ở chế độ Sửa:-->
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($edit_product['id']); ?>"> <!-- Input ẩn: Lưu ID sản phẩm.-->
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_product['image']); ?>"> <!-- Input ẩn: Lưu đường dẫn ảnh cũ.-->
            <input type="hidden" name="current_image2" value="<?php echo htmlspecialchars($edit_product['image2']); ?>">
            <input type="hidden" name="current_image3" value="<?php echo htmlspecialchars($edit_product['image3']); ?>">
            <input type="hidden" name="current_image4" value="<?php echo htmlspecialchars($edit_product['image4']); ?>">

        <?php endif; ?> <!-- Kết thúc if.-->
        
        <input type="text" name="name" placeholder="Tên Sản phẩm" required 
            value="<?php echo htmlspecialchars($edit_product['name']); ?>"> <!-- Input Tên sản phẩm, điền sẵn giá trị cũ.-->
            
        <input type="number" name="price" placeholder="Giá (VND)" required 
            value="<?php echo htmlspecialchars($edit_product['price']); ?>"> <!-- Input Giá, điền sẵn giá trị cũ.-->
            
        <input type="number" name="quantity" placeholder="Số lượng" required 
            value="<?php echo htmlspecialchars($edit_product['quantity']); ?>"> <!-- Input Số lượng, điền sẵn giá trị cũ. -->
        
        <label for="product_image" style="display: block; margin-bottom: 5px; font-weight: bold;">
            Chọn Ảnh Sản phẩm <?php echo $is_edit_mode ? '(Để trống nếu không muốn thay đổi ảnh)' : ''; ?>: <!-- Label cho input file.-->
        </label>
        <input type="file" name="product_image" id="product_image" 
            <?php echo $is_edit_mode ? '' : 'required'; ?> 
            style="border: 1px solid #ddd; padding: 10px 8px; margin-bottom: 20px; width: 100%; box-sizing: border-box;"> <!-- Input file.-->
        <label>Ảnh Phụ 1:</label>
<input type="file" name="product_image2">

<label>Ảnh Phụ 2:</label>
<input type="file" name="product_image3">

<label>Ảnh Phụ 3:</label>
<input type="file" name="product_image4">

        <?php if ($is_edit_mode && !empty($edit_product['image'])): ?> <!-- Nếu đang Sửa và có ảnh cũ:-->
            <div class="current-image-preview"> <!-- Khối hiển thị ảnh cũ.-->
                <span style="font-weight: bold; margin-right: 10px;">Ảnh Hiện Tại:</span>
                <img src="../<?php echo htmlspecialchars($edit_product['image']); ?>" alt="Ảnh Sản phẩm hiện tại" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #ccc;"> <!-- Hiển thị ảnh cũ.-->
            </div>
        <?php endif; ?> <!-- Kết thúc if.-->
        
<textarea name="description" placeholder="Mô tả sản phẩm" rows="3"><?php echo htmlspecialchars($edit_product['description']); ?></textarea> <!-- Textarea Mô tả, điền sẵn giá trị cũ.-->
        
        <button type="submit" class="btn-submit">
        <?php echo $is_edit_mode 
            ? '<i class="fas fa-save"></i> Lưu Thay Đổi' 
            : '<i class="fas fa-plus"></i> Thêm Sản phẩm'; ?>
    </button>

        
        <?php if ($is_edit_mode): ?> <!-- Nếu đang ở chế độ Sửa:-->
            <a href="products.php" class="action-btn" style="background-color: #6c757d; color: white; margin-left: 10px;"> <!-- Nút Hủy bỏ chỉnh sửa.-->
                <i class="fas fa-times"></i> Hoàn Tác Chỉnh Sửa
            </a>
        <?php endif; ?> <!-- Kết thúc if.-->
    </form> <!-- Kết thúc Form.-->
</div> <!-- Kết thúc khối form-add-product.-->

            <h3>Danh Sách Sản Phẩm Hiện Có</h3> <!--Tiêu đề Danh sách sản phẩm.-->
            <table> <!--Bắt đầu bảng.-->
                <thead> <!-- Đầu bảng.-->
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên Sản phẩm</th>
                        <th>Giá</th>
                        <th>Hành động</th>
                    </tr>
                </thead> <!-- Kết thúc đầu bảng.-->
                <tbody><!-- Thân bảng.-->
                    <?php 
                    if ($product_count > 0): // Nếu có sản phẩm:
                        while($row = $products_result->fetch()): ?> <!--Bắt đầu vòng lặp hiển thị từng sản phẩm.-->
                            <tr>
                                <td><?php echo $row['id']; ?></td> <!-- Hiển thị ID.-->
                                <td><img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;"></td> <!-- Hiển thị ảnh.-->
                                <td><?php echo htmlspecialchars($row['name']); ?></td> <!-- Hiển thị Tên.-->
                                <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VND</td> <!-- Hiển thị Giá (đã định dạng tiền tệ).-->
                                <td>
                                    <a href="products.php?edit_id=<?php echo $row['id']; ?>" class="action-btn btn-edit"> <!-- Nút Sửa, truyền edit_id qua URL.-->
                                        <i class="fas fa-pen"></i> Sửa
                                    </a>
                                    
                                    <a href="products.php?delete_id=<?php echo $row['id']; ?>" 
                                       class="action-btn btn-delete" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm ID: <?php echo $row['id']; ?> này không? (Ảnh liên quan cũng sẽ bị xóa)');"> <!-- Xác nhận trước khi xóa.-->
                                       <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>  <!--Kết thúc vòng lặp--> 
                    <?php else: ?> <!-- Nếu không có sản phẩm nào:-->
                        <tr><td colspan="5">Chưa Có Sản Phẩm Nào.</td></tr> <!--Hiển thị thông báo không có sản phẩm.-->
                    <?php endif; ?> <!-- Kết thúc điều kiện.-->
                </tbody> <!-- Kết thúc thân bảng.-->
            </table> <!--Kết thúc bảng.-->
        </div> <!-- Kết thúc khối admin-container.-->
    </main> <!-- Kết thúc thẻ main.-->

</body> <!-- Kết thúc phần thân.-->
</html> <!-- Kết thúc tài liệu HTML.-->