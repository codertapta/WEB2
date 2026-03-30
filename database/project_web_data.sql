-- ============================================================
-- phpMyAdmin SQL Dump — FULL VERSION (schema + data gộp)
-- Database: project_web_data
-- Ngày tạo: 2026-03-30
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- TẠO DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `project_web_data`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `project_web_data`;

-- ============================================================
-- XÓA BẢNG CŨ NẾU CÓ (thứ tự ngược FK)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `import_details`;
DROP TABLE IF EXISTS `import_orders`;
DROP TABLE IF EXISTS `order_details`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. BẢNG admins
-- ============================================================
CREATE TABLE `admins` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `username` varchar(50)  NOT NULL,
  `password` varchar(255) NOT NULL,
  `name`     varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admins` (`username`, `password`, `name`) VALUES
('admin',  'admin123', 'Quản trị viên'),
('admin1', 'admin123', 'Tô Doanh Quang Khu'),
('admin2', 'admin123', 'Trần Tấn Phát'),
('admin3', 'admin123', 'Nguyễn Khánh');

-- ============================================================
-- 2. BẢNG categories
-- ============================================================
CREATE TABLE `categories` (
  `id`     int(11)      NOT NULL AUTO_INCREMENT,
  `code`   varchar(10)  NOT NULL,
  `name`   varchar(255) NOT NULL,
  `status` tinyint(1)   DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`code`, `name`, `status`) VALUES
('A001', 'Laptop AI',        1),
('A002', 'Laptop Mỏng nhẹ', 1),
('A003', 'Laptop Gaming',   1);

-- ============================================================
-- 3. BẢNG users
-- ============================================================
CREATE TABLE `users` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `ho`       varchar(100) DEFAULT NULL,
  `ten`      varchar(100) DEFAULT NULL,
  `sdt`      varchar(20)  DEFAULT NULL,
  `diachi`   varchar(255) DEFAULT NULL,
  `email`    varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status`   varchar(10)  NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`ho`, `ten`, `sdt`, `diachi`, `email`, `password`, `status`) VALUES
('Trần',   'Tấn Phát',    '0123456789', 'TP Hồ Chí Minh',      'phat@gmail.com',          '$2y$10$Mx2Ez2rHIGfz0Y.5bsA9eOsOg0Qjar03GnYU54XquuqubeSUhoGHy', 'active'),
('Nguyễn', 'Văn A',       '0123456789', 'Hà Nội',               'vana@gmail.com',           '123456', 'active'),
('Lê',     'Thị B',       '0912345678', 'Đà Nẵng',              'thib@gmail.com',           '123456', 'active'),
('Phạm',   'Văn C',       '0909090909', 'Cần Thơ',              'vanc@gmail.com',           '123456', 'active'),
('Hoàng',  'Minh Tuấn',   '0901234567', 'Quận 1, TP.HCM',       'tuan.hoang@gmail.com',     '123456', 'active'),
('Võ',     'Thị Lan',     '0912345670', 'Quận 3, TP.HCM',       'lan.vo@gmail.com',         '123456', 'active'),
('Đặng',   'Văn Hùng',    '0923456781', 'Bình Thạnh, TP.HCM',   'hung.dang@gmail.com',      '123456', 'active'),
('Bùi',    'Thị Hoa',     '0934567892', 'Gò Vấp, TP.HCM',       'hoa.bui@gmail.com',        '123456', 'active'),
('Trương', 'Quốc Bảo',    '0945678903', 'Tân Bình, TP.HCM',     'bao.truong@gmail.com',     '123456', 'active'),
('Lý',     'Thành Đạt',   '0956789014', 'Phú Nhuận, TP.HCM',    'dat.ly@gmail.com',         '123456', 'active'),
('Phan',   'Thị Mai',     '0967890125', 'Quận 7, TP.HCM',       'mai.phan@gmail.com',       '123456', 'active'),
('Đinh',   'Văn Long',    '0978901236', 'Thủ Đức, TP.HCM',      'long.dinh@gmail.com',      '123456', 'active'),
('Ngô',    'Thị Phượng',  '0989012347', 'Quận 12, TP.HCM',      'phuong.ngo@gmail.com',     '123456', 'active'),
('Hồ',     'Minh Khoa',   '0990123458', 'Bình Dương',            'khoa.ho@gmail.com',        '123456', 'active'),
('Lâm',    'Thị Ngọc',    '0901122334', 'Đồng Nai',              'ngoc.lam@gmail.com',       '123456', 'active'),
('Dương',  'Văn Phúc',    '0912233445', 'Long An',               'phuc.duong@gmail.com',     '123456', 'active');

-- ============================================================
-- 4. BẢNG products
-- ============================================================
CREATE TABLE `products` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `name`        varchar(255) DEFAULT NULL,
  `price`       int(11)      DEFAULT NULL,
  `image`       varchar(255) DEFAULT NULL,
  `category_id` int(11)      NOT NULL,
  `cpu`         varchar(255) NOT NULL,
  `ram`         varchar(255) NOT NULL,
  `storage`     varchar(255) NOT NULL,
  `gpu`         varchar(255) NOT NULL,
  `screen`      varchar(255) NOT NULL,
  `battery`     varchar(255) NOT NULL,
  `weight`      varchar(255) NOT NULL,
  `os`          varchar(255) NOT NULL,
  `description` text         DEFAULT NULL,
  `unit`        varchar(50)  DEFAULT NULL,
  `quantity`    int(11)      DEFAULT 0,
  `cost_price`  int(11)      DEFAULT 0,
  `profit_rate` int(11)      DEFAULT 0,
  `status`      tinyint(4)   DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- Laptop Mỏng Nhẹ (category_id = 2) — id 1–10
