-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 04:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flowershop`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sender` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_name`, `message`, `created_at`, `sender`) VALUES
(1, 'Khách', 'shop còn hoa cẩm túc cầu không?', '2026-04-05 11:02:32', 'user'),
(2, NULL, 'shop ơi', '2026-04-05 11:14:50', 'user'),
(3, NULL, 'cho e hỏi', '2026-04-05 11:18:37', 'user'),
(4, 'Admin', 'mình còn ạ', '2026-04-05 11:19:01', 'admin'),
(5, NULL, 'Hi Baotrancute:))', '2026-04-07 14:18:30', 'user'),
(6, NULL, 'dạ mình xin chào ạ', '2026-04-07 14:43:59', 'user'),
(7, NULL, 'mình cần hỗ trợ về sản phẩm bông bạn tư vấn giúp mình bông với', '2026-04-07 14:44:20', 'user'),
(8, NULL, 'Cho tôi gặp Ngô Bảo Trân', '2026-04-07 14:50:41', 'user'),
(9, NULL, 'Phản hồi chậm quá shốp ơi', '2026-04-07 14:51:48', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `delivery_time` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Mới'
) ;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `shipping_fee`, `total_amount`, `order_date`, `delivery_time`, `status`) VALUES
(12, NULL, 'Ngo Bao Tran', 'baotranst1510@gmail.com', '0122345789', 'kjn', 30000.00, 830000.00, '2025-12-04 21:32:33', NULL, 'Mới'),
(13, NULL, 'Ngo Bao Tran', 'baotranst1510@gmail.com', '0123456789', 'cần thơ', 30000.00, 830000.00, '2025-12-04 21:39:20', NULL, 'Mới'),
(14, NULL, 'Ngo Bao Tran', 'ngbachau139@gmail.com', '0123456789', 'cần thơ', 30000.00, 590000.00, '2025-12-04 21:44:52', NULL, 'Mới'),
(15, NULL, 'bơ', 'admin@example.com', '0123456789', 'cần thơ', 30000.00, 1530000.00, '2025-12-04 22:01:07', NULL, 'Mới'),
(17, NULL, 'bảo châu', 'admin@example.com', '0337653567', 'soc trang', 30000.00, 830000.00, '2025-12-10 19:22:42', NULL, 'Đã hủy'),
(18, NULL, 'Ngo Bao Tran', 'tr@gmail.com', '0123456789', 'cần thơ', 30000.00, 480000.00, '2025-12-11 21:35:59', NULL, 'Đã hủy'),
(19, NULL, 'Ngô Bảo Trân', 'baotranst1510@gmail.com', '0999988888', 'Vĩnh Châu- Sóc Trăng', 30000.00, 2730000.00, '2025-12-13 17:32:59', NULL, 'Mới'),
(20, NULL, 'Ngô Bảo Trân', 'baotranst1510@gmail.com', '0999988888', 'Vĩnh Châu- Sóc Trăng', 30000.00, 2730000.00, '2025-12-13 17:34:07', NULL, 'Mới'),
(22, NULL, 'Ngo Bao Tran', 'ngbachau139@gmail.com', '0123456789', 'cần thơ', 30000.00, 2430000.00, '2025-12-15 21:28:31', NULL, 'Mới'),
(23, NULL, 'Bảo Trân', 'baotranst1510@gmail.com', '0999988888', 'vĩnh chaau', 30000.00, 1430000.00, '2026-01-23 17:31:21', NULL, 'Mới'),
(26, NULL, 'Ngo Bao Tran DC22V7N533', 'trandc22v7n533@vlvh.ctu.edu.vn', 'sscc', 'dcsw', 30000.00, 880000.00, '2026-03-22 10:32:56', NULL, 'Mới'),
(31, 15, 'Bảo Trân', 'baotranst1510@gmail.com', '0122345789', 'khánh hòa- vĩnh châu- sóc trăng- cần thơ', 30000.00, 2480000.00, '2026-04-07 19:57:47', NULL, 'Đã hủy'),
(32, 15, 'Bảo Trân', 'baotranst1510@gmail.com', '0788964983', 'ninh kiều- cần thơ', 30000.00, 2630000.00, '2026-04-07 20:13:35', NULL, 'Đã hủy'),
(33, 20, 'Bảo châu', 'baotranst5678@gmail.com', '0867493424', 'NINH KIỀU- CẦN THƠ- HẺM 51', 30000.00, 1730000.00, '2026-04-07 21:26:31', '2026-04-10 16:26:31', 'Đã hủy'),
(34, 21, 'user 1', 'user1@gmail.com', '0912344581', 'sjhfjhfhjfjsd', 30000.00, 880000.00, '2026-04-07 21:27:55', '2026-04-10 16:27:55', 'Đã hủy'),
(35, 15, 'Ngo Bao Tran ', 'trandc22v7n533@vlvh.ctu.edu.vn', '0337558678', 'tp hcm ', 30000.00, 880000.00, '2026-04-07 21:34:35', '2026-04-10 16:34:35', 'Đã hủy'),
(36, 22, 'Quan Trí Trung', 'tritrungquan000@gmail.com', '0937810317', 'cầu cồn khương , đường nguyễn hữu cảnh , số nhà biệt thự 16 ', 30000.00, 880000.00, '2026-04-07 21:42:40', '2026-04-10 16:42:40', 'Đã hủy'),
(37, 22, 'Quan Trí Trung', 'tritrungquan000@gmail.com', '0937810317', 'hẻm 175 nguyễn văn cừ , an hòa , ninh kiều , cần thơ ', 30000.00, 8430000.00, '2026-04-07 21:46:05', '2026-04-10 16:46:05', 'Mới');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(15, 12, 9, 1, 800000.00),
(16, 13, 9, 1, 800000.00),
(17, 14, 2, 1, 560000.00),
(18, 15, 9, 1, 800000.00),
(34, 26, 12, 1, 850000.00),
(41, 31, 12, 1, 850000.00),
(42, 31, 11, 1, 650000.00),
(43, 31, 20, 1, 950000.00),
(44, 32, 12, 1, 850000.00),
(45, 32, 20, 1, 950000.00),
(46, 32, 9, 1, 800000.00),
(47, 33, 12, 2, 850000.00),
(48, 34, 12, 1, 850000.00),
(49, 35, 12, 1, 850000.00),
(50, 36, 12, 1, 850000.00),
(51, 37, 2, 15, 560000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `image4` varchar(255) DEFAULT NULL
) ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `description`, `image`, `image2`, `image3`, `image4`) VALUES
(1, 'HOA TULIP HỒNG', 850000.00, 8, 'Hoa tulip – Biểu tượng của tình yêu. Được miêu tả như giọt sương đầu ngày, bó hoa gợi nên cảm giác thuần khiết, trong veo – tượng trưng cho tình yêu nhẹ nhàng, vững bền và sâu sắc từ những điều giản dị nhất.\r\nHoa tulip thường được ưa chuộng dùng trong các như:\r\n-Dịp cưới hỏi, bó hoa cưới xinh xắn rất trang trọng thích hợp cho những ngày trọng đại của mỗi cô gái.\r\nHoặc có thể dành tặng:\r\nSinh nhật, kỷ niệm, hoặc những dịp đặc biệt với người thương.\r\nLời tỏ tình tinh tế và lãng mạn.', 'images/products/1770783302_698c02461b39f.jpg', 'images/products/1770783302_698c02461c7ae.jpg', 'images/products/1770783302_698c02461cec1.jpg', 'images/products/1770783302_698c02461d428.jpg'),
(2, 'HOA TỬ LAN TÍM', 560000.00, 6, 'Với màu sắc tím nổi bật. Thu hút từ cái nhìn đầu tiên nhờ vào tổng thể dịu dàng và tinh tế. Hoa lan hồ điệp màu tím bung nở như những cánh bướm mỏng manh, mang đến cảm giác thư thái, nữ tính và lãng mạn. Lan hồ điệp thể hiện sự trân trọng, lòng biết ơn và cả niềm tự hào. Không rực rỡ hay quá nhẹ nhàng, sắc tím hồng mang lại cảm giác trưởng thành, quyết đoán và thanh lịch. \r\nSự xuất hiện của lan hồ điệp lớn trong những sự kiện như tân gia hay khai trương luôn mang thông điệp tốt lành: may mắn, phát đạt và vững bền.\r\nPhù hợp cho các dịp tân gia, khai trương. Món quà tặng nhân dịp kỷ niệm 8/3, 20/10', 'images/products/1770744957_698b6c7d95aa8.jpg', 'images/products/1770783120_698c0190107aa.jpg', 'images/products/1770783120_698c019010f55.jpg', 'images/products/1770783120_698c019011656.jpg'),
(4, 'HOA HỒNG LẠC THẦN', 650000.00, 7, 'Nếu tulip là biểu tượng của tình yêu thuần khiết thì hoa hồng lạc thần lại tượng trưng cho sự đam mê và cuốn hút. Thì Hoa hồng lại mang dáng vẻ thanh tao, tựa như lời hứa về sự thủy chung, như cỏ dại như một chút tự nhiên được cài vào giữa đô thị tấp nập – bình dị nhưng đầy sức sống.\r\nLà món quà dịu dàng dành cho những tâm hồn đang cần một chút yêu thương, một lời nhắc nhở nhẹ nhàng: “Cuộc sống vẫn đẹp, vẫn đáng yêu như chính màu hoa này.” Sắc hồng thanh thoát, không quá rực rỡ, không quá trầm lặng – vừa đủ để khiến trái tim ai đó dịu lại và mỉm cười. \r\nHoa được bó tinh tế, phối giấy nhẹ nhàng, phù hợp để tặng bạn bè, người yêu, đồng nghiệp hay chính bạn – trong những ngày cần động lực để sống vui hơn.', 'images/products/1770744230_698b69a696f1e.jpg', 'images/products/1770782913_698c00c1f07e2.jpg', 'images/products/1770782913_698c00c1f0cb4.jpg', 'images/products/1770782913_698c00c1f11f9.jpg'),
(8, 'HOA HƯỚNG DƯƠNG', 450000.00, 4, 'Từ khi ra hoa, hướng dương đã luôn hướng về mặt trời, đón nhận sự ấm áp và ấm ủ một tia nắng của riêng mình. Cho dù bây giờ và mai sau đều như vậy. Mỗi bông hoa nở là một Mặt trời bé con. Hướng dương là lời động viên và chúc mừng, tràn đầy niềm hy vọng vào tương lai.\r\nHướng dương thường được tặng vào các dịp lễ tốt nghiệp – Động viên người nhận luôn tiến về phía trước với khát vọng và đam mê, hoặc tặng người thân, bạn bè – Như một lời chúc lạc quan, may mắn và yêu đời.', 'images/products/1770782749_698c001dc3bad.jpg', 'images/products/1770782749_698c001dc53e5.jpg', 'images/products/1770782749_698c001dc5a82.jpg', 'images/products/1770782749_698c001dc6136.jpg'),
(9, 'HOA TÚC CẦU', 800000.00, 5, 'Sắc trắng làm chủ đạo pha lẫn tí hồng phấn nhẹ nhàng, không phô trương nhưng giữ ánh nhìn rất lâu. Hoa túc cầu tượng trưng cho sự tinh khiết và tôn trọng; gửi gắm sự thanh lịch; đồng tiền trắng mang nét vui tươi. \r\nLời chào thanh lịch dành cho những ai yêu phong cách tối giản nhưng không tối nghĩa. Sắc trắng dịu của túc cầu nổi bật trên nền xanh của lá, nhấn bằng dãi màu hài hòa. Tất cả tạo nên một tổng thể nhẹ nhàng, trang nhã và rất thời trang ngay từ khoảnh khắc đầu nhìn thấy.\r\nTúc cầu không chỉ là món quà dành tặng trong các dịp đặc biệt mà còn là biểu tượng của tình yêu chân thành, lãng mạn và ngọt ngào.', 'images/products/1770782542_698bff4eacc31.jpg', 'images/products/1770782542_698bff4ead7b4.jpg', 'images/products/1770782542_698bff4eae037.jpg', 'images/products/1770782542_698bff4eae6fb.jpg'),
(10, 'HOA LAVENDER', 700000.00, 7, 'Được thiết kế gồm hoa Lavender với màu tím lãng mạn kết hợp cùng cỏ đồng tiền. Lấy sắc xanh làm tâm điểm, sáng bừng ngay khi người nhận mở bó. Mỗi nụ lavender được đặt cao độ khác nhau để tạo nhịp chuyển động tự nhiên, tựa một khung hình đang nở dần.Làm lớp đệm dịu mắt, giúp tổng thể cân bằng và tinh tế.\r\nNếu bạn đang tìm kiếm một bó hoa sinh nhật nho nhỏ, xinh xắn để tặng người yêu, người thân hay bạn bè, đồng nghiệp, thì hoa Lavender là lựa chọn hoàn toàn phù hợp.', 'images/products/1770744863_698b6c1f413da.jpg', 'images/products/1770782285_698bfe4d2e0ee.jpg', 'images/products/1770782285_698bfe4d2e7af.jpg', 'images/products/1770782285_698bfe4d2ee37.jpg'),
(11, 'HOA SEN TRẮNG', 650000.00, 4, 'Hoa sen trắng từ lâu đã trở thành biểu tượng của sự thanh khiết, tinh tế và thanh cao.\r\n- Trong phong thủy, hoa sen trắng biểu tượng cho sự thanh lọc và cân bằng \r\n- Trong văn hóa Việt Nam, hoa sen trắng còn đại diện cho sự đức hạnh, lòng nhân ái và vẻ đẹp tâm hồn của người phụ nữ.\r\n- Biểu tượng tình yêu: Hoa sen trắng còn có thể tượng trưng cho tình yêu chân thành và hạnh phúc trong hôn nhân\r\nĐây là món quà sang trọng và ý nghĩa để tập thể lớp gửi tặng thầy cô giáo nhân dịp 20/11, hoặc dành tặng mẹ trong những ngày đặc biệt như Lễ Vu Lan, Ngày của Mẹ, 8/3 hay 20/10,.', 'images/products/1770744797_698b6bdd005e8.png', 'images/products/1770781967_698bfd0f82ea2.jpg', 'images/products/1770781967_698bfd0f8352b.jpg', 'images/products/1770781967_698bfd0f83abb.jpg'),
(12, 'HOA HỒNG VÀNG', 850000.00, 4, 'Được mệnh danh là thỏi vàng của nhà hoa \"hồng\", khác với vẻ thanh tao của hồng trắng, quyền lực của hồng đỏ. Màu từ vàng tươi rực rỡ đến vàng kem tinh tế. Loài hoa này có hương thơm nhẹ nhàng và biểu tượng đa dạng, thích hợp cắm xen kẻ cùng hoa mộc lan trắng.\r\nLà biểu tượng truyền thống của tình bạn, sự quan tâm và ủng hộ.\r\nNiềm tin và hy vọng: Tượng trưng cho niềm tin vào một tương lai tốt đẹp, lạc quan và động lực.\r\nThích hợp cho những cuộc hẹn biểu trưng cho tình bạn, song nó vẫn còn ý nghĩa khác là tượng trưng cho sự phản bội trong tình yêu, đây cũng là điều tạo nên sự khác biệt của hoa hồng vàng', 'images/products/1770744349_698b6a1d27370.jpg', 'images/products/1770781706_698bfc0a75269.jpg', 'images/products/1770781706_698bfc0a75748.jpg', 'images/products/1770781706_698bfc0a75b77.jpg'),
(20, 'HOA TULIP TRẮNG', 950000.00, 10, 'Hoa tulip trắng mang vẻ đẹp tinh khôi, thanh lịch và thuần khiết.Màu trắng của tulip gợi lên cảm giác trong trẻo, nhẹ nhàng và yên bình.\r\nNhững cánh hoa mềm mại, cân đối tạo nên vẻ sang trọng nhưng không phô trương.\r\nTulip trắng thường được xem là biểu tượng của sự chân thành và khởi đầu mới.\r\nLoài hoa này còn mang ý nghĩa tha thứ, hàn gắn và xoa dịu cảm xúc.\r\nVẻ đẹp giản dị của tulip trắng thể hiện tình yêu trong sáng và sự tôn trọng sâu sắc.\r\nNó phù hợp với những mối quan hệ bền vững, không ồn ào nhưng đầy bền chặt.\r\nTulip trắng rất thích hợp để tặng vào dịp xin lỗi, hòa giải hoặc bày tỏ lòng biết ơn.\r\nNgoài ra, đây cũng là món quà ý nghĩa trong đám cưới, lễ kỷ niệm hoặc chúc mừng khởi đầu mới.', 'images/products/1770744304_698b69f01a38b.jpg', 'images/products/1770781558_698bfb76405ce.jpg', 'images/products/1770781558_698bfb7640ae4.jpg', 'images/products/1770781558_698bfb7640f94.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 11, NULL, 5, 'HOA RẤT ĐẸP RẤT ĐÁNG MUA', '2026-04-06 11:30:55'),
(2, 11, NULL, 5, 'GIAO HÀNG NHANH, HOA TƯƠI', '2026-04-06 11:31:09'),
(3, 20, NULL, 4, 'GIAO HÀNG NHANH CHÓNG', '2026-04-06 11:32:54'),
(4, 20, NULL, 5, 'HOA TƯƠI ĐÚNG NHƯ MÔ TẢ', '2026-04-06 11:33:05'),
(5, 20, NULL, 5, 'bhdcgjsbcu, ', '2026-04-06 11:33:12'),
(6, 12, NULL, 5, 'tặng cho mẹ mẹ mình rất thích', '2026-04-06 11:33:31'),
(7, 12, NULL, 5, 'recoment hoa này đi date', '2026-04-06 11:33:42'),
(8, 12, NULL, 1, 'giao hơn 3 tiếng mới có', '2026-04-06 11:33:56'),
(9, 9, NULL, 5, 'túc cầu mùa này k đẹp nhưng shop ship nhanh ok', '2026-04-06 11:35:13'),
(10, 20, NULL, 5, 'sản phẩm đáng mua', '2026-04-07 14:23:40'),
(11, 20, NULL, 5, 'Chủ shop cute:)))\r\n', '2026-04-07 14:24:11'),
(12, 10, NULL, 5, 'hoa đẹp lắm', '2026-04-08 12:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `theme_settings`
--

CREATE TABLE `theme_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theme_settings`
--

INSERT INTO `theme_settings` (`setting_key`, `setting_value`) VALUES
('banner_abc_enabled', '1'),
('banner_a_path', 'images/theme_693e5a7783d4d.png'),
('banner_b_path', 'images/theme_693e597e38011.png'),
('banner_c_path', 'images/theme_693e5a49440f4.png'),
('footer_bg_color', '#4e4356'),
('footer_text_color', '#f3ecec'),
('footer_text_content', '© 2025 Flower Shop. Powered by PHP/MISU'),
('header_bg_color', '#6a5d74'),
('header_height', '100px'),
('header_padding', '0 50px'),
('header_text_color', 'white'),
('hero_headline', 'Món Quà Từ Thiên Nhiên, Trao Trọn Yêu Thương.'),
('hero_image_path', 'images/theme_69d3a9182dab5.png'),
('hero_subtext', 'Tuyển chọn những bó hoa tươi thắm nhất cho mọi dịp.'),
('hero_text_color', '#ffffff'),
('hero_title_color', '#ffffff'),
('logo_path', 'images/theme_698c2e79c3bb1.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `is_admin`, `created_at`) VALUES
(12, 'Thu Ngân', 'ThuNganCaSang@gmail.com', '$2y$10$GmOBY9mb/NQYEFNQ7gwoo.bsHPCPw7dcl7OXOvI1TJdFJ2plfM49G', 1, '2025-12-15 15:48:09'),
(14, 'mi su', 'admin@example.com', '$2y$10$v.L8TY2F3wzhwnQ22qgShu2FBlbu96kvoWoqA7KKnfxMXgs6Yw4Wm', 0, '2025-12-16 16:25:05'),
(15, 'bơ', 'baotranst1510@gmail.com', '$2y$10$8G3OIfR.CGY6.eTo6dWpbew6Ucuu3UDlzJBQmmYcUZ7xV9WL.ornO', 0, '2025-12-16 16:29:10'),
(17, 'bảo trân', 'baotranadmin@gmail.com', '$2y$10$kb/t/RyVcpHW6qcEkJV13O9Fxah4nwr/uNK4CJZ0fL93WAnXtsNum', 1, '2025-12-24 14:38:17'),
(20, 'bảo trân', 'baotranst5678@gmail.com', '$2y$10$AawFdtG/mq9Qi.IN5yGNmeKKBOT5Uaru/vqj0uxgWGqCxAVMdYINa', 0, '2026-04-07 14:25:22'),
(21, 'User 1', 'user1@gmail.com', '$2y$10$E2J.Kkf41GGd5Eg1EXOyjOQKkdriWBva2.7kaNlzL.h.jkW4WvId.', 0, '2026-04-07 14:26:57'),
(22, 'người không tên', 'tritrungquan000@gmail.com', '$2y$10$UYRkEK4qiGgP//oCMua9U.A7Xpoq0QYgVxJ1/JTovTs2WVuYDFqWG', 0, '2026-04-07 14:41:51'),
(23, 'Khánh Duy Đẹp Trai Siêu Cấp Vũ Trụ', 'khanhduy10092k3@gmail.com', '$2y$10$qQiCQIOx9tBd5KK4f3cJ9.RGMY6Yj/t9rNikqLei2c4/VCwLx0GZG', 0, '2026-04-07 14:49:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_products` (`product_id`),
  ADD KEY `fk_reviews_users` (`user_id`);

--
-- Indexes for table `theme_settings`
--
ALTER TABLE `theme_settings`
  ADD PRIMARY KEY (`setting_key`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
