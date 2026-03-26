-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 07:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_web_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`) VALUES
(1, 'admin', 'admin123', 'Quản trị viên'),
(2, 'admin1', 'admin123', 'Tô Doanh Quang Khu'),
(3, 'admin2', 'admin123', 'Trần Tấn Phát'),
(4, 'admin3', 'admin123', 'Nguyễn Khánh');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `code`, `name`, `status`) VALUES
(1, 'A001', 'Laptop AI', 1),
(2, 'A002', 'Laptop Mỏng nhẹ', 1),
(3, 'A003', 'Laptop Gaming', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_price` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `status`) VALUES
(1, 1, '2026-03-13 10:00:00', 25000000, 'pending'),
(2, 2, '2026-03-13 11:30:00', 1500000, 'shipping'),
(3, 3, '2026-03-12 15:20:00', 500000, 'delivered'),
(4, 1, '2026-03-11 20:10:00', 3000000, 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 25000000),
(2, 1, 2, 2, 500000),
(3, 2, 3, 1, 1500000),
(4, 3, 2, 1, 500000),
(5, 4, 3, 2, 1500000);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `cpu` varchar(255) NOT NULL,
  `ram` varchar(255) NOT NULL,
  `storage` varchar(255) NOT NULL,
  `gpu` varchar(255) NOT NULL,
  `screen` varchar(255) NOT NULL,
  `battery` varchar(255) NOT NULL,
  `weight` varchar(255) NOT NULL,
  `os` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `cost_price` int(11) DEFAULT 0,
  `profit_rate` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `category_id`, `cpu`, `ram`, `storage`, `gpu`, `screen`, `battery`, `weight`, `os`, `description`, `unit`, `quantity`, `cost_price`, `profit_rate`, `status`) VALUES
