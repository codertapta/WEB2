-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Mar 24, 2026 at 04:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30
=======
-- Generation Time: Mar 13, 2026 at 04:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
>>>>>>> origin/main

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
<<<<<<< HEAD
-- Database: `project_web_data`
=======
-- Database: `project web data`
>>>>>>> origin/main
--

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(30, 1, 1, 4),
(31, 1, 2, 2),
(32, 1, 3, 1),
(33, 1, 4, 1);

-- --------------------------------------------------------

--
=======
>>>>>>> origin/main
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
<<<<<<< HEAD
  `order_date` datetime DEFAULT current_timestamp(),
  `total_price` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cod'
=======
  `order_date` datetime DEFAULT NULL,
  `total_price` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
>>>>>>> origin/main
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

<<<<<<< HEAD
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `status`, `address`, `payment_method`) VALUES
(1, 1, '2026-03-17 15:25:25', 0, 'pending', 'TP Hồ Chí Minh', 'cod'),
(2, 1, '2026-03-17 15:25:29', 0, 'pending', 'TP Hồ Chí Minh', 'cod'),
(3, 1, '2026-03-18 17:02:38', 34500000, NULL, 'TP Hồ Chí Minh', 'cod'),
(4, 1, '2026-03-18 17:03:46', 34500000, NULL, 'TP Hồ Chí Minh', 'cod'),
(5, 1, '2026-03-18 17:04:23', 18000000, NULL, 'TP Hồ Chí Minh', 'cod'),
(6, 1, '2026-03-18 17:04:50', 51500000, NULL, 'TP Hồ Chí Minh', 'cod'),
(7, 1, '2026-03-18 17:06:53', 67500000, NULL, 'TP Hồ Chí Minh', 'cod'),
(8, 1, '2026-03-18 17:10:41', 46900000, NULL, 'TP Hồ Chí Minh', 'cod'),
(9, 1, '2026-03-18 17:11:43', 15200000, NULL, 'TP Hồ Chí Minh', 'cod'),
(10, 2, '2026-03-18 18:00:53', 18000000, NULL, 'TP.vũng tàu', 'cod');
=======
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `status`) VALUES
(1, 1, '2026-03-13 10:00:00', 25000000, 'pending'),
(2, 2, '2026-03-13 11:30:00', 1500000, 'shipping'),
(3, 3, '2026-03-12 15:20:00', 500000, 'delivered'),
(4, 1, '2026-03-11 20:10:00', 3000000, 'cancelled');
>>>>>>> origin/main

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
<<<<<<< HEAD
(14, 3, 1, 1, 18000000),
(15, 3, 2, 1, 16500000),
(16, 4, 1, 1, 18000000),
(17, 4, 2, 1, 16500000),
(18, 5, 1, 1, 18000000),
(19, 6, 2, 1, 16500000),
(20, 6, 3, 1, 15200000),
(21, 6, 4, 1, 19800000),
(22, 7, 1, 1, 18000000),
(23, 7, 2, 3, 16500000),
(24, 8, 2, 1, 16500000),
(25, 8, 3, 2, 15200000),
(26, 9, 3, 1, 15200000),
(27, 10, 1, 1, 18000000);
=======
(1, 1, 1, 1, 25000000),
(2, 1, 2, 2, 500000),
(3, 2, 3, 1, 1500000),
(4, 3, 2, 1, 500000),
(5, 4, 3, 2, 1500000);
>>>>>>> origin/main

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `description`) VALUES
<<<<<<< HEAD
(1, 'Laptop Dell Inspiron 15', 18000000, 'gm1.webp', ''),
(2, 'Laptop Asus Vivobook X', 16500000, 'sp2.jpg', ''),
(3, 'Laptop HP Pavilion 14', 15200000, 'sp3.jpg', ''),
(4, 'Laptop Lenovo ThinkPad E14', 19800000, 'sp4.jpg', '');
=======
(4, 'Laptop Gaming', 25000000, 'laptop.jpg', 'Laptop mạnh'),
(5, 'Laptop Gaming1', 500000, 'mouse.jpg', 'Laptop Gaming1'),
(6, 'Laptop Gaming2', 1500000, 'keyboard.jpg', 'Laptop Gaming2');
>>>>>>> origin/main

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `ho` varchar(100) DEFAULT NULL,
  `ten` varchar(100) DEFAULT NULL,
<<<<<<< HEAD
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
=======
  `sdt` varchar(20) DEFAULT NULL,
  `diachi` varchar(255) DEFAULT NULL,
>>>>>>> origin/main
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

<<<<<<< HEAD
INSERT INTO `users` (`id`, `ho`, `ten`, `phone`, `address`, `email`, `password`) VALUES
(1, 'Nguyễn', 'Khánh', '0123456789', 'TP Hồ Chí Minh', 'khanh@gmail.com', '123456'),
(2, 'Trần', 'Phát', '12345667', 'TP.vũng tàu', 'phat@gmail.com', '123456');
=======
INSERT INTO `users` (`id`, `ho`, `ten`, `sdt`, `address`, `email`, `password`) VALUES
(1, 'Trần', 'Tấn Phát', '0123456789', 'TP Hồ Chí Minh', 'phat@gmail.com', '123456'),
(2, 'Nguyễn', 'Văn A', '0987654321', 'Hà Nội', 'vana@gmail.com', '123456'),
(3, 'Lê', 'Thị B', '0912345678', 'Đà Nẵng', 'thib@gmail.com', '123456'),
(4, 'Phạm', 'Văn C', '0909090909', 'Cần Thơ', 'vanc@gmail.com', '123456');
>>>>>>> origin/main

--
-- Indexes for dumped tables
--

--
<<<<<<< HEAD
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);
=======
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);
>>>>>>> origin/main

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
<<<<<<< HEAD
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);
=======
  ADD PRIMARY KEY (`id`);
>>>>>>> origin/main

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
<<<<<<< HEAD
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
=======
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
>>>>>>> origin/main

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
>>>>>>> origin/main

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
>>>>>>> origin/main

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
>>>>>>> origin/main
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