-- ----------------------------------------------------------------
INSERT INTO `products` (`name`,`price`,`image`,`category_id`,`cpu`,`ram`,`storage`,`gpu`,`screen`,`battery`,`weight`,`os`,`description`,`unit`,`quantity`,`cost_price`,`profit_rate`,`status`) VALUES
('Dell XPS 13 9315',        0,'../img/sp1.jpg', 2,'Intel Core i5-1230U','8GB LPDDR5', '512GB NVMe SSD','Intel Iris Xe',   '13.4 inch FHD+','49Wh','1.17kg','Windows 11','Laptop mỏng nhẹ cao cấp, màn hình vô cực InfinityEdge, thiết kế nhôm nguyên khối.','Chiếc',0,0,0,1),
('Dell XPS 13 Plus 9320',   0,'../img/sp2.jpg', 2,'Intel Core i7-1260P','16GB LPDDR5','512GB NVMe SSD','Intel Iris Xe',   '13.4 inch 3.5K OLED','55Wh','1.26kg','Windows 11','Màn hình OLED 3.5K, bàn phím haptic, thiết kế tương lai.','Chiếc',0,0,0,1),
('HP Spectre x360 14',      0,'../img/sp3.jpg', 2,'Intel Core i7-1255U','16GB LPDDR4X','1TB NVMe SSD', 'Intel Iris Xe',   '13.5 inch 3K2K OLED','66Wh','1.36kg','Windows 11','Laptop 2-in-1 cao cấp, màn OLED tỉ lệ 3:2, bút stylus đi kèm.','Chiếc',0,0,0,1),
('HP Spectre x360 16',      0,'../img/sp4.jpg', 2,'Intel Core i7-12700H','16GB DDR5', '1TB NVMe SSD', 'Intel Arc A370M','16 inch 3K+ OLED',   '83Wh','2.19kg','Windows 11','Màn OLED 16 inch rực rỡ, chip H-series mạnh mẽ, lý tưởng cho sáng tạo.','Chiếc',0,0,0,1),
('LG Gram 14',              0,'../img/sp5.jpg', 2,'Intel Core i5-1240P', '16GB LPDDR5','512GB NVMe SSD','Intel Iris Xe',   '14 inch IPS QHD+',   '72Wh','0.99kg','Windows 11','Chỉ 999g, vượt chuẩn MIL-STD-810H, pin 72Wh siêu bền.','Chiếc',0,0,0,1),
('LG Gram 16',              0,'../img/sp6.jpg', 2,'Intel Core i7-1260P', '16GB LPDDR5','1TB NVMe SSD',  'Intel Iris Xe',   '16 inch IPS WQXGA',  '80Wh','1.18kg','Windows 11','Laptop 16 inch nhẹ nhất phân khúc, màn WQXGA sắc nét.','Chiếc',0,0,0,1),
('ASUS ZenBook 14 UX3402',  0,'../img/sp7.jpg', 2,'Intel Core i5-1240P', '8GB LPDDR5', '512GB NVMe SSD','Intel Iris Xe',   '14 inch 2.8K OLED',  '75Wh','1.39kg','Windows 11','Màn OLED 2.8K 90Hz, thiết kế ErgoLift, pin cả ngày làm việc.','Chiếc',0,0,0,1),
('ASUS ZenBook 14X UX5401', 0,'../img/sp8.jpg', 2,'Intel Core i7-12700H','16GB LPDDR5','1TB NVMe SSD',  'Intel Iris Xe',   '14 inch 2.8K OLED 90Hz','96Wh','1.58kg','Windows 11','Màn OLED Pantone, chip H-series, pin 96Wh sạc siêu nhanh.','Chiếc',0,0,0,1),
('Samsung Galaxy Book3 Pro', 0,'../img/sp9.jpg', 2,'Intel Core i7-1360P', '16GB LPDDR5','512GB NVMe SSD','Intel Iris Xe',   '14 inch Dynamic AMOLED 2X','63Wh','1.17kg','Windows 11','Màn AMOLED 120Hz HDR, tích hợp hệ sinh thái Samsung.','Chiếc',0,0,0,1),
('Lenovo Yoga 9i Gen 8',    0,'../img/sp10.jpg',2,'Intel Core i7-1360P', '16GB LPDDR5','1TB NVMe SSD',  'Intel Iris Xe',   '14 inch 2.8K OLED Touch','75Wh','1.41kg','Windows 11','2-in-1 premium, loa Bowers & Wilkins, màn OLED xúc giác.','Chiếc',0,0,0,1),

