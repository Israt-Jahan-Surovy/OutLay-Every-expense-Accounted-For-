-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 12:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `expense_budget_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `approvaltable`
--

CREATE TABLE `approvaltable` (
  `approval_id` int(11) NOT NULL,
  `approval_date` date NOT NULL,
  `approval_status` enum('Approved','Rejected') NOT NULL,
  `rejected_reason` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvaltable`
--

INSERT INTO `approvaltable` (`approval_id`, `approval_date`, `approval_status`, `rejected_reason`, `user_id`, `expense_id`) VALUES
(17, '2026-09-15', 'Approved', NULL, 11, 20),
(18, '2026-09-15', 'Approved', NULL, 11, 19);

-- --------------------------------------------------------

--
-- Table structure for table `budgettable`
--

CREATE TABLE `budgettable` (
  `budget_id` int(11) NOT NULL,
  `budget_type` enum('Manager','Employee') NOT NULL,
  `budget_month` date NOT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `parent_budget_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgettable`
--

INSERT INTO `budgettable` (`budget_id`, `budget_type`, `budget_month`, `budget_amount`, `assigned_by`, `assigned_to`, `parent_budget_id`) VALUES
(9, 'Manager', '2026-09-01', 20000.00, 11, 13, NULL),
(10, 'Manager', '2026-09-01', 30000.00, 11, 12, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categorytable`
--

CREATE TABLE `categorytable` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorytable`
--

INSERT INTO `categorytable` (`category_id`, `category_name`) VALUES
(1, 'Food'),
(5, 'Medical'),
(3, 'Office Supplies'),
(2, 'Transport'),
(4, 'Travel');

-- --------------------------------------------------------

--
-- Table structure for table `expensetable`
--

CREATE TABLE `expensetable` (
  `expense_id` int(11) NOT NULL,
  `expense_title` varchar(150) NOT NULL,
  `expense_amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `expense_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `expense_description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expensetable`
--

INSERT INTO `expensetable` (`expense_id`, `expense_title`, `expense_amount`, `expense_date`, `expense_status`, `expense_description`, `user_id`, `category_id`) VALUES
(19, 'lunch with client', 2000.00, '2026-09-13', 'Approved', 'had lunch with a client at ABC restaurant', 12, 1),
(20, 'Team Outing', 2400.00, '2026-09-14', 'Approved', 'Team outing to XYZ site', 12, 4);

-- --------------------------------------------------------

--
-- Table structure for table `usertable`
--

CREATE TABLE `usertable` (
  `user_id` int(11) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_role` enum('Admin','Manager','Employee') NOT NULL,
  `user_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `user_avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertable`
--

INSERT INTO `usertable` (`user_id`, `manager_id`, `user_name`, `user_email`, `user_password`, `user_role`, `user_status`, `user_avatar`) VALUES
(11, NULL, 'System Admin', 'admin@company.com', '$2y$10$DPoCP9SSgnu//U33lpu3N.2mD0y5V.emcuNPawRgDRdmoZ/fV/IYe', 'Admin', 'Active', NULL),
(12, NULL, 'Maisha Mahjabin', 'maisha@company.com', '$2y$10$0fl5yp4EcG3IWxw.lWBiU.bi14pHUS0IMFBPFtmdStm7GItYKU0Na', 'Manager', 'Active', NULL),
(13, NULL, 'Israt Jahan Surovy', 'surovy@company.com', '$2y$10$Wm2OAQPpQ8Ga2ao9Y9m4Ne.QuXFZh0IVdC8RujOwXSLRo8SufibGi', 'Employee', 'Active', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approvaltable`
--
ALTER TABLE `approvaltable`
  ADD PRIMARY KEY (`approval_id`),
  ADD UNIQUE KEY `expense_id` (`expense_id`),
  ADD KEY `fk_approval_user` (`user_id`);

--
-- Indexes for table `budgettable`
--
ALTER TABLE `budgettable`
  ADD PRIMARY KEY (`budget_id`),
  ADD KEY `fk_budget_assigned_by` (`assigned_by`),
  ADD KEY `fk_budget_assigned_to` (`assigned_to`),
  ADD KEY `fk_budget_parent` (`parent_budget_id`);

--
-- Indexes for table `categorytable`
--
ALTER TABLE `categorytable`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `expensetable`
--
ALTER TABLE `expensetable`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `fk_expense_user` (`user_id`),
  ADD KEY `fk_expense_category` (`category_id`);

--
-- Indexes for table `usertable`
--
ALTER TABLE `usertable`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvaltable`
--
ALTER TABLE `approvaltable`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `budgettable`
--
ALTER TABLE `budgettable`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categorytable`
--
ALTER TABLE `categorytable`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `expensetable`
--
ALTER TABLE `expensetable`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `usertable`
--
ALTER TABLE `usertable`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approvaltable`
--
ALTER TABLE `approvaltable`
  ADD CONSTRAINT `fk_approval_expense` FOREIGN KEY (`expense_id`) REFERENCES `expensetable` (`expense_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_approval_user` FOREIGN KEY (`user_id`) REFERENCES `usertable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `budgettable`
--
ALTER TABLE `budgettable`
  ADD CONSTRAINT `fk_budget_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `usertable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_budget_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `usertable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_budget_parent` FOREIGN KEY (`parent_budget_id`) REFERENCES `budgettable` (`budget_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `expensetable`
--
ALTER TABLE `expensetable`
  ADD CONSTRAINT `fk_expense_category` FOREIGN KEY (`category_id`) REFERENCES `categorytable` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expense_user` FOREIGN KEY (`user_id`) REFERENCES `usertable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
