-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2025 at 09:39 AM
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
-- Database: `dental_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action_type`, `details`, `log_timestamp`) VALUES
(1, 1, 'Appointment Completed', 'Marked appointment for patient Nanami Kento (Appt ID: 17) as Completed.', '2025-09-11 00:11:47'),
(2, 1, 'Appointment Completed', 'Marked appointment for patient Kanata Katagiri (Appt ID: 19) as Completed.', '2025-09-11 00:26:48'),
(3, 1, 'Appointment Completed', 'Marked appointment for patient Nanami Kento (Appt ID: 17) as Completed.', '2025-09-11 00:27:04'),
(4, 1, 'Appointment Completed', 'Marked appointment for patient Ichigo Kurosaki (Appt ID: 18) as Completed.', '2025-09-11 00:27:08'),
(5, 5, 'Appointment Completed', 'Marked appointment (ID: 17) for Patient ID: 28 as Completed.', '2025-09-11 00:36:41'),
(6, 1, 'Appointment Edited', 'Edited appointment (ID: 18). Changes: Status changed from \'Completed\' to \'No-Show\'.', '2025-09-11 00:44:35'),
(7, 1, 'Appointment Edited', 'Edited appointment (ID: 18). Changes: Status changed from \'No-Show\' to \'Completed\'.', '2025-09-11 00:45:04'),
(8, 1, 'Appointment Completed', 'Marked appointment (ID: 17) for patient \'Nanami Kento\' as Completed.', '2025-09-11 00:45:13'),
(9, 1, 'Appointment Edited', 'Edited appointment for patient \'Nanami Kento\' (Appt ID: 17). Changes: Status changed from \'Completed\' to \'Scheduled\'.', '2025-09-11 00:50:19'),
(10, 1, 'Appointment Completed', 'Marked appointment (ID: 17) for patient \'Nanami Kento\' as Completed.', '2025-09-11 00:50:33'),
(11, 1, 'Appointment Edited', 'Edited appointment for patient \'Nanami Kento\' (Appt ID: 17). Changes: Status changed from \'Completed\' to \'Scheduled\'.', '2025-09-11 00:51:05'),
(12, 5, 'Appointment Completed', 'Marked appointment (ID: 17) for patient \'Nanami Kento\' as Completed.', '2025-09-11 00:51:32'),
(13, 1, 'Appointment Deleted', 'Deleted appointment for patient \'Kanata Katagiri\' (scheduled for Sep 11, 2025 @ 08:18 AM - Service: fsdfasdf).', '2025-09-11 01:11:55'),
(14, 1, 'Appointment Edited', 'Edited appointment for patient \'Kanata Katagiri\' (Appt ID: 20). Changes: Patient changed from \'Tetsuya Kuroko\' to \'Kanata Katagiri\'; Dentist changed from \'Dr. Maynard\' to \'Dr. Bokbok\'; Service/Reason was updated..', '2025-09-11 01:16:00'),
(15, 1, 'Appointment Edited', 'Edited appointment for patient \'Ichigo Kurosaki\' (Appt ID: 20). Changes: Patient changed from \'Kanata Katagiri\' to \'Ichigo Kurosaki\'.', '2025-09-11 01:16:07'),
(16, 1, 'Appointment Edited', 'Edited appointment for patient \'Ichigo Kurosaki\' (Appt ID: 20). Changes: Status changed from \'Scheduled\' to \'Completed\'.', '2025-09-11 01:16:13'),
(17, 1, 'Appointment Deleted', 'Deleted appointment for patient \'Ichigo Kurosaki\' (scheduled for Sep 25, 2025 @ 09:00 AM - Service: dfasda).', '2025-09-11 01:16:35'),
(18, 1, 'Appointment Edited', 'Edited appointment for patient \'Tetsuya Kuroko\' (Appt ID: 21). Changes: Time changed from Sep 21, 2025 @ 11:53 PM to Sep 30, 2025 @ 11:52 AM.', '2025-09-21 03:52:57'),
(19, 1, 'Login Failed', 'Failed login attempt for username \'admin\' (Incorrect password).', '2025-09-21 07:03:42'),
(20, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-21 07:03:58'),
(21, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-21 07:04:29'),
(22, 3, 'Login Failed', 'Failed login attempt for username \'dentist\' (Incorrect password).', '2025-09-21 07:05:59'),
(23, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-09-21 07:06:06'),
(24, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-21 07:06:28'),
(25, 1, 'User Created', 'Created a new user account for \'katagiri\' (Username: kanata, Role: Dentist).', '2025-09-21 07:12:58'),
(26, 1, 'User Edited', 'Updated profile for user \'katagiri\'. Details changed (Password updated: No).', '2025-09-21 07:13:15'),
(27, 1, 'User Edited', 'Updated profile for user \'katagiri\'. Details changed (Password updated: No).', '2025-09-21 07:13:53'),
(28, 1, 'User Deleted', 'Permanently deleted the user account for \'katagiri\'.', '2025-09-21 07:24:49'),
(29, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-23 05:37:36'),
(30, 1, 'Appointment Deleted', 'Deleted appointment for patient \'Nanami Kento\' (scheduled for Sep 24, 2025 @ 09:00 AM - Service: asdfsadf).', '2025-09-23 08:37:02'),
(31, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-23 10:48:37'),
(32, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-23 10:54:20'),
(33, 1, 'Appointment Edited', 'Edited appointment for patient \'Yuki Itadori\' (Appt ID: 22). Changes: Status changed from \'Scheduled\' to \'Completed\'.', '2025-09-23 11:05:29'),
(34, 1, 'Appointment Edited', 'Edited appointment for patient \'Tetsuya Kuroko\' (Appt ID: 21). Changes: Time changed from Oct 02, 2025 @ 11:52 AM to Oct 02, 2025 @ 05:01 PM.', '2025-09-23 11:09:12'),
(35, 1, 'User Created', 'Created a new user account for \'yuju\' (Username: dentist3, Role: Dentist).', '2025-09-23 11:10:40'),
(36, 1, 'User Edited', 'Updated profile for user \'yuju\'. Details changed (Password updated: Yes).', '2025-09-23 11:10:56'),
(37, 8, 'Login Failed', 'Failed login attempt for username \'dentist3\' (Incorrect password).', '2025-09-23 11:15:40'),
(38, 8, 'Login Success', 'User \'yuju\' logged in successfully.', '2025-09-23 11:15:48'),
(39, 8, 'Appointment Edited', 'Edited appointment for patient \'Yuki Itadori\' (Appt ID: 22). Changes: Status changed from \'Completed\' to \'No-Show\'.', '2025-09-23 11:17:41'),
(40, 2, 'Login Success', 'User \'Mang Kepweng\' logged in successfully.', '2025-09-23 11:18:11'),
(41, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-09-23 11:19:03'),
(42, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 10:31:37'),
(43, 1, 'Appointment Completed', 'Marked appointment (Appt ID: 23) for patient \'Yuki Itadori\' (Patient ID: 32) as Completed.', '2025-10-15 11:07:38'),
(44, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 15:35:13'),
(45, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 16:07:54'),
(46, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 16:16:32'),
(47, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 16:29:43'),
(48, 3, 'Login Failed', 'Failed login attempt for username \'dentist \' (Incorrect password).', '2025-10-15 17:27:25'),
(49, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-15 17:27:49'),
(50, 5, 'Appointment Deleted', 'Deleted appointment for patient \'Tetsuya Kuroko\' (scheduled for Oct 09, 2025 @ 05:01 PM - Service: fdasdf).', '2025-10-15 17:49:04'),
(51, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-15 18:03:05'),
(52, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 18:19:49'),
(53, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 18:50:32'),
(54, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-15 18:53:38'),
(55, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 18:54:27'),
(56, 1, 'User Edited', 'Updated profile for user \'Dr. Maynard\'. Details changed (Password updated: Yes).', '2025-10-15 20:07:23'),
(57, 1, 'User Edited', 'Updated profile for user \'Dr. Maynard\'. Details changed (Password updated: No).', '2025-10-15 20:07:28'),
(58, 1, 'User Deleted', 'Permanently deleted the user account for \'yuju\'.', '2025-10-15 20:07:40'),
(59, 5, 'Login Failed', 'Failed login attempt for username \'dentist2\' (Incorrect password).', '2025-10-15 20:29:36'),
(60, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-15 20:29:48'),
(61, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 20:30:41'),
(62, 1, 'Appointment Edited', 'Edited appointment for patient \'Khenriev Bituin\' (Appt ID: 27). Changes: Patient changed from \'laromi kalami\' to \'Khenriev Bituin\'.', '2025-10-15 21:49:59'),
(63, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 21:50:54'),
(64, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-15 21:57:13'),
(65, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 01:25:54'),
(66, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 01:40:32'),
(67, 1, 'User Status Changed', 'Status for user \'Dr. Quack Quack\' was changed to \'On Leave\'.', '2025-10-16 02:24:03'),
(68, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 02:24:14'),
(69, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 02:24:25'),
(70, 1, 'User Status Changed', 'Status for user \'Dr. Quack Quack\' was changed to \'Available\'.', '2025-10-16 02:25:08'),
(71, 1, 'User Edited', 'Updated profile for user \'Dr. Bokbok\'. Details changed (Password updated: No).', '2025-10-16 02:31:35'),
(72, 1, 'User Status Changed', 'Status for user \'Dr. Bokbok\' was changed to \'Available\'.', '2025-10-16 02:31:40'),
(73, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 02:35:17'),
(74, 2, 'Login Success', 'User \'Mang Kepweng\' logged in successfully.', '2025-10-16 02:37:08'),
(75, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 02:48:47'),
(76, 1, 'Appointment Deleted', 'Deleted appointment for patient \'Khenriev Bituin\' (scheduled for Oct 10, 2025 @ 09:00 AM - Service: asdas).', '2025-10-16 03:01:20'),
(77, 1, 'User Created', 'Created a new user account for \'Dr. Kali\' (Username: kalil, Role: Dentist).', '2025-10-16 03:21:04'),
(78, 1, 'User Edited', 'Updated profile for user \'Dr. Bokbok\'. Details changed (Password updated: No).', '2025-10-16 03:22:17'),
(79, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 03:44:56'),
(80, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Sick Day\'.', '2025-10-16 03:47:25'),
(81, 1, 'Appointment Deleted', 'Deleted appointment for patient \'Khenriev Bituin\' (scheduled for Oct 10, 2025 @ 09:00 AM - Service: ..).', '2025-10-16 03:47:58'),
(82, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 03:48:10'),
(83, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-16 03:56:39'),
(84, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 05:10:33'),
(85, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 05:34:16'),
(86, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 05:34:37'),
(87, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 05:55:48'),
(88, 1, 'Appointment Deleted', 'Deleted appointment for patient \'nanimo kalahi\' (scheduled for Oct 16, 2025 @ 02:03 PM - Service: asdasdasdasdas).', '2025-10-16 06:04:08'),
(89, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 06:19:25'),
(90, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 06:19:44'),
(91, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 06:30:24'),
(92, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 06:31:19'),
(93, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 06:42:06'),
(94, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 06:55:46'),
(95, 2, 'Login Success', 'User \'Mang Kepweng\' logged in successfully.', '2025-10-16 07:07:07'),
(96, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 07:08:34'),
(97, 1, 'Appointment Edited', 'Edited appointment for patient \'Yuki Itadori\' (Appt ID: 23). Changes: Time changed from Oct 16, 2025 @ 12:00 AM to Oct 16, 2025 @ 08:00 AM.', '2025-10-16 07:09:04'),
(98, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-16 07:58:15'),
(99, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 07:58:55'),
(100, 1, 'User Edited', 'Updated profile for user \'Dr. Bokbok\'. Details changed (Password updated: No).', '2025-10-16 08:15:12'),
(101, 1, 'User Edited', 'Updated profile for user \'Dr. Bokbok\'. Details changed (Password updated: No).', '2025-10-16 08:15:30'),
(102, 1, 'User Status Changed', 'Status for user \'Dr. Bokbok\' was changed to \'Available\'.', '2025-10-16 08:15:34'),
(103, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 08:15:42'),
(104, 1, 'User Status Changed', 'Status for user \'Dr. Kali\' was changed to \'On Leave\'.', '2025-10-16 08:15:55'),
(105, 1, 'User Status Changed', 'Status for user \'Dr. Kali\' was changed to \'Available\'.', '2025-10-16 08:16:00'),
(106, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 08:16:02'),
(107, 1, 'User Status Changed', 'Status for user \'Dr. Bokbok\' was changed to \'On Leave\'.', '2025-10-16 08:26:18'),
(108, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Training\'.', '2025-10-16 08:26:30'),
(109, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 08:26:32'),
(110, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 08:26:34'),
(111, 1, 'User Status Changed', 'Status for user \'Dr. Bokbok\' was changed to \'Available\'.', '2025-10-16 08:26:35'),
(112, 5, 'Login Success', 'User \'Dr. Maynard\' logged in successfully.', '2025-10-16 08:41:35'),
(113, 5, 'Appointment Edited', 'Edited appointment for patient \'Yuki Itadori\' (Appt ID: 23). Changes: Time changed from Oct 16, 2025 @ 12:00 AM to Oct 16, 2025 @ 08:00 AM.', '2025-10-16 08:42:00'),
(114, 2, 'Login Success', 'User \'Mang Kepweng\' logged in successfully.', '2025-10-16 08:47:28'),
(115, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 08:58:20'),
(116, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 09:15:00'),
(117, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'On Leave\'.', '2025-10-16 09:16:09'),
(118, 1, 'Login Success', 'User \'Dr. Juan Dela Cruz\' logged in successfully.', '2025-10-16 09:18:11'),
(119, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Training\'.', '2025-10-16 09:23:18'),
(120, 1, 'User Status Changed', 'Status for user \'Dr. Juan Dela Cruz\' was changed to \'Available\'.', '2025-10-16 09:46:18');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `service_description` text NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled','No-Show','Pending Approval') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `dentist_id`, `appointment_date`, `service_description`, `status`, `created_at`) VALUES
(8, 27, 6, '2025-08-12 21:02:00', 'asd', 'Completed', '2025-08-10 13:03:10'),
(9, 26, 1, '2025-08-26 13:00:00', 'check up', 'Completed', '2025-08-25 23:38:58'),
(10, 27, 3, '2025-08-26 17:00:00', 'follow up', 'Scheduled', '2025-08-25 23:39:32'),
(11, 28, 1, '2025-09-07 15:24:00', 'lorem ipsum', 'Scheduled', '2025-09-07 04:24:37'),
(12, 27, 5, '2025-09-07 13:41:00', 'asdf', 'Scheduled', '2025-09-07 05:41:21'),
(16, 26, 1, '2025-09-01 09:00:00', 'asdfsdafsdf', 'Completed', '2025-09-11 00:05:52'),
(18, 27, 6, '2025-09-24 09:00:00', 'fdfasdf', 'Completed', '2025-09-11 00:17:51'),
(22, 32, 1, '2025-09-24 07:00:00', 'Cleaning', 'No-Show', '2025-09-23 11:04:58'),
(23, 32, 5, '2025-10-16 08:00:00', 'asdf', 'Completed', '2025-10-15 10:56:23'),
(24, 15, 1, '2025-10-15 17:35:02', 'lorem ipsum', 'Scheduled', '2025-10-15 15:35:02'),
(25, 33, 1, '2025-10-15 10:07:44', 'loremipsum', 'Scheduled', '2025-10-15 16:07:44'),
(26, 33, 1, '2025-10-13 21:30:00', 'PATIENT REQUEST: asdfasdf', 'Completed', '2025-10-15 16:16:23'),
(27, 31, 1, '2025-10-23 03:00:00', 'PATIENT REQUEST: asdfasdf', 'Scheduled', '2025-10-15 16:29:37'),
(28, 33, 1, '2025-10-30 15:30:00', 'PATIENT REQUEST: fdsadf', 'Scheduled', '2025-10-15 18:02:52'),
(29, 32, 6, '2025-11-01 08:00:00', 'asd', 'Completed', '2025-10-15 21:13:21'),
(30, 33, 1, '2025-10-30 16:00:00', 'PATIENT REQUEST: sdfsdf', 'Scheduled', '2025-10-15 21:50:45'),
(31, 34, 1, '2025-10-17 14:30:00', 'PATIENT REQUEST: asdfsdf', 'Scheduled', '2025-10-15 21:57:07'),
(35, 36, 9, '2025-10-31 14:00:00', 'PATIENT REQUEST: Follow-up Check', 'Scheduled', '2025-10-16 06:19:10'),
(36, 33, 1, '2025-10-27 11:30:00', 'PATIENT REQUEST: Root Canal Treatment', 'Cancelled', '2025-10-16 06:31:10'),
(37, 33, 1, '2025-10-28 15:00:00', 'PATIENT REQUEST: Root Canal Treatment', 'Cancelled', '2025-10-16 06:41:51'),
(38, 33, 1, '2025-10-29 15:00:00', 'PATIENT REQUEST: Veneer Placement', 'Cancelled', '2025-10-16 08:42:33'),
(39, 33, 1, '2025-10-29 13:30:00', 'PATIENT REQUEST: Veneer Placement', 'Scheduled', '2025-10-16 08:47:11'),
(40, 38, 5, '2025-11-03 10:00:00', 'PATIENT REQUEST: Veneer Placement', 'Scheduled', '2025-10-16 09:14:47'),
(41, 39, 1, '2025-11-03 14:30:00', 'PATIENT REQUEST: Veneer Placement', 'Cancelled', '2025-10-16 09:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `clinical_findings`
--

CREATE TABLE `clinical_findings` (
  `finding_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `finding_date` date NOT NULL,
  `custom_findings_notes` text DEFAULT NULL,
  `diagnosis` text NOT NULL,
  `custom_treatment_notes` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinical_findings`
--

INSERT INTO `clinical_findings` (`finding_id`, `patient_id`, `dentist_id`, `finding_date`, `custom_findings_notes`, `diagnosis`, `custom_treatment_notes`, `remarks`, `created_at`) VALUES
(4, 24, 2, '2025-08-07', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', '2025-08-07 02:27:41'),
(6, 26, 1, '2025-08-26', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', '2025-08-25 23:42:01'),
(17, 29, 1, '2025-09-23', 'qweqwe', 'qweqweqwe', 'ewqwe', 'weqweqw', '2025-09-23 09:03:10'),
(18, 15, 1, '2025-09-23', '123', 'qweqwe', 'qweqw', 'eqweqwe', '2025-09-23 09:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `clinical_finding_links`
--

CREATE TABLE `clinical_finding_links` (
  `id` int(11) NOT NULL,
  `finding_id` int(11) NOT NULL,
  `lookup_finding_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinical_finding_links`
--

INSERT INTO `clinical_finding_links` (`id`, `finding_id`, `lookup_finding_id`) VALUES
(26, 17, 5),
(27, 17, 1),
(28, 17, 6),
(31, 18, 6),
(32, 18, 4);

-- --------------------------------------------------------

--
-- Table structure for table `clinical_treatment_links`
--

CREATE TABLE `clinical_treatment_links` (
  `id` int(11) NOT NULL,
  `finding_id` int(11) NOT NULL,
  `lookup_treatment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinical_treatment_links`
--

INSERT INTO `clinical_treatment_links` (`id`, `finding_id`, `lookup_treatment_id`) VALUES
(22, 17, 6),
(23, 17, 2),
(24, 17, 1),
(27, 18, 1),
(28, 18, 4);

-- --------------------------------------------------------

--
-- Table structure for table `finding_attachments`
--

CREATE TABLE `finding_attachments` (
  `attachment_id` int(11) NOT NULL,
  `finding_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finding_attachments`
--

INSERT INTO `finding_attachments` (`attachment_id`, `finding_id`, `file_name`, `file_path`, `file_type`, `is_deleted`, `uploaded_at`) VALUES
(22, 17, 'Screenshot 2025-06-07 232632.png', 'uploads/68d2624e91abd-Screenshot 2025-06-07 232632.png', 'image/png', 0, '2025-09-23 09:03:10'),
(23, 17, 'Screenshot 2025-06-08 000046.png', 'uploads/68d2624e9248e-Screenshot 2025-06-08 000046.png', 'image/png', 0, '2025-09-23 09:03:10'),
(24, 17, 'Screenshot 2025-06-08 001834.png', 'uploads/68d2624e92ae4-Screenshot 2025-06-08 001834.png', 'image/png', 0, '2025-09-23 09:03:10'),
(25, 18, 'Screenshot 2025-06-07 232622.png', 'uploads/68d262a1f1558-Screenshot 2025-06-07 232622.png', 'image/png', 0, '2025-09-23 09:04:33'),
(26, 18, 'Screenshot 2025-06-07 232632.png', 'uploads/68d262a1f1cc1-Screenshot 2025-06-07 232632.png', 'image/png', 1, '2025-09-23 09:04:33'),
(27, 18, 'Screenshot 2025-06-08 000046.png', 'uploads/68d262a1f296c-Screenshot 2025-06-08 000046.png', 'image/png', 1, '2025-09-23 09:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `lookup_findings`
--

CREATE TABLE `lookup_findings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lookup_findings`
--

INSERT INTO `lookup_findings` (`id`, `name`, `is_active`) VALUES
(1, 'Caries', 1),
(2, 'Gingivitis', 1),
(3, 'Periodontitis', 1),
(4, 'Fractured Tooth', 1),
(5, 'Abrasion', 1),
(6, 'Erosion', 1),
(11, 'sample 1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lookup_procedures`
--

CREATE TABLE `lookup_procedures` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lookup_procedures`
--

INSERT INTO `lookup_procedures` (`id`, `name`, `is_active`) VALUES
(1, 'Oral Prophylaxis (Cleaning)', 1),
(2, 'Composite Restoration (Filling)', 1),
(3, 'Tooth Extraction (Simple)', 1),
(4, 'Tooth Extraction (Surgical/Impacted)', 1),
(5, 'Root Canal Treatment', 1),
(6, 'Jacket Crown Installation', 1),
(7, 'Veneer Placement', 1),
(8, 'Dental Sealants', 1),
(9, 'Fluoride Application', 1),
(10, 'Consultation', 1),
(11, 'Follow-up Check', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lookup_treatments`
--

CREATE TABLE `lookup_treatments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lookup_treatments`
--

INSERT INTO `lookup_treatments` (`id`, `name`, `is_active`) VALUES
(1, 'Restoration (Composite)', 1),
(2, 'Oral Prophylaxis (Cleaning)', 1),
(3, 'Tooth Extraction', 1),
(4, 'Root Canal Treatment', 1),
(5, 'Veneers', 1),
(6, 'Crown Installation', 1),
(7, 'sample 2', 1);

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `history_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `are_you_in_good_health` tinyint(1) NOT NULL,
  `is_under_medical_treatment` tinyint(1) NOT NULL,
  `medical_treatment_details` text DEFAULT NULL,
  `had_serious_illness_or_operation` tinyint(1) NOT NULL,
  `illness_operation_details` text DEFAULT NULL,
  `has_been_hospitalized` tinyint(1) DEFAULT NULL,
  `hospitalization_details` text DEFAULT NULL,
  `is_taking_medication` tinyint(1) DEFAULT NULL,
  `medication_details` text DEFAULT NULL,
  `is_on_diet` tinyint(1) DEFAULT NULL,
  `diet_details` text DEFAULT NULL,
  `drinks_alcoholic_beverages` tinyint(1) DEFAULT NULL,
  `alcohol_frequency` varchar(255) DEFAULT NULL,
  `uses_tobacco` tinyint(1) DEFAULT NULL,
  `tobacco_details` varchar(255) DEFAULT NULL,
  `allergic_anesthetics` tinyint(1) NOT NULL DEFAULT 0,
  `allergic_penicillin` tinyint(1) NOT NULL DEFAULT 0,
  `allergic_latex` tinyint(1) NOT NULL DEFAULT 0,
  `allergic_aspirin` tinyint(1) NOT NULL DEFAULT 0,
  `allergic_others_details` text DEFAULT NULL,
  `allergy_reaction_details` text DEFAULT NULL,
  `has_high_blood_pressure` tinyint(1) NOT NULL DEFAULT 0,
  `has_low_blood_pressure` tinyint(1) NOT NULL DEFAULT 0,
  `has_epilepsy_convulsions` tinyint(1) NOT NULL DEFAULT 0,
  `has_aids_hiv` tinyint(1) NOT NULL DEFAULT 0,
  `has_stomach_troubles_ulcer` tinyint(1) NOT NULL DEFAULT 0,
  `has_fainting_seizure` tinyint(1) NOT NULL DEFAULT 0,
  `has_rapid_weight_loss` tinyint(1) NOT NULL DEFAULT 0,
  `had_radiation_therapy` tinyint(1) NOT NULL DEFAULT 0,
  `has_joint_replacement_implant` tinyint(1) NOT NULL DEFAULT 0,
  `had_heart_surgery` tinyint(1) NOT NULL DEFAULT 0,
  `had_heart_attack` tinyint(1) NOT NULL DEFAULT 0,
  `has_heart_disease` tinyint(1) NOT NULL DEFAULT 0,
  `has_heart_murmur` tinyint(1) NOT NULL DEFAULT 0,
  `has_rheumatic_fever_disease` tinyint(1) NOT NULL DEFAULT 0,
  `has_hay_fever_allergies` tinyint(1) NOT NULL DEFAULT 0,
  `has_respiratory_problems` tinyint(1) NOT NULL DEFAULT 0,
  `has_hepatitis_jaundice` tinyint(1) NOT NULL DEFAULT 0,
  `has_tuberculosis` tinyint(1) NOT NULL DEFAULT 0,
  `has_swollen_ankles` tinyint(1) NOT NULL DEFAULT 0,
  `has_kidney_disease` tinyint(1) NOT NULL DEFAULT 0,
  `has_diabetes` tinyint(1) NOT NULL DEFAULT 0,
  `has_bleeding_blood_disease` tinyint(1) NOT NULL DEFAULT 0,
  `has_arthritis_rheumatism` tinyint(1) NOT NULL DEFAULT 0,
  `has_cancer_tumor` tinyint(1) NOT NULL DEFAULT 0,
  `has_anemia` tinyint(1) NOT NULL DEFAULT 0,
  `has_angina` tinyint(1) NOT NULL DEFAULT 0,
  `has_asthma` tinyint(1) NOT NULL DEFAULT 0,
  `has_thyroid_problem` tinyint(1) NOT NULL DEFAULT 0,
  `has_emphysema` tinyint(1) NOT NULL DEFAULT 0,
  `has_breathing_problems` tinyint(1) NOT NULL DEFAULT 0,
  `had_stroke` tinyint(1) NOT NULL DEFAULT 0,
  `has_chest_pain` tinyint(1) NOT NULL DEFAULT 0,
  `other_diseases_details` text DEFAULT NULL,
  `other_conditions_to_know` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_history`
--

INSERT INTO `medical_history` (`history_id`, `patient_id`, `are_you_in_good_health`, `is_under_medical_treatment`, `medical_treatment_details`, `had_serious_illness_or_operation`, `illness_operation_details`, `has_been_hospitalized`, `hospitalization_details`, `is_taking_medication`, `medication_details`, `is_on_diet`, `diet_details`, `drinks_alcoholic_beverages`, `alcohol_frequency`, `uses_tobacco`, `tobacco_details`, `allergic_anesthetics`, `allergic_penicillin`, `allergic_latex`, `allergic_aspirin`, `allergic_others_details`, `allergy_reaction_details`, `has_high_blood_pressure`, `has_low_blood_pressure`, `has_epilepsy_convulsions`, `has_aids_hiv`, `has_stomach_troubles_ulcer`, `has_fainting_seizure`, `has_rapid_weight_loss`, `had_radiation_therapy`, `has_joint_replacement_implant`, `had_heart_surgery`, `had_heart_attack`, `has_heart_disease`, `has_heart_murmur`, `has_rheumatic_fever_disease`, `has_hay_fever_allergies`, `has_respiratory_problems`, `has_hepatitis_jaundice`, `has_tuberculosis`, `has_swollen_ankles`, `has_kidney_disease`, `has_diabetes`, `has_bleeding_blood_disease`, `has_arthritis_rheumatism`, `has_cancer_tumor`, `has_anemia`, `has_angina`, `has_asthma`, `has_thyroid_problem`, `has_emphysema`, `has_breathing_problems`, `had_stroke`, `has_chest_pain`, `other_diseases_details`, `other_conditions_to_know`) VALUES
(1, 15, 1, 1, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 0, 'Lorem ipsum', 0, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 1, 1, 1, 1, 'Ipsum Lorem', 'Ipsum Lorem', 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Lorem ipsum', 'Ipsum Lorem'),
(3, 24, 1, 1, 'Lorem ipsum', 1, 'Lorem ipsum', 0, '', 0, '', 1, 'Lorem ipsum', 0, '', 0, '', 0, 1, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum'),
(5, 26, 1, 1, 'ipsum', 0, 'Lorem ipsum', 0, 'Lorem ipsum', 0, 'ipsum', 0, 'Lorem ipsum', 1, 'ipsum', 0, 'ipsum', 1, 0, 0, 0, 'ipsum', 'ipsum', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum'),
(6, 28, 1, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(7, 29, 1, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(9, 31, 1, 1, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 0, 'Lorem ipsum', 1, '', 1, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Lorem ipsum', 'Lorem ipsum'),
(10, 32, 1, 0, '', 0, '', 0, '', 0, '', 0, '', 0, '', 0, '', 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', ''),
(11, 33, 1, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(12, 34, 1, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(13, 35, 1, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(14, 36, 1, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(15, 37, 0, 0, '', 1, 'lorem ipsum', 1, 'lorem ipsum', 1, 'lorem ipsum', 0, 'lorem ipsum', 0, 'lorem ipsum', 1, 'lorem ipsum', 1, 1, 0, 0, 'lorem ipsum', 'lorem ipsum', 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 'lorem ipsum', 'lorem ipsum'),
(16, 38, 1, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(17, 39, 1, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `branch` enum('Lucban','Sta. Rosa') NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `address` text NOT NULL,
  `mobile_no` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `parent_guardian_name` varchar(255) DEFAULT NULL COMMENT 'For minors',
  `parent_guardian_occupation` varchar(150) DEFAULT NULL,
  `chief_complaint` text NOT NULL,
  `history_of_present_illness` text NOT NULL,
  `previous_dentist` varchar(255) DEFAULT NULL,
  `last_dental_visit` date DEFAULT NULL,
  `procedures_done_perio` tinyint(1) DEFAULT 0,
  `procedures_done_resto` tinyint(1) DEFAULT 0,
  `procedures_done_os` tinyint(1) DEFAULT 0,
  `procedures_done_prostho` tinyint(1) DEFAULT 0,
  `procedures_done_endo` tinyint(1) DEFAULT 0,
  `procedures_done_ortho` tinyint(1) DEFAULT 0,
  `procedures_done_others_specify` text DEFAULT NULL,
  `complications` text DEFAULT NULL,
  `physician_name` varchar(255) DEFAULT NULL,
  `physician_address` text DEFAULT NULL,
  `physician_phone` varchar(20) DEFAULT NULL,
  `last_physical_exam` date DEFAULT NULL,
  `blood_pressure` varchar(50) DEFAULT NULL,
  `respiratory_rate` varchar(50) DEFAULT NULL,
  `pulse_rate` varchar(50) DEFAULT NULL,
  `temperature` varchar(50) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `branch`, `last_name`, `first_name`, `middle_name`, `nickname`, `birthdate`, `age`, `gender`, `civil_status`, `nationality`, `religion`, `occupation`, `address`, `mobile_no`, `email`, `password`, `last_login`, `parent_guardian_name`, `parent_guardian_occupation`, `chief_complaint`, `history_of_present_illness`, `previous_dentist`, `last_dental_visit`, `procedures_done_perio`, `procedures_done_resto`, `procedures_done_os`, `procedures_done_prostho`, `procedures_done_endo`, `procedures_done_ortho`, `procedures_done_others_specify`, `complications`, `physician_name`, `physician_address`, `physician_phone`, `last_physical_exam`, `blood_pressure`, `respiratory_rate`, `pulse_rate`, `temperature`, `registration_date`) VALUES
(15, 'Lucban', 'Francia', 'Kali', 'C', 'Kaka', '2002-02-05', 23, 'F', 'Married', 'American', 'Catholic', 'Unemployed', 'Barangay, Malinao, Nagcarlan, Laguna', '09682554626', NULL, NULL, NULL, 'Ipsum Lorem', 'Ipsum Lorem', 'Ipsum Lorem', 'Ipsum Lorem', 'Mang Kepweng', '2025-08-01', 0, 0, 1, 1, 0, 0, 'Ipsum Lorem', 'Ipsum Lorem', 'Mang Kepweng', 'Ipsum Lorem', '123321123321', '2025-07-03', '123/90', '32', '12', '23.4', '2025-08-05 01:43:18'),
(24, 'Sta. Rosa', 'Monkey', 'Luffy', 'D', 'Strawhat', '2019-07-11', 6, 'M', 'Single', 'Filipino', 'Catholic', 'Explorer', '456 Oak Ave, Anytown', '09682554626', NULL, NULL, NULL, 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Mang Kepweng', '2025-09-21', 0, 1, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 'Mang Kepweng', 'Lorem ipsum', '1233211233211', '2025-09-21', '120/90', '12.2', '32.2', '98.6', '2025-08-07 02:27:04'),
(26, 'Sta. Rosa', 'Kuroko', 'Tetsuya', 'A', 'Tetsu', '2013-07-10', 12, 'M', 'Single', 'Japanese', 'Catholic', 'Unemployed', '456 Oak Ave, Anytown', '09682554626', NULL, NULL, NULL, 'Lorem ipsum', 'Lorem ipsum', 'ipsum', 'ipsum', 'ipsum', '2025-07-02', 1, 1, 1, 1, 1, 1, 'ipsum', 'ipsum', 'ipsum', 'ipsum', '123321123321', '2025-07-16', '120/90', '12.2', '32', '98.6', '2025-08-10 12:45:31'),
(27, 'Sta. Rosa', 'Kurosaki', 'Ichigo', NULL, NULL, '1900-01-01', 0, '', 'N/A', 'N/A', NULL, NULL, '', '12312312312', NULL, NULL, NULL, NULL, NULL, 'N/A', 'N/A', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-10 13:02:40'),
(28, 'Sta. Rosa', 'Kento', 'Nanami', NULL, NULL, '1900-01-01', 0, 'M', 'N/A', '', NULL, NULL, 'N/A', '12312312312', NULL, NULL, NULL, NULL, NULL, 'For Registration', 'For Registration', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-07 04:23:55'),
(29, 'Sta. Rosa', 'Katagiri', 'Kanata', NULL, NULL, '1900-01-01', 0, 'M', 'N/A', '', NULL, NULL, 'N/A', '12312312312', NULL, NULL, NULL, NULL, NULL, 'For Registration', 'For Registration', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-10 23:38:58'),
(31, 'Sta. Rosa', 'Bituin', 'Khenriev', 'C', 'Kaleb', '2003-11-05', 21, 'M', 'Single', 'Filipino', 'Catholic', 'Unemployed', 'Barangay, Malinao, Nagcarlan, Laguna', '09682554626', '', NULL, NULL, 'Bituin Edwin', 'Farmer', 'Lorem ipsum', 'Lorem ipsum', 'Dr. Juan Dela Cruz', '2025-09-02', 1, 1, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 'Dr. Juan Dela Cruz', 'Lorem ipsum', '09682554626', '2025-09-02', '110/90', '23', '80', '36.5', '2025-09-23 10:59:58'),
(32, 'Sta. Rosa', 'Itadori', 'Yuki', '', '', '1900-01-01', 125, 'M', 'N/A', '', '', '', 'N/A', '09852220955', NULL, NULL, NULL, '', '', 'For Registration', 'For Registration', '', NULL, 0, 0, 0, 0, 0, 0, '', '', '', '', '', NULL, '', '', '', '', '2025-09-23 11:04:29'),
(33, 'Sta. Rosa', 'kalami', 'laromi', NULL, NULL, '1900-01-01', 0, 'M', '', '', NULL, NULL, '', '09875876324', 'khenrievb@gmail.com', NULL, NULL, NULL, NULL, 'Online Request', '', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-15 16:07:44'),
(34, 'Sta. Rosa', 'moryo', 'kalihan', NULL, NULL, '1900-01-01', 0, 'M', '', '', NULL, NULL, '', '09842887444', 'bituinkhenriev832@gmail.com', NULL, NULL, NULL, NULL, 'Online Request', '', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-15 21:57:07'),
(35, 'Sta. Rosa', 'kalahi', 'nanimo', NULL, NULL, '1900-01-01', 0, 'M', 'N/A', '', NULL, NULL, 'N/A', '0810827878', 'bit@gmail.com', NULL, NULL, NULL, NULL, 'For Registration', 'For Registration', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-16 06:03:48'),
(36, 'Lucban', 'Bituin', 'Khenriev', NULL, NULL, '1900-01-01', 0, 'M', '', '', NULL, NULL, '', '09472999774', 'bituinkhenriev833@gmail.com', NULL, NULL, NULL, NULL, 'Online Request', '', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-16 06:19:10'),
(37, 'Sta. Rosa', 'kanami', 'kali', 'c', 'Ali', '2025-10-01', 0, 'M', 'sdfdasdf', 'Filipino', 'Agnostic', 'Unemployed', 'Barangay, Malinao, Nagcarlan, Laguna', '09682554626', 'assistant@gmail.com', NULL, NULL, 'Lorem ipsum', 'Lorem ipsum', 'lorem ipsum', 'lorem ipsum', 'lorem ipsum', '2025-10-01', 0, 0, 1, 1, 0, 0, 'Lorem ipsum', 'lorem ipsum', 'Dr. Juan Dela Cruz', 'Lorem ipsum', '123321123321', '2025-10-02', '120/90', '12.2', '32.2', '23.4', '2025-10-16 08:38:52'),
(38, 'Sta. Rosa', 'Bituin', 'Khenriev', NULL, NULL, '1900-01-01', 0, 'M', '', '', NULL, NULL, '', '09682554626', 'kaeighleb@gmail.com', NULL, NULL, NULL, NULL, 'Online Request', '', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-16 09:14:47'),
(39, 'Sta. Rosa', 'Bituin', 'Khenriev', NULL, NULL, '1900-01-01', 0, 'M', '', '', NULL, NULL, '', '09682554626', 'keighleb@gmail.com', NULL, NULL, NULL, NULL, 'Online Request', '', NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-16 09:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `pending_attachment_deletions`
--

CREATE TABLE `pending_attachment_deletions` (
  `id` int(11) NOT NULL,
  `attachment_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `treatment_records`
--

CREATE TABLE `treatment_records` (
  `record_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `procedure_date` date NOT NULL,
  `procedure_done` varchar(255) NOT NULL,
  `tooth_no` varchar(50) DEFAULT NULL,
  `amount_charged` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `next_appt` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatment_records`
--

INSERT INTO `treatment_records` (`record_id`, `patient_id`, `dentist_id`, `procedure_date`, `procedure_done`, `tooth_no`, `amount_charged`, `amount_paid`, `balance`, `next_appt`, `created_at`) VALUES
(11, 28, 1, '2025-09-23', 'Fluoride Application', '', 123.00, 123.00, 0.00, '2025-09-30', '2025-09-23 08:06:58'),
(16, 29, 1, '2025-09-23', 'Jacket Crown Installation', '2', 123.00, 123.00, 0.00, '2025-09-24', '2025-09-23 09:02:36'),
(22, 15, 1, '2025-09-23', 'Oral Prophylaxis (Cleaning)', '21', 123.00, 123.00, 0.00, '2025-09-29', '2025-09-23 09:36:56'),
(23, 26, 1, '2025-09-23', 'Tooth Extraction (Surgical/Impacted)', '21', 123.00, 123.00, 0.00, '2025-09-27', '2025-09-23 09:42:43'),
(24, 24, 1, '2025-09-23', 'Tooth Extraction (Surgical/Impacted)', '21', 123.00, 123.00, 0.00, '2025-09-30', '2025-09-23 09:46:07'),
(25, 31, 1, '2025-09-23', 'Oral Prophylaxis (Cleaning)', '12', 123.00, 123.00, 0.00, '2025-09-09', '2025-09-23 11:03:07'),
(30, 33, 1, '2025-10-16', 'Fluoride Application', '213', 123.00, 123.00, 0.00, '2025-10-31', '2025-10-16 05:36:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Must be stored as a hash',
  `full_name` varchar(255) NOT NULL,
  `role` enum('Admin','Dentist','Assistant') NOT NULL,
  `branch` enum('Lucban','Sta. Rosa') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `availability_status` enum('Available','On Leave','Training','Sick Day') NOT NULL DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `branch`, `is_active`, `availability_status`, `created_at`) VALUES
(1, 'admin', '$2y$10$RgTwOdrPrhxdqPv9qZbtYOHTpvEGoMzNMoNtxusI6A6v1WgCscQze', 'Dr. Juan Dela Cruz', 'Admin', 'Sta. Rosa', 1, 'Available', '2025-07-21 23:00:36'),
(2, 'assistant', '$2y$10$0QYvmQXot.1Sigh/AqoGyu7sav5rHkRRtqXrgMXU9i9bxmh8z/oaa', 'Mang Kepweng', 'Assistant', 'Sta. Rosa', 1, 'Available', '2025-08-05 03:32:27'),
(3, 'dentist', '$2y$10$LG9fdSE2Wvan.k3nOyycNO9V4HNEwFa8Qu9sZh/mKfrTQCmECNXPG', 'Dr. Quack Quack', 'Dentist', 'Lucban', 1, 'Available', '2025-08-06 23:03:39'),
(4, 'assistant2', '$2y$10$8QBXKhDNH4WHs1K5ggM.Dengtfp.j5bK37.4gvqNADnaeuIkMaDzm', 'Mang Tomas', 'Assistant', 'Lucban', 1, 'Available', '2025-08-06 23:29:12'),
(5, 'dentist2', '$2y$10$E9m40GPeTbrWbqvj.JI6ZOQiOKXl4oPMkHlNv5Oqz67r7k9sB3duu', 'Dr. Maynard', 'Dentist', 'Sta. Rosa', 1, 'Available', '2025-08-06 23:29:50'),
(6, 'admin2', '$2y$10$wK6LABtmvKYL13P/EAB0c.HkxC4x5SaKj.QE4cwjjnhKfhdcpVsSG', 'Dr. Bokbok', 'Admin', 'Lucban', 0, 'Available', '2025-08-10 12:07:19'),
(9, 'kalil', '$2y$10$Q8YBeUYXTd95o0DREGN4L.g5PaQbLQXAlkKCwQWAqvs5c3qMfHdwu', 'Dr. Kali', 'Dentist', 'Lucban', 1, 'Available', '2025-10-16 03:21:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `dentist_id` (`dentist_id`);

--
-- Indexes for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  ADD PRIMARY KEY (`finding_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `dentist_id` (`dentist_id`);

--
-- Indexes for table `clinical_finding_links`
--
ALTER TABLE `clinical_finding_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `finding_id` (`finding_id`),
  ADD KEY `lookup_finding_id` (`lookup_finding_id`);

--
-- Indexes for table `clinical_treatment_links`
--
ALTER TABLE `clinical_treatment_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `finding_id` (`finding_id`),
  ADD KEY `lookup_treatment_id` (`lookup_treatment_id`);

--
-- Indexes for table `finding_attachments`
--
ALTER TABLE `finding_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `finding_id` (`finding_id`);

--
-- Indexes for table `lookup_findings`
--
ALTER TABLE `lookup_findings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lookup_procedures`
--
ALTER TABLE `lookup_procedures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lookup_treatments`
--
ALTER TABLE `lookup_treatments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`history_id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pending_attachment_deletions`
--
ALTER TABLE `pending_attachment_deletions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachment_id` (`attachment_id`);

--
-- Indexes for table `treatment_records`
--
ALTER TABLE `treatment_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `dentist_id` (`dentist_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  MODIFY `finding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `clinical_finding_links`
--
ALTER TABLE `clinical_finding_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `clinical_treatment_links`
--
ALTER TABLE `clinical_treatment_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `finding_attachments`
--
ALTER TABLE `finding_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `lookup_findings`
--
ALTER TABLE `lookup_findings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `lookup_procedures`
--
ALTER TABLE `lookup_procedures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lookup_treatments`
--
ALTER TABLE `lookup_treatments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `pending_attachment_deletions`
--
ALTER TABLE `pending_attachment_deletions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `treatment_records`
--
ALTER TABLE `treatment_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  ADD CONSTRAINT `clinical_findings_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clinical_findings_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `clinical_finding_links`
--
ALTER TABLE `clinical_finding_links`
  ADD CONSTRAINT `fk_link_to_finding` FOREIGN KEY (`finding_id`) REFERENCES `clinical_findings` (`finding_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_link_to_lookup_finding` FOREIGN KEY (`lookup_finding_id`) REFERENCES `lookup_findings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clinical_treatment_links`
--
ALTER TABLE `clinical_treatment_links`
  ADD CONSTRAINT `fk_link_to_finding_treatment` FOREIGN KEY (`finding_id`) REFERENCES `clinical_findings` (`finding_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_link_to_lookup_treatment` FOREIGN KEY (`lookup_treatment_id`) REFERENCES `lookup_treatments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finding_attachments`
--
ALTER TABLE `finding_attachments`
  ADD CONSTRAINT `fk_finding_attachments` FOREIGN KEY (`finding_id`) REFERENCES `clinical_findings` (`finding_id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `treatment_records`
--
ALTER TABLE `treatment_records`
  ADD CONSTRAINT `treatment_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `treatment_records_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