-- ----------------------------------------------------------------
-- Laptop AI (category_id = 1) — id 11–30
-- ----------------------------------------------------------------
('ASUS Vivobook S 15 S5507', 0,'../img/sp11.jpg',1,'Snapdragon X Elite X1E-78-100','16GB LPDDR5X','1TB NVMe SSD','Qualcomm Adreno','15.6 inch 3K OLED 120Hz','70Wh','1.42kg','Windows 11','Laptop AI Copilot+ đầu tiên, NPU 45 TOPS, pin cả ngày siêu bền.','Chiếc',0,0,0,1),
('HP OmniBook X 14',         0,'../img/sp12.jpg',1,'Snapdragon X Elite X1E-78-100','16GB LPDDR5X','1TB NVMe SSD','Qualcomm Adreno','14 inch IPS 2.2K 90Hz',  '59Wh','1.34kg','Windows 11','Copilot+ PC, tự động hoá tác vụ AI, kết nối 5G tùy chọn.','Chiếc',0,0,0,1),
('Dell XPS 14 9440',         0,'../img/sp13.jpg',1,'Intel Core Ultra 7 155H','32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4060 Max-Q','14.5 inch 3.2K OLED Touch','69.5Wh','1.64kg','Windows 11','NPU Intel tích hợp, màn OLED cảm ứng, RTX 4060 rất mạnh.','Chiếc',0,0,0,1),
('Dell XPS 16 9640',         0,'../img/sp14.jpg',1,'Intel Core Ultra 9 185H','64GB LPDDR5X','2TB NVMe SSD','NVIDIA RTX 4070 Max-Q','16 inch 3.2K OLED Touch','99.5Wh','1.86kg','Windows 11','Workstation di động đỉnh cao AI, RAM 64GB, pin 99.5Wh.','Chiếc',0,0,0,1),
('Lenovo ThinkPad X1 Carbon Gen 12',0,'../img/sp15.jpg',1,'Intel Core Ultra 7 165U','32GB LPDDR5','1TB NVMe SSD','Intel Graphics','14 inch IPS 2.8K 120Hz','57Wh','1.12kg','Windows 11','Dòng business AI flagship, bảo mật ThinkShield, siêu nhẹ 1.12kg.','Chiếc',0,0,0,1),
('Lenovo Yoga Pro 9i Gen 9', 0,'../img/sp16.jpg',1,'Intel Core Ultra 9 185H','32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4070 Max-Q','16 inch Mini-LED 165Hz',  '99.9Wh','1.99kg','Windows 11','Mini-LED 165Hz Dolby Vision, Copilot+, lý tưởng cho nhà sáng tạo AI.','Chiếc',0,0,0,1),
('HP EliteBook 840 G11',     0,'../img/sp17.jpg',1,'Intel Core Ultra 7 165U','32GB LPDDR5','512GB NVMe SSD','Intel Graphics','14 inch IPS 2.8K',          '51Wh','1.32kg','Windows 11','Doanh nghiệp AI, bảo mật Wolf Security, camera IR nhận diện khuôn mặt.','Chiếc',0,0,0,1),
('ASUS ProArt Studiobook 16',0,'../img/sp18.jpg',1,'Intel Core Ultra 9 185H','64GB DDR5', '2TB NVMe SSD','NVIDIA RTX 4070','16 inch OLED 3.2K 120Hz',    '96Wh','2.40kg','Windows 11','Laptop sáng tạo AI chuyên nghiệp, Pantone Validated, ASUS Dial.','Chiếc',0,0,0,1),
('Microsoft Surface Laptop 7',0,'../img/sp19.jpg',1,'Snapdragon X Elite X1E-80-100','32GB LPDDR5X','1TB NVMe SSD','Qualcomm Adreno','15 inch PixelSense 2496×1664','54Wh','1.66kg','Windows 11','Copilot+ PC chuẩn Microsoft, màn PixelSense, bàn phím Alcantara.','Chiếc',0,0,0,1),
('Samsung Galaxy Book4 Ultra',0,'../img/sp20.jpg',1,'Intel Core Ultra 9 185H','32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4070','16 inch Dynamic AMOLED 2X 120Hz','76Wh','1.86kg','Windows 11','AMOLED 120Hz HDR+, Galaxy AI tích hợp, sạc nhanh 140W.','Chiếc',0,0,0,1),
('ASUS Zenbook Pro 16X',     0,'../img/sp21.jpg',1,'Intel Core Ultra 9 185H','32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4070','16 inch OLED 4K 120Hz',     '96Wh','2.40kg','Windows 11','Màn OLED 4K 120Hz, ASUS AAS Ultra tản nhiệt, OLED Touch.','Chiếc',0,0,0,1),
('Lenovo ThinkBook 16p Gen 5',0,'../img/sp22.jpg',1,'AMD Ryzen AI 9 HX 370','32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4060','16 inch IPS 3.2K 165Hz',    '90Wh','1.99kg','Windows 11','Ryzen AI NPU mạnh, màn 3.2K 165Hz, thiết kế doanh nghiệp hiện đại.','Chiếc',0,0,0,1),
('HP Spectre x360 14 AI',    0,'../img/sp23.jpg',1,'Intel Core Ultra 7 155H','32GB LPDDR5X','1TB NVMe SSD','Intel Arc','14 inch 2.8K OLED Touch',        '66Wh','1.41kg','Windows 11','2-in-1 AI cao cấp, Copilot+, bút stylus MPP 2.0, màn OLED.','Chiếc',0,0,0,1),
('Acer Swift X 14 AI',       0,'../img/sp24.jpg',1,'Intel Core Ultra 5 125H','16GB LPDDR5X','512GB NVMe SSD','NVIDIA RTX 4050','14 inch IPS 2.8K 120Hz',   '73Wh','1.60kg','Windows 11','Hiệu năng AI tầm trung, RTX 4050 sáng tạo, pin 73Wh.','Chiếc',0,0,0,1),
('Acer Swift Go 16 AI',      0,'../img/sp25.jpg',1,'AMD Ryzen AI 9 365',   '16GB LPDDR5X','512GB NVMe SSD','AMD Radeon 890M','16 inch IPS 2K 144Hz',      '65Wh','1.65kg','Windows 11','Màn 16 inch 144Hz, Ryzen AI 9, tích hợp Copilot+, giá tốt.','Chiếc',0,0,0,1),
('MSI Prestige 16 AI Evo',   0,'../img/sp26.jpg',1,'Intel Core Ultra 7 155H','32GB LPDDR5','1TB NVMe SSD','Intel Arc A370M','16 inch QHD+ 165Hz',          '99.9Wh','1.75kg','Windows 11','Dòng AI chuyên nghiệp MSI, pin gần 100Wh, màn QHD+ sắc nét.','Chiếc',0,0,0,1),
('Lenovo IdeaPad Pro 5 Gen 9',0,'../img/sp27.jpg',1,'AMD Ryzen AI 9 HX 370','32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4060','16 inch IPS 3.2K 120Hz',    '84Wh','1.98kg','Windows 11','Laptop AI tầm trung cao, Ryzen AI, màn 3.2K chuẩn màu.','Chiếc',0,0,0,1),
('ASUS Vivobook Pro 15 OLED',0,'../img/sp28.jpg',1,'AMD Ryzen 9 7940HS',   '16GB DDR5',  '512GB NVMe SSD','NVIDIA RTX 4060','15.6 inch OLED 2.8K 120Hz', '96Wh','1.80kg','Windows 11','OLED 2.8K 120Hz Dolby Vision, RTX 4060, giá hợp lý.','Chiếc',0,0,0,1),
('HP Victus 16 AI',          0,'../img/sp29.jpg',1,'Intel Core Ultra 5 125H','16GB DDR5',  '512GB NVMe SSD','NVIDIA RTX 4060','16 inch IPS FHD 144Hz',     '70Wh','2.29kg','Windows 11','AI + gaming tầm trung, RTX 4060, tản nhiệt ổn, giá sinh viên.','Chiếc',0,0,0,1),
('Lenovo Yoga Slim 7x',      0,'../img/sp30.jpg',1,'Snapdragon X Elite X1E-78-100','32GB LPDDR5X','1TB NVMe SSD','Qualcomm Adreno','14.5 inch OLED 3K 90Hz','64Wh','1.28kg','Windows 11','Copilot+ PC siêu mỏng, màn OLED 3K, pin nguyên ngày.','Chiếc',0,0,0,1),

