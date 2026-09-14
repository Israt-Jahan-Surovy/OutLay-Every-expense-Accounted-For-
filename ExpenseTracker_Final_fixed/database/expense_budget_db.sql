-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2026 at 01:06 PM
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
(1, '2026-09-13', 'Rejected', 'Budget limit exceeded for team lunch', 2, 1),
(2, '2026-09-13', 'Approved', NULL, 2, 4),
(3, '2026-09-13', 'Rejected', 'Investor meeting expense exceeds travel policy', 2, 2),
(4, '2026-09-13', 'Approved', NULL, 2, 3),
(5, '2026-09-14', 'Approved', NULL, 1, 12),
(10, '2026-09-14', 'Rejected', 'Over budget', 1, 7),
(11, '2026-09-14', 'Approved', NULL, 2, 15),
(12, '2026-09-14', 'Rejected', 'No proper desciption added', 1, 6),
(13, '2026-09-14', 'Rejected', 'Not accepted', 2, 13),
(14, '2026-09-14', 'Approved', NULL, 2, 17);

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
(1, 'Employee', '2026-09-01', 2200.00, 2, 3, NULL),
(2, 'Manager', '2026-09-01', 12800.00, 1, 2, NULL),
(3, 'Manager', '2026-09-01', 25000.00, 1, 5, NULL),
(4, 'Employee', '2026-09-01', 3800.00, 2, 6, NULL),
(5, 'Employee', '2026-09-01', 200.00, 2, 6, NULL),
(6, 'Employee', '2026-09-01', 1200.00, 2, 3, NULL);

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
(1, 'Lunch with team manage', 2000.00, '2026-09-09', 'Rejected', '', 3, 1),
(2, 'Meeting with investor', 2500.00, '2026-09-10', 'Rejected', 'Have to meet with the investors for meeting', 3, 2),
(3, 'Travel to meet foreign client', 50000.00, '2026-09-12', 'Approved', 'Meeting with foreign client', 3, 4),
(4, 'Dinner with team', 1000.00, '2026-09-14', 'Approved', 'Dinner with team after office', 3, 1),
(5, 'Travel with team', 3000.00, '2026-09-15', 'Pending', 'travel with team for a meeting', 2, 4),
(6, 'Breakfast with team', 3000.00, '2026-09-15', 'Rejected', 'nzowinw', 2, 1),
(7, 'bsxbkj', 4589.00, '2026-09-16', 'Rejected', 'swmlkw', 2, 3),
(8, 'Office supplies needed', 2700.00, '2026-09-18', 'Pending', 'enieijejd', 2, 3),
(9, 'Lunch Bill', 1000.00, '2026-09-01', 'Approved', 'Team lunch', 1, 1),
(10, 'Taxi Fare', 2000.00, '2026-08-30', 'Pending', 'Client meet travel', 2, 2),
(11, 'Team Lunch', 1000.00, '2026-09-01', 'Approved', 'Food expense', 1, 1),
(12, 'Travel Allowance', 2000.00, '2026-08-30', 'Approved', 'Taxi fare for client meeting', 2, 2),
(13, 'Client Lunch', 1000.00, '2026-09-01', 'Rejected', 'Lunch with client', 3, 1),
(15, 'Uber fee', 1200.00, '2026-09-12', 'Approved', 'Meeting with a client for which a transportation fee needed to be paid', 3, 2),
(17, 'Doctor treatment', 1000.00, '2026-09-13', 'Approved', 'Needed doctor to treat chest ache problem', 6, 5);

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
  `user_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertable`
--

INSERT INTO `usertable` (`user_id`, `manager_id`, `user_name`, `user_email`, `user_password`, `user_role`, `user_status`) VALUES
(1, NULL, 'Admin', 'admin@company.com', '$12ab', 'Admin', 'Active'),
(2, 1, 'Fatima Rahman', 'manager@company.com', '$2345', 'Manager', 'Active'),
(3, 2, 'Rahim hasan arko', 'employee1@company.com', '$1234', 'Employee', 'Active'),
(5, NULL, 'Rifat Haider', 'rifat@company.com', '$12345', 'Manager', 'Active'),
(6, 2, 'Arni Hasan', 'arni@company.com', '$23900', 'Employee', 'Active');

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
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `budgettable`
--
ALTER TABLE `budgettable`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categorytable`
--
ALTER TABLE `categorytable`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `expensetable`
--
ALTER TABLE `expensetable`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `usertable`
--
ALTER TABLE `usertable`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