(1, 'Laptop 1', 1000000, '../img/sp1.jpg', 2, 'i5-1135G7', '8GB', '256GB SSD', 'GTX 1650', '15.6 inch', '45Wh', '1.8kg', 'Windows 10', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', '', 0, 0, 0, 1),
(2, 'Laptop 2', 11000000, '../img/sp2.jpg', 2, 'i5-11400H', '8GB', '512GB SSD', 'GTX 1650', '15.6 inch', '50Wh', '2.0kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(3, 'Laptop 3', 12000000, '../img/sp3.jpg', 2, 'i7-1165G7', '16GB', '512GB SSD', 'RTX 3050', '14 inch', '60Wh', '1.6kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(4, 'Laptop 4', 13000000, '../img/sp4.jpg', 2, 'i7-11800H', '16GB', '1TB SSD', 'RTX 3050', '15.6 inch', '70Wh', '2.2kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(5, 'Laptop 5', 14000000, '../img/sp5.jpg', 2, 'i9-11900H', '32GB', '1TB SSD', 'RTX 3060', '15.6 inch', '80Wh', '2.4kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(6, 'Laptop 6', 10500000, '../img/sp6.jpg', 2, 'Ryzen 5 4600H', '8GB', '256GB SSD', 'GTX 1650', '15.6 inch', '50Wh', '2.1kg', 'Windows 10', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(7, 'Laptop 7', 11500000, '../img/sp7.jpg', 2, 'Ryzen 5 5600H', '16GB', '512GB SSD', 'RTX 3050', '15.6 inch', '60Wh', '2.0kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(8, 'Laptop 8', 12500000, '../img/sp8.jpg', 2, 'Ryzen 7 5800H', '16GB', '512GB SSD', 'RTX 3060', '15.6 inch', '70Wh', '2.2kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(9, 'Laptop 9', 13500000, '../img/sp9.jpg', 2, 'Ryzen 9 5900HX', '32GB', '1TB SSD', 'RTX 3070', '15.6 inch', '80Wh', '2.3kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(10, 'Laptop 10', 14500000, '../img/sp10.jpg', 2, 'i5-12400H', '8GB', '512GB SSD', 'RTX 3050', '14 inch', '55Wh', '1.7kg', 'Windows 11', 'Laptop giá rẻ phù hợp sinh viên, học online và làm việc cơ bản.', NULL, 0, 0, 0, 1),
(11, 'Laptop 11', 15000000, '../img/sp11.jpg', 1, 'i7-12700H', '16GB', '1TB SSD', 'RTX 3060', '15.6 inch', '65Wh', '2.1kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(12, 'Laptop 12', 16000000, '../img/sp12.jpg', 1, 'i9-12900H', '32GB', '1TB SSD', 'RTX 3070', '16 inch', '75Wh', '2.5kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(13, 'Laptop 13', 17000000, '../img/sp13.jpg', 1, 'Ryzen 5 6600H', '16GB', '512GB SSD', 'RTX 3050', '14 inch', '60Wh', '1.8kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(14, 'Laptop 14', 18000000, '../img/sp14.jpg', 1, 'Ryzen 7 6800H', '16GB', '1TB SSD', 'RTX 3060', '15.6 inch', '70Wh', '2.2kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(15, 'Laptop 15', 19000000, '../img/sp15.jpg', 1, 'Ryzen 9 6900HX', '32GB', '1TB SSD', 'RTX 3070', '16 inch', '80Wh', '2.4kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(16, 'Laptop 16', 15500000, '../img/sp16.jpg', 1, 'i5-12500H', '8GB', '512GB SSD', 'RTX 3050', '15.6 inch', '55Wh', '2.0kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(17, 'Laptop 17', 16500000, '../img/sp17.jpg', 1, 'i7-12650H', '16GB', '512GB SSD', 'RTX 3060', '15.6 inch', '65Wh', '2.2kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(18, 'Laptop 18', 17500000, '../img/sp18.jpg', 1, 'i7-12700H', '16GB', '1TB SSD', 'RTX 3070', '16 inch', '75Wh', '2.3kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(19, 'Laptop 19', 18500000, '../img/sp19.jpg', 1, 'i9-12900HX', '32GB', '1TB SSD', 'RTX 3080', '17 inch', '90Wh', '2.8kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(20, 'Laptop 20', 19500000, '../img/sp20.jpg', 1, 'Ryzen 5 7535HS', '8GB', '512GB SSD', 'RTX 3050', '15.6 inch', '60Wh', '2.0kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(21, 'Laptop 21', 20000000, '../img/sp21.jpg', 1, 'Ryzen 7 7735HS', '16GB', '1TB SSD', 'RTX 3060', '15.6 inch', '70Wh', '2.2kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(22, 'Laptop 22', 21000000, '../img/sp22.jpg', 1, 'Ryzen 9 7940HS', '32GB', '1TB SSD', 'RTX 3070', '16 inch', '80Wh', '2.4kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(23, 'Laptop 23', 22000000, '../img/sp23.jpg', 1, 'i5-13420H', '8GB', '512GB SSD', 'RTX 3050', '14 inch', '55Wh', '1.7kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(24, 'Laptop 24', 23000000, '../img/sp24.jpg', 1, 'i7-13620H', '16GB', '1TB SSD', 'RTX 3060', '15.6 inch', '65Wh', '2.1kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(25, 'Laptop 25', 24000000, '../img/sp25.jpg', 1, 'i9-13900H', '32GB', '1TB SSD', 'RTX 3070', '16 inch', '75Wh', '2.3kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(26, 'Laptop 26', 20500000, '../img/sp26.jpg', 1, 'Ryzen 5 7640HS', '16GB', '512GB SSD', 'RTX 3050', '14 inch', '60Wh', '1.8kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(27, 'Laptop 27', 21500000, '../img/sp27.jpg', 1, 'Ryzen 7 7840HS', '16GB', '1TB SSD', 'RTX 3060', '15.6 inch', '70Wh', '2.2kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(28, 'Laptop 28', 22500000, '../img/sp28.jpg', 1, 'Ryzen 9 7945HX', '32GB', '1TB SSD', 'RTX 3080', '17 inch', '90Wh', '2.8kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(29, 'Laptop 29', 23500000, '../img/sp29.jpg', 1, 'i5-13500H', '8GB', '512GB SSD', 'RTX 3050', '14 inch', '55Wh', '1.7kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(30, 'Laptop 30', 24500000, '../img/sp30.jpg', 1, 'i7-13700H', '16GB', '1TB SSD', 'RTX 3060', '15.6 inch', '65Wh', '2.1kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(31, 'Laptop 31', 25000000, '../img/sp31.jpg', 3, 'i9-13980HX', '32GB', '1TB SSD', 'RTX 4080', '17 inch', '99Wh', '3.0kg', 'Windows 11', 'Laptop tầm trung, cân tốt học tập, lập trình và giải trí.', NULL, 0, 0, 0, 1),
(32, 'Laptop 32', 26000000, '../img/sp32.jpg', 3, 'Ryzen 7 7845HX', '16GB', '1TB SSD', 'RTX 4070', '16 inch', '80Wh', '2.5kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(33, 'Laptop 33', 27000000, '../img/sp33.jpg', 3, 'Ryzen 9 7950X', '32GB', '2TB SSD', 'RTX 4090', '17 inch', '99Wh', '3.2kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(34, 'Laptop 34', 28000000, '../img/sp34.jpg', 3, 'i7-13700HX', '16GB', '1TB SSD', 'RTX 4070', '16 inch', '85Wh', '2.6kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(35, 'Laptop 35', 29000000, '../img/sp35.jpg', 3, 'i9-13950HX', '32GB', '2TB SSD', 'RTX 4080', '17 inch', '99Wh', '3.0kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(36, 'Laptop 36', 30000000, '../img/sp36.jpg', 3, 'Ryzen 5 5500U', '8GB', '256GB SSD', 'Integrated', '14 inch', '45Wh', '1.5kg', 'Windows 10', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(37, 'Laptop 37', 31000000, '../img/sp37.jpg', 3, 'Ryzen 7 5700U', '16GB', '512GB SSD', 'Integrated', '14 inch', '50Wh', '1.6kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(38, 'Laptop 38', 32000000, '../img/sp38.jpg', 3, 'i5-11300H', '8GB', '512GB SSD', 'GTX 1650', '15.6 inch', '55Wh', '1.9kg', 'Windows 10', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(39, 'Laptop 39', 33000000, '../img/sp39.jpg', 3, 'i7-11600H', '16GB', '1TB SSD', 'RTX 3050', '15.6 inch', '65Wh', '2.1kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1),
(40, 'Laptop 40', 34000000, '../img/sp40.jpg', 3, 'i9-11980HK', '32GB', '1TB SSD', 'RTX 3080', '17 inch', '90Wh', '2.9kg', 'Windows 11', 'Laptop cao cấp, hiệu năng mạnh mẽ, phù hợp gaming và công việc chuyên sâu.', NULL, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `ho` varchar(100) DEFAULT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `diachi` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ho`, `ten`, `sdt`, `diachi`, `email`, `password`, `status`) VALUES
(1, 'Trần', 'Tấn Phát', '0123456789', 'TP Hồ Chí Minh', 'phat@gmail.com', '$2y$10$Mx2Ez2rHIGfz0Y.5bsA9eOsOg0Qjar03GnYU54XquuqubeSUhoGHy', 'active'),
(2, 'Nguyễn', 'Văn A ', '0123456789', 'Hà Nội', 'vana@gmail.com', '123456', 'active'),
(3, 'Lê', 'Thị B', '0912345678', 'Đà Nẵng', 'thib@gmail.com', '123456', 'active'),
(4, 'Phạm', 'Văn C', '0909090909', 'Cần Thơ', 'vanc@gmail.com', '123456', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