-- ----------------------------------------------------------------
-- Laptop Gaming (category_id = 3) — id 31–40
-- ----------------------------------------------------------------
('ASUS ROG Strix G16 2024',  0,'../img/sp31.jpg',3,'Intel Core i9-14900HX','32GB DDR5', '1TB NVMe SSD','NVIDIA RTX 4080 Laptop','16 inch QHD+ 240Hz ROG Nebula','90Wh','2.60kg','Windows 11','RTX 4080, màn ROG Nebula QHD+ 240Hz, tản nhiệt Tri-Fan.','Chiếc',0,0,0,1),
('ASUS ROG Zephyrus G16 2024',0,'../img/sp32.jpg',3,'AMD Ryzen 9 8945HS', '32GB LPDDR5X','1TB NVMe SSD','NVIDIA RTX 4090 Laptop','16 inch QHD+ 240Hz OLED',    '90Wh','1.85kg','Windows 11','RTX 4090 trong thân máy mỏng 1.85kg, màn OLED QHD+ 240Hz.','Chiếc',0,0,0,1),
('MSI Titan GT77 HX',        0,'../img/sp33.jpg',3,'Intel Core i9-13980HX','64GB DDR5', '2TB NVMe SSD','NVIDIA RTX 4090 Laptop','17.3 inch UHD 144Hz IPS',    '99.9Wh','3.10kg','Windows 11','Flagship gaming MSI, RTX 4090, bàn phím Cherry MX cơ.','Chiếc',0,0,0,1),
('MSI Raider GE78 HX',       0,'../img/sp34.jpg',3,'Intel Core i9-14900HX','32GB DDR5', '2TB NVMe SSD','NVIDIA RTX 4080 Laptop','17 inch QHD+ 240Hz',         '99.9Wh','2.99kg','Windows 11','Màn QHD+ 240Hz, RTX 4080 full power, tản nhiệt liquid metal.','Chiếc',0,0,0,1),
('Lenovo Legion Pro 7i Gen 9',0,'../img/sp35.jpg',3,'Intel Core i9-14900HX','32GB DDR5', '1TB NVMe SSD','NVIDIA RTX 4080 Laptop','16 inch IPS 2.5K 240Hz',    '99.9Wh','2.55kg','Windows 11','AI-Powered cooling, màn 2.5K 240Hz, sạc 330W siêu nhanh.','Chiếc',0,0,0,1),
('Lenovo Legion 5 Pro Gen 9', 0,'../img/sp36.jpg',3,'AMD Ryzen 9 7945HX',  '32GB DDR5', '1TB NVMe SSD','NVIDIA RTX 4070 Laptop','16 inch IPS 2.5K 165Hz',    '80Wh','2.50kg','Windows 11','Giá tốt phân khúc gaming cao cấp, màn 2.5K 165Hz, RTX 4070.','Chiếc',0,0,0,1),
('Acer Predator Helios 18',  0,'../img/sp37.jpg',3,'Intel Core i9-13900HX','32GB DDR5', '2TB NVMe SSD','NVIDIA RTX 4080 Laptop','18 inch IPS QHD+ 250Hz',    '90Wh','3.10kg','Windows 11','Màn 18 inch khổng lồ QHD+ 250Hz, tản nhiệt 5th Gen AeroBlade.','Chiếc',0,0,0,1),
('Acer Predator Triton 500 SE',0,'../img/sp38.jpg',3,'Intel Core i9-13900H','32GB DDR5', '1TB NVMe SSD','NVIDIA RTX 4080 Laptop','16 inch IPS QHD+ 240Hz',    '99.9Wh','2.47kg','Windows 11','Gaming mỏng nhẹ, RTX 4080, MUX Switch, màn QHD+ 240Hz.','Chiếc',0,0,0,1),
('Razer Blade 16 2024',      0,'../img/sp39.jpg',3,'Intel Core i9-14900HX','32GB DDR5', '2TB NVMe SSD','NVIDIA RTX 4090 Laptop','16 inch OLED QHD+ 240Hz',   '95.2Wh','2.34kg','Windows 11','Thiết kế CNC nhôm nguyên khối, OLED QHD+ 240Hz, RTX 4090 Max-Q.','Chiếc',0,0,0,1),
('Razer Blade 18 2024',      0,'../img/sp40.jpg',3,'Intel Core i9-14900HX','64GB DDR5', '2TB NVMe SSD','NVIDIA RTX 4090 Laptop','18 inch IPS QHD+ 300Hz',    '99.9Wh','3.04kg','Windows 11','Desktop replacement đỉnh cao, RAM 64GB, màn 300Hz, bàn phím cơ.','Chiếc',0,0,0,1);

-- ============================================================
-- 5. BẢNG import_orders
-- ============================================================
CREATE TABLE `import_orders` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `import_date` date         NOT NULL,
  `importer`    varchar(100) NOT NULL,
  `status`      tinyint(1)   NOT NULL DEFAULT 0 COMMENT '0=Chưa hoàn thành, 1=Hoàn thành',
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `import_orders` (`import_date`, `importer`, `status`, `created_at`) VALUES
-- Đợt nhập tháng 1/2026 (đã hoàn thành)
('2026-01-05', 'Nguyễn Văn A',       1, '2026-01-05 08:00:00'),  -- id 1: Laptop Mỏng Nhẹ
('2026-01-05', 'Trần Tấn Phát',      1, '2026-01-05 09:00:00'),  -- id 2: Laptop AI lần 1
('2026-01-06', 'Nguyễn Văn A',       1, '2026-01-06 08:30:00'),  -- id 3: Laptop Gaming lần 1
-- Đợt nhập tháng 2/2026 (đã hoàn thành)
('2026-02-10', 'Trần Tấn Phát',      1, '2026-02-10 10:00:00'),  -- id 4: Laptop Mỏng Nhẹ lần 2
('2026-02-10', 'Nguyễn Văn A',       1, '2026-02-10 11:00:00'),  -- id 5: Laptop AI lần 2
('2026-02-15', 'Tô Doanh Quang Khu', 1, '2026-02-15 09:00:00'),  -- id 6: Laptop Gaming lần 2
-- Đợt nhập tháng 3/2026 (đã hoàn thành)
('2026-03-01', 'Trần Tấn Phát',      1, '2026-03-01 08:00:00'),  -- id 7: Laptop Mỏng Nhẹ lần 3
('2026-03-01', 'Nguyễn Khánh',       1, '2026-03-01 09:00:00'),  -- id 8: Laptop AI lần 3
('2026-03-10', 'Nguyễn Văn A',       1, '2026-03-10 10:00:00'),  -- id 9: Gaming lần 3
('2026-03-15', 'Tô Doanh Quang Khu', 1, '2026-03-15 08:00:00'),  -- id 10: bổ sung đều
-- Đang xử lý (chưa hoàn thành — KHÔNG tính vào giá vốn)
('2026-03-28', 'Nguyễn Khánh',       0, '2026-03-28 14:00:00'),  -- id 11
('2026-03-29', 'Trần Tấn Phát',      0, '2026-03-29 09:00:00');  -- id 12

