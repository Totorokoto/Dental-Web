-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 01:43 PM
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
-- Database: `zubieto`
--

-- --------------------------------------------------------

--
-- Table structure for table `clinicalfindings`
--

CREATE TABLE `clinicalfindings` (
  `ClinicalFindingsID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DateObserved` date DEFAULT NULL,
  `ToothNumber` varchar(255) DEFAULT NULL,
  `Diagnosis` varchar(255) DEFAULT NULL,
  `ProposedTreatment` varchar(255) DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinicalfindings`
--

INSERT INTO `clinicalfindings` (`ClinicalFindingsID`, `PatientID`, `DateObserved`, `ToothNumber`, `Diagnosis`, `ProposedTreatment`, `Remarks`) VALUES
(7, 1, '2025-06-10', '3', 'Caries', 'Filling', 'Small cavity observed on the occlusal surface');

-- --------------------------------------------------------

--
-- Table structure for table `dentalhistories`
--

CREATE TABLE `dentalhistories` (
  `DentalHistoryID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DateTaken` datetime DEFAULT current_timestamp(),
  `ChiefComplaint` text DEFAULT NULL,
  `HistoryOfPresentIllness` text DEFAULT NULL,
  `PreviousDentist` varchar(255) DEFAULT NULL,
  `LastDentalVisit` date DEFAULT NULL,
  `Complications` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentalhistories`
--

INSERT INTO `dentalhistories` (`DentalHistoryID`, `PatientID`, `DateTaken`, `ChiefComplaint`, `HistoryOfPresentIllness`, `PreviousDentist`, `LastDentalVisit`, `Complications`) VALUES
(2, 1, '2025-06-08 00:47:27', 'Tooth sensitivity to cold', 'Patient reports sharp pain in tooth #19 when consuming cold beverages.', 'Dr. Jane Doe', '2024-11-15', 'None reported'),
(4, 4, '2025-06-08 13:42:24', 'qweqweqw', 'qweqweqwe', 'Dr. Jane Doe', '2025-06-11', 'qweqweqw');

-- --------------------------------------------------------

--
-- Table structure for table `medicalhistories`
--

CREATE TABLE `medicalhistories` (
  `MedicalHistoryID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DateTaken` datetime DEFAULT current_timestamp(),
  `NameOfPhysician` varchar(255) DEFAULT NULL,
  `PhysicianAddress` varchar(255) DEFAULT NULL,
  `PhysicianPhoneNumber` varchar(20) DEFAULT NULL,
  `DateOfLastPhysicalExam` date DEFAULT NULL,
  `AreYouInGoodHealth` tinyint(1) DEFAULT NULL,
  `UnderMedicalTreatment` tinyint(1) DEFAULT NULL,
  `TreatmentDetails` text DEFAULT NULL,
  `HadSeriousIllness` tinyint(1) DEFAULT NULL,
  `IllnessDetails` text DEFAULT NULL,
  `EverHospitalized` tinyint(1) DEFAULT NULL,
  `HospitalizationDetails` text DEFAULT NULL,
  `Medications` text DEFAULT NULL,
  `DietaryRestrictions` text DEFAULT NULL,
  `UseAlcohol` varchar(255) DEFAULT NULL,
  `UseTobacco` varchar(255) DEFAULT NULL,
  `Allergies` text DEFAULT NULL,
  `AllergyDetails` text DEFAULT NULL,
  `VitalSigns` text DEFAULT NULL,
  `BloodPressure` varchar(255) DEFAULT NULL,
  `RespiratoryRate` varchar(255) DEFAULT NULL,
  `PulseRate` varchar(255) DEFAULT NULL,
  `Temperature` varchar(255) DEFAULT NULL,
  `OtherConditions` text DEFAULT NULL,
  `HighBloodPressure` tinyint(1) DEFAULT 0,
  `EpilepsyConvulsions` tinyint(1) DEFAULT 0,
  `AIDSOrHIVInfection` tinyint(1) DEFAULT 0,
  `StomachTroublesUlcer` tinyint(1) DEFAULT 0,
  `HeartFailure` tinyint(1) DEFAULT 0,
  `RapidWeightLoss` tinyint(1) DEFAULT 0,
  `RadiationTherapy` tinyint(1) DEFAULT 0,
  `JointReplacement` tinyint(1) DEFAULT 0,
  `HeartSurgery` tinyint(1) DEFAULT 0,
  `HeartAttack` tinyint(1) DEFAULT 0,
  `HeartDisease` tinyint(1) DEFAULT 0,
  `HeartMurmur` tinyint(1) DEFAULT 0,
  `HepatitisOrLiverDisease` tinyint(1) DEFAULT 0,
  `RheumaticFever` tinyint(1) DEFAULT 0,
  `HayFeverAllergies` tinyint(1) DEFAULT 0,
  `RespiratoryProblems` tinyint(1) DEFAULT 0,
  `HepatitisJaundice` tinyint(1) DEFAULT 0,
  `Tuberculosis` tinyint(1) DEFAULT 0,
  `SwollenAnkles` tinyint(1) DEFAULT 0,
  `KidneyDisease` tinyint(1) DEFAULT 0,
  `Diabetes` tinyint(1) DEFAULT 0,
  `JointInjuriesBloodDisease` tinyint(1) DEFAULT 0,
  `ArthritisRheumatism` tinyint(1) DEFAULT 0,
  `CancerTumor` tinyint(1) DEFAULT 0,
  `Anemia` tinyint(1) DEFAULT 0,
  `Angina` tinyint(1) DEFAULT 0,
  `Asthma` tinyint(1) DEFAULT 0,
  `ThyroidProblem` tinyint(1) DEFAULT 0,
  `Emphysema` tinyint(1) DEFAULT 0,
  `BleedingProblems` tinyint(1) DEFAULT 0,
  `Stroke` tinyint(1) DEFAULT 0,
  `ChestPain` tinyint(1) DEFAULT 0,
  `OtherConditionsDetails` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicalhistories`
--

INSERT INTO `medicalhistories` (`MedicalHistoryID`, `PatientID`, `DateTaken`, `NameOfPhysician`, `PhysicianAddress`, `PhysicianPhoneNumber`, `DateOfLastPhysicalExam`, `AreYouInGoodHealth`, `UnderMedicalTreatment`, `TreatmentDetails`, `HadSeriousIllness`, `IllnessDetails`, `EverHospitalized`, `HospitalizationDetails`, `Medications`, `DietaryRestrictions`, `UseAlcohol`, `UseTobacco`, `Allergies`, `AllergyDetails`, `VitalSigns`, `BloodPressure`, `RespiratoryRate`, `PulseRate`, `Temperature`, `OtherConditions`, `HighBloodPressure`, `EpilepsyConvulsions`, `AIDSOrHIVInfection`, `StomachTroublesUlcer`, `HeartFailure`, `RapidWeightLoss`, `RadiationTherapy`, `JointReplacement`, `HeartSurgery`, `HeartAttack`, `HeartDisease`, `HeartMurmur`, `HepatitisOrLiverDisease`, `RheumaticFever`, `HayFeverAllergies`, `RespiratoryProblems`, `HepatitisJaundice`, `Tuberculosis`, `SwollenAnkles`, `KidneyDisease`, `Diabetes`, `JointInjuriesBloodDisease`, `ArthritisRheumatism`, `CancerTumor`, `Anemia`, `Angina`, `Asthma`, `ThyroidProblem`, `Emphysema`, `BleedingProblems`, `Stroke`, `ChestPain`, `OtherConditionsDetails`) VALUES
(1, 1, '2025-06-08 00:50:41', 'Dr. John Smith', '123 Main St, Anytown', '555-123-4567', '2025-01-10', 1, 0, NULL, 0, NULL, 0, NULL, 'None', NULL, 'Occasionally', 'No', 'None', NULL, NULL, '120/80', '16', '72', '98.6', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
(3, 4, '2025-06-08 13:42:24', 'Dr. John Smith', '123 Main St, Anytown', '555-123-4567', '2025-06-20', 1, 0, NULL, 0, NULL, 0, NULL, 'qweqweqwe', NULL, NULL, NULL, 'qweqweqwe', NULL, NULL, '120/80', '16', '72', '98.6', NULL, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 'qweqweqw');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `PatientID` int(11) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Birthdate` date DEFAULT NULL,
  `Religion` varchar(100) DEFAULT NULL,
  `CivilStatus` varchar(100) DEFAULT NULL,
  `Nationality` varchar(100) DEFAULT NULL,
  `MobileNumber` varchar(20) DEFAULT NULL,
  `ParentGuardianName` varchar(255) DEFAULT NULL,
  `Occupation` varchar(255) DEFAULT NULL,
  `Age` int(11) DEFAULT NULL,
  `Gender` enum('M','F','Other') DEFAULT NULL,
  `Nickname` varchar(255) DEFAULT NULL,
  `PatientNumber` varchar(50) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`PatientID`, `FirstName`, `LastName`, `MiddleName`, `Address`, `Birthdate`, `Religion`, `CivilStatus`, `Nationality`, `MobileNumber`, `ParentGuardianName`, `Occupation`, `Age`, `Gender`, `Nickname`, `PatientNumber`, `Email`) VALUES
(1, 'Alice', 'Johnson', 'Marie', '456 Oak Ave, Anytown', '1988-05-20', 'Catholic', 'Married', 'American', '555-987-6543', '', 'Teacher', 37, 'F', 'Ali', 'PT-20250607-002', 'alice.johnson@example.com'),
(4, 'kehn', 'bituin', 'a', 'Barangay, Malinao, Nagcarlan, Laguna', '2025-06-24', 'Catholic', 'Married', 'American', '09682554626', 'Edwin T. Bituin', 'Teacher', 21, 'M', 'Kaleb', 'PT-20250607-003', 'alice.johnson@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `procedures`
--

CREATE TABLE `procedures` (
  `ProcedureID` int(11) NOT NULL,
  `ProcedureName` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procedures`
--

INSERT INTO `procedures` (`ProcedureID`, `ProcedureName`, `Description`) VALUES
(1, 'Cleaning', 'Prophylactic cleaning of teeth'),
(2, 'Filling - Amalgam', 'Amalgam filling for cavity'),
(3, 'Extraction', 'Removal of a tooth'),
(4, 'Root Canal', 'Endodontic treatment to save an infected tooth'),
(5, 'Crown', 'Permanent covering for a tooth'),
(6, 'Teeth Whitening', 'Cosmetic procedure to lighten teeth.');

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `TreatmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `ProcedureID` int(11) NOT NULL,
  `ToothNumber` varchar(255) DEFAULT NULL,
  `AmountCharged` decimal(10,2) DEFAULT NULL,
  `AmountPaid` decimal(10,2) DEFAULT NULL,
  `Balance` decimal(10,2) DEFAULT NULL,
  `NextAppointment` date DEFAULT NULL,
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatments`
--

INSERT INTO `treatments` (`TreatmentID`, `PatientID`, `Date`, `ProcedureID`, `ToothNumber`, `AmountCharged`, `AmountPaid`, `Balance`, `NextAppointment`, `Notes`) VALUES
(1, 1, '2025-06-08', 1, '8', 75.00, 75.00, 0.00, '2025-12-08', 'Routine cleaning completed.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FirstName` varchar(255) DEFAULT NULL,
  `LastName` varchar(255) DEFAULT NULL,
  `Role` enum('Dentist','Hygienist','Assistant','Admin') DEFAULT 'Assistant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Password`, `FirstName`, `LastName`, `Role`) VALUES
(1, 'admin', 'admin', 'admin', 'admin', 'Admin'),
(2, 'SuperAdmin', '$2y$10$24G8.WsRZW0cfZWFXlCRnORtH9OFibTlDLzYO8viZbJZtuicKWEze', 'Admin', NULL, 'Admin'),
(3, 'Assistant', '$2y$10$bQBfqhumi71XP6KFJYNSHePnBWU.ABx6dxjDmGEEDOppPqUISjAPu', 'Assistant', 'Assistant', 'Assistant');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clinicalfindings`
--
ALTER TABLE `clinicalfindings`
  ADD PRIMARY KEY (`ClinicalFindingsID`),
  ADD KEY `PatientID` (`PatientID`);

--
-- Indexes for table `dentalhistories`
--
ALTER TABLE `dentalhistories`
  ADD PRIMARY KEY (`DentalHistoryID`),
  ADD KEY `PatientID` (`PatientID`);

--
-- Indexes for table `medicalhistories`
--
ALTER TABLE `medicalhistories`
  ADD PRIMARY KEY (`MedicalHistoryID`),
  ADD KEY `PatientID` (`PatientID`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`PatientID`),
  ADD KEY `LastName` (`LastName`);

--
-- Indexes for table `procedures`
--
ALTER TABLE `procedures`
  ADD PRIMARY KEY (`ProcedureID`),
  ADD UNIQUE KEY `ProcedureName` (`ProcedureName`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`TreatmentID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `ProcedureID` (`ProcedureID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clinicalfindings`
--
ALTER TABLE `clinicalfindings`
  MODIFY `ClinicalFindingsID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dentalhistories`
--
ALTER TABLE `dentalhistories`
  MODIFY `DentalHistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medicalhistories`
--
ALTER TABLE `medicalhistories`
  MODIFY `MedicalHistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `procedures`
--
ALTER TABLE `procedures`
  MODIFY `ProcedureID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `treatments`
--
ALTER TABLE `treatments`
  MODIFY `TreatmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clinicalfindings`
--
ALTER TABLE `clinicalfindings`
  ADD CONSTRAINT `clinicalfindings_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE;

--
-- Constraints for table `dentalhistories`
--
ALTER TABLE `dentalhistories`
  ADD CONSTRAINT `dentalhistories_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE;

--
-- Constraints for table `medicalhistories`
--
ALTER TABLE `medicalhistories`
  ADD CONSTRAINT `medicalhistories_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE;

--
-- Constraints for table `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `treatments_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `treatments_ibfk_2` FOREIGN KEY (`ProcedureID`) REFERENCES `procedures` (`ProcedureID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
