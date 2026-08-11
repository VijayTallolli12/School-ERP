-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table school_erp.academic_calendars: ~2 rows (approximately)
INSERT INTO `academic_calendars` (`id`, `school_id`, `academic_year_id`, `title`, `event_type`, `start_date`, `end_date`, `description`, `audience`, `location`, `is_published`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 'Independence Day Celebration', 'holiday', '2026-10-10', NULL, 'School will remain closed for Independence Day.', 'all', NULL, 1, 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 1, 'Parent-Teacher Meeting', 'meeting', '2026-09-10', NULL, 'PTM for all classes.', 'parents', NULL, 1, 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.academic_terms: ~2 rows (approximately)
INSERT INTO `academic_terms` (`id`, `school_id`, `academic_year_id`, `name`, `starts_on`, `ends_on`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 'Term 1', '2026-04-01', '2026-09-30', 1, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(2, 1, 1, 'Term 2', '2026-10-01', '2027-03-31', 2, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL);

-- Dumping data for table school_erp.academic_years: ~1 rows (approximately)
INSERT INTO `academic_years` (`id`, `school_id`, `name`, `starts_on`, `ends_on`, `is_active`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, '2026-2027', '2026-04-01', '2027-03-31', 1, 'active', '2026-08-10 09:39:36', '2026-08-10 09:39:36', NULL);

-- Dumping data for table school_erp.activity_log: ~0 rows (approximately)

-- Dumping data for table school_erp.admissions: ~0 rows (approximately)

-- Dumping data for table school_erp.admission_documents: ~0 rows (approximately)

-- Dumping data for table school_erp.agent_executions: ~0 rows (approximately)

-- Dumping data for table school_erp.ai_query_logs: ~0 rows (approximately)

-- Dumping data for table school_erp.attendances: ~60 rows (approximately)
INSERT INTO `attendances` (`id`, `school_id`, `student_id`, `class_section_id`, `academic_year_id`, `attendance_date`, `status`, `marked_by`, `remarks`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 1, '2026-07-13', 'present', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(2, 1, 2, 2, 1, '2026-07-13', 'excused', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(3, 1, 3, 3, 1, '2026-07-13', 'late', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(4, 1, 1, 1, 1, '2026-07-14', 'late', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(5, 1, 2, 2, 1, '2026-07-14', 'present', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(6, 1, 3, 3, 1, '2026-07-14', 'present', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(7, 1, 1, 1, 1, '2026-07-15', 'late', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(8, 1, 2, 2, 1, '2026-07-15', 'late', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(9, 1, 3, 3, 1, '2026-07-15', 'absent', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(10, 1, 1, 1, 1, '2026-07-16', 'late', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(11, 1, 2, 2, 1, '2026-07-16', 'absent', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(12, 1, 3, 3, 1, '2026-07-16', 'present', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(13, 1, 1, 1, 1, '2026-07-17', 'late', 1, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(14, 1, 2, 2, 1, '2026-07-17', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(15, 1, 3, 3, 1, '2026-07-17', 'half_day', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(16, 1, 1, 1, 1, '2026-07-20', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(17, 1, 2, 2, 1, '2026-07-20', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(18, 1, 3, 3, 1, '2026-07-20', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(19, 1, 1, 1, 1, '2026-07-21', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(20, 1, 2, 2, 1, '2026-07-21', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(21, 1, 3, 3, 1, '2026-07-21', 'half_day', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(22, 1, 1, 1, 1, '2026-07-22', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(23, 1, 2, 2, 1, '2026-07-22', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(24, 1, 3, 3, 1, '2026-07-22', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(25, 1, 1, 1, 1, '2026-07-23', 'excused', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(26, 1, 2, 2, 1, '2026-07-23', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(27, 1, 3, 3, 1, '2026-07-23', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(28, 1, 1, 1, 1, '2026-07-24', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(29, 1, 2, 2, 1, '2026-07-24', 'half_day', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(30, 1, 3, 3, 1, '2026-07-24', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(31, 1, 1, 1, 1, '2026-07-27', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(32, 1, 2, 2, 1, '2026-07-27', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(33, 1, 3, 3, 1, '2026-07-27', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(34, 1, 1, 1, 1, '2026-07-28', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(35, 1, 2, 2, 1, '2026-07-28', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(36, 1, 3, 3, 1, '2026-07-28', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(37, 1, 1, 1, 1, '2026-07-29', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(38, 1, 2, 2, 1, '2026-07-29', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(39, 1, 3, 3, 1, '2026-07-29', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(40, 1, 1, 1, 1, '2026-07-30', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(41, 1, 2, 2, 1, '2026-07-30', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(42, 1, 3, 3, 1, '2026-07-30', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(43, 1, 1, 1, 1, '2026-07-31', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(44, 1, 2, 2, 1, '2026-07-31', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(45, 1, 3, 3, 1, '2026-07-31', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(46, 1, 1, 1, 1, '2026-08-03', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(47, 1, 2, 2, 1, '2026-08-03', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(48, 1, 3, 3, 1, '2026-08-03', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(49, 1, 1, 1, 1, '2026-08-04', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(50, 1, 2, 2, 1, '2026-08-04', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(51, 1, 3, 3, 1, '2026-08-04', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(52, 1, 1, 1, 1, '2026-08-05', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(53, 1, 2, 2, 1, '2026-08-05', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(54, 1, 3, 3, 1, '2026-08-05', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(55, 1, 1, 1, 1, '2026-08-06', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(56, 1, 2, 2, 1, '2026-08-06', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(57, 1, 3, 3, 1, '2026-08-06', 'present', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(58, 1, 1, 1, 1, '2026-08-07', 'late', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(59, 1, 2, 2, 1, '2026-08-07', 'absent', 1, NULL, '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(60, 1, 3, 3, 1, '2026-08-07', 'excused', 1, 'Medical leave', '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL);

-- Dumping data for table school_erp.cache: ~0 rows (approximately)

-- Dumping data for table school_erp.cache_locks: ~0 rows (approximately)

-- Dumping data for table school_erp.classes: ~5 rows (approximately)
INSERT INTO `classes` (`id`, `school_id`, `name`, `code`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Class 1', 'C1', 1, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL),
	(2, 1, 'Class 2', 'C2', 2, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL),
	(3, 1, 'Class 3', 'C3', 3, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL),
	(4, 1, 'Class 4', 'C4', 4, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL),
	(5, 1, 'Class 5', 'C5', 5, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL);

-- Dumping data for table school_erp.class_section: ~10 rows (approximately)
INSERT INTO `class_section` (`id`, `school_id`, `class_id`, `section_id`, `class_teacher_id`, `status`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, NULL, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52'),
	(2, 1, 1, 2, NULL, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52'),
	(3, 1, 2, 1, NULL, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52'),
	(4, 1, 2, 2, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53'),
	(5, 1, 3, 1, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53'),
	(6, 1, 3, 2, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53'),
	(7, 1, 4, 1, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53'),
	(8, 1, 4, 2, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53'),
	(9, 1, 5, 1, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53'),
	(10, 1, 5, 2, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53');

-- Dumping data for table school_erp.class_subjects: ~25 rows (approximately)
INSERT INTO `class_subjects` (`id`, `school_id`, `academic_year_id`, `class_id`, `subject_id`, `teacher_id`, `weekly_periods`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 1, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(2, 1, 1, 1, 2, NULL, 6, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(3, 1, 1, 1, 3, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(4, 1, 1, 1, 4, NULL, 4, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(5, 1, 1, 1, 5, NULL, 3, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(6, 1, 1, 2, 1, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(7, 1, 1, 2, 2, NULL, 6, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(8, 1, 1, 2, 3, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(9, 1, 1, 2, 4, NULL, 4, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(10, 1, 1, 2, 5, NULL, 3, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(11, 1, 1, 3, 1, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(12, 1, 1, 3, 2, NULL, 6, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(13, 1, 1, 3, 3, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(14, 1, 1, 3, 4, NULL, 4, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(15, 1, 1, 3, 5, NULL, 3, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(16, 1, 1, 4, 1, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(17, 1, 1, 4, 2, NULL, 6, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(18, 1, 1, 4, 3, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(19, 1, 1, 4, 4, NULL, 4, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(20, 1, 1, 4, 5, NULL, 3, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(21, 1, 1, 5, 1, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(22, 1, 1, 5, 2, NULL, 6, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(23, 1, 1, 5, 3, NULL, 5, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(24, 1, 1, 5, 4, NULL, 4, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(25, 1, 1, 5, 5, NULL, 3, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL);

-- Dumping data for table school_erp.drivers: ~4 rows (approximately)
INSERT INTO `drivers` (`id`, `school_id`, `name`, `mobile`, `license_number`, `license_expiry_date`, `address`, `status`, `created_at`, `updated_at`, `deleted_at`, `user_id`) VALUES
	(1, 1, 'Rajesh Kumar', '9876500001', 'DL-2024-IND-001', '2031-08-10', '45 Station Road, Old Town, Demo City', 'active', '2026-08-10 09:40:06', '2026-08-10 09:40:06', NULL, 11),
	(2, 1, 'Suresh Patil', '9876500002', 'DL-2024-IND-002', '2031-08-10', '102 Lake View Colony, Demo City', 'active', '2026-08-10 09:40:08', '2026-08-10 09:40:08', NULL, 12),
	(3, 1, 'Mahesh Gowda', '9876500003', 'DL-2024-IND-003', '2031-08-10', '7 Market Square Road, Demo City', 'active', '2026-08-10 09:40:09', '2026-08-10 09:40:09', NULL, 13),
	(4, 1, 'Venkatesh Iyer', '9876500004', 'DL-2024-IND-004', '2031-08-10', '220 Airport Road, Demo City', 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL, 14);

-- Dumping data for table school_erp.driver_sos_alerts: ~0 rows (approximately)

-- Dumping data for table school_erp.employees: ~0 rows (approximately)

-- Dumping data for table school_erp.employee_contracts: ~0 rows (approximately)

-- Dumping data for table school_erp.employee_documents: ~0 rows (approximately)

-- Dumping data for table school_erp.employee_payslips: ~1 rows (approximately)
INSERT INTO `employee_payslips` (`id`, `school_id`, `payroll_run_id`, `payroll_item_id`, `payslip_number`, `employee_type`, `employee_id`, `employee_name`, `department_name`, `designation_name`, `earnings_json`, `deductions_json`, `gross_salary`, `total_deductions`, `net_salary`, `generated_by`, `generated_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 'PSL-1-1', 'teacher', 'T-1001', 'Aisha Khan', 'Teaching Staff', 'Senior Teacher', '[{"name": "Basic Salary", "amount": 35000}, {"name": "HRA", "amount": 3500}]', '[{"name": "Provident Fund", "amount": 4200}, {"name": "Income Tax", "amount": 2500}]', 38500.00, 6700.00, 31800.00, 1, '2026-08-10 09:40:19', NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19');

-- Dumping data for table school_erp.employee_salary_structures: ~1 rows (approximately)
INSERT INTO `employee_salary_structures` (`id`, `school_id`, `employee_id`, `employee_type`, `pay_grade_id`, `effective_from`, `effective_to`, `total_ctc`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'T-1001', 'teacher', 1, '2026-04-01', NULL, 420000.00, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.exams: ~50 rows (approximately)
INSERT INTO `exams` (`id`, `school_id`, `uuid`, `academic_year_id`, `class_section_id`, `subject_id`, `exam_name`, `exam_type`, `exam_date`, `maximum_marks`, `pass_marks`, `status`, `is_published`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, NULL, 1, 1, 5, 'Mid Term Exam', 'mid_term', '2026-07-03', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(2, 1, NULL, 1, 1, 1, 'Mid Term Exam', 'mid_term', '2026-06-22', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(3, 1, NULL, 1, 1, 2, 'Mid Term Exam', 'mid_term', '2026-07-05', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(4, 1, NULL, 1, 1, 3, 'Mid Term Exam', 'mid_term', '2026-07-18', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(5, 1, NULL, 1, 1, 4, 'Mid Term Exam', 'mid_term', '2026-07-25', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(6, 1, NULL, 1, 2, 5, 'Mid Term Exam', 'mid_term', '2026-07-10', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(7, 1, NULL, 1, 2, 1, 'Mid Term Exam', 'mid_term', '2026-07-28', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(8, 1, NULL, 1, 2, 2, 'Mid Term Exam', 'mid_term', '2026-06-26', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(9, 1, NULL, 1, 2, 3, 'Mid Term Exam', 'mid_term', '2026-06-25', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(10, 1, NULL, 1, 2, 4, 'Mid Term Exam', 'mid_term', '2026-07-18', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(11, 1, NULL, 1, 3, 5, 'Mid Term Exam', 'mid_term', '2026-07-09', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(12, 1, NULL, 1, 3, 1, 'Mid Term Exam', 'mid_term', '2026-06-23', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(13, 1, NULL, 1, 3, 2, 'Mid Term Exam', 'mid_term', '2026-07-05', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(14, 1, NULL, 1, 3, 3, 'Mid Term Exam', 'mid_term', '2026-07-02', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(15, 1, NULL, 1, 3, 4, 'Mid Term Exam', 'mid_term', '2026-06-14', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(16, 1, NULL, 1, 4, 5, 'Mid Term Exam', 'mid_term', '2026-06-17', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(17, 1, NULL, 1, 4, 1, 'Mid Term Exam', 'mid_term', '2026-07-07', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(18, 1, NULL, 1, 4, 2, 'Mid Term Exam', 'mid_term', '2026-06-27', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(19, 1, NULL, 1, 4, 3, 'Mid Term Exam', 'mid_term', '2026-06-26', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(20, 1, NULL, 1, 4, 4, 'Mid Term Exam', 'mid_term', '2026-06-23', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(21, 1, NULL, 1, 5, 5, 'Mid Term Exam', 'mid_term', '2026-07-28', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(22, 1, NULL, 1, 5, 1, 'Mid Term Exam', 'mid_term', '2026-07-26', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(23, 1, NULL, 1, 5, 2, 'Mid Term Exam', 'mid_term', '2026-06-17', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(24, 1, NULL, 1, 5, 3, 'Mid Term Exam', 'mid_term', '2026-07-06', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(25, 1, NULL, 1, 5, 4, 'Mid Term Exam', 'mid_term', '2026-07-16', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(26, 1, NULL, 1, 6, 5, 'Mid Term Exam', 'mid_term', '2026-07-30', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(27, 1, NULL, 1, 6, 1, 'Mid Term Exam', 'mid_term', '2026-06-20', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(28, 1, NULL, 1, 6, 2, 'Mid Term Exam', 'mid_term', '2026-06-19', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(29, 1, NULL, 1, 6, 3, 'Mid Term Exam', 'mid_term', '2026-07-08', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(30, 1, NULL, 1, 6, 4, 'Mid Term Exam', 'mid_term', '2026-06-17', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(31, 1, NULL, 1, 7, 5, 'Mid Term Exam', 'mid_term', '2026-06-16', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(32, 1, NULL, 1, 7, 1, 'Mid Term Exam', 'mid_term', '2026-06-19', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(33, 1, NULL, 1, 7, 2, 'Mid Term Exam', 'mid_term', '2026-07-05', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(34, 1, NULL, 1, 7, 3, 'Mid Term Exam', 'mid_term', '2026-07-21', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(35, 1, NULL, 1, 7, 4, 'Mid Term Exam', 'mid_term', '2026-07-03', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(36, 1, NULL, 1, 8, 5, 'Mid Term Exam', 'mid_term', '2026-06-16', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(37, 1, NULL, 1, 8, 1, 'Mid Term Exam', 'mid_term', '2026-07-04', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(38, 1, NULL, 1, 8, 2, 'Mid Term Exam', 'mid_term', '2026-07-24', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(39, 1, NULL, 1, 8, 3, 'Mid Term Exam', 'mid_term', '2026-07-26', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(40, 1, NULL, 1, 8, 4, 'Mid Term Exam', 'mid_term', '2026-06-29', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(41, 1, NULL, 1, 9, 5, 'Mid Term Exam', 'mid_term', '2026-06-13', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(42, 1, NULL, 1, 9, 1, 'Mid Term Exam', 'mid_term', '2026-07-27', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(43, 1, NULL, 1, 9, 2, 'Mid Term Exam', 'mid_term', '2026-06-25', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(44, 1, NULL, 1, 9, 3, 'Mid Term Exam', 'mid_term', '2026-06-16', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(45, 1, NULL, 1, 9, 4, 'Mid Term Exam', 'mid_term', '2026-06-23', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(46, 1, NULL, 1, 10, 5, 'Mid Term Exam', 'mid_term', '2026-07-10', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(47, 1, NULL, 1, 10, 1, 'Mid Term Exam', 'mid_term', '2026-06-15', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(48, 1, NULL, 1, 10, 2, 'Mid Term Exam', 'mid_term', '2026-06-11', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(49, 1, NULL, 1, 10, 3, 'Mid Term Exam', 'mid_term', '2026-07-17', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(50, 1, NULL, 1, 10, 4, 'Mid Term Exam', 'mid_term', '2026-06-12', 100, 40, 'completed', 1, 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL);

-- Dumping data for table school_erp.exam_marks: ~0 rows (approximately)

-- Dumping data for table school_erp.exam_results: ~15 rows (approximately)
INSERT INTO `exam_results` (`id`, `school_id`, `exam_id`, `student_id`, `marks_obtained`, `grade`, `remarks`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 73, 'B', NULL, 'published', NULL, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(2, 1, 2, 1, 78, 'A', NULL, 'published', NULL, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(3, 1, 3, 1, 60, 'B', NULL, 'published', NULL, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(4, 1, 4, 1, 36, 'F', NULL, 'published', NULL, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(5, 1, 5, 1, 50, 'C', NULL, 'published', NULL, NULL, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(6, 1, 6, 2, 54, 'C', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(7, 1, 7, 2, 39, 'F', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(8, 1, 8, 2, 61, 'B', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(9, 1, 9, 2, 74, 'B', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(10, 1, 10, 2, 40, 'C', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(11, 1, 11, 3, 39, 'F', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(12, 1, 12, 3, 34, 'F', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(13, 1, 13, 3, 26, 'F', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(14, 1, 14, 3, 76, 'A', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL),
	(15, 1, 15, 3, 80, 'A', NULL, 'published', NULL, NULL, '2026-08-10 09:40:17', '2026-08-10 09:40:17', NULL);

-- Dumping data for table school_erp.exam_schedules: ~0 rows (approximately)

-- Dumping data for table school_erp.failed_jobs: ~0 rows (approximately)

-- Dumping data for table school_erp.fee_categories: ~5 rows (approximately)
INSERT INTO `fee_categories` (`id`, `school_id`, `code`, `name`, `description`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'tuition', 'Tuition', NULL, 0, '2026-08-10 09:39:54', '2026-08-10 09:39:54', NULL),
	(2, 1, 'transport', 'Transport', NULL, 1, '2026-08-10 09:39:54', '2026-08-10 09:39:54', NULL),
	(3, 1, 'hostel', 'Hostel', NULL, 2, '2026-08-10 09:39:54', '2026-08-10 09:39:54', NULL),
	(4, 1, 'exam', 'Exam Fees', NULL, 3, '2026-08-10 09:39:54', '2026-08-10 09:39:54', NULL),
	(5, 1, 'miscellaneous', 'Miscellaneous', NULL, 4, '2026-08-10 09:39:54', '2026-08-10 09:39:54', NULL);

-- Dumping data for table school_erp.fee_payments: ~2 rows (approximately)
INSERT INTO `fee_payments` (`id`, `school_id`, `student_id`, `academic_year_id`, `receipt_number`, `payment_mode`, `amount`, `remarks`, `paid_on`, `collected_by`, `voided_by`, `created_at`, `updated_at`, `deleted_at`, `status`, `void_reason`, `voided_at`) VALUES
	(1, 1, 1, 1, 'RCP-1-1', 'cheque', 1000.00, NULL, '2026-08-01', 1, NULL, '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL, 'completed', NULL, NULL),
	(2, 1, 3, 1, 'RCP-3-1', 'cheque', 3000.00, NULL, '2026-07-19', 1, NULL, '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL, 'completed', NULL, NULL);

-- Dumping data for table school_erp.fee_payment_items: ~2 rows (approximately)
INSERT INTO `fee_payment_items` (`id`, `school_id`, `fee_payment_id`, `student_fee_item_id`, `amount`, `created_at`, `updated_at`) VALUES
	(1, NULL, 1, 5, 1000.00, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(2, NULL, 2, 15, 3000.00, '2026-08-10 09:40:13', '2026-08-10 09:40:13');

-- Dumping data for table school_erp.fee_receipt_sequences: ~0 rows (approximately)

-- Dumping data for table school_erp.fee_structures: ~10 rows (approximately)
INSERT INTO `fee_structures` (`id`, `school_id`, `academic_year_id`, `class_section_id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 'Fee Structure - 1', 'active', '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(2, 1, 1, 2, 'Fee Structure - 2', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(3, 1, 1, 3, 'Fee Structure - 3', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(4, 1, 1, 4, 'Fee Structure - 4', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(5, 1, 1, 5, 'Fee Structure - 5', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(6, 1, 1, 6, 'Fee Structure - 6', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(7, 1, 1, 7, 'Fee Structure - 7', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(8, 1, 1, 8, 'Fee Structure - 8', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(9, 1, 1, 9, 'Fee Structure - 9', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(10, 1, 1, 10, 'Fee Structure - 10', 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL);

-- Dumping data for table school_erp.fee_structure_items: ~50 rows (approximately)
INSERT INTO `fee_structure_items` (`id`, `fee_structure_id`, `fee_category_id`, `amount`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 1, 4, 3469.00, 3, '2026-08-10 09:40:12', '2026-08-10 09:40:12'),
	(2, 1, 3, 4966.00, 2, '2026-08-10 09:40:12', '2026-08-10 09:40:12'),
	(3, 1, 5, 3250.00, 4, '2026-08-10 09:40:12', '2026-08-10 09:40:12'),
	(4, 1, 2, 3664.00, 1, '2026-08-10 09:40:12', '2026-08-10 09:40:12'),
	(5, 1, 1, 3611.00, 0, '2026-08-10 09:40:12', '2026-08-10 09:40:12'),
	(6, 2, 4, 3780.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(7, 2, 3, 2280.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(8, 2, 5, 3342.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(9, 2, 2, 4471.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(10, 2, 1, 4638.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(11, 3, 4, 2973.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(12, 3, 3, 4163.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(13, 3, 5, 1394.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(14, 3, 2, 1996.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(15, 3, 1, 4162.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(16, 4, 4, 1782.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(17, 4, 3, 1874.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(18, 4, 5, 3151.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(19, 4, 2, 3872.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(20, 4, 1, 1545.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(21, 5, 4, 3602.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(22, 5, 3, 2472.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(23, 5, 5, 3254.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(24, 5, 2, 2145.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(25, 5, 1, 4214.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(26, 6, 4, 4538.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(27, 6, 3, 2285.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(28, 6, 5, 3159.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(29, 6, 2, 2620.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(30, 6, 1, 3515.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(31, 7, 4, 4359.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(32, 7, 3, 566.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(33, 7, 5, 3229.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(34, 7, 2, 3100.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(35, 7, 1, 3658.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(36, 8, 4, 3465.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(37, 8, 3, 4742.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(38, 8, 5, 1206.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(39, 8, 2, 4415.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(40, 8, 1, 2052.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(41, 9, 4, 1471.00, 3, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(42, 9, 3, 2396.00, 2, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(43, 9, 5, 4294.00, 4, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(44, 9, 2, 3159.00, 1, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(45, 9, 1, 3156.00, 0, '2026-08-10 09:40:13', '2026-08-10 09:40:13'),
	(46, 10, 4, 4028.00, 3, '2026-08-10 09:40:14', '2026-08-10 09:40:14'),
	(47, 10, 3, 1441.00, 2, '2026-08-10 09:40:14', '2026-08-10 09:40:14'),
	(48, 10, 5, 3616.00, 4, '2026-08-10 09:40:14', '2026-08-10 09:40:14'),
	(49, 10, 2, 4236.00, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14'),
	(50, 10, 1, 2284.00, 0, '2026-08-10 09:40:14', '2026-08-10 09:40:14');

-- Dumping data for table school_erp.grade_scales: ~0 rows (approximately)

-- Dumping data for table school_erp.homework: ~50 rows (approximately)
INSERT INTO `homework` (`id`, `school_id`, `academic_year_id`, `class_section_id`, `subject_id`, `title`, `description`, `assigned_date`, `due_date`, `attachment`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 5.', '2026-07-30', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(2, 1, 1, 1, 1, 'English Assignment', 'Complete the exercises from Chapter 2.', '2026-08-02', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(3, 1, 1, 1, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 2.', '2026-08-04', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(4, 1, 1, 1, 3, 'Science Assignment', 'Complete the exercises from Chapter 7.', '2026-07-29', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(5, 1, 1, 1, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 10.', '2026-08-03', '2026-08-17', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(6, 1, 1, 2, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 6.', '2026-08-05', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(7, 1, 1, 2, 1, 'English Assignment', 'Complete the exercises from Chapter 7.', '2026-08-03', '2026-08-17', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(8, 1, 1, 2, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 9.', '2026-07-27', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(9, 1, 1, 2, 3, 'Science Assignment', 'Complete the exercises from Chapter 4.', '2026-08-08', '2026-08-16', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(10, 1, 1, 2, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 10.', '2026-08-04', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(11, 1, 1, 3, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 5.', '2026-08-01', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(12, 1, 1, 3, 1, 'English Assignment', 'Complete the exercises from Chapter 4.', '2026-08-06', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(13, 1, 1, 3, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 2.', '2026-07-31', '2026-08-12', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(14, 1, 1, 3, 3, 'Science Assignment', 'Complete the exercises from Chapter 9.', '2026-08-09', '2026-08-17', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(15, 1, 1, 3, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 10.', '2026-07-30', '2026-08-16', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(16, 1, 1, 4, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 3.', '2026-08-01', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(17, 1, 1, 4, 1, 'English Assignment', 'Complete the exercises from Chapter 4.', '2026-08-07', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(18, 1, 1, 4, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 6.', '2026-08-08', '2026-08-16', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(19, 1, 1, 4, 3, 'Science Assignment', 'Complete the exercises from Chapter 10.', '2026-08-03', '2026-08-17', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(20, 1, 1, 4, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 2.', '2026-08-04', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(21, 1, 1, 5, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 7.', '2026-07-28', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(22, 1, 1, 5, 1, 'English Assignment', 'Complete the exercises from Chapter 1.', '2026-08-03', '2026-08-11', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(23, 1, 1, 5, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 2.', '2026-07-28', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(24, 1, 1, 5, 3, 'Science Assignment', 'Complete the exercises from Chapter 9.', '2026-07-30', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(25, 1, 1, 5, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 2.', '2026-07-29', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(26, 1, 1, 6, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 3.', '2026-08-03', '2026-08-12', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(27, 1, 1, 6, 1, 'English Assignment', 'Complete the exercises from Chapter 9.', '2026-07-29', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(28, 1, 1, 6, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 4.', '2026-08-04', '2026-08-11', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(29, 1, 1, 6, 3, 'Science Assignment', 'Complete the exercises from Chapter 6.', '2026-08-06', '2026-08-12', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(30, 1, 1, 6, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 10.', '2026-08-03', '2026-08-17', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(31, 1, 1, 7, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 6.', '2026-08-08', '2026-08-17', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(32, 1, 1, 7, 1, 'English Assignment', 'Complete the exercises from Chapter 8.', '2026-07-31', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(33, 1, 1, 7, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 9.', '2026-07-30', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(34, 1, 1, 7, 3, 'Science Assignment', 'Complete the exercises from Chapter 3.', '2026-08-05', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(35, 1, 1, 7, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 6.', '2026-08-05', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(36, 1, 1, 8, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 6.', '2026-07-28', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(37, 1, 1, 8, 1, 'English Assignment', 'Complete the exercises from Chapter 9.', '2026-08-01', '2026-08-12', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(38, 1, 1, 8, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 4.', '2026-07-29', '2026-08-11', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(39, 1, 1, 8, 3, 'Science Assignment', 'Complete the exercises from Chapter 4.', '2026-08-01', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(40, 1, 1, 8, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 5.', '2026-07-27', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(41, 1, 1, 9, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 7.', '2026-08-06', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(42, 1, 1, 9, 1, 'English Assignment', 'Complete the exercises from Chapter 2.', '2026-08-08', '2026-08-12', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(43, 1, 1, 9, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 3.', '2026-08-01', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(44, 1, 1, 9, 3, 'Science Assignment', 'Complete the exercises from Chapter 6.', '2026-08-06', '2026-08-15', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(45, 1, 1, 9, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 9.', '2026-07-29', '2026-08-16', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(46, 1, 1, 10, 5, 'Computer Science Assignment', 'Complete the exercises from Chapter 6.', '2026-07-31', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(47, 1, 1, 10, 1, 'English Assignment', 'Complete the exercises from Chapter 1.', '2026-08-02', '2026-08-13', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(48, 1, 1, 10, 2, 'Mathematics Assignment', 'Complete the exercises from Chapter 3.', '2026-08-09', '2026-08-14', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(49, 1, 1, 10, 3, 'Science Assignment', 'Complete the exercises from Chapter 8.', '2026-07-31', '2026-08-11', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL),
	(50, 1, 1, 10, 4, 'Social Studies Assignment', 'Complete the exercises from Chapter 4.', '2026-08-02', '2026-08-12', NULL, 'active', 1, NULL, '2026-08-10 09:40:18', '2026-08-10 09:40:18', NULL);

-- Dumping data for table school_erp.jobs: ~0 rows (approximately)

-- Dumping data for table school_erp.job_batches: ~0 rows (approximately)

-- Dumping data for table school_erp.leave_requests: ~0 rows (approximately)

-- Dumping data for table school_erp.leave_types: ~4 rows (approximately)
INSERT INTO `leave_types` (`id`, `school_id`, `name`, `description`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Sick Leave', NULL, 1, 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 'Casual Leave', NULL, 1, 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(3, 1, 'Annual Leave', NULL, 1, 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(4, 1, 'Emergency Leave', NULL, 1, 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.library_authors: ~2 rows (approximately)
INSERT INTO `library_authors` (`id`, `school_id`, `name`, `biography`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'R.K. Narayan', NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 'J.K. Rowling', NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.library_books: ~5 rows (approximately)
INSERT INTO `library_books` (`id`, `school_id`, `isbn`, `title`, `category_id`, `author_id`, `publisher_id`, `edition`, `language`, `rack_number`, `quantity`, `available_copies`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, '978-0-19-123456-7', 'Mathematics for Class 5', 1, 1, 1, NULL, 'English', NULL, 10, 8, NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, '978-0-19-234567-8', 'English Grammar Guide', 1, 2, 2, NULL, 'English', NULL, 15, 12, NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(3, 1, '978-0-19-345678-9', 'Science Encyclopedia', 2, 1, 1, NULL, 'English', NULL, 5, 3, NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(4, 1, '978-0-19-456789-0', 'World History Atlas', 2, 2, 2, NULL, 'English', NULL, 8, 6, NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(5, 1, '978-0-19-567890-1', 'Computer Science Basics', 1, 1, 1, NULL, 'English', NULL, 12, 10, NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.library_categories: ~2 rows (approximately)
INSERT INTO `library_categories` (`id`, `school_id`, `name`, `description`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Academic', NULL, 1, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 'Reference', NULL, 2, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.library_fine_settings: ~1 rows (approximately)
INSERT INTO `library_fine_settings` (`id`, `school_id`, `fine_per_day`, `max_fine`, `grace_period_days`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 5.00, 500.00, 3, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.library_issues: ~2 rows (approximately)
INSERT INTO `library_issues` (`id`, `school_id`, `book_id`, `issueable_type`, `issueable_id`, `issue_date`, `due_date`, `return_date`, `fine_amount`, `fine_paid`, `notes`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 2, 'App\\Modules\\Students\\Models\\Student', 2, '2026-08-05', '2026-08-08', NULL, 0.00, 0, NULL, 'issued', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 4, 'App\\Modules\\Students\\Models\\Student', 2, '2026-07-31', '2026-08-07', NULL, 0.00, 0, NULL, 'issued', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.library_publishers: ~2 rows (approximately)
INSERT INTO `library_publishers` (`id`, `school_id`, `name`, `address`, `contact`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Oxford Press', NULL, 'info@oxfordpress.com', 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 'Scholastic', NULL, 'info@scholastic.com', 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.login_activities: ~0 rows (approximately)

-- Dumping data for table school_erp.migrations: ~73 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2024_01_01_000010_create_schools_table', 1),
	(5, '2024_01_01_000020_create_academic_years_table', 1),
	(6, '2024_01_01_000030_add_erp_fields_to_users_table', 1),
	(7, '2024_01_01_000040_create_school_user_table', 1),
	(8, '2024_01_01_000050_create_login_activities_table', 1),
	(9, '2024_01_02_000010_create_classes_table', 1),
	(10, '2024_01_02_000020_create_sections_table', 1),
	(11, '2024_01_02_000030_create_class_section_table', 1),
	(12, '2024_01_02_000040_create_subjects_table', 1),
	(13, '2024_01_02_000050_create_class_subjects_table', 1),
	(14, '2024_01_02_000060_create_academic_terms_table', 1),
	(15, '2024_01_03_000010_create_students_table', 1),
	(16, '2024_01_03_000020_create_student_guardians_table', 1),
	(17, '2024_01_03_000030_create_student_documents_table', 1),
	(18, '2024_01_03_000040_create_student_sessions_table', 1),
	(19, '2024_01_04_000010_create_attendances_table', 1),
	(20, '2024_01_05_000010_create_fee_categories_table', 1),
	(21, '2024_01_05_000020_create_fee_structures_table', 1),
	(22, '2024_01_05_000030_create_fee_structure_items_table', 1),
	(23, '2024_01_05_000040_create_student_fees_table', 1),
	(24, '2024_01_05_000050_create_student_fee_items_table', 1),
	(25, '2024_01_05_000060_create_fee_receipt_sequences_table', 1),
	(26, '2024_01_05_000070_create_fee_payments_table', 1),
	(27, '2024_01_05_000080_create_fee_payment_items_table', 1),
	(28, '2026_05_13_000010_create_teachers_module_tables', 1),
	(29, '2026_05_13_000020_create_exams_module_tables', 1),
	(30, '2026_05_13_075201_create_activity_log_table', 1),
	(31, '2026_05_13_075201_create_permission_tables', 1),
	(32, '2026_05_13_075202_add_event_column_to_activity_log_table', 1),
	(33, '2026_05_13_075202_create_personal_access_tokens_table', 1),
	(34, '2026_05_13_075203_add_batch_uuid_column_to_activity_log_table', 1),
	(35, '2026_05_14_000010_update_teacher_timetable_slots_for_timetable_module', 1),
	(36, '2026_05_15_000010_create_parents_table', 1),
	(37, '2026_05_15_000020_create_parent_student_table', 1),
	(38, '2026_05_15_000030_create_parent_notifications_table', 1),
	(39, '2026_05_17_000010_create_notifications_table', 1),
	(40, '2026_05_19_000010_fix_fee_structures_unique_constraint', 1),
	(41, '2026_05_19_000020_fix_student_fees_unique_constraint', 1),
	(42, '2026_05_19_000030_add_school_id_to_teacher_pivot_tables', 1),
	(43, '2026_06_04_000010_create_homework_tables', 1),
	(44, '2026_06_04_000020_create_leave_types_table', 1),
	(45, '2026_06_04_000021_create_leave_requests_table', 1),
	(46, '2026_06_04_000022_create_academic_calendars_table', 1),
	(47, '2026_06_04_000023_alter_student_documents_table', 1),
	(48, '2026_06_10_000001_add_performance_indexes', 1),
	(49, '2026_06_10_000002_add_school_id_to_timetable_slots', 1),
	(50, '2026_06_18_000001_create_transportation_tables', 1),
	(51, '2026_06_18_000002_add_search_indexes', 1),
	(52, '2026_06_19_000001_create_library_tables', 1),
	(53, '2026_06_19_000002_create_payroll_tables', 1),
	(54, '2026_06_19_000003_create_payroll_processing_tables', 1),
	(55, '2026_06_19_000004_create_employee_payslips_table', 1),
	(56, '2026_06_22_000001_create_agent_executions_table', 1),
	(57, '2026_06_23_000001_create_user_devices_table', 1),
	(58, '2026_06_23_000002_create_vehicle_locations_table', 1),
	(59, '2026_06_23_000003_add_user_id_to_drivers', 1),
	(60, '2026_06_29_000001_create_trips_table', 1),
	(61, '2026_06_29_000002_create_trip_students_table', 1),
	(62, '2026_06_29_000003_create_trip_events_table', 1),
	(63, '2026_06_30_052653_make_trip_id_nullable_in_trip_events', 1),
	(64, '2026_07_07_000001_create_hr_tables', 1),
	(65, '2026_07_07_000002_create_exam_enhancement_tables', 1),
	(66, '2026_07_07_000003_create_ai_query_logs_table', 1),
	(67, '2026_08_03_000001_add_school_id_to_student_fee_items', 1),
	(68, '2026_08_03_000010_create_admissions_tables', 1),
	(69, '2026_08_03_000020_create_student_transfers_table', 1),
	(70, '2026_08_04_000001_add_void_flow_and_payment_item_school_scope', 1),
	(71, '2026_08_05_000001_add_performance_indexes', 1),
	(72, '2026_08_07_000001_add_lat_lng_to_route_stops', 1),
	(73, '2026_08_10_000002_create_driver_sos_alerts_table', 1);

-- Dumping data for table school_erp.model_has_permissions: ~0 rows (approximately)

-- Dumping data for table school_erp.model_has_roles: ~14 rows (approximately)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`, `school_id`) VALUES
	(1, 'App\\Models\\User', 1, 1),
	(2, 'App\\Models\\User', 2, 1),
	(4, 'App\\Models\\User', 3, 1),
	(4, 'App\\Models\\User', 4, 1),
	(4, 'App\\Models\\User', 5, 1),
	(5, 'App\\Models\\User', 6, 1),
	(5, 'App\\Models\\User', 7, 1),
	(5, 'App\\Models\\User', 8, 1),
	(6, 'App\\Models\\User', 9, 1),
	(6, 'App\\Models\\User', 10, 1),
	(7, 'App\\Models\\User', 11, 1),
	(7, 'App\\Models\\User', 12, 1),
	(7, 'App\\Models\\User', 13, 1),
	(7, 'App\\Models\\User', 14, 1);

-- Dumping data for table school_erp.notifications: ~1 rows (approximately)
INSERT INTO `notifications` (`id`, `school_id`, `title`, `message`, `type`, `priority`, `status`, `target_type`, `channel`, `scheduled_at`, `sent_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Welcome to Demo Public School', 'Your account has been created successfully. Please login to access the portal.', 'announcement', 'high', 'sent', 'all', 'in_app', NULL, '2026-08-10 09:40:19', 1, NULL, '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.notification_user: ~5 rows (approximately)
INSERT INTO `notification_user` (`id`, `notification_id`, `user_id`, `is_read`, `read_at`, `delivery_status`, `created_at`, `updated_at`) VALUES
	(1, 1, 2, 0, NULL, 'delivered', '2026-08-10 09:40:19', '2026-08-10 09:40:19'),
	(2, 1, 11, 0, NULL, 'delivered', '2026-08-10 09:40:19', '2026-08-10 09:40:19'),
	(3, 1, 9, 0, NULL, 'delivered', '2026-08-10 09:40:19', '2026-08-10 09:40:19'),
	(4, 1, 1, 0, NULL, 'delivered', '2026-08-10 09:40:19', '2026-08-10 09:40:19'),
	(5, 1, 3, 0, NULL, 'delivered', '2026-08-10 09:40:19', '2026-08-10 09:40:19');

-- Dumping data for table school_erp.parents: ~2 rows (approximately)
INSERT INTO `parents` (`id`, `school_id`, `user_id`, `uuid`, `first_name`, `last_name`, `email`, `phone`, `occupation`, `address`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 9, 'fa0334ff-0a76-4973-a366-da25d786f3a5', 'Rajesh', 'Verma', 'parent@school.com', '+91 98765 43210', 'Engineer', '123 Main St, Demo City', 'active', NULL, NULL, '2026-08-10 09:40:02', '2026-08-10 09:40:03', NULL),
	(2, 1, 10, '30940f60-484e-4586-bee5-60518867a4e5', 'Nilesh', 'Patel', 'nilesh.patel@example.com', '+91 98765 43211', 'Doctor', '456 Oak Ave, Demo City', 'active', NULL, NULL, '2026-08-10 09:40:04', '2026-08-10 09:40:05', NULL);

-- Dumping data for table school_erp.parent_notifications: ~0 rows (approximately)

-- Dumping data for table school_erp.parent_student: ~2 rows (approximately)
INSERT INTO `parent_student` (`id`, `parent_id`, `student_id`, `relationship`, `is_primary`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'father', 1, '2026-08-10 09:40:03', '2026-08-10 09:40:03'),
	(2, 2, 2, 'father', 1, '2026-08-10 09:40:05', '2026-08-10 09:40:05');

-- Dumping data for table school_erp.password_reset_tokens: ~0 rows (approximately)

-- Dumping data for table school_erp.payroll_departments: ~1 rows (approximately)
INSERT INTO `payroll_departments` (`id`, `school_id`, `name`, `description`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Teaching Staff', NULL, 1, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.payroll_designations: ~1 rows (approximately)
INSERT INTO `payroll_designations` (`id`, `school_id`, `department_id`, `name`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 'Senior Teacher', NULL, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.payroll_items: ~1 rows (approximately)
INSERT INTO `payroll_items` (`id`, `school_id`, `payroll_run_id`, `employee_type`, `employee_id`, `gross_salary`, `total_deductions`, `net_salary`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 'teacher', 'T-1001', 38500.00, 6700.00, 31800.00, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.payroll_runs: ~1 rows (approximately)
INSERT INTO `payroll_runs` (`id`, `school_id`, `month`, `year`, `status`, `generated_by`, `generated_at`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 7, 2026, 'locked', 1, '2026-08-10 09:40:19', 'Monthly payroll run', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.payroll_settings: ~0 rows (approximately)

-- Dumping data for table school_erp.pay_grades: ~1 rows (approximately)
INSERT INTO `pay_grades` (`id`, `school_id`, `name`, `description`, `min_salary`, `max_salary`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Grade A', NULL, 30000.00, 50000.00, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.permissions: ~121 rows (approximately)
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'dashboard.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(2, 'roles.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(3, 'roles.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(4, 'roles.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(5, 'roles.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(6, 'permissions.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(7, 'permissions.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(8, 'permissions.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(9, 'permissions.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(10, 'users.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(11, 'users.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(12, 'users.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(13, 'users.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(14, 'students.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(15, 'students.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(16, 'students.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(17, 'students.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(18, 'students.export', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(19, 'admissions.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(20, 'admissions.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(21, 'admissions.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(22, 'admissions.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(23, 'admissions.verify', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(24, 'admissions.approve', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(25, 'admissions.reject', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(26, 'admissions.convert', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(27, 'student_lifecycle.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(28, 'student_lifecycle.promote', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(29, 'student_lifecycle.transfer', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(30, 'student_lifecycle.tc', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(31, 'student_lifecycle.alumni', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(32, 'teachers.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(33, 'teachers.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(34, 'teachers.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(35, 'teachers.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(36, 'teachers.reports', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(37, 'parents.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(38, 'parents.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(39, 'parents.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(40, 'parents.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(41, 'parents.reports', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(42, 'academics.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(43, 'academics.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(44, 'academics.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(45, 'academics.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(46, 'attendance.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(47, 'attendance.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(48, 'attendance.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(49, 'attendance.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(50, 'attendance.reports', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(51, 'fees.view', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(52, 'fees.create', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(53, 'fees.collect', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(54, 'fees.update', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(55, 'fees.delete', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(56, 'fees.reports', 'web', '2026-08-10 09:39:37', '2026-08-10 09:39:37'),
	(57, 'exams.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(58, 'exams.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(59, 'exams.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(60, 'exams.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(61, 'exams.publish', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(62, 'exams.reports', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(63, 'homework.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(64, 'homework.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(65, 'homework.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(66, 'homework.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(67, 'leave_management.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(68, 'leave_management.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(69, 'leave_management.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(70, 'leave_management.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(71, 'leave_management.approve', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(72, 'timetable.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(73, 'timetable.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(74, 'timetable.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(75, 'timetable.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(76, 'timetable.reports', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(77, 'academic_calendar.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(78, 'academic_calendar.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(79, 'academic_calendar.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(80, 'academic_calendar.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(81, 'academic_calendar.publish', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(82, 'student_documents.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(83, 'student_documents.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(84, 'student_documents.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(85, 'student_documents.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(86, 'student_documents.verify', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(87, 'transport.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(88, 'transport.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(89, 'transport.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(90, 'transport.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(91, 'transport.export', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(92, 'transport.location.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(93, 'library.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(94, 'library.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(95, 'library.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(96, 'library.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(97, 'library.export', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(98, 'payroll.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(99, 'payroll.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(100, 'payroll.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(101, 'payroll.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(102, 'payroll.export', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(103, 'payroll.process', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(104, 'payroll.lock', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(105, 'payroll.payslip.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(106, 'payroll.payslip.generate', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(107, 'payroll.payslip.export', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(108, 'hr.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(109, 'hr.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(110, 'hr.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(111, 'hr.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(112, 'hr.verify', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(113, 'notifications.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(114, 'notifications.create', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(115, 'notifications.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(116, 'notifications.delete', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(117, 'notifications.send', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(118, 'reports.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(119, 'reports.export', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(120, 'settings.view', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(121, 'settings.update', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38');

-- Dumping data for table school_erp.personal_access_tokens: ~0 rows (approximately)

-- Dumping data for table school_erp.roles: ~13 rows (approximately)
INSERT INTO `roles` (`id`, `school_id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Super Admin', 'web', '2026-08-10 09:39:38', '2026-08-10 09:39:38'),
	(2, 1, 'School Admin', 'web', '2026-08-10 09:39:40', '2026-08-10 09:39:40'),
	(3, 1, 'Principal', 'web', '2026-08-10 09:39:41', '2026-08-10 09:39:41'),
	(4, 1, 'Teacher', 'web', '2026-08-10 09:39:42', '2026-08-10 09:39:42'),
	(5, 1, 'Student', 'web', '2026-08-10 09:39:43', '2026-08-10 09:39:43'),
	(6, 1, 'Parent', 'web', '2026-08-10 09:39:43', '2026-08-10 09:39:43'),
	(7, 1, 'Driver', 'web', '2026-08-10 09:39:44', '2026-08-10 09:39:44'),
	(8, 1, 'Accountant', 'web', '2026-08-10 09:39:45', '2026-08-10 09:39:45'),
	(9, 1, 'Librarian', 'web', '2026-08-10 09:39:45', '2026-08-10 09:39:45'),
	(10, 1, 'Payroll Manager', 'web', '2026-08-10 09:39:46', '2026-08-10 09:39:46'),
	(11, 1, 'Receptionist', 'web', '2026-08-10 09:39:46', '2026-08-10 09:39:46'),
	(12, 1, 'HR', 'web', '2026-08-10 09:39:47', '2026-08-10 09:39:47'),
	(13, 1, 'Staff', 'web', '2026-08-10 09:39:47', '2026-08-10 09:39:47');

-- Dumping data for table school_erp.role_has_permissions: ~389 rows (approximately)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 1),
	(6, 1),
	(7, 1),
	(8, 1),
	(9, 1),
	(10, 1),
	(11, 1),
	(12, 1),
	(13, 1),
	(14, 1),
	(15, 1),
	(16, 1),
	(17, 1),
	(18, 1),
	(19, 1),
	(20, 1),
	(21, 1),
	(22, 1),
	(23, 1),
	(24, 1),
	(25, 1),
	(26, 1),
	(27, 1),
	(28, 1),
	(29, 1),
	(30, 1),
	(31, 1),
	(32, 1),
	(33, 1),
	(34, 1),
	(35, 1),
	(36, 1),
	(37, 1),
	(38, 1),
	(39, 1),
	(40, 1),
	(41, 1),
	(42, 1),
	(43, 1),
	(44, 1),
	(45, 1),
	(46, 1),
	(47, 1),
	(48, 1),
	(49, 1),
	(50, 1),
	(51, 1),
	(52, 1),
	(53, 1),
	(54, 1),
	(55, 1),
	(56, 1),
	(57, 1),
	(58, 1),
	(59, 1),
	(60, 1),
	(61, 1),
	(62, 1),
	(63, 1),
	(64, 1),
	(65, 1),
	(66, 1),
	(67, 1),
	(68, 1),
	(69, 1),
	(70, 1),
	(71, 1),
	(72, 1),
	(73, 1),
	(74, 1),
	(75, 1),
	(76, 1),
	(77, 1),
	(78, 1),
	(79, 1),
	(80, 1),
	(81, 1),
	(82, 1),
	(83, 1),
	(84, 1),
	(85, 1),
	(86, 1),
	(87, 1),
	(88, 1),
	(89, 1),
	(90, 1),
	(91, 1),
	(92, 1),
	(93, 1),
	(94, 1),
	(95, 1),
	(96, 1),
	(97, 1),
	(98, 1),
	(99, 1),
	(100, 1),
	(101, 1),
	(102, 1),
	(103, 1),
	(104, 1),
	(105, 1),
	(106, 1),
	(107, 1),
	(108, 1),
	(109, 1),
	(110, 1),
	(111, 1),
	(112, 1),
	(113, 1),
	(114, 1),
	(115, 1),
	(116, 1),
	(117, 1),
	(118, 1),
	(119, 1),
	(120, 1),
	(121, 1),
	(1, 2),
	(2, 2),
	(3, 2),
	(4, 2),
	(5, 2),
	(6, 2),
	(7, 2),
	(8, 2),
	(9, 2),
	(10, 2),
	(11, 2),
	(12, 2),
	(13, 2),
	(14, 2),
	(15, 2),
	(16, 2),
	(17, 2),
	(18, 2),
	(19, 2),
	(20, 2),
	(21, 2),
	(22, 2),
	(23, 2),
	(24, 2),
	(25, 2),
	(26, 2),
	(27, 2),
	(28, 2),
	(29, 2),
	(30, 2),
	(31, 2),
	(32, 2),
	(33, 2),
	(34, 2),
	(35, 2),
	(36, 2),
	(37, 2),
	(38, 2),
	(39, 2),
	(40, 2),
	(41, 2),
	(42, 2),
	(43, 2),
	(44, 2),
	(45, 2),
	(46, 2),
	(47, 2),
	(48, 2),
	(49, 2),
	(50, 2),
	(51, 2),
	(52, 2),
	(53, 2),
	(54, 2),
	(55, 2),
	(56, 2),
	(57, 2),
	(58, 2),
	(59, 2),
	(60, 2),
	(61, 2),
	(62, 2),
	(63, 2),
	(64, 2),
	(65, 2),
	(66, 2),
	(67, 2),
	(68, 2),
	(69, 2),
	(70, 2),
	(71, 2),
	(72, 2),
	(73, 2),
	(74, 2),
	(75, 2),
	(76, 2),
	(77, 2),
	(78, 2),
	(79, 2),
	(80, 2),
	(81, 2),
	(82, 2),
	(83, 2),
	(84, 2),
	(85, 2),
	(86, 2),
	(87, 2),
	(88, 2),
	(89, 2),
	(90, 2),
	(91, 2),
	(92, 2),
	(93, 2),
	(94, 2),
	(95, 2),
	(96, 2),
	(97, 2),
	(98, 2),
	(99, 2),
	(100, 2),
	(101, 2),
	(102, 2),
	(103, 2),
	(104, 2),
	(105, 2),
	(106, 2),
	(107, 2),
	(108, 2),
	(109, 2),
	(110, 2),
	(111, 2),
	(112, 2),
	(113, 2),
	(114, 2),
	(115, 2),
	(116, 2),
	(117, 2),
	(118, 2),
	(119, 2),
	(120, 2),
	(121, 2),
	(1, 3),
	(14, 3),
	(19, 3),
	(20, 3),
	(21, 3),
	(23, 3),
	(24, 3),
	(25, 3),
	(26, 3),
	(27, 3),
	(28, 3),
	(29, 3),
	(30, 3),
	(31, 3),
	(32, 3),
	(36, 3),
	(37, 3),
	(41, 3),
	(42, 3),
	(46, 3),
	(47, 3),
	(48, 3),
	(50, 3),
	(51, 3),
	(56, 3),
	(57, 3),
	(61, 3),
	(62, 3),
	(63, 3),
	(64, 3),
	(65, 3),
	(66, 3),
	(67, 3),
	(68, 3),
	(71, 3),
	(72, 3),
	(73, 3),
	(74, 3),
	(75, 3),
	(76, 3),
	(77, 3),
	(78, 3),
	(79, 3),
	(81, 3),
	(82, 3),
	(83, 3),
	(84, 3),
	(86, 3),
	(87, 3),
	(88, 3),
	(89, 3),
	(90, 3),
	(113, 3),
	(114, 3),
	(115, 3),
	(116, 3),
	(117, 3),
	(118, 3),
	(1, 4),
	(14, 4),
	(42, 4),
	(46, 4),
	(47, 4),
	(48, 4),
	(50, 4),
	(57, 4),
	(58, 4),
	(59, 4),
	(62, 4),
	(63, 4),
	(64, 4),
	(65, 4),
	(66, 4),
	(72, 4),
	(76, 4),
	(77, 4),
	(82, 4),
	(1, 5),
	(46, 5),
	(51, 5),
	(57, 5),
	(113, 5),
	(1, 6),
	(14, 6),
	(46, 6),
	(51, 6),
	(57, 6),
	(63, 6),
	(67, 6),
	(68, 6),
	(72, 6),
	(77, 6),
	(82, 6),
	(113, 6),
	(1, 7),
	(87, 7),
	(89, 7),
	(92, 7),
	(113, 7),
	(1, 8),
	(51, 8),
	(52, 8),
	(53, 8),
	(54, 8),
	(56, 8),
	(87, 8),
	(118, 8),
	(1, 9),
	(93, 9),
	(94, 9),
	(95, 9),
	(96, 9),
	(97, 9),
	(118, 9),
	(1, 10),
	(98, 10),
	(99, 10),
	(100, 10),
	(101, 10),
	(102, 10),
	(103, 10),
	(104, 10),
	(105, 10),
	(106, 10),
	(107, 10),
	(118, 10),
	(1, 11),
	(14, 11),
	(15, 11),
	(19, 11),
	(20, 11),
	(21, 11),
	(23, 11),
	(37, 11),
	(38, 11),
	(1, 12),
	(32, 12),
	(33, 12),
	(34, 12),
	(36, 12),
	(108, 12),
	(109, 12),
	(110, 12),
	(111, 12),
	(112, 12),
	(118, 12),
	(1, 13);

-- Dumping data for table school_erp.routes: ~3 rows (approximately)
INSERT INTO `routes` (`id`, `school_id`, `route_name`, `start_point`, `end_point`, `distance`, `vehicle_id`, `driver_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Route A - North Campus', 'North Gate', 'School Main', 12.50, 1, 1, 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(2, 1, 'Route B - East Campus', 'East Bus Stand', 'School Main', 15.25, 2, 2, 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(3, 1, 'Route C - South Campus', 'South Circle', 'School Main', 10.75, 1, 1, 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL);

-- Dumping data for table school_erp.route_stops: ~13 rows (approximately)
INSERT INTO `route_stops` (`id`, `school_id`, `route_id`, `stop_name`, `latitude`, `longitude`, `pickup_time`, `drop_time`, `sequence`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 'Station Road', 15.4520000, 75.0120000, '07:00:00', '14:00:00', 1, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(2, 1, 1, 'City Center', 15.4601000, 75.0021000, '07:15:00', '14:15:00', 2, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(3, 1, 1, 'Market Square', 15.4655000, 74.9930000, '07:30:00', '14:30:00', 3, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(4, 1, 1, 'Lake View', 15.4720000, 74.9810000, '07:45:00', '14:45:00', 4, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(5, 1, 2, 'East Bus Stand', 15.4410000, 75.0280000, '07:00:00', '14:00:00', 1, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(6, 1, 2, 'Indira Nagar', 15.4480000, 75.0190000, '07:15:00', '14:15:00', 2, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(7, 1, 2, 'Rajiv Nagar', 15.4530000, 75.0090000, '07:30:00', '14:30:00', 3, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(8, 1, 2, 'Green Park', 15.4580000, 74.9990000, '07:45:00', '14:45:00', 4, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(9, 1, 2, 'Temple Road', 15.4630000, 74.9890000, '07:00:00', '14:00:00', 5, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(10, 1, 3, 'South Circle', 15.4350000, 75.0110000, '07:00:00', '14:00:00', 1, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(11, 1, 3, 'MIG Colony', 15.4400000, 75.0040000, '07:15:00', '14:15:00', 2, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(12, 1, 3, 'Vidya Nagar', 15.4440000, 74.9970000, '07:30:00', '14:30:00', 3, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(13, 1, 3, 'Shanti Nagar', 15.4490000, 74.9900000, '07:45:00', '14:45:00', 4, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL);

-- Dumping data for table school_erp.salary_components: ~5 rows (approximately)
INSERT INTO `salary_components` (`id`, `school_id`, `name`, `name_display`, `component_type`, `calculation_type`, `value`, `description`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'basic', 'Basic Salary', 'earning', 'fixed', 35000.00, NULL, 1, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(2, 1, 'hra', 'House Rent Allowance', 'earning', 'percentage', 10.00, NULL, 2, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(3, 1, 'da', 'Dearness Allowance', 'earning', 'percentage', 5.00, NULL, 3, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(4, 1, 'pf', 'Provident Fund', 'deduction', 'percentage', 12.00, NULL, 1, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL),
	(5, 1, 'tax', 'Income Tax', 'deduction', 'fixed', 2500.00, NULL, 2, 'active', '2026-08-10 09:40:19', '2026-08-10 09:40:19', NULL);

-- Dumping data for table school_erp.schools: ~1 rows (approximately)
INSERT INTO `schools` (`id`, `uuid`, `code`, `name`, `slug`, `email`, `phone`, `address`, `city`, `state`, `country`, `postal_code`, `logo_path`, `timezone`, `currency`, `date_format`, `status`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'd8f3c5e8-0fd1-4006-be4c-b432836c426b', 'DEMO', 'Demo Public School', 'demo-public-school', 'school@example.com', '+91 98765 43210', 'Main Campus Road', 'Dharwad', 'Karnataka', 'India', NULL, NULL, 'Asia/Kolkata', 'INR', 'd-m-Y', 'active', NULL, '2026-08-10 09:39:36', '2026-08-10 09:39:36', NULL);

-- Dumping data for table school_erp.school_user: ~14 rows (approximately)
INSERT INTO `school_user` (`id`, `school_id`, `user_id`, `designation`, `employee_code`, `joined_at`, `status`, `is_primary`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'Platform Administrator', NULL, '2026-08-10', 'active', 1, '2026-08-10 09:39:50', '2026-08-10 09:39:50'),
	(2, 1, 2, 'Administrator', NULL, '2026-08-10', 'active', 1, '2026-08-10 09:39:52', '2026-08-10 09:39:52'),
	(3, 1, 3, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:39:55', '2026-08-10 09:39:55'),
	(4, 1, 4, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:39:57', '2026-08-10 09:39:57'),
	(5, 1, 5, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:39:58', '2026-08-10 09:39:58'),
	(6, 1, 6, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:00', '2026-08-10 09:40:00'),
	(7, 1, 7, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:01', '2026-08-10 09:40:01'),
	(8, 1, 8, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:02', '2026-08-10 09:40:02'),
	(9, 1, 9, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:03', '2026-08-10 09:40:03'),
	(10, 1, 10, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:05', '2026-08-10 09:40:05'),
	(11, 1, 11, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:06', '2026-08-10 09:40:06'),
	(12, 1, 12, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:08', '2026-08-10 09:40:08'),
	(13, 1, 13, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:09', '2026-08-10 09:40:09'),
	(14, 1, 14, NULL, NULL, NULL, 'active', 1, '2026-08-10 09:40:10', '2026-08-10 09:40:10');

-- Dumping data for table school_erp.sections: ~2 rows (approximately)
INSERT INTO `sections` (`id`, `school_id`, `name`, `code`, `capacity`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Section A', 'A', 40, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL),
	(2, 1, 'Section B', 'B', 40, 'active', '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL);

-- Dumping data for table school_erp.sessions: ~0 rows (approximately)

-- Dumping data for table school_erp.students: ~3 rows (approximately)
INSERT INTO `students` (`id`, `uuid`, `school_id`, `user_id`, `admission_no`, `admission_date`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `blood_group`, `religion`, `category`, `caste`, `nationality`, `mother_tongue`, `aadhar_no`, `photo_path`, `current_address`, `permanent_address`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, '26f9ae18-7a1f-40c7-b22b-e83ceadd9148', 1, 6, 'ADM0001', '2024-08-10', 'Arjun', NULL, 'Verma', '2016-08-10', 'male', 'O+', NULL, NULL, NULL, 'Indian', NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-10 09:39:58', '2026-08-10 09:40:00', NULL),
	(2, 'b24706b2-b84d-40c2-bc75-03c78983b1bd', 1, 7, 'ADM0002', '2024-08-10', 'Priya', NULL, 'Patel', '2015-08-10', 'female', 'O+', NULL, NULL, NULL, 'Indian', NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-10 09:40:00', '2026-08-10 09:40:01', NULL),
	(3, '433b6441-1412-484c-ba3d-b7deab2fddd4', 1, 8, 'ADM0003', '2024-08-10', 'Rohit', NULL, 'Sharma', '2014-08-10', 'male', 'O+', NULL, NULL, NULL, 'Indian', NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-10 09:40:01', '2026-08-10 09:40:02', NULL);

-- Dumping data for table school_erp.student_documents: ~0 rows (approximately)

-- Dumping data for table school_erp.student_fees: ~3 rows (approximately)
INSERT INTO `student_fees` (`id`, `school_id`, `student_id`, `academic_year_id`, `fee_structure_id`, `status`, `assigned_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 1, 'active', '2026-08-10 09:40:12', '2026-08-10 09:40:12', '2026-08-10 09:40:12', NULL),
	(2, 1, 2, 1, 2, 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(3, 1, 3, 1, 3, 'active', '2026-08-10 09:40:13', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL);

-- Dumping data for table school_erp.student_fee_items: ~15 rows (approximately)
INSERT INTO `student_fee_items` (`id`, `school_id`, `student_fee_id`, `fee_category_id`, `amount`, `due_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, NULL, 1, 1, 3611.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(2, NULL, 1, 2, 3664.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(3, NULL, 1, 3, 4966.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(4, NULL, 1, 4, 3469.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(5, NULL, 1, 5, 3250.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(6, NULL, 2, 1, 4638.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(7, NULL, 2, 2, 4471.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(8, NULL, 2, 3, 2280.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(9, NULL, 2, 4, 3780.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(10, NULL, 2, 5, 3342.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(11, NULL, 3, 1, 4162.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(12, NULL, 3, 2, 1996.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(13, NULL, 3, 3, 4163.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(14, NULL, 3, 4, 2973.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL),
	(15, NULL, 3, 5, 1394.00, '2026-09-09', '2026-08-10 09:40:13', '2026-08-10 09:40:13', NULL);

-- Dumping data for table school_erp.student_guardians: ~0 rows (approximately)

-- Dumping data for table school_erp.student_sessions: ~3 rows (approximately)
INSERT INTO `student_sessions` (`id`, `school_id`, `academic_year_id`, `student_id`, `class_section_id`, `roll_no`, `joined_on`, `left_on`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 1, '1', '2024-08-10', NULL, 'active', '2026-08-10 09:39:58', '2026-08-10 09:39:58', NULL),
	(2, 1, 1, 2, 2, '2', '2024-08-10', NULL, 'active', '2026-08-10 09:40:00', '2026-08-10 09:40:00', NULL),
	(3, 1, 1, 3, 3, '3', '2024-08-10', NULL, 'active', '2026-08-10 09:40:01', '2026-08-10 09:40:01', NULL);

-- Dumping data for table school_erp.student_transfers: ~0 rows (approximately)

-- Dumping data for table school_erp.subjects: ~5 rows (approximately)
INSERT INTO `subjects` (`id`, `school_id`, `name`, `code`, `type`, `credit_hours`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'English', 'ENG', 'core', 5, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(2, 1, 'Mathematics', 'MATH', 'core', 6, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(3, 1, 'Science', 'SCI', 'core', 5, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(4, 1, 'Social Studies', 'SST', 'core', 4, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL),
	(5, 1, 'Computer Science', 'CS', 'elective', 3, NULL, 'active', '2026-08-10 09:39:53', '2026-08-10 09:39:53', NULL);

-- Dumping data for table school_erp.teachers: ~3 rows (approximately)
INSERT INTO `teachers` (`id`, `school_id`, `user_id`, `uuid`, `employee_id`, `first_name`, `middle_name`, `last_name`, `gender`, `date_of_birth`, `qualification`, `experience_years`, `joining_date`, `phone`, `email`, `address`, `photo_path`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 3, '9131bc38-c735-47cf-b053-9830dc7d11ef', 'T-1001', 'Aisha', NULL, 'Khan', 'female', NULL, 'M.Sc. Mathematics', 8, '2021-08-10', '9876543210', 'teacher@school.com', '12 Rose Lane, Demo City', NULL, 'active', NULL, NULL, '2026-08-10 09:39:55', '2026-08-10 09:39:55', NULL),
	(2, 1, 4, '0c7fae86-62c8-499f-b1c8-f34f11951caa', 'T-1002', 'Rahul', NULL, 'Mehta', 'male', NULL, 'M.A. English', 6, '2022-08-10', '9123456780', 'rahul.mehta@example.com', '8 Garden Street, Demo City', NULL, 'active', NULL, NULL, '2026-08-10 09:39:57', '2026-08-10 09:39:57', NULL),
	(3, 1, 5, '078ad223-f3f2-46a0-8441-b84d12381724', 'T-1003', 'Priya', NULL, 'Sharma', 'female', NULL, 'M.Sc. Physics', 10, '2019-08-10', '9988776655', 'priya.sharma@example.com', '221 Baker Street, Demo City', NULL, 'active', NULL, NULL, '2026-08-10 09:39:58', '2026-08-10 09:39:58', NULL);

-- Dumping data for table school_erp.teacher_attendances: ~195 rows (approximately)
INSERT INTO `teacher_attendances` (`id`, `teacher_id`, `attendance_date`, `status`, `remarks`, `marked_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, '2026-05-12', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(2, 2, '2026-05-12', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(3, 3, '2026-05-12', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(4, 1, '2026-05-13', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(5, 2, '2026-05-13', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(6, 3, '2026-05-13', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(7, 1, '2026-05-14', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(8, 2, '2026-05-14', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(9, 3, '2026-05-14', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(10, 1, '2026-05-15', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(11, 2, '2026-05-15', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(12, 3, '2026-05-15', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(13, 1, '2026-05-18', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(14, 2, '2026-05-18', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(15, 3, '2026-05-18', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(16, 1, '2026-05-19', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(17, 2, '2026-05-19', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(18, 3, '2026-05-19', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(19, 1, '2026-05-20', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(20, 2, '2026-05-20', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(21, 3, '2026-05-20', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(22, 1, '2026-05-21', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(23, 2, '2026-05-21', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(24, 3, '2026-05-21', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(25, 1, '2026-05-22', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(26, 2, '2026-05-22', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(27, 3, '2026-05-22', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(28, 1, '2026-05-25', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(29, 2, '2026-05-25', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(30, 3, '2026-05-25', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(31, 1, '2026-05-26', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(32, 2, '2026-05-26', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(33, 3, '2026-05-26', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(34, 1, '2026-05-27', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(35, 2, '2026-05-27', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(36, 3, '2026-05-27', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(37, 1, '2026-05-28', 'absent', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(38, 2, '2026-05-28', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(39, 3, '2026-05-28', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(40, 1, '2026-05-29', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(41, 2, '2026-05-29', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(42, 3, '2026-05-29', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(43, 1, '2026-06-01', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(44, 2, '2026-06-01', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(45, 3, '2026-06-01', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(46, 1, '2026-06-02', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(47, 2, '2026-06-02', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(48, 3, '2026-06-02', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(49, 1, '2026-06-03', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(50, 2, '2026-06-03', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(51, 3, '2026-06-03', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(52, 1, '2026-06-04', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(53, 2, '2026-06-04', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(54, 3, '2026-06-04', 'present', NULL, 1, '2026-08-10 09:40:14', '2026-08-10 09:40:14', NULL),
	(55, 1, '2026-06-05', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(56, 2, '2026-06-05', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(57, 3, '2026-06-05', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(58, 1, '2026-06-08', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(59, 2, '2026-06-08', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(60, 3, '2026-06-08', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(61, 1, '2026-06-09', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(62, 2, '2026-06-09', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(63, 3, '2026-06-09', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(64, 1, '2026-06-10', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(65, 2, '2026-06-10', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(66, 3, '2026-06-10', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(67, 1, '2026-06-11', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(68, 2, '2026-06-11', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(69, 3, '2026-06-11', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(70, 1, '2026-06-12', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(71, 2, '2026-06-12', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(72, 3, '2026-06-12', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(73, 1, '2026-06-15', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(74, 2, '2026-06-15', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(75, 3, '2026-06-15', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(76, 1, '2026-06-16', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(77, 2, '2026-06-16', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(78, 3, '2026-06-16', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(79, 1, '2026-06-17', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(80, 2, '2026-06-17', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(81, 3, '2026-06-17', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(82, 1, '2026-06-18', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(83, 2, '2026-06-18', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(84, 3, '2026-06-18', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(85, 1, '2026-06-19', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(86, 2, '2026-06-19', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(87, 3, '2026-06-19', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(88, 1, '2026-06-22', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(89, 2, '2026-06-22', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(90, 3, '2026-06-22', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(91, 1, '2026-06-23', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(92, 2, '2026-06-23', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(93, 3, '2026-06-23', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(94, 1, '2026-06-24', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(95, 2, '2026-06-24', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(96, 3, '2026-06-24', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(97, 1, '2026-06-25', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(98, 2, '2026-06-25', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(99, 3, '2026-06-25', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(100, 1, '2026-06-26', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(101, 2, '2026-06-26', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(102, 3, '2026-06-26', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(103, 1, '2026-06-29', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(104, 2, '2026-06-29', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(105, 3, '2026-06-29', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(106, 1, '2026-06-30', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(107, 2, '2026-06-30', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(108, 3, '2026-06-30', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(109, 1, '2026-07-01', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(110, 2, '2026-07-01', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(111, 3, '2026-07-01', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(112, 1, '2026-07-02', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(113, 2, '2026-07-02', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(114, 3, '2026-07-02', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(115, 1, '2026-07-03', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(116, 2, '2026-07-03', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(117, 3, '2026-07-03', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(118, 1, '2026-07-06', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(119, 2, '2026-07-06', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(120, 3, '2026-07-06', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(121, 1, '2026-07-07', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(122, 2, '2026-07-07', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(123, 3, '2026-07-07', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(124, 1, '2026-07-08', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(125, 2, '2026-07-08', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(126, 3, '2026-07-08', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(127, 1, '2026-07-09', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(128, 2, '2026-07-09', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(129, 3, '2026-07-09', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(130, 1, '2026-07-10', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(131, 2, '2026-07-10', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(132, 3, '2026-07-10', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(133, 1, '2026-07-13', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(134, 2, '2026-07-13', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(135, 3, '2026-07-13', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(136, 1, '2026-07-14', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(137, 2, '2026-07-14', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(138, 3, '2026-07-14', 'absent', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(139, 1, '2026-07-15', 'present', NULL, 1, '2026-08-10 09:40:15', '2026-08-10 09:40:15', NULL),
	(140, 2, '2026-07-15', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(141, 3, '2026-07-15', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(142, 1, '2026-07-16', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(143, 2, '2026-07-16', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(144, 3, '2026-07-16', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(145, 1, '2026-07-17', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(146, 2, '2026-07-17', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(147, 3, '2026-07-17', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(148, 1, '2026-07-20', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(149, 2, '2026-07-20', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(150, 3, '2026-07-20', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(151, 1, '2026-07-21', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(152, 2, '2026-07-21', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(153, 3, '2026-07-21', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(154, 1, '2026-07-22', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(155, 2, '2026-07-22', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(156, 3, '2026-07-22', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(157, 1, '2026-07-23', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(158, 2, '2026-07-23', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(159, 3, '2026-07-23', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(160, 1, '2026-07-24', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(161, 2, '2026-07-24', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(162, 3, '2026-07-24', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(163, 1, '2026-07-27', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(164, 2, '2026-07-27', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(165, 3, '2026-07-27', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(166, 1, '2026-07-28', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(167, 2, '2026-07-28', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(168, 3, '2026-07-28', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(169, 1, '2026-07-29', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(170, 2, '2026-07-29', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(171, 3, '2026-07-29', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(172, 1, '2026-07-30', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(173, 2, '2026-07-30', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(174, 3, '2026-07-30', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(175, 1, '2026-07-31', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(176, 2, '2026-07-31', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(177, 3, '2026-07-31', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(178, 1, '2026-08-03', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(179, 2, '2026-08-03', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(180, 3, '2026-08-03', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(181, 1, '2026-08-04', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(182, 2, '2026-08-04', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(183, 3, '2026-08-04', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(184, 1, '2026-08-05', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(185, 2, '2026-08-05', 'absent', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(186, 3, '2026-08-05', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(187, 1, '2026-08-06', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(188, 2, '2026-08-06', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(189, 3, '2026-08-06', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(190, 1, '2026-08-07', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(191, 2, '2026-08-07', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(192, 3, '2026-08-07', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(193, 1, '2026-08-10', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(194, 2, '2026-08-10', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL),
	(195, 3, '2026-08-10', 'present', NULL, 1, '2026-08-10 09:40:16', '2026-08-10 09:40:16', NULL);

-- Dumping data for table school_erp.teacher_class_section: ~3 rows (approximately)
INSERT INTO `teacher_class_section` (`teacher_id`, `class_section_id`, `school_id`, `is_class_teacher`) VALUES
	(1, 1, 1, 1),
	(2, 1, 1, 1),
	(3, 1, 1, 1);

-- Dumping data for table school_erp.teacher_documents: ~0 rows (approximately)

-- Dumping data for table school_erp.teacher_leaves: ~0 rows (approximately)

-- Dumping data for table school_erp.teacher_subject: ~6 rows (approximately)
INSERT INTO `teacher_subject` (`teacher_id`, `subject_id`, `school_id`) VALUES
	(1, 1, 1),
	(1, 5, 1),
	(2, 1, 1),
	(2, 5, 1),
	(3, 1, 1),
	(3, 5, 1);

-- Dumping data for table school_erp.teacher_timetable_slots: ~3 rows (approximately)
INSERT INTO `teacher_timetable_slots` (`id`, `teacher_id`, `class_section_id`, `subject_id`, `day_of_week`, `period_number`, `start_time`, `end_time`, `period_label`, `room`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`, `academic_year_id`, `school_id`) VALUES
	(1, 1, 1, 5, 1, 1, '08:30:00', '09:15:00', 'Period 1', 'A1', 'active', NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL, 1, NULL),
	(2, 1, 1, 5, 1, 2, '09:20:00', '10:05:00', 'Period 2', 'A1', 'active', NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL, 1, NULL),
	(3, 1, 1, 5, 2, 1, '08:30:00', '09:15:00', 'Period 1', 'A1', 'active', NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL, 1, NULL);

-- Dumping data for table school_erp.transport_assignments: ~3 rows (approximately)
INSERT INTO `transport_assignments` (`id`, `school_id`, `student_id`, `route_id`, `route_stop_id`, `vehicle_id`, `pickup_point`, `monthly_fee`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 1, 1, 'Station Road', 1500.00, 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(2, 1, 2, 2, 6, 2, 'Indira Nagar', 1500.00, 'active', '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(3, 1, 3, 3, 12, 1, 'Vidya Nagar', 1500.00, 'active', '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL);

-- Dumping data for table school_erp.trips: ~8 rows (approximately)
INSERT INTO `trips` (`id`, `school_id`, `driver_id`, `vehicle_id`, `route_id`, `type`, `status`, `trip_date`, `started_at`, `completed_at`, `total_students`, `picked_up_count`, `dropped_off_count`, `total_distance`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, 1, 1, 'pickup', 'scheduled', '2026-08-10', NULL, NULL, 1, 0, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(2, 1, 1, 1, 1, 'drop', 'scheduled', '2026-08-10', NULL, NULL, 1, 0, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(3, 1, 2, 2, 2, 'pickup', 'scheduled', '2026-08-10', NULL, NULL, 1, 0, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(4, 1, 2, 2, 2, 'drop', 'scheduled', '2026-08-10', NULL, NULL, 1, 0, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(5, 1, 1, 1, 3, 'pickup', 'in_progress', '2026-08-10', '2026-08-10 14:50:11', NULL, 1, 0, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(6, 1, 1, 1, 3, 'drop', 'scheduled', '2026-08-10', NULL, NULL, 1, 0, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(7, 1, 1, 1, 1, 'pickup', 'completed', '2026-08-09', '2026-08-09 01:30:00', '2026-08-09 03:15:00', 1, 1, 0, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL),
	(8, 1, 1, 1, 1, 'drop', 'completed', '2026-08-09', '2026-08-09 09:00:00', '2026-08-09 10:30:00', 1, 0, 1, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11', NULL);

-- Dumping data for table school_erp.trip_events: ~0 rows (approximately)

-- Dumping data for table school_erp.trip_students: ~8 rows (approximately)
INSERT INTO `trip_students` (`id`, `school_id`, `trip_id`, `student_id`, `route_stop_id`, `pickup_status`, `drop_status`, `picked_up_at`, `dropped_off_at`, `pickup_latitude`, `pickup_longitude`, `drop_latitude`, `drop_longitude`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 1, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(2, 1, 2, 1, 1, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(3, 1, 3, 2, 6, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(4, 1, 4, 2, 6, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(5, 1, 5, 3, 12, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(6, 1, 6, 3, 12, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(7, 1, 7, 1, 1, 'picked_up', 'pending', '2026-08-09 01:35:00', NULL, NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11'),
	(8, 1, 8, 1, 1, 'pending', 'dropped_off', NULL, '2026-08-09 09:05:00', NULL, NULL, NULL, NULL, '2026-08-10 09:40:11', '2026-08-10 09:40:11');

-- Dumping data for table school_erp.users: ~14 rows (approximately)
INSERT INTO `users` (`id`, `uuid`, `name`, `email`, `phone`, `email_verified_at`, `password`, `avatar_path`, `status`, `is_super_admin`, `current_school_id`, `last_login_at`, `last_login_ip`, `force_password_change`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'a8e3ddac-cf01-4eab-a355-c13fd9ae78eb', 'Super Admin', 'superadmin@school.com', '+91 90000 00001', '2026-08-10 09:39:50', '$2y$12$L5273sLew1RVG.ja3esYjOQ4IhXk.filyLMLWo0n/aO79p2i3SN/O', NULL, 'active', 1, 1, NULL, NULL, 0, NULL, '2026-08-10 09:39:50', '2026-08-10 09:39:50', NULL),
	(2, 'd2ae6ab5-54ff-4089-84c9-a4d53939840c', 'School Admin', 'admin@school.com', '+91 90000 00002', '2026-08-10 09:39:52', '$2y$12$TrxfONXRbkIOAKjF7COpEOlK05zuy3FlEjFX8mBMEt.jHRsVVMXaG', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:39:52', '2026-08-10 09:39:52', NULL),
	(3, '36609835-3d7a-4862-b08b-8c0cf09a4b1b', 'Aisha Khan', 'teacher@school.com', '9876543210', '2026-08-10 09:39:55', '$2y$12$uFPw6qRNmco5RjCxPpGvt.7btCJHxB6gmx.wQbAIRm1ETNUmtK7BC', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:39:55', '2026-08-10 09:39:55', NULL),
	(4, 'a8a3d5a7-60d3-4534-9bc6-4768a573c687', 'Rahul Mehta', 'rahul.mehta@example.com', '9123456780', '2026-08-10 09:39:57', '$2y$12$nZIrzxUTEEUJrxFXbe3ow.CfI4MgXylK4Xqg9bMyneBJZnMrEUrV2', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:39:57', '2026-08-10 09:39:57', NULL),
	(5, '9f2e37f9-341d-4c18-a1fb-45d2bc7bbbd2', 'Priya Sharma', 'priya.sharma@example.com', '9988776655', '2026-08-10 09:39:58', '$2y$12$GtngpUidXRBJqpx13N5iWeRB6yB3Hry/R/Z7UAdo4AAy1T76F68F2', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:39:58', '2026-08-10 09:39:58', NULL),
	(6, 'de021df8-9787-4f39-b31b-e5adfd0eb927', 'Arjun Verma', 'student@school.com', NULL, '2026-08-10 09:40:00', '$2y$12$tKRi3w2MhpX46NTTJCVtlOekm5qdn2Getmv59azSKaBxh5hc3VvSu', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:00', '2026-08-10 09:40:00', NULL),
	(7, '8ad12de7-9698-482c-b576-29737a8e5406', 'Priya Patel', 'priya.patel@example.com', NULL, '2026-08-10 09:40:01', '$2y$12$1qgTVXgFrEv52YD1/v3YHuvZ.9ZpOyFyH73KCKR0zXocXBnt3kpM6', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:01', '2026-08-10 09:40:01', NULL),
	(8, '4a75c42e-afb8-4054-bb06-3a068885fcea', 'Rohit Sharma', 'rohit.sharma@example.com', NULL, '2026-08-10 09:40:02', '$2y$12$lwnl32q4cQQgAakaDMw.6.cminCqHpHz/QJxllcLY6n2D4ItWqTK6', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:02', '2026-08-10 09:40:02', NULL),
	(9, 'bfbc4fa8-a1d7-47b6-a33c-d1508da9f3ab', 'Rajesh Verma', 'parent@school.com', '+91 98765 43210', '2026-08-10 09:40:03', '$2y$12$6aa/rFZHCYK4A5a.xVh.ZeKbmHCZrEqKajnrKGb06PBEvcuS6Hqeu', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:03', '2026-08-10 09:40:03', NULL),
	(10, '83f94fc4-f7eb-42f0-a3cf-7dc1fd9e9190', 'Nilesh Patel', 'nilesh.patel@example.com', '+91 98765 43211', '2026-08-10 09:40:05', '$2y$12$Ta0IkFsf2LyPbPYQ/KoR2OPowi/f5/spUaFy8nsZM/5TpeULW3zb6', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:05', '2026-08-10 09:40:05', NULL),
	(11, '6f314023-2d9c-4645-9e03-4fa0fdf09486', 'Rajesh Kumar', 'driver@school.com', '9876500001', '2026-08-10 09:40:06', '$2y$12$kwkwUdxfJvtBuUK91VbI0uF28CV39WJ.zXIZAo7ZPykAr/zAK8w7i', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:06', '2026-08-10 09:40:06', NULL),
	(12, '3d3e04d0-2236-4447-9ca0-09c072179455', 'Suresh Patil', 'suresh.patil@example.com', '9876500002', '2026-08-10 09:40:08', '$2y$12$Nvtx9vuikNy2YWHoxODICe0og8qUbOv5IV72ageDHdJQWlN9oMySy', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:08', '2026-08-10 09:40:08', NULL),
	(13, '3beb828e-7b1b-4d1c-a027-0f93356c9968', 'Mahesh Gowda', 'mahesh.gowda@example.com', '9876500003', '2026-08-10 09:40:09', '$2y$12$7Mjy68jLnH36OYOCIUUSAeBGBGdk0J/Ad6KuSZXWAGgsmW36VSdMO', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:09', '2026-08-10 09:40:09', NULL),
	(14, '32dfe911-8c05-4077-ada0-b3cb5781c2e2', 'Venkatesh Iyer', 'venkatesh.iyer@example.com', '9876500004', '2026-08-10 09:40:10', '$2y$12$tC5A7k7OpMCjKHW/xW7sF.6HpzaJxcUU9aqfijj1oacVc3d8eFQQG', NULL, 'active', 0, 1, NULL, NULL, 0, NULL, '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL);

-- Dumping data for table school_erp.user_devices: ~0 rows (approximately)

-- Dumping data for table school_erp.vehicles: ~2 rows (approximately)
INSERT INTO `vehicles` (`id`, `school_id`, `vehicle_number`, `vehicle_name`, `vehicle_type`, `capacity`, `driver_id`, `attendant`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'KA-01-AB-1234', 'School Bus 1', 'bus', 40, 1, 'Lakshmi Bai', 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL),
	(2, 1, 'KA-01-CD-5678', 'School Bus 2', 'bus', 45, 2, 'Sunita Devi', 'active', '2026-08-10 09:40:10', '2026-08-10 09:40:10', NULL);

-- Dumping data for table school_erp.vehicle_locations: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
