-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 08:50 AM
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
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `ma_don` varchar(20) DEFAULT NULL,
  `total_price` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `ma_don`, `total_price`, `status`, `payment_method`, `address`) VALUES
(1, 1, '2026-03-13 10:00:00', NULL, 25000000, 'pending', NULL, NULL),
(2, 2, '2026-03-13 11:30:00', NULL, 1500000, 'shipping', NULL, NULL),
(3, 3, '2026-03-12 15:20:00', NULL, 500000, 'delivered', NULL, NULL),
(4, 1, '2026-03-11 20:10:00', NULL, 3000000, 'cancelled', NULL, NULL),
(0, 2, '2026-03-25 12:10:04', NULL, 161000000, NULL, 'cod', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 12:10:32', NULL, 132000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 12:10:56', NULL, 52000000, NULL, 'cod', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 12:11:38', NULL, 25000000, NULL, 'cod', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 12:12:08', NULL, 15000000, NULL, 'bank', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 12:12:37', NULL, 52000000, NULL, 'cod', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 13:23:18', NULL, 31000000, NULL, 'bank', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 13:23:52', NULL, 33000000, NULL, 'cod', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 13:25:02', NULL, 11000000, NULL, 'cod', '351/7 Lê Quang Sung'),
(0, 2, '2026-03-25 13:32:55', 'DH1774420375', 13000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 13:33:13', 'DH1774420393', 11000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 13:35:19', 'DH1774420519', 13000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 13:37:04', 'DH1774420624', 12000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 13:37:47', 'DH1774420667', 11000000, NULL, 'cod', '123'),
(0, 2, '2026-03-25 13:38:35', 'DH1774420715', 20000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 13:43:56', 'DH1774421036', 13000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 13:51:20', 'DH1774421480', 12000000, NULL, 'bank', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 14:16:05', 'DH1774422965', 22000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 14:17:03', 'DH1774423023', 10000000, NULL, 'bank', 'sdsdsd'),
(0, 2, '2026-03-25 14:18:59', 'DH1774423139', 13000000, NULL, 'cod', '192 Phạm Đức Sơn '),
(0, 2, '2026-03-25 14:19:52', 'DH1774423192', 13000000, NULL, 'cod', 'édfsdfs'),
(0, 2, '2026-03-25 14:26:47', 'DH1774423607', 10000000, NULL, 'cod', '211 ádd'),
(0, 2, '2026-03-25 14:30:41', 'DH1774423841', 10000000, NULL, 'cod', '123'),
(0, 2, '2026-03-25 14:33:40', 'DH1774424020', 30000000, NULL, 'cod', '345'),
(0, 2, '2026-03-25 14:34:08', 'DH1774424048', 10000000, NULL, 'cod', '234'),
(0, 1, '2026-03-25 14:35:32', 'DH1774424132', 40000000, NULL, 'cod', '122'),
(0, 2, '2026-03-25 14:36:24', 'DH1774424184', 10000000, NULL, 'cod', '1234'),
(0, 2, '2026-03-25 14:37:02', 'DH1774424222', 10000000, NULL, 'cod', '123'),
(0, 1, '2026-03-25 14:37:42', 'DH1774424262', 34000000, NULL, 'cod', '123'),
(0, 1, '2026-03-25 14:41:01', 'DH1774424461', 13000000, NULL, 'cod', 'TP Hồ Chí Minh'),
(0, 1, '2026-03-25 14:43:45', 'DH1774424625', 11000000, NULL, 'cod', '123'),
(0, 1, '2026-03-25 14:44:09', 'DH1774424649', 10000000, NULL, 'cod', 'TP Hồ Chí Minh');

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
(5, 4, 3, 2, 1500000),
(0, 0, 1, 4, 10000000),
(0, 0, 2, 11, 11000000),
(0, 0, 3, 11, 12000000),
(0, 0, 4, 4, 13000000),
(0, 0, 8, 2, 12500000),
(0, 0, 11, 1, 15000000),
(0, 0, 32, 2, 26000000),
(0, 0, 37, 1, 31000000),
(0, 0, 2, 3, 11000000),
(0, 0, 2, 1, 11000000),
(0, 0, 4, 1, 13000000),
(0, 0, 2, 1, 11000000),
(0, 0, 4, 1, 13000000),
(0, 0, 3, 1, 12000000),
(0, 0, 2, 1, 11000000),
(0, 0, 1, 2, 10000000),
(0, 0, 4, 1, 13000000),
(0, 0, 3, 1, 12000000),
(0, 0, 2, 2, 11000000),
(0, 0, 1, 1, 10000000),
(0, 0, 4, 1, 13000000),
(0, 0, 4, 1, 13000000),
(0, 0, 1, 1, 10000000),
(0, 0, 1, 1, 10000000),
(0, 0, 1, 3, 10000000),
(0, 0, 1, 1, 10000000),
(0, 0, 1, 4, 10000000),
(0, 0, 1, 1, 10000000),
(0, 0, 1, 1, 10000000),
(0, 0, 2, 2, 11000000),
(0, 0, 3, 1, 12000000),
(0, 0, 4, 1, 13000000),
(0, 0, 2, 1, 11000000),
(0, 0, 1, 1, 10000000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `ho` varchar(50) DEFAULT NULL,
  `ten` varchar(50) DEFAULT NULL,
  `sdt` varchar(15) DEFAULT NULL,
  `diachi` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ho`, `ten`, `sdt`, `diachi`, `email`, `password`) VALUES
(1, 'Trần', 'Phát đẹp trai', '0123456789', 'TP Hồ Chí Minh', 'phat@gmail.com', '123456'),
(2, 'Nguyễn', 'Văn D', '0987654321', 'TP ha noi', 'vana@gmail.com', '123456'),
(3, 'Lê', 'Thị B', '0912345678', 'Đà Nẵng', 'thib@gmail.com', '123456'),
(4, 'Phạm', 'Văn C', '0909090909', 'Cần Thơ', 'vanc@gmail.com', '123456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
