-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 12, 2026 at 07:20 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spftrack_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_year`
--

DROP TABLE IF EXISTS `academic_year`;
CREATE TABLE IF NOT EXISTS `academic_year` (
  `year_id` int NOT NULL AUTO_INCREMENT,
  `year_name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_year`
--

INSERT INTO `academic_year` (`year_id`, `year_name`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, '2020/2021', '2020-09-01', '2021-08-31', 'completed', '2026-03-11 13:52:00', '2026-03-11 13:52:00'),
(2, '2021/2022', '2021-09-01', '2022-08-31', 'completed', '2026-03-11 13:52:00', '2026-03-11 13:52:00'),
(3, '2022/2023', '2022-09-01', '2023-08-31', 'completed', '2026-03-11 13:52:00', '2026-03-11 13:52:00'),
(4, '2023/2024', '2023-09-01', '2024-08-31', 'completed', '2026-03-11 13:52:00', '2026-03-11 13:52:00'),
(5, '2024/2025', '2024-09-01', '2025-08-31', 'active', '2026-03-11 13:52:00', '2026-03-11 13:52:00'),
(6, '2025/2026', '2025-09-01', '2026-08-31', 'upcoming', '2026-03-11 13:52:00', '2026-03-11 13:52:00'),
(7, '2026/2027', '2026-09-01', '2027-08-31', 'upcoming', '2026-03-11 13:52:00', '2026-03-11 13:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `admin_email` (`admin_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `created_at`, `updated_at`) VALUES
(1, 'Elham', 'elham@gmail.com', '1234', '2026-03-11 13:52:01', '2026-03-11 13:52:01');

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

DROP TABLE IF EXISTS `assignment`;
CREATE TABLE IF NOT EXISTS `assignment` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `courseUnit_id` int DEFAULT NULL,
  `assignment_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assignment_description` text COLLATE utf8mb4_unicode_ci,
  `due_date` datetime DEFAULT NULL,
  `max_score` decimal(5,2) DEFAULT '100.00',
  `assignment_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `fk_assignment_courseunit` (`courseUnit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignment_id`, `courseUnit_id`, `assignment_title`, `assignment_description`, `due_date`, `max_score`, `assignment_created`, `created_at`, `updated_at`) VALUES
(1, 7, 'Create java application', 'Don\'t forget to made it work very well ', '2026-03-23 00:00:00', 20.00, '2026-03-12 06:34:33', '2026-03-12 06:34:33', '2026-03-12 06:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE IF NOT EXISTS `course` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `course_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`),
  KEY `fk_course_department` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `course_name`, `department_id`, `created_at`, `updated_at`) VALUES
(38, 'Computer science', 11, '2026-03-11 13:52:01', '2026-03-11 17:58:19');

-- --------------------------------------------------------

--
-- Table structure for table `courseunit`
--

DROP TABLE IF EXISTS `courseunit`;
CREATE TABLE IF NOT EXISTS `courseunit` (
  `courseUnit_id` int NOT NULL AUTO_INCREMENT,
  `courseUnit_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `courseUnit_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `teacher_id` int DEFAULT NULL,
  `semester_id` int DEFAULT NULL,
  `course_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`courseUnit_id`),
  UNIQUE KEY `courseUnit_code` (`courseUnit_code`),
  KEY `fk_courseunit_teacher` (`teacher_id`),
  KEY `fk_courseunit_semester` (`semester_id`),
  KEY `fk_courseunit_course` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courseunit`
--

INSERT INTO `courseunit` (`courseUnit_id`, `courseUnit_name`, `courseUnit_code`, `description`, `start_date`, `end_date`, `teacher_id`, `semester_id`, `course_id`, `created_at`, `updated_at`) VALUES
(7, 'Introduction to java', 'JAVA 101', 'it\'s good course unit for computer science ', NULL, NULL, 11, 5, 38, '2026-03-11 13:52:01', '2026-03-11 17:56:58');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
CREATE TABLE IF NOT EXISTS `department` (
  `department_id` int NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`department_id`),
  KEY `fk_department_hod` (`teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `teacher_id`, `created_at`, `updated_at`) VALUES
