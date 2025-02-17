-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2024 at 07:57 AM
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
-- Database: `kless_lock_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_table`
--

CREATE TABLE `attendance_table` (
  `Attendance_ID` int(10) NOT NULL,
  `User_ID` int(10) DEFAULT NULL,
  `Labclass_ID` int(5) DEFAULT NULL,
  `Record_ID` int(5) DEFAULT NULL,
  `Attendance_Date` timestamp NULL DEFAULT current_timestamp(),
  `Status_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow`
--

CREATE TABLE `borrow` (
  `Borrow_ID` int(10) NOT NULL,
  `User_ID` int(10) DEFAULT NULL,
  `iPad_ID` int(10) DEFAULT NULL,
  `Borrow_datetime` datetime DEFAULT NULL,
  `Return_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow`
--



-- --------------------------------------------------------

--
-- Table structure for table `course_table`
--

CREATE TABLE `course_table` (
  `Course_ID` int(10) NOT NULL,
  `Course_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_table`
--

INSERT INTO `course_table` (`Course_ID`, `Course_name`) VALUES
(1, 'Computer Science'),
(2, 'Information Technology'),
(3, 'Engineering'),
(4, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_table`
--

CREATE TABLE `enrollment_table` (
  `Enrollment_ID` int(10) NOT NULL,
  `User_ID` int(10) NOT NULL,
  `Course_ID` int(10) NOT NULL,
  `Year_ID` int(10) NOT NULL,
  `Section_ID` int(10) NOT NULL,
  `Labclass_ID` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_table`
--



-- --------------------------------------------------------

--
-- Table structure for table `entry_table`
--

CREATE TABLE `entry_table` (
  `Entry_ID` int(10) NOT NULL,
  `User_ID` int(10) DEFAULT NULL,
  `Record_ID` int(10) DEFAULT NULL,
  `School_ID` varchar(255) DEFAULT NULL,
  `Date_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_table`
--

CREATE TABLE `fingerprint_table` (
  `Fingerprint_ID` int(5) NOT NULL,
  `User_ID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprint_table`
--


-- --------------------------------------------------------

--
-- Table structure for table `ipad_table`
--

CREATE TABLE `ipad_table` (
  `iPad_ID` int(10) NOT NULL,
  `Serial_num` varchar(255) DEFAULT NULL,
  `Model` varchar(255) DEFAULT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `Status_ID` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ipad_table`
--

INSERT INTO `ipad_table` (`iPad_ID`, `Serial_num`, `Model`, `Status`, `Status_ID`) VALUES
(1, 'SN001', 'iPad Air', 'Available', 1),
(2, 'SN002', 'iPad Pro', 'In Use', 1),
(3, 'SN003', 'iPad Mini', 'Unavailable', 1),
(4, 'SN004', 'iPad Local', 'Available', 1),
(5, 'SN005', 'Samsung Galaxy Tab S7', 'Available', 1),
(6, 'SN006', 'Google Pixel Slate', 'Available', 1),
(7, 'SN007', 'Lenovo Tab P11', 'Available', 1),
(8, 'SN008', 'Huawei MatePad', 'Available', 1),
(9, 'SN009', 'Xiaomi Pad 5', 'Available', 1);


-- --------------------------------------------------------

--
-- Table structure for table `laboratory_class`
--

CREATE TABLE `laboratory_class` (
  `Labclass_ID` int(5) NOT NULL,
  `User_ID` int(10) DEFAULT NULL,
  `Class_name` varchar(255) DEFAULT NULL,
  `Start_Time` time DEFAULT NULL,
  `End_Time` time DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Course_ID` int(11) NOT NULL,
  `Year_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratory_class`
--

-- --------------------------------------------------------

--
-- Table structure for table `record_table`
--

CREATE TABLE `record_table` (
  `Record_ID` int(5) NOT NULL,
  `Fingerprint_ID` int(10) DEFAULT NULL,
  `Time_and_Date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `record_table`
--



-- --------------------------------------------------------

--
-- Table structure for table `scheduling_table`
--

CREATE TABLE `scheduling_table` (
  `Schedule_ID` int(5) NOT NULL,
  `User_ID` int(10) DEFAULT NULL,
  `Sched_time` time DEFAULT NULL,
  `End_time` time DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Label` varchar(255) DEFAULT NULL,
  `List` varchar(255) NOT NULL,
  `Status_ID` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scheduling_table`
--


-- --------------------------------------------------------

--
-- Table structure for table `section_table`
--

CREATE TABLE `section_table` (
  `Section_ID` int(10) NOT NULL,
  `Section_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_table`
--

INSERT INTO `section_table` (`Section_ID`, `Section_name`) VALUES
(1, 'Section A'),
(2, 'Section B'),
(3, 'Section C'),
(4, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `status_table`
--

CREATE TABLE `status_table` (
  `Status_ID` int(5) NOT NULL,
  `Status_Name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_table`
--

INSERT INTO `status_table` (`Status_ID`, `Status_Name`) VALUES
(1, 'Available'),
(2, 'Unavailable'),
(3, 'In Use'),
(4, 'Approved'),
(5, 'Disapproved'),
(6, 'Present'),
(7, 'Late'),
(8, 'Pending'),
(9, 'Returned'),
(10, 'Scheduled'),
(11, 'Entry');

-- --------------------------------------------------------

--
-- Table structure for table `userschedule_table`
--

CREATE TABLE `userschedule_table` (
  `Usersched_ID` int(5) NOT NULL,
  `User_ID` int(10) DEFAULT NULL,
  `Schedule_ID` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usertype_table`
--

CREATE TABLE `usertype_table` (
  `Usertype_ID` int(5) NOT NULL,
  `Type_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertype_table`
--

INSERT INTO `usertype_table` (`Usertype_ID`, `Type_name`) VALUES
(1, 'Admin'),
(2, 'Faculty'),
(3, 'Student');


-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `User_ID` int(10) NOT NULL,
  `School_ID` varchar(255) DEFAULT NULL,
  `First_name` varchar(255) DEFAULT NULL,
  `Middle_name` varchar(255) DEFAULT NULL,
  `Last_name` varchar(255) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Birthday` date DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Contact_num` varchar(11) DEFAULT NULL,
  `Usertype_ID` int(5) DEFAULT NULL,
  `Course_ID` int(10) DEFAULT NULL,
  `Year_ID` int(10) DEFAULT NULL,
  `Section_ID` int(10) DEFAULT NULL,
  `User_email` varchar(255) DEFAULT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`User_ID`, `School_ID`, `First_name`, `Middle_name`, `Last_name`, `Gender`, `Birthday`, `Address`, `Contact_num`, `Usertype_ID`, `Course_ID`, `Year_ID`, `Section_ID`, `User_email`, `password`) VALUES
(1, 'c2423535', 'Louie', 'Podawan', 'Aguilar', 'Male', '2024-10-25', 'Polangui', '09356776243', 3, 2, 4, 2, 'louie@my.cspc.edu.ph', '21232f297a57a5a743894a0e4a801fc3'),
(2, 'c2423535', 'John paul', 'Sedonio', 'Manansala', 'Male', '2024-10-25', 'Iriga', '0923456382', 1, NULL, NULL, NULL, 'jampol@my.cspc.edu.ph', '21232f297a57a5a743894a0e4a801fc3'),
(3, 'c2423535', 'Julius', 'S', 'Sayson', 'Male', '2024-10-25', 'Agus', '09234557182', 2, NULL, NULL, NULL, 'julius@my.cspc.edu.ph', '21232f297a57a5a743894a0e4a801fc3');

-- --------------------------------------------------------

--
-- Table structure for table `year_table`
--

CREATE TABLE `year_table` (
  `Year_ID` int(10) NOT NULL,
  `Year_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `year_table`
--

INSERT INTO `year_table` (`Year_ID`, `Year_name`) VALUES
(1, 'First Year'),
(2, 'Second Year'),
(3, 'Third Year'),
(4, 'Fourth Year'),
(5, 'None');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_table`
--
ALTER TABLE `attendance_table`
  ADD PRIMARY KEY (`Attendance_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Labclass_ID` (`Labclass_ID`),
  ADD KEY `Record_ID` (`Record_ID`),
  ADD KEY `Status_ID` (`Status_ID`);

--
-- Indexes for table `borrow`
--
ALTER TABLE `borrow`
  ADD PRIMARY KEY (`Borrow_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `iPad_ID` (`iPad_ID`);

--
-- Indexes for table `course_table`
--
ALTER TABLE `course_table`
  ADD PRIMARY KEY (`Course_ID`);

--
-- Indexes for table `enrollment_table`
--
ALTER TABLE `enrollment_table`
  ADD PRIMARY KEY (`Enrollment_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Course_ID` (`Course_ID`),
  ADD KEY `Year_ID` (`Year_ID`),
  ADD KEY `Section_ID` (`Section_ID`),
  ADD KEY `Labclass_ID` (`Labclass_ID`);

--
-- Indexes for table `entry_table`
--
ALTER TABLE `entry_table`
  ADD PRIMARY KEY (`Entry_ID`),
  ADD UNIQUE KEY `unique_entry` (`Record_ID`,`User_ID`,`Date_time`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `fingerprint_table`
--
ALTER TABLE `fingerprint_table`
  ADD PRIMARY KEY (`Fingerprint_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `ipad_table`
--
ALTER TABLE `ipad_table`
  ADD PRIMARY KEY (`iPad_ID`),
  ADD KEY `Status_ID` (`Status_ID`);

--
-- Indexes for table `laboratory_class`
--
ALTER TABLE `laboratory_class`
  ADD PRIMARY KEY (`Labclass_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `fk_year_id` (`Year_ID`);

--
-- Indexes for table `record_table`
--
ALTER TABLE `record_table`
  ADD PRIMARY KEY (`Record_ID`),
  ADD KEY `Fingerprint_ID` (`Fingerprint_ID`);

--
-- Indexes for table `scheduling_table`
--
ALTER TABLE `scheduling_table`
  ADD PRIMARY KEY (`Schedule_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Status_ID` (`Status_ID`);

--
-- Indexes for table `section_table`
--
ALTER TABLE `section_table`
  ADD PRIMARY KEY (`Section_ID`);

--
-- Indexes for table `status_table`
--
ALTER TABLE `status_table`
  ADD PRIMARY KEY (`Status_ID`);

--
-- Indexes for table `userschedule_table`
--
ALTER TABLE `userschedule_table`
  ADD PRIMARY KEY (`Usersched_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Schedule_ID` (`Schedule_ID`);

--
-- Indexes for table `usertype_table`
--
ALTER TABLE `usertype_table`
  ADD PRIMARY KEY (`Usertype_ID`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`User_ID`),
  ADD KEY `Usertype_ID` (`Usertype_ID`),
  ADD KEY `Course_ID` (`Course_ID`),
  ADD KEY `Year_ID` (`Year_ID`),
  ADD KEY `Section_ID` (`Section_ID`);

--
-- Indexes for table `year_table`
--
ALTER TABLE `year_table`
  ADD PRIMARY KEY (`Year_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_table`
--
ALTER TABLE `attendance_table`
  MODIFY `Attendance_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `borrow`
--
ALTER TABLE `borrow`
  MODIFY `Borrow_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `course_table`
--
ALTER TABLE `course_table`
  MODIFY `Course_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollment_table`
--
ALTER TABLE `enrollment_table`
  MODIFY `Enrollment_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `entry_table`
--
ALTER TABLE `entry_table`
  MODIFY `Entry_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `ipad_table`
--
ALTER TABLE `ipad_table`
  MODIFY `iPad_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `laboratory_class`
--
ALTER TABLE `laboratory_class`
  MODIFY `Labclass_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `record_table`
--
ALTER TABLE `record_table`
  MODIFY `Record_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `scheduling_table`
--
ALTER TABLE `scheduling_table`
  MODIFY `Schedule_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `section_table`
--
ALTER TABLE `section_table`
  MODIFY `Section_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `status_table`
--
ALTER TABLE `status_table`
  MODIFY `Status_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `userschedule_table`
--
ALTER TABLE `userschedule_table`
  MODIFY `Usersched_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `usertype_table`
--
ALTER TABLE `usertype_table`
  MODIFY `Usertype_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `User_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `year_table`
--
ALTER TABLE `year_table`
  MODIFY `Year_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_table`
--
ALTER TABLE `attendance_table`
  ADD CONSTRAINT `attendance_table_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`),
  ADD CONSTRAINT `attendance_table_ibfk_2` FOREIGN KEY (`Labclass_ID`) REFERENCES `laboratory_class` (`Labclass_ID`),
  ADD CONSTRAINT `attendance_table_ibfk_3` FOREIGN KEY (`Record_ID`) REFERENCES `record_table` (`Record_ID`),
  ADD CONSTRAINT `attendance_table_ibfk_4` FOREIGN KEY (`Status_ID`) REFERENCES `status_table` (`Status_ID`);

--
-- Constraints for table `borrow`
--
ALTER TABLE `borrow`
  ADD CONSTRAINT `borrow_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`),
  ADD CONSTRAINT `borrow_ibfk_2` FOREIGN KEY (`iPad_ID`) REFERENCES `ipad_table` (`iPad_ID`);

--
-- Constraints for table `enrollment_table`
--
ALTER TABLE `enrollment_table`
  ADD CONSTRAINT `enrollment_table_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_table_ibfk_2` FOREIGN KEY (`Course_ID`) REFERENCES `course_table` (`Course_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_table_ibfk_3` FOREIGN KEY (`Year_ID`) REFERENCES `year_table` (`Year_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_table_ibfk_4` FOREIGN KEY (`Section_ID`) REFERENCES `section_table` (`Section_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_table_ibfk_5` FOREIGN KEY (`Labclass_ID`) REFERENCES `laboratory_class` (`Labclass_ID`) ON DELETE SET NULL;

--
-- Constraints for table `entry_table`
--
ALTER TABLE `entry_table`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fingerprint_table`
--
ALTER TABLE `fingerprint_table`
  ADD CONSTRAINT `fingerprint_table_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`);

--
-- Constraints for table `ipad_table`
--
ALTER TABLE `ipad_table`
  ADD CONSTRAINT `ipad_table_ibfk_1` FOREIGN KEY (`Status_ID`) REFERENCES `status_table` (`Status_ID`);

--
-- Constraints for table `laboratory_class`
--
ALTER TABLE `laboratory_class`
  ADD CONSTRAINT `fk_year_id` FOREIGN KEY (`Year_ID`) REFERENCES `year_table` (`Year_ID`),
  ADD CONSTRAINT `laboratory_class_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`);

--
-- Constraints for table `record_table`
--
ALTER TABLE `record_table`
  ADD CONSTRAINT `record_table_ibfk_1` FOREIGN KEY (`Fingerprint_ID`) REFERENCES `fingerprint_table` (`Fingerprint_ID`);

--
-- Constraints for table `scheduling_table`
--
ALTER TABLE `scheduling_table`
  ADD CONSTRAINT `scheduling_table_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`),
  ADD CONSTRAINT `scheduling_table_ibfk_2` FOREIGN KEY (`Status_ID`) REFERENCES `status_table` (`Status_ID`);

--
-- Constraints for table `userschedule_table`
--
ALTER TABLE `userschedule_table`
  ADD CONSTRAINT `userschedule_table_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`),
  ADD CONSTRAINT `userschedule_table_ibfk_2` FOREIGN KEY (`Schedule_ID`) REFERENCES `scheduling_table` (`Schedule_ID`);

--
-- Constraints for table `user_table`
--
ALTER TABLE `user_table`
  ADD CONSTRAINT `user_table_ibfk_1` FOREIGN KEY (`Usertype_ID`) REFERENCES `usertype_table` (`Usertype_ID`),
  ADD CONSTRAINT `user_table_ibfk_2` FOREIGN KEY (`Course_ID`) REFERENCES `course_table` (`Course_ID`),
  ADD CONSTRAINT `user_table_ibfk_3` FOREIGN KEY (`Year_ID`) REFERENCES `year_table` (`Year_ID`),
  ADD CONSTRAINT `user_table_ibfk_4` FOREIGN KEY (`Section_ID`) REFERENCES `section_table` (`Section_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