-- ============================================================
-- 6. BẢNG import_details
-- ============================================================
CREATE TABLE `import_details` (
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `import_order_id` int(11) NOT NULL,
  `product_id`      int(11) NOT NULL,
  `cost_price`      int(11) NOT NULL DEFAULT 0,
  `quantity`        int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `import_order_id` (`import_order_id`),
  KEY `product_id`      (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `import_details` (`import_order_id`, `product_id`, `cost_price`, `quantity`) VALUES
-- ── Phiếu 1 (2026-01-05): Laptop Mỏng Nhẹ lần nhập đầu ──────────────────────
(1,  1,  8500000, 8),   -- Dell XPS 13 9315       — nhập 1
(1,  2,  9200000, 6),   -- Dell XPS 13 Plus 9320  — nhập 1
(1,  3,  9800000, 5),   -- HP Spectre x360 14     — nhập 1
(1,  4, 10500000, 4),   -- HP Spectre x360 16     — nhập 1
(1,  5,  8000000, 6),   -- LG Gram 14             — nhập 1
(1,  6,  9000000, 5),   -- LG Gram 16             — nhập 1
(1,  7,  8300000, 6),   -- ASUS ZenBook 14        — nhập 1
(1,  8,  9600000, 4),   -- ASUS ZenBook 14X       — nhập 1
(1,  9,  9100000, 5),   -- Samsung Galaxy Book3   — nhập 1
(1, 10,  9500000, 4),   -- Lenovo Yoga 9i         — nhập 1

-- ── Phiếu 2 (2026-01-05): Laptop AI lần nhập đầu ────────────────────────────
(2, 11, 12000000, 5),   -- ASUS Vivobook S 15     — nhập 1
(2, 12, 13500000, 4),   -- HP OmniBook X 14       — nhập 1
(2, 13, 16000000, 4),   -- Dell XPS 14 9440       — nhập 1
(2, 14, 22000000, 3),   -- Dell XPS 16 9640       — nhập 1
(2, 15, 18000000, 3),   -- Lenovo ThinkPad X1     — nhập 1
(2, 16, 19000000, 3),   -- Lenovo Yoga Pro 9i     — nhập 1
(2, 17, 16500000, 3),   -- HP EliteBook 840       — nhập 1
(2, 18, 21000000, 2),   -- ASUS ProArt 16         — nhập 1
(2, 19, 17500000, 3),   -- MS Surface Laptop 7    — nhập 1
(2, 20, 20000000, 3),   -- Samsung Galaxy Book4   — nhập 1

-- ── Phiếu 3 (2026-01-06): Laptop Gaming lần nhập đầu ───────────────────────
(3, 31, 20000000, 3),   -- ROG Strix G16          — nhập 1
(3, 32, 23000000, 3),   -- ROG Zephyrus G16       — nhập 1
(3, 33, 26000000, 2),   -- MSI Titan GT77         — nhập 1
(3, 34, 24000000, 2),   -- MSI Raider GE78        — nhập 1
(3, 35, 22000000, 3),   -- Legion Pro 7i          — nhập 1
(3, 36, 18000000, 4),   -- Legion 5 Pro           — nhập 1
(3, 37, 22500000, 2),   -- Predator Helios 18     — nhập 1
(3, 38, 21000000, 3),   -- Predator Triton 500    — nhập 1
(3, 39, 26000000, 2),   -- Razer Blade 16         — nhập 1
(3, 40, 29000000, 2),   -- Razer Blade 18         — nhập 1

-- ── Phiếu 4 (2026-02-10): Laptop Mỏng Nhẹ lần nhập 2 ───────────────────────
(4,  1,  8300000, 5),   -- Dell XPS 13 9315       — nhập 2 (giá giảm)
(4,  2,  9000000, 4),   -- Dell XPS 13 Plus       — nhập 2
(4,  3,  9600000, 4),   -- HP Spectre x360 14     — nhập 2
(4,  5,  7800000, 5),   -- LG Gram 14             — nhập 2
(4,  6,  8800000, 4),   -- LG Gram 16             — nhập 2
(4,  7,  8100000, 5),   -- ASUS ZenBook 14        — nhập 2
(4,  9,  8900000, 4),   -- Samsung Galaxy Book3   — nhập 2
(4, 10,  9300000, 3),   -- Lenovo Yoga 9i         — nhập 2

-- ── Phiếu 5 (2026-02-10): Laptop AI lần nhập 2 ──────────────────────────────
(5, 11, 11800000, 4),   -- ASUS Vivobook S 15     — nhập 2 (giá giảm)
(5, 12, 13200000, 3),   -- HP OmniBook X 14       — nhập 2
(5, 13, 15700000, 3),   -- Dell XPS 14 9440       — nhập 2
(5, 15, 17700000, 3),   -- Lenovo ThinkPad X1     — nhập 2
(5, 16, 18700000, 3),   -- Lenovo Yoga Pro 9i     — nhập 2
(5, 17, 16200000, 3),   -- HP EliteBook 840       — nhập 2
(5, 21, 20000000, 3),   -- ASUS Zenbook Pro 16X   — nhập 1
(5, 22, 18500000, 3),   -- Lenovo ThinkBook 16p   — nhập 1
(5, 23, 15500000, 4),   -- HP Spectre x360 AI     — nhập 1
(5, 24, 13000000, 5),   -- Acer Swift X 14        — nhập 1

-- ── Phiếu 6 (2026-02-15): Laptop Gaming lần nhập 2 ──────────────────────────
(6, 31, 19500000, 4),   -- ROG Strix G16          — nhập 2 (giá giảm)
(6, 32, 22500000, 3),   -- ROG Zephyrus G16       — nhập 2
(6, 35, 21500000, 3),   -- Legion Pro 7i          — nhập 2
(6, 36, 17500000, 4),   -- Legion 5 Pro           — nhập 2
(6, 37, 22000000, 3),   -- Predator Helios 18     — nhập 2
(6, 38, 20500000, 3),   -- Predator Triton 500    — nhập 2

-- ── Phiếu 7 (2026-03-01): Laptop Mỏng Nhẹ lần nhập 3 ───────────────────────
(7,  1,  8100000, 6),   -- Dell XPS 13 9315       — nhập 3
(7,  2,  8800000, 4),   -- Dell XPS 13 Plus       — nhập 3
(7,  4, 10200000, 3),   -- HP Spectre x360 16     — nhập 2
(7,  5,  7600000, 5),   -- LG Gram 14             — nhập 3
(7,  7,  7900000, 5),   -- ASUS ZenBook 14        — nhập 3
(7,  8,  9400000, 4),   -- ASUS ZenBook 14X       — nhập 2
(7,  9,  8700000, 4),   -- Samsung Galaxy Book3   — nhập 3
(7, 10,  9100000, 4),   -- Lenovo Yoga 9i         — nhập 3

-- ── Phiếu 8 (2026-03-01): Laptop AI lần nhập 3 ──────────────────────────────
(8, 11, 11500000, 5),   -- ASUS Vivobook S 15     — nhập 3
(8, 12, 13000000, 4),   -- HP OmniBook X 14       — nhập 3
(8, 13, 15500000, 4),   -- Dell XPS 14 9440       — nhập 3
(8, 14, 21500000, 3),   -- Dell XPS 16 9640       — nhập 2
(8, 18, 20500000, 2),   -- ASUS ProArt 16         — nhập 2
(8, 19, 17200000, 3),   -- MS Surface Laptop 7    — nhập 2
(8, 20, 19500000, 3),   -- Samsung Galaxy Book4   — nhập 2
(8, 25, 13800000, 4),   -- Acer Swift Go 16       — nhập 1
(8, 26, 17000000, 3),   -- MSI Prestige 16        — nhập 1
(8, 27, 18000000, 3),   -- Lenovo IdeaPad Pro 5   — nhập 1
(8, 28, 15000000, 4),   -- ASUS Vivobook Pro 15   — nhập 1
(8, 29, 13500000, 5),   -- HP Victus 16 AI        — nhập 1
(8, 30, 14500000, 4),   -- Lenovo Yoga Slim 7x    — nhập 1

-- ── Phiếu 9 (2026-03-10): Laptop Gaming lần nhập 3 ──────────────────────────
(9, 31, 19000000, 4),   -- ROG Strix G16          — nhập 3
(9, 32, 22000000, 4),   -- ROG Zephyrus G16       — nhập 3
(9, 33, 25500000, 2),   -- MSI Titan GT77         — nhập 2
(9, 34, 23500000, 3),   -- MSI Raider GE78        — nhập 2
(9, 35, 21000000, 3),   -- Legion Pro 7i          — nhập 3
(9, 36, 17000000, 5),   -- Legion 5 Pro           — nhập 3
(9, 39, 25500000, 3),   -- Razer Blade 16         — nhập 2
(9, 40, 28500000, 2),   -- Razer Blade 18         — nhập 2

-- ── Phiếu 10 (2026-03-15): bổ sung đều ──────────────────────────────────────
(10,  3,  9400000, 4),  -- HP Spectre x360 14     — nhập 3
(10,  6,  8600000, 4),  -- LG Gram 16             — nhập 3
(10, 16, 18500000, 3),  -- Lenovo Yoga Pro 9i     — nhập 3
(10, 22, 18200000, 3),  -- Lenovo ThinkBook 16p   — nhập 2
(10, 24, 12700000, 4),  -- Acer Swift X 14        — nhập 2
(10, 32, 21800000, 3),  -- ROG Zephyrus G16       — nhập 4
(10, 36, 16800000, 4),  -- Legion 5 Pro           — nhập 4
(10, 38, 20000000, 3),  -- Predator Triton 500    — nhập 3

-- ── Phiếu 11 & 12 (chưa hoàn thành — KHÔNG tính giá vốn) ───────────────────
(11, 21, 19500000, 4),
(11, 23, 15200000, 5),
(12, 33, 25000000, 3),
(12, 34, 23000000, 3);

-- ============================================================
-- 7. BẢNG orders
-- ============================================================
CREATE TABLE `orders` (
  `id`          int(11)     NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)     DEFAULT NULL,
  `order_date`  datetime    DEFAULT NULL,
  `total_price` int(11)     DEFAULT NULL,
  `status`      varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`user_id`, `order_date`, `total_price`, `status`) VALUES
-- Tháng 1/2026
(1,  '2026-01-10 09:15:00', 11000000, 'delivered'),   -- 1
(2,  '2026-01-12 14:20:00', 14000000, 'delivered'),   -- 2
(3,  '2026-01-15 10:30:00', 10800000, 'delivered'),   -- 3
(4,  '2026-01-18 16:00:00', 25000000, 'delivered'),   -- 4
(5,  '2026-01-20 11:45:00', 23000000, 'delivered'),   -- 5
(6,  '2026-01-22 13:00:00', 13000000, 'cancelled'),   -- 6
(7,  '2026-01-25 10:00:00', 12000000, 'delivered'),   -- 7
(8,  '2026-01-27 14:00:00', 15600000, 'delivered'),   -- 8
-- Tháng 2/2026
(1,  '2026-02-03 09:00:00', 20300000, 'delivered'),   -- 9
(2,  '2026-02-05 10:30:00', 28000000, 'delivered'),   -- 10
(3,  '2026-02-08 14:00:00', 17600000, 'delivered'),   -- 11
(9,  '2026-02-10 11:00:00', 11700000, 'delivered'),   -- 12
(4,  '2026-02-12 15:30:00', 22000000, 'delivered'),   -- 13
(10, '2026-02-14 09:45:00', 14400000, 'cancelled'),   -- 14
(5,  '2026-02-16 13:15:00', 27000000, 'delivered'),   -- 15
(11, '2026-02-18 10:00:00', 18700000, 'delivered'),   -- 16
(6,  '2026-02-20 14:30:00', 32000000, 'delivered'),   -- 17
(12, '2026-02-23 09:00:00', 19500000, 'delivered'),   -- 18
(7,  '2026-02-25 11:00:00', 23500000, 'delivered'),   -- 19
(8,  '2026-02-27 14:00:00', 14300000, 'delivered'),   -- 20
-- Tháng 3/2026
(1,  '2026-03-02 09:00:00', 29000000, 'delivered'),   -- 21
(2,  '2026-03-04 11:00:00', 11400000, 'delivered'),   -- 22
(3,  '2026-03-06 14:00:00', 21000000, 'delivered'),   -- 23
(9,  '2026-03-08 10:30:00', 15600000, 'delivered'),   -- 24
(4,  '2026-03-10 09:00:00', 27500000, 'delivered'),   -- 25
(10, '2026-03-12 13:00:00', 29500000, 'delivered'),   -- 26
(5,  '2026-03-14 15:00:00', 34000000, 'delivered'),   -- 27
(11, '2026-03-16 09:30:00', 17500000, 'shipping'),    -- 28
(6,  '2026-03-18 11:00:00', 20000000, 'shipping'),    -- 29
(12, '2026-03-20 14:00:00', 22000000, 'shipping'),    -- 30
(7,  '2026-03-22 10:00:00', 14000000, 'processing'),  -- 31
(8,  '2026-03-24 09:00:00', 28000000, 'processing'),  -- 32
(1,  '2026-03-25 13:00:00', 19000000, 'pending'),     -- 33
(2,  '2026-03-26 11:00:00', 25000000, 'pending'),     -- 34
(3,  '2026-03-27 14:30:00', 32000000, 'pending'),     -- 35
(4,  '2026-03-28 09:00:00', 22500000, 'pending'),     -- 36
(5,  '2026-03-29 10:00:00', 18000000, 'pending');     -- 37

-- ============================================================
-- 8. BẢNG order_details
-- ============================================================
CREATE TABLE `order_details` (
  `id`         int(11) NOT NULL AUTO_INCREMENT,
  `order_id`   int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity`   int(11) DEFAULT NULL,
  `price`      int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_details` (`order_id`, `product_id`, `quantity`, `price`) VALUES
-- ĐH 1: Dell XPS 13
(1,  1, 1, 11000000),
-- ĐH 2: HP Spectre x360 14
(2,  3, 1, 12740000),
-- ĐH 3: ASUS ZenBook 14 + LG Gram 14
(3,  7, 1, 10790000),
-- ĐH 4: Dell XPS 16
(4, 14, 1, 28600000),
-- ĐH 5: ROG Zephyrus G16
(5, 32, 1, 29875000),
-- ĐH 6: HP Spectre x360 14 — CANCELLED
(6,  3, 1, 12740000),
-- ĐH 7: LG Gram 16
(7,  6, 1, 11700000),
-- ĐH 8: Lenovo Yoga 9i
(8, 10, 1, 12350000),
-- ĐH 9: ASUS Vivobook S 15 + Dell XPS 13
(9, 11, 1, 14963000),
(9,  1, 1,  8918000),
-- ĐH 10: MSI Titan GT77
(10, 33, 1, 33150000),
-- ĐH 11: Lenovo ThinkPad X1
(11, 15, 1, 22446000),
-- ĐH 12: ASUS ZenBook 14X
(12,  8, 1, 12480000),
-- ĐH 13: Dell XPS 14 9440
(13, 13, 1, 20046000),
-- ĐH 14: HP Spectre x360 14 AI — CANCELLED
(14, 23, 1, 20150000),
-- ĐH 15: ROG Strix G16
(15, 31, 1, 25350000),
-- ĐH 16: HP EliteBook 840
(16, 17, 1, 21056000),
-- ĐH 17: MSI Raider GE78
(17, 34, 1, 31050000),
-- ĐH 18: Samsung Galaxy Book3
(18,  9, 1, 11557000),
-- ĐH 19: Legion Pro 7i
(19, 35, 1, 28163000),
-- ĐH 20: Samsung Galaxy Book4 Ultra
(20, 20, 1, 25350000),
-- ĐH 21: ROG Zephyrus G16
(21, 32, 1, 29875000),
-- ĐH 22: Dell XPS 13 Plus
(22,  2, 1, 11700000),
-- ĐH 23: HP OmniBook X 14
(23, 12, 1, 17030000),
-- ĐH 24: LG Gram 14 x2
(24,  5, 2,  7820000),
-- ĐH 25: ASUS Zenbook Pro 16X
(25, 21, 1, 28500000),
-- ĐH 26: Razer Blade 16
(26, 39, 1, 34000000),
-- ĐH 27: Legion 5 Pro + Lenovo Yoga 9i
(27, 36, 1, 22100000),
(27, 10, 1, 12350000),
-- ĐH 28: Lenovo Yoga Pro 9i — SHIPPING
(28, 16, 1, 24650000),
-- ĐH 29: ASUS Vivobook S 15 — SHIPPING
(29, 11, 1, 14963000),
-- ĐH 30: MSI Prestige 16 — SHIPPING
(30, 26, 1, 22100000),
-- ĐH 31: ASUS ZenBook 14 — PROCESSING
(31,  7, 1, 10659000),
-- ĐH 32: ROG Strix G16 + Legion 5 Pro — PROCESSING
(32, 31, 1, 25350000),
(32, 36, 1, 22100000),
-- ĐH 33: Lenovo IdeaPad Pro 5 — PENDING
(33, 27, 1, 23400000),
-- ĐH 34: ASUS ROG Zephyrus — PENDING
(34, 32, 1, 28600000),
-- ĐH 35: MSI Titan GT77 — PENDING
(35, 33, 1, 33150000),
-- ĐH 36: Acer Predator Triton + Acer Swift X — PENDING
(36, 38, 1, 26000000),
(36, 24, 1, 16510000),
-- ĐH 37: Lenovo Yoga Slim 7x — PENDING
(37, 30, 1, 18850000);

-- ============================================================
-- 9. FOREIGN KEYS
-- ============================================================
ALTER TABLE `import_details`
  ADD CONSTRAINT `fk_idet_order`   FOREIGN KEY (`import_order_id`) REFERENCES `import_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_idet_product` FOREIGN KEY (`product_id`)      REFERENCES `products`       (`id`) ON DELETE CASCADE;

-- ============================================================
-- 10. TÍNH LẠI quantity TỒN KHO
--     tồn = tổng nhập (status=1) - tổng bán (không cancelled)
-- ============================================================
UPDATE products p
LEFT JOIN (
    SELECT idet.product_id,
           COALESCE(SUM(idet.quantity), 0) AS total_imported
    FROM   import_details idet
    JOIN   import_orders  io ON io.id = idet.import_order_id
    WHERE  io.status = 1
    GROUP  BY idet.product_id
) imp ON imp.product_id = p.id
LEFT JOIN (
    SELECT od.product_id,
           COALESCE(SUM(od.quantity), 0) AS total_sold
    FROM   order_details od
    JOIN   orders o ON o.id = od.order_id
    WHERE  o.status != 'cancelled'
    GROUP  BY od.product_id
) sol ON sol.product_id = p.id
SET p.quantity = COALESCE(imp.total_imported, 0) - COALESCE(sol.total_sold, 0);

-- ============================================================
-- 11. TÍNH COST_PRICE BÌNH QUÂN GIA QUYỀN (Stored Procedure)
-- ============================================================
DROP PROCEDURE IF EXISTS UpdateAllCostPrices;

DELIMITER $$
CREATE PROCEDURE UpdateAllCostPrices()
BEGIN
    DECLARE v_done     INT DEFAULT FALSE;
    DECLARE v_pid      INT;
    DECLARE v_imp_qty  INT;
    DECLARE v_imp_cost DECIMAL(15,4);

    DECLARE cur CURSOR FOR
        SELECT idet.product_id,
               idet.quantity,
               idet.cost_price
        FROM   import_details idet
        JOIN   import_orders  io ON io.id = idet.import_order_id
        WHERE  io.status = 1
        ORDER  BY io.import_date ASC, io.id ASC, idet.id ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

    DROP TEMPORARY TABLE IF EXISTS tmp_cost_bq;
    CREATE TEMPORARY TABLE tmp_cost_bq (
        product_id INT          PRIMARY KEY,
        qty        INT          DEFAULT 0,
        cost       DECIMAL(15,4) DEFAULT 0
    );

    OPEN cur;
    bq_loop: LOOP
        FETCH cur INTO v_pid, v_imp_qty, v_imp_cost;
        IF v_done THEN LEAVE bq_loop; END IF;

        -- Lấy giá trị hiện tại từ bảng tạm
        SET @cur_qty  = 0;
        SET @cur_cost = 0;
        SELECT qty, cost INTO @cur_qty, @cur_cost
        FROM   tmp_cost_bq
        WHERE  product_id = v_pid;

        -- Công thức BQGQ: (tồn * giá_cũ + nhập * giá_nhập) / (tồn + nhập)
        IF (@cur_qty + v_imp_qty) > 0 THEN
            SET @cur_cost = (@cur_qty * @cur_cost + v_imp_qty * v_imp_cost)
                            / (@cur_qty + v_imp_qty);
        END IF;
        SET @cur_qty = @cur_qty + v_imp_qty;

        INSERT INTO tmp_cost_bq (product_id, qty, cost)
        VALUES (v_pid, @cur_qty, @cur_cost)
        ON DUPLICATE KEY UPDATE
            qty  = @cur_qty,
            cost = @cur_cost;
    END LOOP;
    CLOSE cur;

    -- Cập nhật cost_price về bảng products
    UPDATE products p
    JOIN   tmp_cost_bq t ON t.product_id = p.id
    SET    p.cost_price = ROUND(t.cost);

    DROP TEMPORARY TABLE IF EXISTS tmp_cost_bq;
END$$
DELIMITER ;

CALL UpdateAllCostPrices();

-- ============================================================
-- 12. ĐẶT GIÁ BÁN MẪU theo tỷ lệ lợi nhuận từng dòng
-- ============================================================
-- Laptop Mỏng Nhẹ: lợi nhuận 30%
UPDATE products
SET    profit_rate = 30,
       price       = ROUND(cost_price * 1.30)
WHERE  category_id = 2 AND cost_price > 0;

-- Laptop AI: lợi nhuận 28%
UPDATE products
SET    profit_rate = 28,
       price       = ROUND(cost_price * 1.28)
WHERE  category_id = 1 AND cost_price > 0;

-- Laptop Gaming: lợi nhuận 25%
UPDATE products
SET    profit_rate = 25,
       price       = ROUND(cost_price * 1.25)
WHERE  category_id = 3 AND cost_price > 0;

-- ============================================================
-- 13. XÁC NHẬN KẾT QUẢ
-- ============================================================
SELECT p.id,
       p.name,
       c.name        AS category,
       p.cost_price  AS gia_von_bq,
       p.profit_rate AS ty_le_ln,
       p.price       AS gia_ban,
       p.quantity    AS ton_kho
FROM   products p
JOIN   categories c ON c.id = p.category_id
ORDER  BY p.id;

SELECT 'Tổng đơn'    AS label, COUNT(*) AS value FROM orders
UNION ALL SELECT 'delivered',  COUNT(*) FROM orders WHERE status='delivered'
UNION ALL SELECT 'shipping',   COUNT(*) FROM orders WHERE status='shipping'
UNION ALL SELECT 'processing', COUNT(*) FROM orders WHERE status='processing'
UNION ALL SELECT 'pending',    COUNT(*) FROM orders WHERE status='pending'
UNION ALL SELECT 'cancelled',  COUNT(*) FROM orders WHERE status='cancelled';

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;