(11, 'Science & technology', 11, '2026-03-11 13:52:01', '2026-03-11 17:57:57');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

DROP TABLE IF EXISTS `enrollment`;
CREATE TABLE IF NOT EXISTS `enrollment` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `enrollment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `enrollment_status` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'enrolled',
  `semester_id` int DEFAULT NULL,
  `year_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`enrollment_id`),
  KEY `fk_enrollment_student` (`student_id`),
  KEY `fk_enrollment_semester` (`semester_id`),
  KEY `fk_enrollment_year` (`year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`enrollment_id`, `student_id`, `enrollment_date`, `enrollment_status`, `semester_id`, `year_id`, `created_at`, `updated_at`) VALUES
(7, 6, '2026-03-11 13:52:02', 'enrolled', 5, 7, '2026-03-11 13:52:02', '2026-03-11 17:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `grade`
--

DROP TABLE IF EXISTS `grade`;
CREATE TABLE IF NOT EXISTS `grade` (
  `grade_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `grade_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`grade_id`),
  KEY `fk_grade_submission` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
CREATE TABLE IF NOT EXISTS `materials` (
  `material_id` int NOT NULL AUTO_INCREMENT,
  `courseUnit_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `material_title` varchar(255) NOT NULL,
  `material_description` text,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `sender_type` varchar(20) NOT NULL,
  `receiver_id` int NOT NULL,
  `receiver_type` varchar(20) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `sender_type`, `receiver_id`, `receiver_type`, `message_text`, `is_read`, `created_at`) VALUES
