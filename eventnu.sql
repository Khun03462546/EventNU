-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 07, 2025 at 07:58 PM
-- Server version: 8.0.17
-- PHP Version: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eventnu`
--

-- --------------------------------------------------------

--
-- Table structure for table `audience`
--

CREATE TABLE `audience` (
  `Audience_email` varchar(255) NOT NULL,
  `Audience_FirstName` varchar(100) NOT NULL,
  `Audience_LastName` varchar(100) NOT NULL,
  `FacultyID` int(11) DEFAULT NULL,
  `MajorID` int(11) DEFAULT NULL,
  `Audience_Password` varchar(255) NOT NULL,
  `Audience_Phone` varchar(20) DEFAULT NULL,
  `GenderID` int(11) NOT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `Audience_Role` enum('Student','Lecturer','Guest User','organizer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Previous_Role` varchar(50) DEFAULT NULL,
  `Audience_Image` varchar(255) DEFAULT '../assets/imgs/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audience`
--

INSERT INTO `audience` (`Audience_email`, `Audience_FirstName`, `Audience_LastName`, `FacultyID`, `MajorID`, `Audience_Password`, `Audience_Phone`, `GenderID`, `StudentID`, `Audience_Role`, `Previous_Role`, `Audience_Image`) VALUES
('hh@gmail.com', 'hh', 'hh', 1, 78, '$2y$10$OoUDd.xCIrfkxEYcmmpzlOBT4SqwQozFyhfTbqfN/Gf6EFMGkQ/qe', '00000000000', 3, NULL, 'Lecturer', NULL, '../assets/imgs/default.png'),
('jang@gmail.com', 'Jang', 'Cool', 1, 1, '$2y$10$B0vhE6d..nFPh5uKj/Pp7O4FIs7opRl3XEBKFeeCxY34U.KcLm6Ym', '088123456', 1, 65310202, 'Student', NULL, '../assets/imgs/default.png'),
('jo@gmail.com', 'Jo', 'Ja', 1, 1, '$2y$10$yBay1YNtAfnm0qiKIx.iEeFjmIHBJ5W0hhbq2CeDXu0257nfSoZ/S', '0891452230', 3, 0, 'Guest User', NULL, '../assets/imgs/default.png'),
('jus@gamil.com', 'Kanokwan', 'Boonyo', 9, 28, '$2y$10$uBaNiP0Zwnk3WeI6n28./.TIAlq68LNbifmaQQqRSRFfquZn9z/ZS', '0123456789', 2, 65310029, 'Student', NULL, '../assets/imgs/default.png'),
('k@gmail.com', 'jang', 'kun', 2, 3, '$2y$10$CJHPnzPRNbW/MrMCoPdL7Okg99s4ODX1vei3rHK441CwszJjhRr4.', '0861234545', 1, 65310123, 'Lecturer', NULL, '../assets/imgs/default.png'),
('kanokwanb65@nu.ac.th', 'Kanokwan', 'Boonyo', 9, 28, '$2y$10$M0XkOZTV4218d/18yXJKv.BOCWCOKyvQ.2FlGoPl7sMS9mCdsixk2', '0985499270', 2, 65310029, 'Student', 'Student', '../assets/imgs/default.png'),
('worapichayak65@nu.ac.th', 'worapichaya', 'boontam', 9, 28, '$2y$10$gVAaiL9PX12rROV.qdVsQO1XF9tnE6QcmH6xXyWHajWCFGCFsQD32', '0000000000', 1, 65314447, 'organizer', NULL, '../assets/imgs/default.png');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(8) NOT NULL,
  `EventID` int(11) NOT NULL,
  `Audience_email` varchar(255) NOT NULL,
  `ticket_count` int(11) NOT NULL,
  `ticket_totalprice` decimal(10,2) NOT NULL,
  `booking_receipt` varchar(255) DEFAULT NULL,
  `paytime` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `EventID`, `Audience_email`, `ticket_count`, `ticket_totalprice`, `booking_receipt`, `paytime`) VALUES
