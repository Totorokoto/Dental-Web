-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2025 at 02:46 PM
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
-- Database: `zubieto-dental`
--

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
  `status` enum('Scheduled','Completed','Cancelled','No-Show') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinical_findings`
--

CREATE TABLE `clinical_findings` (
  `finding_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `finding_date` date NOT NULL,
  `clinical_findings` text NOT NULL,
  `diagnosis` text NOT NULL,
  `proposed_treatment` text NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinical_findings`
--

INSERT INTO `clinical_findings` (`finding_id`, `patient_id`, `dentist_id`, `finding_date`, `clinical_findings`, `diagnosis`, `proposed_treatment`, `remarks`, `created_at`) VALUES
(4, 24, 2, '2025-08-07', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', '2025-08-07 02:27:41');

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
(1, 15, 1, 1, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 0, 'Lorem ipsum', 0, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 0, 0, 0, 0, 'Ipsum Lorem', 'Ipsum Lorem', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 'Lorem ipsum', 'Ipsum Lorem'),
(3, 24, 1, 1, 'Lorem ipsum', 1, 'Lorem ipsum', 0, '', 0, '', 1, 'Lorem ipsum', 0, '', 0, '', 0, 1, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum'),
(5, 26, 1, 0, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 0, 'Lorem ipsum', 1, 'Lorem ipsum', 1, '', 0, '', 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 'Lorem ipsum');

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

INSERT INTO `patients` (`patient_id`, `branch`, `last_name`, `first_name`, `middle_name`, `nickname`, `birthdate`, `age`, `gender`, `civil_status`, `nationality`, `religion`, `occupation`, `address`, `mobile_no`, `parent_guardian_name`, `parent_guardian_occupation`, `chief_complaint`, `history_of_present_illness`, `previous_dentist`, `last_dental_visit`, `procedures_done_perio`, `procedures_done_resto`, `procedures_done_os`, `procedures_done_prostho`, `procedures_done_endo`, `procedures_done_ortho`, `procedures_done_others_specify`, `complications`, `physician_name`, `physician_address`, `physician_phone`, `last_physical_exam`, `blood_pressure`, `respiratory_rate`, `pulse_rate`, `temperature`, `registration_date`) VALUES
(15, 'Lucban', 'Francia', 'Kali', 'C', 'Kaka', '2002-02-05', 23, 'F', 'Married', 'American', 'Catholic', 'Unemployed', 'Barangay, Malinao, Nagcarlan, Laguna', '09682554626', 'Ipsum Lorem', 'Ipsum Lorem', 'Ipsum Lorem', 'Ipsum Lorem', 'Mang Kepweng', '2025-08-01', 0, 0, 0, 0, 0, 0, 'Ipsum Lorem', 'Ipsum Lorem', 'Mang Kepweng', 'Ipsum Lorem', '123321123321', '2025-07-03', '123/90', '32', '12', '23.4', '2025-08-05 01:43:18'),
(24, 'Sta. Rosa', 'Monkey', 'Luffy', 'D', 'Strawhat', '2019-07-11', 6, 'M', 'Single', 'Filipino', 'Catholic', 'Explorer', '456 Oak Ave, Anytown', '09682554626', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Mang Kepweng', '2025-07-31', 0, 1, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 'Mang Kepweng', 'Lorem ipsum', '123321123321', '2025-07-28', '120/90', '12.2', '32.2', '98.6', '2025-08-07 02:27:04'),
(26, 'Sta. Rosa', 'Kuroko', 'Tetsuya', 'A', 'Tetsu', '2013-07-10', 12, 'M', 'Single', 'Japanese', 'Catholic', 'Unemployed', '456 Oak Ave, Anytown', '09682554626', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Lorem ipsum', 'Mang Kepweng', '2025-07-02', 1, 0, 0, 0, 0, 0, 'Lorem ipsum', 'Lorem ipsum', 'Mang Kepweng', 'Lorem ipsum', '123321123321', '2025-07-16', '120/90', '12.2', '32', '98.6', '2025-08-10 12:45:31');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `branch`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$RgTwOdrPrhxdqPv9qZbtYOHTpvEGoMzNMoNtxusI6A6v1WgCscQze', 'Dr. Juan Dela Cruz', 'Admin', 'Sta. Rosa', 1, '2025-07-21 23:00:36'),
(2, 'assistant', '$2y$10$//.KegIDYhLMLwY.L5Wn4ebYFmH9wny.q.ipe4unvNojQ0icd5tyC', 'Mang Kepweng', 'Assistant', 'Sta. Rosa', 1, '2025-08-05 03:32:27'),
(3, 'dentist', '$2y$10$67atl8t24YIVbKkYzsWG9eROOb2w4jIeL/8zQfYOw1CBKJyWA1bUu', 'Dr. Quack Quack', 'Dentist', 'Lucban', 1, '2025-08-06 23:03:39'),
(4, 'assistant2', '$2y$10$GGtgY2sSytSAgTAXXdGbwu5M12ib9AKIKrOtG62/qTIWcFqafnN9u', 'Mang Tomas', 'Assistant', 'Lucban', 1, '2025-08-06 23:29:12'),
(5, 'dentist2', '$2y$10$2AnxSMob8DKKViBjF2cznuYSEPVeWlc1TT2xuZYX.Cbq8RKwC9dvm', 'Dr. Maynard', 'Dentist', 'Sta. Rosa', 1, '2025-08-06 23:29:50'),
(6, 'admin2', '$2y$10$KyhwzKDboxSLOoIgznd/aetZ/pNxkv3XAXoAOf8p130xhhvp0lHq.', 'Dr. Bokbok', 'Admin', 'Lucban', 1, '2025-08-10 12:07:19');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`history_id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  MODIFY `finding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `treatment_records`
--
ALTER TABLE `treatment_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