(1, 1, 'admin', 11, 'teacher', 'Hi, how are you?', 1, '2026-03-12 05:53:15'),
(2, 1, 'admin', 6, 'student', 'Hi, how are you?', 1, '2026-03-12 05:53:35'),
(3, 6, 'student', 1, 'admin', 'fine, thank you', 1, '2026-03-12 05:54:22'),
(4, 11, 'teacher', 1, 'admin', 'fine, thank you', 1, '2026-03-12 05:55:35'),
(5, 11, 'teacher', 1, 'admin', 'fine, thank you', 1, '2026-03-12 06:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `teacher_id` int DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `notification_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_status` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  `sender_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_sent` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `fk_notification_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progressreport`
--

DROP TABLE IF EXISTS `progressreport`;
CREATE TABLE IF NOT EXISTS `progressreport` (
  `progressReport_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `courseUnit_id` int DEFAULT NULL,
  `average_score` decimal(5,2) DEFAULT NULL,
  `progress_level` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`progressReport_id`),
  KEY `fk_progressreport_student` (`student_id`),
  KEY `fk_progressreport_courseunit` (`courseUnit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result`
--

DROP TABLE IF EXISTS `result`;
CREATE TABLE IF NOT EXISTS `result` (
  `result_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int DEFAULT NULL,
  `grade_id` int DEFAULT NULL,
  `cw_mark` int DEFAULT NULL,
  `cw_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_mark` int DEFAULT NULL,
  `final_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`result_id`),
  KEY `fk_result_submission` (`submission_id`),
  KEY `fk_result_grade` (`grade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
CREATE TABLE IF NOT EXISTS `schools` (
  `school_id` int NOT NULL AUTO_INCREMENT,
  `school_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_address` text COLLATE utf8mb4_unicode_ci,
  `school_contact` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `school_name`, `school_address`, `school_contact`, `created_at`, `updated_at`) VALUES
(1, 'IUEA', 'Kansanga', '0765434567', '2026-03-11 17:56:06', '2026-03-11 17:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

DROP TABLE IF EXISTS `semester`;
CREATE TABLE IF NOT EXISTS `semester` (
  `semester_id` int NOT NULL AUTO_INCREMENT,
  `semester_name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`semester_id`),
  KEY `fk_semester_course` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`semester_id`, `semester_name`, `course_id`, `created_at`, `updated_at`) VALUES
(5, 'Semester1', 38, '2026-03-11 13:52:01', '2026-03-11 13:52:01');

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `session_name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`session_id`, `session_name`, `created_at`, `updated_at`) VALUES
(5, 'Day', '2026-03-11 13:52:01', '2026-03-11 17:57:35'),
(6, 'Weekend', '2026-03-11 13:52:01', '2026-03-11 17:57:19');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `student_id` int NOT NULL AUTO_INCREMENT,
  `student_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `student_enrollmentDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `course_id` int DEFAULT NULL,
  `session_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_email` (`student_email`),
  KEY `fk_student_course` (`course_id`),
  KEY `fk_student_session` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `student_email`, `student_password`, `date_of_birth`, `age`, `gender`, `phone_number`, `address`, `student_enrollmentDate`, `course_id`, `session_id`, `created_at`, `updated_at`) VALUES
(6, 'Mohammed', 'mohammed@gmail.com', '123456', '2009-06-08', NULL, 'Male', '0876543567', 'nattete', '2026-03-11 13:52:02', 38, 5, '2026-03-11 13:52:02', '2026-03-11 18:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

DROP TABLE IF EXISTS `submission`;
CREATE TABLE IF NOT EXISTS `submission` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `submission_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submission_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `submission_status` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `score` decimal(5,2) DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `courseUnit_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`submission_id`),
  KEY `fk_submission_assignment` (`assignment_id`),
  KEY `fk_submission_student` (`student_id`),
  KEY `fk_submission_courseunit` (`courseUnit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`submission_id`, `assignment_id`, `student_id`, `submission_file`, `submission_date`, `submission_status`, `score`, `comments`, `courseUnit_id`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 'uploads/submissions/submission_6_1_1773297622.pdf', '2026-03-12 03:40:22', 'graded', 18.00, 'It\'s good keep it this way', NULL, '2026-03-12 06:40:22', '2026-03-12 07:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

DROP TABLE IF EXISTS `teacher`;
CREATE TABLE IF NOT EXISTS `teacher` (
  `teacher_id` int NOT NULL AUTO_INCREMENT,
  `teacher_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `date_joined` date DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `courseUnit_id` int DEFAULT NULL,
  `user_type` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT 'teacher',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `teacher_email` (`teacher_email`),
  KEY `fk_teacher_department` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `teacher_name`, `teacher_email`, `teacher_password`, `date_of_birth`, `age`, `gender`, `phone_number`, `address`, `date_joined`, `department_id`, `courseUnit_id`, `user_type`, `created_at`, `updated_at`) VALUES
(11, 'Jamilla', 'jamilla@gmail.com', '12345', '2005-01-11', NULL, 'Female', '0765435678', 'nattete', NULL, 11, 7, 'teacher', '2026-03-11 13:52:01', '2026-03-11 18:02:07');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE SET NULL;

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `fk_assignment_courseunit` FOREIGN KEY (`courseUnit_id`) REFERENCES `courseunit` (`courseUnit_id`) ON DELETE CASCADE;

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_course_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_course_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE CASCADE;

--
-- Constraints for table `courseunit`
--
ALTER TABLE `courseunit`
  ADD CONSTRAINT `fk_courseunit_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_courseunit_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_courseunit_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_courseunit_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `fk_department_hod` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_department_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `fk_enrollment_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_enrollment_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollment_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollment_year` FOREIGN KEY (`year_id`) REFERENCES `academic_year` (`year_id`) ON DELETE CASCADE;

--
-- Constraints for table `grade`
--
ALTER TABLE `grade`
  ADD CONSTRAINT `fk_grade_submission` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `progressreport`
--
ALTER TABLE `progressreport`
  ADD CONSTRAINT `fk_progressreport_courseunit` FOREIGN KEY (`courseUnit_id`) REFERENCES `courseunit` (`courseUnit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progressreport_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `fk_result_grade` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`grade_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_result_submission` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`) ON DELETE CASCADE;

--
-- Constraints for table `semester`
--
ALTER TABLE `semester`
  ADD CONSTRAINT `fk_semester_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_student_session` FOREIGN KEY (`session_id`) REFERENCES `session` (`session_id`) ON DELETE SET NULL;

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `fk_submission_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_submission_courseunit` FOREIGN KEY (`courseUnit_id`) REFERENCES `courseunit` (`courseUnit_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_submission_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_teacher_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