(23, 13, 'jus@gamil.com', 4, '396.00', '../assets/imgs/pomtpay/หลักฐาน_99.jpg', '2025-03-27 13:40:28'),
(24, 13, 'jo@gmail.com', 3, '396.00', '../receipt/receipt_1740808073_หลักฐาน_99.jpg\r\n', '0000-00-00 00:00:00'),
(25, 13, 'k@gmail.com', 4, '396.00', 'receipt_1743108028_BD-BB-0617-R (6)-268x268.jpg\r\n', '0000-00-00 00:00:00'),
(26, 13, 'kanokwanb65@nu.ac.th', 6, '594.00', 'receipt_1743219813_หลักฐาน_369.jpg', '2025-03-29 03:43:33'),
(27, 12, 'worapichayak65@nu.ac.th', 6, '594.00', 'receipt_1743222451_หลักฐาน_369.jpg', '2025-03-29 04:27:31'),
(28, 15, 'worapichayak65@nu.ac.th', 4, '396.00', 'receipt_1743223305_หลักฐาน_495.jpg', '2025-03-29 04:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`Category_ID`, `Category_Name`) VALUES
(1, 'อีเว้นท์ที่จัดในโรงละคร'),
(2, 'อีเว้นท์ตามเทศกาล'),
(3, 'อีเว้นท์ที่ช่วยเสริมทักษะ'),
(4, 'อีเว้นท์แสดงความสามารถของนิสิต'),
(5, 'อีเว้นท์ตามช่วงเวลา'),
(6, 'อีเว้นท์นิทรรศการ'),
(7, 'อีเว้นท์ประกวดผลงาน'),
(8, 'อีเว้นท์พิธีทางวัฒนธรรมและศาสนา'),
(9, 'อีเว้นท์ส่งเสริมทักษะของนิสิต'),
(10, 'อีเว้นท์ด้านจิตอาสา'),
(11, 'อีเว้นท์บริการด้านสุขภาพ');

-- --------------------------------------------------------

--
-- Table structure for table `categoriestwo`
--

CREATE TABLE `categoriestwo` (
  `CategoryTwo_ID` int(11) NOT NULL,
  `Category_ID` int(11) NOT NULL,
  `CategoryTwo_Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoriestwo`
--

INSERT INTO `categoriestwo` (`CategoryTwo_ID`, `Category_ID`, `CategoryTwo_Name`) VALUES
(1, 1, 'ละครเวที'),
(2, 1, 'คอนเสิร์ตและการแสดงดนตรี'),
(3, 1, 'การแสดงศิลปะและแสงสี'),
(4, 1, 'การแสดงผสมผสาน'),
(5, 2, 'เทศกาลไทย'),
(6, 2, 'เทศกาลต่างชาติ'),
(7, 3, 'ด้านเทคโนโลยี'),
(8, 3, 'ด้านภาษา'),
(9, 4, 'การแสดงดนตรีและร้องเพลง'),
(10, 4, 'การแสดงเต้น'),
(11, 4, 'การแสดงละครและการแสดงเวที'),
(12, 4, 'การแสดงไลฟ์โชว์และงานแฟร์'),
(13, 5, 'อีเว้นท์ช่วงกลางวัน'),
(14, 5, 'อีเว้นท์ช่วงกลางคืน'),
(15, 6, 'นิทรรศการวิชาการ'),
(16, 6, 'นิทรรศการศิลปะ'),
(17, 7, 'การประกวดด้านศิลปะและการออกแบบ'),
(18, 7, 'การประกวดด้านวิทยาศาสตร์และเทคโนโลยี'),
(19, 7, 'การประกวดด้านธุรกิจและการนำเสนอ'),
(20, 8, 'พิธีทางศาสนาเพื่อสืบสานวัฒนธรรมและจารีตประเพณี'),
(21, 8, 'พิธีทางศาสนาในโอกาสพิเศษ'),
(22, 8, 'พิธีทางศาสนาเฉพาะกลุ่มและเฉพาะสาขา'),
(23, 9, 'ส่งเสริมด้านสังคม'),
(24, 9, 'ส่งเสริมด้านวิชาการ'),
(25, 9, 'ส่งเสริมด้านจิตใจ'),
(26, 10, 'กิจกรรมจิตอาสาด้านการช่วยเหลือสังคมและผู้ด้อยโอกาส'),
(27, 10, 'กิจกรรมจิตอาสาด้านการศึกษาและพัฒนาเยาวชน'),
(28, 10, 'กิจกรรมจิตอาสาด้านสิ่งแวดล้อมและชุมชน'),
(29, 11, 'ทันตกรรม'),
(30, 11, 'วัคซีน');

-- --------------------------------------------------------

--
-- Table structure for table `committee`
--

CREATE TABLE `committee` (
  `committee_email` varchar(255) NOT NULL,
  `committee_firstname` varchar(100) NOT NULL,
  `committee_lastname` varchar(100) NOT NULL,
  `committee_password` varchar(255) NOT NULL,
  `committee_phone` varchar(20) DEFAULT NULL,
  `committee_id` int(11) NOT NULL,
  `FacultyID` int(11) NOT NULL,
  `MajorID` int(11) NOT NULL,
  `GenderID` int(11) NOT NULL,
  `committee_Image` varchar(255) DEFAULT '../assets/imgs/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `committee`
--

INSERT INTO `committee` (`committee_email`, `committee_firstname`, `committee_lastname`, `committee_password`, `committee_phone`, `committee_id`, `FacultyID`, `MajorID`, `GenderID`, `committee_Image`) VALUES
('ava@gmail.com', 'Ava ', 'Martin', '$2y$10$bWhlRGaP0D/NG1lvExHcfOO0NPw4.bI3WQg0z8eaUndPSOvv529pS', '0874567890', 14, 14, 50, 2, '../assets/imgs/default.png'),
('char@gmail.com', 'Charles', 'Moore', '$2y$10$Cf9Kvc7566M1zj9FqSWIJOCPTsIWqNDX6GjBOrD4Uvw/P/2Sx2da2', '0879012345', 9, 9, 28, 2, '../assets/imgs/default.png'),
('dean@gmail.com', 'Dean', 'X', '$2y$10$Cl6LojVfEARkNi8AZ5KDsuJalptQ1qJH69m950Z4WC0G2rIu3sLaq', '0123456789', 112, 2, 4, 1, '../assets/imgs/default.png'),
('edward@gmail.com', 'Edward ', 'Harris', '$2y$10$ps9dTLSA/zDZLy.ZQG0cwOPERwEcxe3fuQxPSomzUKgv20VXS5Ss2', '0873456789', 13, 13, 81, 1, '../assets/imgs/default.png'),
('emily@gmail.com', 'Emily ', 'Johnson', '$2y$10$C.haR3gPp6scFN9tUA2dV.FH4b0K1Yk5HqpqsLzUsAcrarCp/Ox82', '0872345678', 2, 2, 79, 2, '../assets/imgs/default.png'),
('george@gmail.com', 'George ', 'Williams', '$2y$10$pw4Nz1Ajna3.qL6IGI4fpeDGXvHs5khTUFXKEGD4mPTdUY88WVfBm', '0875678901', 5, 5, 9, 1, '../assets/imgs/default.png'),
('grace@gmail.com', 'Grace ', 'Lee', '$2y$10$Lr21LuYkXNYEfcHyuXF1uOqNhlZKBkpkHjIAVicUzXDP/TVpuob3K', '0876789012', 16, 16, 70, 2, '../assets/imgs/default.png'),
('hen@gmail.com', 'Hen', 'Miller', '$2y$10$3SamyaNGu2D3HpFdrOvvL..C67sa9SQrDOh2wFWNE/tybPxyMM0my', '0877890123', 7, 7, 80, 1, '../assets/imgs/default.png'),
('isa@gmail.com', 'Isa', 'Davis', '$2y$10$xLS/98Q8uCLa45U07mY/TOa2Ad/WCbQ32HvKg5b/8klmsNULjCbAi', '0876789012', 6, 6, 14, 2, '../assets/imgs/default.png'),
('james@gmail.com', 'James', 'Smith', '$2y$10$XC1a6Z2F98Y9WE0FQnW9T.1.hq2OLNR8iKYJQM0NgZVXIy44tna26', '0871234567', 1, 1, 78, 1, '../assets/imgs/default.png'),
('lily@gmail.com', 'Lily ', 'White', '$2y$10$k4FjzsLVSxa/rk8jbTGTNuNZIXZHbtnilm2tvWGlDDZw7FYNSxxou', '0872345678', 12, 12, 47, 2, '../assets/imgs/default.png'),
('lotte@gmail.com', 'Charlotte ', 'Anderson', '$2y$10$zvwtx1N1FoHyhe4dUGWa6ehQJm3jKP2MbcnXYQ73pVtOSBCG.ljte', '0870123456', 10, 10, 35, 2, '../assets/imgs/default.png'),
('olivia@gmail.com', 'Olivia ', 'Taylor', '$2y$10$uK.2oMc086oYe6ndrgvMdO4iYbUIT1LNA8MbTFtqN1txdqVpZVcgu', '0874567890', 4, 4, 5, 2, '../assets/imgs/default.png'),
('sop@gmail.com', 'Sop ', 'Wilson', '$2y$10$1XVerRk3G6w75L9oDBhxx.Wrn4uWdBTZYv5G8SrPiKTbD01fQk17u', '0878901234', 8, 8, 16, 2, '../assets/imgs/default.png'),
('thomas@gmail.com', 'Thomas ', 'Jackson', '$2y$10$sVBlBFFddSUC7w9eAYofpuHmJ1SFKOhHuVUo2G8MWmKH4Lj1WTU4a', '0871234567', 11, 11, 43, 1, '../assets/imgs/default.png'),
('william@gmail.com', 'William', 'Brown', '$2y$10$/6GbLGwXczcM2Bosx.7A/Ojpu/5RDgGNNgHb70CUje/9eetqhtAgC', '0873456789', 3, 3, 4, 1, '../assets/imgs/default.png');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `EventID` int(11) NOT NULL,
  `Event_Name` varchar(255) NOT NULL,
  `EventOrganizer_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Event_Date` date NOT NULL,
  `Event_EndDate` date NOT NULL,
  `Event_Time` time NOT NULL,
  `Event_EndTime` time NOT NULL,
  `public_sale_date` date DEFAULT NULL,
  `public_sale_time` time DEFAULT NULL,
  `Event_Layout` text,
  `Event_Location` varchar(255) NOT NULL,
  `Event_Detail` text,
  `Event_Picture` varchar(255) DEFAULT NULL,
  `Category_ID` int(11) NOT NULL,
  `Event_Price` decimal(10,2) NOT NULL,
  `TypeRegister` enum('yes','no') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`EventID`, `Event_Name`, `EventOrganizer_email`, `Event_Date`, `Event_EndDate`, `Event_Time`, `Event_EndTime`, `public_sale_date`, `public_sale_time`, `Event_Layout`, `Event_Location`, `Event_Detail`, `Event_Picture`, `Category_ID`, `Event_Price`, `TypeRegister`) VALUES
(1, 'ConsertNU', 'worapichayak65@nu.ac.th', '2025-03-01', '0000-00-00', '18:00:00', '00:00:00', '2025-02-25', '15:00:00', 'ggg', 'BEC MORNOR', 'เตรียมตัวพบกับกิจกรรมที่น่าสนใจจาก 5 ภาควิชา\r\n- กิจกรรมรอบที่ 1: 09.00-10.00 น.\r\n- กิจกรรมรอบที่ 2: 10.30-11.30 น.\r\nผู้เข้าร่วมจะได้รับเกียรติบัตรจากงาน Open House สามารถนำไปใช้ในการสมัคร TCAS 69 รอบ 1 Portfolio ของคณะวิทยาศาสตร์ มหาวิทยาลัยนเรศวร (รับตรงงานสัปดาห์วิทยาศาสตร์)', 'uploads/image.png', 5, '200.00', 'yes'),
(2, 'FREE_NO_REGISTER', 'worapichayak65@nu.ac.th', '2025-03-05', '0000-00-00', '12:00:00', '00:00:00', '0000-00-00', '00:00:00', 'NO DATA', 'MORNOR', 'ff', 'uploads/dog.jpg', 1, '0.00', 'no'),
(3, 'TEST', 'worapichayak65@nu.ac.th', '2025-04-30', '2025-05-01', '10:00:00', '15:00:00', '0000-00-00', '00:00:00', 'GG', 'Sci', 'KK', 'uploads/cat.jpg', 3, '0.00', 'yes'),
(4, 'STUDENTTEST', 'worapichayak65@nu.ac.th', '2025-03-31', '0000-00-00', '09:00:00', '00:00:00', '0000-00-00', '00:00:00', 'YY', 'Classroom', 'ggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg', 'uploads/penguin.jpg', 1, '0.00', 'yes'),
(7, 'กิจกรรมสานสัมพันธ์วิศวกรรมศาสตร์', 'worapichayak65@nu.ac.th', '2025-01-30', '2025-01-30', '17:30:00', '21:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/MzHEWEbM4bn8htr59', 'อาคารอเนกประสงค์', 'เชิญชวน นิสิตชั้นปีที่ 1 คณะวิศวกรรมศาสตร์ ทุกท่าน เข้าร่วม “กิจกรรมสานสัมพันธ์วิศวกรรมศาสตร์”', '../assets/imgs/event/กิจกรรมสานสัมพันธ์วิศวกรรมศาสตร์.jpg', 1, '0.00', 'yes'),
(8, 'Entaneer Sport Week ครั้งที่ 10', 'worapichayak65@nu.ac.th', '2024-12-02', '2024-12-08', '13:00:00', '18:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/MzHEWEbM4bn8htr59', 'อาคารอเนกประสงค์', 'วันที่ 8 ธันวาคมนี้ เตรียมตัวให้พร้อมสำหรับความสนุก เพลิดเพลิน จากกิจกรรมภายในงาน Entaneer Sport Week ครั้งที่ 10 และ คอนเสิร์ตปิด จากวง “Memories”', '../assets/imgs/event/eng sport week10.jpg', 1, '0.00', 'yes'),
(9, 'กิจกรรมโครงการสานสัมพันธ์ SMO EN', 'worapichayak65@nu.ac.th', '2025-01-26', '2025-01-26', '08:30:00', '16:30:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/PwKBMuTzHe5fxz149', 'อาคารขวัญเมือง', 'กิจกรรมโครงการสานสัมพันธ์ SMO EN ⚙️⚙️ วันพฤหัส ที่ 26 มกราคม 2568 ได้ทั้งความรู้และความสนุกสนานไปกับกิจกรรม', '../assets/imgs/event/smo en.jpg', 1, '0.00', 'no'),
(10, 'พิธีครอบครูพระวิษณุกรรมและไหว้ครู คณะวิศวกรรมศาสตร์', 'worapichayak65@nu.ac.th', '2024-07-14', '2024-07-14', '06:00:00', '14:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/7FgSSqE8e1Zdb8E98', 'คณะวิศวกรรมศาสตร์', 'ขอเรียนเชิญ คณะผู้บริหาร คณาจารย์ บุคลากร ศิษย์เก่า ศิษย์ปัจจุบัน และนิสิตชั้นปีที่ 1 (รหัส 67 เกียร์รุ่นที่ 31) เข้าร่วม พิธีครอบครูพระวิษณุกรรมและไหว้ครู คณะวิศวกรรมศาสตร์', '../assets/imgs/event/enge.jpg', 1, '0.00', 'yes'),
(11, 'Gearlaxy Esports & Cosplay Fest', 'worapichayak65@nu.ac.th', '2024-08-20', '2024-08-20', '08:30:00', '17:30:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/Yq2dmFYxgdF25Wdp6', 'คณะวิศวกรรมศาสตร์', 'Say Hi!! ชาวมอนอ เตรียมตัวพบกับงานยิ่งใหญ่แห่งปี ของชาวมอนอ กับกิจกรรม Gearlaxy Esports & Cosplay Fest', '../assets/imgs/event/cosplay fes.jpg', 1, '0.00', 'no'),
(12, 'Draw Drew Die ปริศนาภาพวาดสั่งตาย', 'worapichayak65@nu.ac.th', '2024-07-19', '2024-07-20', '12:00:00', '17:00:00', '2024-06-07', '12:00:00', 'https://maps.app.goo.gl/B2Y31XdESTqbHvCR7', 'QS Theater', 'สิ้นสุดการรอคอยกับการกลับมาอีกครั้งของละครเวทีนิเทศศาสตร์ ปีการศึกษา 2567 “Draw Drew Die ปริศนาภาพวาดสั่งตาย”', '../assets/imgs/event/die.jpg', 1, '99.00', 'yes'),
(13, 'ดุริยางคศาสตร์คอนเสิร์ต ครั้งที่ 6 เรื่อง มังกร', 'worapichayak65@nu.ac.th', '2024-07-22', '2024-07-22', '13:00:00', '16:30:00', '2024-07-10', '00:00:00', 'https://maps.app.goo.gl/B2Y31XdESTqbHvCR7', 'QS Theater', 'การนำเสนอผลงานทางดุริยางคศิลป์ในโครงการดุริยางคศาสตร์คอนเสิร์ต ครั้งที่ 6 เรื่อง มังกร : สหชาติ นิวาศมังกร อวยพรรุ่งเรือง', '../assets/imgs/event/dragon.jpg', 1, '99.00', 'yes'),
(14, 'เรื่อง \"LA LUMIÈRE\" ใต้แสงดารา', 'worapichayak65@nu.ac.th', '2024-07-15', '2024-07-16', '11:30:00', '16:00:00', '2024-06-23', '10:00:00', 'https://maps.app.goo.gl/B2Y31XdESTqbHvCR7', 'QS Theater', 'งานละคร', '../assets/imgs/event/หาข้อมูลยากชห.jpg', 1, '99.00', 'yes'),
(15, 'The Poison Love : กุหลาบกาลี', 'worapichayak65@nu.ac.th', '2024-02-25', '2024-02-26', '12:00:00', '17:00:00', '2024-01-07', '10:00:00', 'https://maps.app.goo.gl/B2Y31XdESTqbHvCR7', 'QS Theater', 'ละครเวทีคณะศึกษาศาสตร์ครั้งที่ 2 \"The Poison Love : กุหลาบกาลี\"', '../assets/imgs/event/The Poison Love.jpg', 1, '99.00', 'yes'),
(16, 'CosFair ครั้งที่ 17 : Glam & Glow - The Art of Beauty and Music', 'kanokwanb65@nu.ac.th', '2025-02-26', '2025-02-26', '17:00:00', '23:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/DhqG3WXNBzKiQyZWA', 'คณะเภสัชศาสตร์', 'CosFair ครั้งที่ 17 : Glam & Glow - The Art of Beauty and Music งานที่รวมผลงานสร้างสรรค์ผลิตภัณฑ์เครื่องสำอาง และเทศกาลดนตรีไว้ในที่เดียว', '../assets/imgs/event/cosfair.jpg', 1, '0.00', 'no'),
(17, 'NU Art & Craft Fun Fair 2025: Art & Coffee Lover', 'kanokwanb65@nu.ac.th', '2025-02-13', '2025-02-14', '12:00:00', '21:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/LZpjYrDssfm8bapj6', 'พิพิธภัณฑ์ผ้า มหาวิทยาลัยนเรศวร', 'มหาวิทยาลัยนเรศวร โดยกองส่งเสริมศิลปวัฒนธรรม ร่วมกับวิทยาลัยเพื่อการค้นคว้าระดับรากฐาน ขอเชิญร่วมงาน “NU Art & Craft Fun Fair 2025”: Art & Coffee Lover', '../assets/imgs/event/art and craft.jpg', 1, '0.00', 'no'),
(18, 'กีฬาเชื่อมสัมพันธ์กลุ่มวิทยาศาสตร์และเทคโนโลยี ครั้งที่ 13', 'kanokwanb65@nu.ac.th', '2025-02-06', '2025-02-06', '17:30:00', '20:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/CdtcwaXRUiWqLMcU7', 'อาคารกีฬาอเนกประสงค์', 'ขอเชิญชวนเข้าร่วมงานกีฬาเชื่อมสัมพันธ์กลุ่มวิทยาศาสตร์และเทคโนโลยี ครั้งที่ 13', '../assets/imgs/event/sci-tech.jpg', 1, '0.00', 'no'),
(19, 'งานมหกรรมหนังสือมหาวิทยาลัยนเรศวร NU Book Fair ครั้งที่ 25', 'kanokwanb65@nu.ac.th', '2024-12-18', '2024-12-24', '10:00:00', '19:30:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/zFGYpfMzm3wePLaU8', 'อาคารเฉลิมพระเกียรติ', 'งานมหกรรมหนังสือมหาวิทยาลัยนเรศวร NU Book Fair ครั้งที่ 25 #nubookfair #มหกรรมหนังสือ #มหาวิทยาลัยนเรศวร', '../assets/imgs/event/nu book fair.jpg', 1, '0.00', 'no'),
(20, 'โครงการมหกรรมศึกษาศาสตร์ EDU Festival 2025', 'kanokwanb65@nu.ac.th', '2025-01-17', '2025-01-18', '19:00:00', '21:00:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/7WUL9FNeb5U6hgcSA', 'คณะศึกษาศาสตร์', 'นับถอยหลัง 3 วันก่อนถึง…โครงการมหกรรมศึกษาศาสตร์ EDU Festival 2025 ตอน มนต์รักศึกษาศาสตร์', '../assets/imgs/event/edu fes.jpg', 1, '0.00', 'no'),
(21, 'Happy Workplace for all เฟส (three)', 'kanokwanb65@nu.ac.th', '2025-01-06', '2025-03-31', '16:30:00', '18:30:00', '0000-00-00', '00:00:00', 'https://maps.app.goo.gl/ZE8qayMckuJXG2176', 'อาคารปราบไตรจักร 2', 'เริ่มตั้งแต่วันที่ 6 ม.ค.-31 มี.ค.68 (สัปดาห์ละ 3 วัน เวลา 16.30-18.30 น.) ณ ห้องปราบไตรจักร 2-217 ขอเชิญชวนบุคลากรและนิสิตคณะสังคมศาสตร์', '../assets/imgs/event/happy.jpg', 1, '0.00', 'yes'),
(22, 'AAA', 'worapichayak65@nu.ac.th', '2025-04-09', '2025-04-09', '03:26:00', '03:26:00', '2025-04-10', '00:00:00', 'FFF', 'FFF', 'GFG', '../assets/imgs/event/openhouse.jpg', 6, '50.00', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `eventorganizer`
--

CREATE TABLE `eventorganizer` (
  `EventOrganizer_email` varchar(255) NOT NULL,
  `EventOrganizer_FirstName` varchar(100) NOT NULL,
  `EventOrganizer_LastName` varchar(100) NOT NULL,
  `FacultyID` int(11) DEFAULT NULL,
  `MajorID` int(11) DEFAULT NULL,
  `EventOrganizer_Password` varchar(255) NOT NULL,
  `EventOrganizer_Phone` varchar(20) DEFAULT NULL,
  `GenderID` int(11) NOT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `EventOrganizer_Role` enum('EventOrganizer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eventorganizer`
--

INSERT INTO `eventorganizer` (`EventOrganizer_email`, `EventOrganizer_FirstName`, `EventOrganizer_LastName`, `FacultyID`, `MajorID`, `EventOrganizer_Password`, `EventOrganizer_Phone`, `GenderID`, `StudentID`, `EventOrganizer_Role`) VALUES
('andy@gmail.com', 'Andy', 'Indy', NULL, NULL, '$2y$10$8NZ5lvp/ZxdK2L8i5WGhquV6X6esgbtwncNTitB5f8/KVL1pzydWW', '0987654321', 1, NULL, 'EventOrganizer'),
('eiei@gmail.com', 'Eiei', 'Eiei', 3, 2, '$2y$10$CDQ8IWPQWrjUEI4AArrkJOIEaFx0LmHMLVfRRRlp.YXcXZcnPhr4S', '0987654321', 2, NULL, 'EventOrganizer');

-- --------------------------------------------------------

--
-- Table structure for table `event_approvals`
--

CREATE TABLE `event_approvals` (
  `approval_id` int(11) NOT NULL,
  `request_event_id` int(11) NOT NULL,
  `President_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_date` datetime NOT NULL,
  `approval_status` enum('อนุมัติ','ไม่อนุมัติ') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_approvals`
--

INSERT INTO `event_approvals` (`approval_id`, `request_event_id`, `President_email`, `approved_date`, `approval_status`) VALUES
(0, 10, 'peter@gmail.com', '2025-04-07 18:37:05', 'อนุมัติ');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `FacultyID` int(11) NOT NULL,
  `Faculty_Name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`FacultyID`, `Faculty_Name`) VALUES
(1, 'Faculty of Dentistry'),
(2, 'Faculty of Nursing'),
(3, 'Faculty of Medical Science'),
(4, 'Faculty of Allied Health Sciences'),
(5, 'Faculty of Public Health'),
(6, 'Faculty of Pharmacy'),
(7, 'Faculty of Medicine'),
(8, 'Faculty of Agriculture, Natural Resources and Environment'),
(9, 'Faculty of Science'),
(10, 'Faculty of Engineering'),
(11, 'Faculty of Architecture, Art and Design'),
(12, 'Faculty of Logistics and Digital Supply Chain'),
(13, 'Faculty of Law'),
(14, 'Faculty of Business Administration, Economics and Communication'),
(15, 'Faculty of Humanities'),
(16, 'Faculty of Education'),
(17, 'Faculty of Social Sciences');

-- --------------------------------------------------------

--
-- Table structure for table `favorite`
--

CREATE TABLE `favorite` (
  `favorite_id` int(11) NOT NULL,
  `Audience_email` varchar(255) NOT NULL,
  `EventID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorite`
--

INSERT INTO `favorite` (`favorite_id`, `Audience_email`, `EventID`) VALUES
(11, 'jus@gamil.com', 15),
(13, 'jus@gamil.com', 13),
(14, 'jo@gmail.com', 13),
(15, 'kanokwanb65@nu.ac.th', 11),
(17, 'worapichayak65@nu.ac.th', 12),
(18, 'worapichayak65@nu.ac.th', 17),
(19, 'kanokwanb65@nu.ac.th', 19),
(20, 'worapichayak65@nu.ac.th', 19),
(21, 'jo@gmail.com', 19),
(22, 'k@gmail.com', 19),
(23, 'jus@gamil.com', 20),
(24, 'jus@gamil.com', 17);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `Audience_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `EventID` int(11) NOT NULL,
  `feedback_comment` text,
  `feedback_point` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `feedback_option` text
) ;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `Audience_email`, `EventID`, `feedback_comment`, `feedback_point`, `created_at`, `feedback_option`) VALUES
(10, 'jus@gamil.com', 1, 'RR', 15, '2025-02-27 10:28:36', '{\"\\u0e2a\\u0e16\\u0e32\\u0e19\\u0e17\\u0e35\\u0e48\\u0e40\\u0e2b\\u0e21\\u0e32\\u0e30\\u0e2a\\u0e21\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e23\\u0e49\\u0e32\\u0e19\\u0e04\\u0e49\\u0e32\":\"\\u0e21\\u0e32\\u0e01\"}'),
(11, 'jus@gamil.com', 1, 'เย่มๆๆๆ', 11, '2025-02-27 10:32:53', '{\"\\u0e2a\\u0e16\\u0e32\\u0e19\\u0e17\\u0e35\\u0e48\\u0e40\\u0e2b\\u0e21\\u0e32\\u0e30\\u0e2a\\u0e21\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\",\"\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\",\"\\u0e23\\u0e49\\u0e32\\u0e19\\u0e04\\u0e49\\u0e32\":\"\\u0e21\\u0e32\\u0e01\"}'),
(13, 'jus@gamil.com', 3, 'โหล่ยโท้ย', 11, '2025-02-27 12:37:35', '{\"\\u0e2a\\u0e16\\u0e32\\u0e19\\u0e17\\u0e35\\u0e48\\u0e40\\u0e2b\\u0e21\\u0e32\\u0e30\\u0e2a\\u0e21\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\",\"\\u0e23\\u0e49\\u0e32\\u0e19\\u0e04\\u0e49\\u0e32\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\"}'),
(14, 'jang@gmail.com', 1, 'ffffffffff', 11, '2025-03-01 05:58:49', '{\"\\u0e2a\\u0e16\\u0e32\\u0e19\\u0e17\\u0e35\\u0e48\\u0e40\\u0e2b\\u0e21\\u0e32\\u0e30\\u0e2a\\u0e21\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\",\"\\u0e23\\u0e49\\u0e32\\u0e19\\u0e04\\u0e49\\u0e32\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\"}'),
(15, 'jus@gamil.com', 13, 'DDDDDDDD', 15, '2025-03-28 16:42:58', '{\"\\u0e2a\\u0e16\\u0e32\\u0e19\\u0e17\\u0e35\\u0e48\\u0e40\\u0e2b\\u0e21\\u0e32\\u0e30\\u0e2a\\u0e21\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e23\\u0e49\\u0e32\\u0e19\\u0e04\\u0e49\\u0e32\":\"\\u0e21\\u0e32\\u0e01\"}'),
(16, 'jus@gamil.com', 13, 'KKKKKKK', 13, '2025-03-28 16:43:11', '{\"\\u0e2a\\u0e16\\u0e32\\u0e19\\u0e17\\u0e35\\u0e48\\u0e40\\u0e2b\\u0e21\\u0e32\\u0e30\\u0e2a\\u0e21\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\":\"\\u0e21\\u0e32\\u0e01\",\"\\u0e23\\u0e49\\u0e32\\u0e19\\u0e04\\u0e49\\u0e32\":\"\\u0e1b\\u0e32\\u0e19\\u0e01\\u0e25\\u0e32\\u0e07\"}');

-- --------------------------------------------------------

--
-- Table structure for table `gender`
--

CREATE TABLE `gender` (
  `GenderID` int(11) NOT NULL,
  `GenderType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gender`
--

INSERT INTO `gender` (`GenderID`, `GenderType`) VALUES
(1, 'Male'),
(2, 'Female'),
(3, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `major`
--

CREATE TABLE `major` (
  `MajorID` int(11) NOT NULL,
  `FacultyID` int(11) NOT NULL,
  `Major_Name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `major`
--

INSERT INTO `major` (`MajorID`, `FacultyID`, `Major_Name`) VALUES
(1, 3, 'Microbiology'),
(2, 3, 'Biochemistry and Molecular Biology'),
(3, 3, 'Anatomical Pathology'),
(4, 3, 'Medical Science'),
(5, 4, 'Physical Therapy'),
(6, 4, 'Radiologic Technology'),
(7, 4, 'Medical Technology'),
(8, 4, 'Cardiovascular and Thoracic Technology'),
(9, 5, 'Environmental Health'),
(10, 5, 'Occupational Health and Safety'),
(11, 5, 'Elderly Health Care and Management'),
(12, 5, 'Community Health'),
(13, 6, 'Cosmetic Science and Natural Products'),
(14, 6, 'Pharmaceutical Care'),
(15, 8, 'Natural Resources and Environment'),
(16, 8, 'Geography'),
(17, 8, 'Fisheries Science'),
(18, 8, 'Agricultural Science'),
(19, 8, 'Food Science and Technology'),
(20, 8, 'Animal Science and Feed Technology'),
(21, 8, 'Precision Agriculture'),
(22, 8, 'Agricultural Biotechnology'),
(23, 9, 'Mathematics'),
(24, 9, 'Biology'),
(25, 9, 'Physics'),
(26, 9, 'Applied Physics'),
(27, 9, 'Data Science and Analytics'),
(28, 9, 'Computer Science'),
(29, 9, 'Statistics'),
(30, 9, 'Chemistry'),
(31, 9, 'Measurement Technology and Intelligent Systems'),
(32, 9, 'Energy Innovation and Environmental Technology'),
(33, 9, 'Information Technology'),
(34, 10, 'Computer Engineering'),
(35, 10, 'Intelligent Innovation Engineering (English Program)'),
(36, 10, 'Materials Engineering'),
(37, 10, 'Environmental Engineering'),
(38, 10, 'Industrial Engineering'),
(39, 10, 'Chemical Engineering'),
(40, 10, 'Mechanical Engineering'),
(41, 10, 'Civil Engineering'),
(42, 10, 'Electrical Engineering'),
(43, 11, 'Architectural Technology'),
(44, 11, 'Innovative Media Design'),
(45, 11, 'Visual Arts'),
(46, 11, 'Product and Packaging Design'),
(47, 12, 'Logistics and Digital Supply Chain'),
(48, 12, 'Logistics and Digital Supply Chain (Continuing Program)'),
(49, 14, 'Mass Communication'),
(50, 14, 'Finance'),
(51, 14, 'Digital Business'),
(52, 14, 'Business Administration'),
(53, 14, 'Innovation and Creative Marketing'),
(54, 14, 'Tourism Management'),
(55, 15, 'Western Music'),
(56, 15, 'Thai Music'),
(57, 15, 'Thai Classical Dance'),
(58, 15, 'Myanmar Studies'),
(59, 15, 'Chinese Language'),
(60, 15, 'Japanese Language'),
(61, 15, 'French Language'),
(62, 15, 'English Language'),
(63, 15, 'Korean Language'),
(64, 15, 'Thai Language'),
(65, 16, 'Mathematics Education'),
(66, 16, 'Computer Education'),
(67, 16, 'Biology Education'),
(68, 16, 'Physical Education and Exercise Science'),
(69, 16, 'Physics Education'),
(70, 16, 'English Education'),
(71, 16, 'Thai Education'),
(72, 16, 'Chemistry Education'),
(73, 16, 'Educational Technology and Communication'),
(74, 17, 'Psychology'),
(75, 17, 'History'),
(76, 17, 'Social Development'),
(77, 17, 'Global Studies (Bilingual Program)'),
(78, 1, 'Dentistry'),
(79, 2, 'Nursing'),
(80, 7, 'Medicine'),
(81, 13, 'Law');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `Audience_email` varchar(255) NOT NULL,
  `favorite_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `Audience_email`, `favorite_id`, `created_at`) VALUES
(7, 'kanokwanb65@nu.ac.th', 15, '2025-03-29 03:46:01'),
(14, 'jus@gamil.com', 23, '2025-04-05 10:32:15');

-- --------------------------------------------------------

--
-- Table structure for table `president`
--

CREATE TABLE `president` (
  `President_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `President_firstname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `President_lastname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `President_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `President_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `President_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GenderID` int(11) NOT NULL,
  `President_Image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '../assets/imgs/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `president`
--

INSERT INTO `president` (`President_email`, `President_firstname`, `President_lastname`, `President_id`, `President_password`, `President_phone`, `GenderID`, `President_Image`) VALUES
('peter@gmail.com', 'Peter', 'Parker', '17644599', '$2y$10$mXvnH8ZHRAYOmr3Cb6DLc.iH/SJGgsuhpEfkYyO7M3l.v.YjProFW', '0123456789', 1, '../assets/imgs/default.png');

-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `register_id` int(11) NOT NULL,
  `Audience_email` varchar(255) NOT NULL,
  `EventID` int(11) NOT NULL,
  `Register_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `register_status` enum('pending','verifying','completed') DEFAULT NULL,
  `booking_id` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `register`
--

INSERT INTO `register` (`register_id`, `Audience_email`, `EventID`, `Register_date`, `register_status`, `booking_id`) VALUES
(51, 'jus@gamil.com', 13, '2025-03-27 20:37:35', 'verifying', 23),
(52, 'jo@gmail.com', 13, '2025-03-28 21:20:24', 'completed', 24),
(54, 'k@gmail.com', 13, '2025-03-28 21:21:12', 'completed', 25),
(55, 'kanokwanb65@nu.ac.th', 13, '2025-03-29 03:42:42', 'completed', 26),
(56, 'worapichayak65@nu.ac.th', 12, '2025-03-29 04:27:02', 'completed', 27),
(57, 'worapichayak65@nu.ac.th', 15, '2025-03-29 04:41:27', 'completed', 28);

-- --------------------------------------------------------

--
-- Table structure for table `request_event`
--

CREATE TABLE `request_event` (
  `request_event_id` int(11) NOT NULL,
  `Audience_email` varchar(255) NOT NULL,
  `request_event_status` enum('committee','president','completed','not completed','end') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_event_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `request_event_detail` text,
  `request_event_picture` text NOT NULL,
  `request_event_date` date NOT NULL,
  `request_event_saletime` time DEFAULT NULL,
  `request_event_saledate` date DEFAULT NULL,
  `request_event_time` time NOT NULL,
  `request_event_location` varchar(255) NOT NULL,
  `request_event_map` text NOT NULL,
  `request_event_price` decimal(10,2) NOT NULL,
  `request_event_type` enum('yes','no') NOT NULL,
  `request_event_budget` decimal(10,2) NOT NULL,
  `request_event_point` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_event`
--

INSERT INTO `request_event` (`request_event_id`, `Audience_email`, `request_event_status`, `request_event_name`, `category_id`, `request_event_detail`, `request_event_picture`, `request_event_date`, `request_event_saletime`, `request_event_saledate`, `request_event_time`, `request_event_location`, `request_event_map`, `request_event_price`, `request_event_type`, `request_event_budget`, `request_event_point`) VALUES
(10, 'kanokwanb65@nu.ac.th', 'completed', 'DYAB', 1, 'DDDDDSSSAAAAAA', '../assets/imgs/jusmine.png', '2025-04-18', '16:00:00', '2025-04-09', '09:00:00', 'NU', 'NU', '0.00', 'no', '45555.00', 4);

-- --------------------------------------------------------

--
-- Table structure for table `studentaffairs`
--

CREATE TABLE `studentaffairs` (
  `StudentAffairs_email` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `personal_id` varchar(20) NOT NULL,
  `GenderID` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `studentaffairs_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `StudentAffairs_Image` varchar(255) DEFAULT '../assets/imgs/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `studentaffairs`
--

INSERT INTO `studentaffairs` (`StudentAffairs_email`, `first_name`, `last_name`, `personal_id`, `GenderID`, `position`, `phone`, `studentaffairs_password`, `StudentAffairs_Image`) VALUES
('jack@gmail.com', 'jack', 'G', '45678', 1, 'StudentAffairs', '0456789012', '$2y$10$f3LXPR8bF1j24u7AArw.MuQd7Jn4idmtQA/y0nohWyVFGL5.NLA5O', '../assets/imgs/default.png');

-- --------------------------------------------------------

--
-- Table structure for table `studentsdata`
--

CREATE TABLE `studentsdata` (
  `email` varchar(100) NOT NULL,
  `student_id` varchar(8) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  `major` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `studentsdata`
--

INSERT INTO `studentsdata` (`email`, `student_id`, `first_name`, `last_name`, `faculty`, `major`) VALUES
('kanokwanb65@nu.ac.th', '65310029', 'Kanokwan', 'Boonyo', 'Science', 'Computer Science'),
('narawitp65@nu.ac.th', '65312399', 'Narawit', 'Nemsanit', 'Science', 'Computer Science'),
('natthidap65@nu.ac.th', '65310037', 'Natthida', 'Phonchai', 'Business', 'Marketing'),
('pakaphont65@nu.ac.th', '65313631', 'Pakaphon', 'Thaitae', 'Science', 'Computer Science'),
('tanagonk65@nu.ac.th', '65311835', 'Tanagon', 'khumnuan', 'Science', 'Computer Science'),
('thanapols65@nu.ac.th', '65311897', 'Thanapol', 'subsiriwan', 'Science', 'Computer Science'),
('thanawicht65@nu.ac.th', '65311972', 'Thanawich', 'Teerachainukul', 'Science', 'Computer Science'),
('trirongj65@nu.ac.th', '65310038', 'Trirong', 'Junsri', 'Political Science', 'International Relations'),
('witchayada65@nu.ac.th', '65314669', 'Witchayada', 'Kehathan', 'Science', 'Computer Science'),
('worapichayak65@nu.ac.th', '65314447', 'Worapichaya', 'Boontam', 'Science', 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `vote`
--

CREATE TABLE `vote` (
  `vote_id` int(11) NOT NULL,
  `committee_email` varchar(255) NOT NULL,
  `request_event_id` int(11) NOT NULL,
  `vote_status` enum('อนุมัติ','ไม่อนุมัติ') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vote`
--

INSERT INTO `vote` (`vote_id`, `committee_email`, `request_event_id`, `vote_status`) VALUES
(12, 'dean@gmail.com', 10, 'ไม่อนุมัติ'),
(13, 'ava@gmail.com', 10, 'อนุมัติ'),
(14, 'char@gmail.com', 10, 'อนุมัติ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audience`
--
ALTER TABLE `audience`
  ADD PRIMARY KEY (`Audience_email`),
  ADD KEY `fk_gender` (`GenderID`),
  ADD KEY `fk_faculty_audience` (`FacultyID`),
  ADD KEY `fk_major_audience` (`MajorID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `EventID` (`EventID`),
  ADD KEY `Audience_email` (`Audience_email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `categoriestwo`
--
ALTER TABLE `categoriestwo`
  ADD PRIMARY KEY (`CategoryTwo_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `committee`
--
ALTER TABLE `committee`
  ADD PRIMARY KEY (`committee_email`),
  ADD KEY `FacultyID` (`FacultyID`),
  ADD KEY `MajorID` (`MajorID`),
  ADD KEY `GenderID` (`GenderID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`EventID`),
  ADD KEY `fk_event_1` (`EventOrganizer_email`),
  ADD KEY `fk_event_2` (`Category_ID`);

--
-- Indexes for table `eventorganizer`
--
ALTER TABLE `eventorganizer`
  ADD PRIMARY KEY (`EventOrganizer_email`),
  ADD KEY `fk_organizer_1` (`GenderID`),
  ADD KEY `fk_organizer_2` (`FacultyID`),
  ADD KEY `fk_organizer_3` (`MajorID`);

--
-- Indexes for table `event_approvals`
--
ALTER TABLE `event_approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `fk_approve_1` (`request_event_id`),
  ADD KEY `fk_approve_2` (`President_email`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`FacultyID`);

--
-- Indexes for table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `fk_favorite_1` (`Audience_email`),
  ADD KEY `fk_favorite_2` (`EventID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `audience_email` (`Audience_email`),
  ADD KEY `EventID` (`EventID`);

--
-- Indexes for table `gender`
--
ALTER TABLE `gender`
  ADD PRIMARY KEY (`GenderID`);

--
-- Indexes for table `major`
--
ALTER TABLE `major`
  ADD PRIMARY KEY (`MajorID`),
  ADD KEY `fk_faculty` (`FacultyID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_noti_1` (`Audience_email`),
  ADD KEY `fk_noti_2` (`favorite_id`);

--
-- Indexes for table `president`
--
ALTER TABLE `president`
  ADD PRIMARY KEY (`President_email`),
  ADD KEY `fk_president_1` (`GenderID`);

--
-- Indexes for table `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`register_id`),
  ADD KEY `Audience_email` (`Audience_email`),
  ADD KEY `EventID` (`EventID`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `request_event`
--
ALTER TABLE `request_event`
  ADD PRIMARY KEY (`request_event_id`),
  ADD KEY `Audience_email` (`Audience_email`);

--
-- Indexes for table `studentaffairs`
--
ALTER TABLE `studentaffairs`
  ADD PRIMARY KEY (`StudentAffairs_email`),
  ADD KEY `fk_affair_1` (`GenderID`);

--
-- Indexes for table `studentsdata`
--
ALTER TABLE `studentsdata`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `committee_email` (`committee_email`),
  ADD KEY `fk_vote_2` (`request_event_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categoriestwo`
--
ALTER TABLE `categoriestwo`
  MODIFY `CategoryTwo_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `EventID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `favorite`
--
ALTER TABLE `favorite`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `register`
--
ALTER TABLE `register`
  MODIFY `register_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `request_event`
--
ALTER TABLE `request_event`
  MODIFY `request_event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vote`
--
ALTER TABLE `vote`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audience`
--
ALTER TABLE `audience`
  ADD CONSTRAINT `fk_faculty_audience` FOREIGN KEY (`FacultyID`) REFERENCES `faculty` (`FacultyID`),
  ADD CONSTRAINT `fk_gender` FOREIGN KEY (`GenderID`) REFERENCES `gender` (`GenderID`),
  ADD CONSTRAINT `fk_major_audience` FOREIGN KEY (`MajorID`) REFERENCES `major` (`MajorID`);

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`Audience_email`) REFERENCES `audience` (`Audience_email`);

--
-- Constraints for table `categoriestwo`
--
ALTER TABLE `categoriestwo`
  ADD CONSTRAINT `categoriestwo_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `committee`
--
ALTER TABLE `committee`
  ADD CONSTRAINT `committee_ibfk_1` FOREIGN KEY (`FacultyID`) REFERENCES `faculty` (`FacultyID`),
  ADD CONSTRAINT `committee_ibfk_2` FOREIGN KEY (`MajorID`) REFERENCES `major` (`MajorID`),
  ADD CONSTRAINT `committee_ibfk_3` FOREIGN KEY (`GenderID`) REFERENCES `gender` (`GenderID`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `fk_event_1` FOREIGN KEY (`EventOrganizer_email`) REFERENCES `audience` (`Audience_email`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_event_2` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `eventorganizer`
--
ALTER TABLE `eventorganizer`
  ADD CONSTRAINT `fk_organizer_1` FOREIGN KEY (`GenderID`) REFERENCES `gender` (`GenderID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_organizer_2` FOREIGN KEY (`FacultyID`) REFERENCES `faculty` (`FacultyID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_organizer_3` FOREIGN KEY (`MajorID`) REFERENCES `major` (`MajorID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `event_approvals`
--
ALTER TABLE `event_approvals`
  ADD CONSTRAINT `fk_approve_1` FOREIGN KEY (`request_event_id`) REFERENCES `request_event` (`request_event_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_approve_2` FOREIGN KEY (`President_email`) REFERENCES `president` (`President_email`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `fk_favorite_1` FOREIGN KEY (`Audience_email`) REFERENCES `audience` (`Audience_email`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_favorite_2` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`Audience_email`) REFERENCES `audience` (`Audience_email`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`) ON DELETE CASCADE;

--
-- Constraints for table `major`
--
ALTER TABLE `major`
  ADD CONSTRAINT `fk_faculty` FOREIGN KEY (`FacultyID`) REFERENCES `faculty` (`FacultyID`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_noti_1` FOREIGN KEY (`Audience_email`) REFERENCES `audience` (`Audience_email`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_noti_2` FOREIGN KEY (`favorite_id`) REFERENCES `favorite` (`favorite_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `president`
--
ALTER TABLE `president`
  ADD CONSTRAINT `fk_president_1` FOREIGN KEY (`GenderID`) REFERENCES `gender` (`GenderID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `register`
--
ALTER TABLE `register`
  ADD CONSTRAINT `fk_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`),
  ADD CONSTRAINT `register_ibfk_1` FOREIGN KEY (`Audience_email`) REFERENCES `audience` (`Audience_email`),
  ADD CONSTRAINT `register_ibfk_2` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`);

--
-- Constraints for table `request_event`
--
ALTER TABLE `request_event`
  ADD CONSTRAINT `request_event_ibfk_1` FOREIGN KEY (`Audience_email`) REFERENCES `audience` (`Audience_email`);

--
-- Constraints for table `studentaffairs`
--
ALTER TABLE `studentaffairs`
  ADD CONSTRAINT `fk_affair_1` FOREIGN KEY (`GenderID`) REFERENCES `gender` (`GenderID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `fk_vote_1` FOREIGN KEY (`committee_email`) REFERENCES `committee` (`committee_email`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_vote_2` FOREIGN KEY (`request_event_id`) REFERENCES `request_event` (`request_event_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
