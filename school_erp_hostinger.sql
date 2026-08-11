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

-- Dumping structure for table school_erp.academic_calendars
CREATE TABLE IF NOT EXISTS `academic_calendars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `audience` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_calendars_academic_year_id_foreign` (`academic_year_id`),
  KEY `academic_calendars_school_id_event_type_index` (`school_id`,`event_type`),
  KEY `academic_calendars_school_id_start_date_end_date_index` (`school_id`,`start_date`,`end_date`),
  KEY `academic_calendars_school_id_academic_year_id_index` (`school_id`,`academic_year_id`),
  KEY `academic_calendars_event_type_index` (`event_type`),
  KEY `academic_calendars_audience_index` (`audience`),
  KEY `academic_calendars_is_published_index` (`is_published`),
  CONSTRAINT `academic_calendars_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_calendars_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.academic_terms
CREATE TABLE IF NOT EXISTS `academic_terms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_terms_school_id_academic_year_id_name_unique` (`school_id`,`academic_year_id`,`name`),
  KEY `academic_terms_academic_year_id_foreign` (`academic_year_id`),
  KEY `academic_terms_school_id_academic_year_id_status_index` (`school_id`,`academic_year_id`,`status`),
  KEY `academic_terms_status_index` (`status`),
  CONSTRAINT `academic_terms_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_terms_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.academic_years
CREATE TABLE IF NOT EXISTS `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_years_school_id_name_unique` (`school_id`,`name`),
  KEY `academic_years_school_id_starts_on_ends_on_index` (`school_id`,`starts_on`,`ends_on`),
  KEY `academic_years_is_active_index` (`is_active`),
  KEY `academic_years_status_index` (`status`),
  CONSTRAINT `academic_years_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.activity_log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.admissions
CREATE TABLE IF NOT EXISTS `admissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `admission_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `blood_group` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caste` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Indian',
  `mother_tongue` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aadhar_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_address` text COLLATE utf8mb4_unicode_ci,
  `permanent_address` text COLLATE utf8mb4_unicode_ci,
  `class_section_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enquiry',
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'walk_in',
  `guardian_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_relation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_occupation` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `applied_on` date DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `converted_by` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admissions_school_id_admission_no_unique` (`school_id`,`admission_no`),
  KEY `admissions_class_section_id_foreign` (`class_section_id`),
  KEY `admissions_academic_year_id_foreign` (`academic_year_id`),
  KEY `admissions_verified_by_foreign` (`verified_by`),
  KEY `admissions_approved_by_foreign` (`approved_by`),
  KEY `admissions_converted_by_foreign` (`converted_by`),
  KEY `admissions_student_id_foreign` (`student_id`),
  KEY `admissions_created_by_foreign` (`created_by`),
  KEY `admissions_updated_by_foreign` (`updated_by`),
  KEY `admissions_school_id_status_index` (`school_id`,`status`),
  KEY `admissions_school_id_applied_on_index` (`school_id`,`applied_on`),
  KEY `admissions_admission_no_index` (`admission_no`),
  KEY `admissions_status_index` (`status`),
  KEY `admissions_source_index` (`source`),
  CONSTRAINT `admissions_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_converted_by_foreign` FOREIGN KEY (`converted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.admission_documents
CREATE TABLE IF NOT EXISTS `admission_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `admission_id` bigint unsigned NOT NULL,
  `document_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admission_documents_admission_id_foreign` (`admission_id`),
  KEY `admission_documents_verified_by_foreign` (`verified_by`),
  KEY `admission_documents_created_by_foreign` (`created_by`),
  KEY `admission_documents_updated_by_foreign` (`updated_by`),
  KEY `admission_documents_school_id_admission_id_index` (`school_id`,`admission_id`),
  KEY `admission_documents_school_id_document_type_index` (`school_id`,`document_type`),
  KEY `admission_documents_document_type_index` (`document_type`),
  CONSTRAINT `admission_documents_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admission_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admission_documents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admission_documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admission_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.agent_executions
CREATE TABLE IF NOT EXISTS `agent_executions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_by` bigint unsigned NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `records_processed` int NOT NULL DEFAULT '0',
  `result_summary` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `input_params` json DEFAULT NULL,
  `output_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_executions_agent_name_status_index` (`agent_name`,`status`),
  KEY `agent_executions_executed_by_created_at_index` (`executed_by`,`created_at`),
  KEY `agent_executions_status_index` (`status`),
  CONSTRAINT `agent_executions_executed_by_foreign` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.ai_query_logs
CREATE TABLE IF NOT EXISTS `ai_query_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json DEFAULT NULL,
  `response_summary` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_query_logs_school_id_index` (`school_id`),
  KEY `ai_query_logs_user_id_index` (`user_id`),
  KEY `ai_query_logs_intent_index` (`intent`),
  KEY `ai_query_logs_status_index` (`status`),
  KEY `ai_query_logs_created_at_index` (`created_at`),
  CONSTRAINT `ai_query_logs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_query_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.attendances
CREATE TABLE IF NOT EXISTS `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','half_day','excused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `marked_by` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_school_id_student_id_attendance_date_unique` (`school_id`,`student_id`,`attendance_date`),
  KEY `attendances_academic_year_id_foreign` (`academic_year_id`),
  KEY `attendances_school_id_class_section_id_attendance_date_index` (`school_id`,`class_section_id`,`attendance_date`),
  KEY `attendances_school_id_academic_year_id_attendance_date_index` (`school_id`,`academic_year_id`,`attendance_date`),
  KEY `attendances_marked_by_index` (`marked_by`),
  KEY `idx_attendances_class_date` (`class_section_id`,`attendance_date`),
  KEY `idx_attendances_student_date` (`student_id`,`attendance_date`),
  CONSTRAINT `attendances_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.classes
CREATE TABLE IF NOT EXISTS `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classes_school_id_code_unique` (`school_id`,`code`),
  KEY `classes_school_id_status_index` (`school_id`,`status`),
  KEY `classes_status_index` (`status`),
  CONSTRAINT `classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.class_section
CREATE TABLE IF NOT EXISTS `class_section` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `section_id` bigint unsigned NOT NULL,
  `class_teacher_id` bigint unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_section_school_id_class_id_section_id_unique` (`school_id`,`class_id`,`section_id`),
  KEY `class_section_class_id_foreign` (`class_id`),
  KEY `class_section_section_id_foreign` (`section_id`),
  KEY `class_section_class_teacher_id_foreign` (`class_teacher_id`),
  KEY `class_section_status_index` (`status`),
  CONSTRAINT `class_section_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_section_class_teacher_id_foreign` FOREIGN KEY (`class_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `class_section_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_section_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.class_subjects
CREATE TABLE IF NOT EXISTS `class_subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned DEFAULT NULL,
  `weekly_periods` smallint unsigned NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_subject_unique` (`school_id`,`academic_year_id`,`class_id`,`subject_id`),
  KEY `class_subjects_academic_year_id_foreign` (`academic_year_id`),
  KEY `class_subjects_class_id_foreign` (`class_id`),
  KEY `class_subjects_subject_id_foreign` (`subject_id`),
  KEY `class_subjects_teacher_id_foreign` (`teacher_id`),
  KEY `class_subjects_school_id_academic_year_id_class_id_index` (`school_id`,`academic_year_id`,`class_id`),
  KEY `class_subjects_status_index` (`status`),
  CONSTRAINT `class_subjects_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.drivers
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_number` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_expiry_date` date NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `drivers_school_id_status_index` (`school_id`,`status`),
  KEY `drivers_status_index` (`status`),
  KEY `drivers_user_id_index` (`user_id`),
  CONSTRAINT `drivers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `drivers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.driver_sos_alerts
CREATE TABLE IF NOT EXISTS `driver_sos_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `trip_id` bigint unsigned DEFAULT NULL,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `handled_by` bigint unsigned DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `driver_sos_alerts_driver_id_foreign` (`driver_id`),
  KEY `driver_sos_alerts_vehicle_id_foreign` (`vehicle_id`),
  KEY `driver_sos_alerts_handled_by_foreign` (`handled_by`),
  KEY `driver_sos_alerts_school_id_status_index` (`school_id`,`status`),
  KEY `driver_sos_alerts_trip_id_index` (`trip_id`),
  KEY `driver_sos_alerts_school_id_index` (`school_id`),
  CONSTRAINT `driver_sos_alerts_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `driver_sos_alerts_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `driver_sos_alerts_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE SET NULL,
  CONSTRAINT `driver_sos_alerts_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.employees
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `employee_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` text COLLATE utf8mb4_unicode_ci,
  `address_line2` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_ifsc_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uan_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pf_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `esi_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_leaving` date DEFAULT NULL,
  `employment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permanent',
  `employment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `department_id` bigint unsigned DEFAULT NULL,
  `designation_id` bigint unsigned DEFAULT NULL,
  `reporting_to_id` bigint unsigned DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_code_unique` (`employee_code`),
  KEY `employees_school_id_foreign` (`school_id`),
  KEY `employees_department_id_foreign` (`department_id`),
  KEY `employees_designation_id_foreign` (`designation_id`),
  KEY `employees_reporting_to_id_foreign` (`reporting_to_id`),
  KEY `employees_created_by_foreign` (`created_by`),
  KEY `employees_updated_by_foreign` (`updated_by`),
  CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `payroll_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `payroll_designations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_reporting_to_id_foreign` FOREIGN KEY (`reporting_to_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.employee_contracts
CREATE TABLE IF NOT EXISTS `employee_contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `contract_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permanent',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `probation_period_months` int DEFAULT NULL,
  `notice_period_days` int DEFAULT NULL,
  `documents_json` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_contracts_school_id_foreign` (`school_id`),
  KEY `employee_contracts_employee_id_foreign` (`employee_id`),
  KEY `employee_contracts_created_by_foreign` (`created_by`),
  KEY `employee_contracts_updated_by_foreign` (`updated_by`),
  CONSTRAINT `employee_contracts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_contracts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_contracts_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_contracts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.employee_documents
CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_documents_school_id_foreign` (`school_id`),
  KEY `employee_documents_employee_id_foreign` (`employee_id`),
  KEY `employee_documents_verified_by_foreign` (`verified_by`),
  KEY `employee_documents_created_by_foreign` (`created_by`),
  KEY `employee_documents_updated_by_foreign` (`updated_by`),
  CONSTRAINT `employee_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_documents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.employee_payslips
CREATE TABLE IF NOT EXISTS `employee_payslips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `payroll_run_id` bigint unsigned NOT NULL,
  `payroll_item_id` bigint unsigned NOT NULL,
  `payslip_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `earnings_json` json NOT NULL,
  `deductions_json` json NOT NULL,
  `gross_salary` decimal(12,2) NOT NULL,
  `total_deductions` decimal(12,2) NOT NULL,
  `net_salary` decimal(12,2) NOT NULL,
  `generated_by` bigint unsigned NOT NULL,
  `generated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payslip_run_item_unique` (`payroll_run_id`,`payroll_item_id`),
  UNIQUE KEY `employee_payslips_payslip_number_unique` (`payslip_number`),
  KEY `employee_payslips_school_id_foreign` (`school_id`),
  KEY `employee_payslips_payroll_item_id_foreign` (`payroll_item_id`),
  KEY `employee_payslips_generated_by_foreign` (`generated_by`),
  CONSTRAINT `employee_payslips_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `employee_payslips_payroll_item_id_foreign` FOREIGN KEY (`payroll_item_id`) REFERENCES `payroll_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_payslips_payroll_run_id_foreign` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_payslips_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.employee_salary_structures
CREATE TABLE IF NOT EXISTS `employee_salary_structures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `employee_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pay_grade_id` bigint unsigned DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `total_ctc` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_salary_structures_pay_grade_id_foreign` (`pay_grade_id`),
  KEY `payroll_ess_sch_emp_type_id_idx` (`school_id`,`employee_type`,`employee_id`),
  KEY `employee_salary_structures_school_id_status_index` (`school_id`,`status`),
  KEY `employee_salary_structures_status_index` (`status`),
  CONSTRAINT `employee_salary_structures_pay_grade_id_foreign` FOREIGN KEY (`pay_grade_id`) REFERENCES `pay_grades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_salary_structures_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.exams
CREATE TABLE IF NOT EXISTS `exams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `exam_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date NOT NULL,
  `maximum_marks` smallint unsigned NOT NULL DEFAULT '100',
  `pass_marks` smallint unsigned NOT NULL DEFAULT '40',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exams_uuid_unique` (`uuid`),
  KEY `exams_academic_year_id_foreign` (`academic_year_id`),
  KEY `exams_class_section_id_foreign` (`class_section_id`),
  KEY `exams_subject_id_foreign` (`subject_id`),
  KEY `exams_school_id_class_section_id_index` (`school_id`,`class_section_id`),
  KEY `exams_school_id_subject_id_index` (`school_id`,`subject_id`),
  KEY `exams_status_index` (`status`),
  KEY `exams_is_published_index` (`is_published`),
  KEY `idx_exams_school_year_class` (`school_id`,`academic_year_id`,`class_section_id`),
  CONSTRAINT `exams_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.exam_marks
CREATE TABLE IF NOT EXISTS `exam_marks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `exam_schedule_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `marks_obtained` decimal(8,2) DEFAULT NULL,
  `grade` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_point` decimal(4,2) DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_marks_exam_schedule_id_student_id_unique` (`exam_schedule_id`,`student_id`),
  KEY `exam_marks_student_id_foreign` (`student_id`),
  KEY `exam_marks_exam_schedule_id_student_id_index` (`exam_schedule_id`,`student_id`),
  KEY `exam_marks_school_id_exam_schedule_id_index` (`school_id`,`exam_schedule_id`),
  KEY `exam_marks_status_index` (`status`),
  CONSTRAINT `exam_marks_exam_schedule_id_foreign` FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_marks_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.exam_results
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `exam_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `marks_obtained` smallint unsigned NOT NULL DEFAULT '0',
  `grade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_results_exam_id_student_id_unique` (`exam_id`,`student_id`),
  KEY `exam_results_student_id_foreign` (`student_id`),
  KEY `exam_results_exam_id_student_id_index` (`exam_id`,`student_id`),
  KEY `exam_results_status_index` (`status`),
  KEY `idx_exam_results_school_exam_status` (`school_id`,`exam_id`,`status`),
  KEY `idx_exam_results_school_student_exam` (`school_id`,`student_id`,`exam_id`),
  KEY `idx_exam_results_school_exam_student` (`school_id`,`exam_id`,`student_id`),
  CONSTRAINT `exam_results_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_results_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_results_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.exam_schedules
CREATE TABLE IF NOT EXISTS `exam_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `exam_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maximum_marks` smallint unsigned NOT NULL DEFAULT '100',
  `pass_marks` smallint unsigned NOT NULL DEFAULT '40',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exam_schedules_subject_id_foreign` (`subject_id`),
  KEY `exam_schedules_exam_id_subject_id_index` (`exam_id`,`subject_id`),
  KEY `exam_schedules_school_id_exam_id_index` (`school_id`,`exam_id`),
  CONSTRAINT `exam_schedules_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_schedules_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.fee_categories
CREATE TABLE IF NOT EXISTS `fee_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_categories_school_id_code_unique` (`school_id`,`code`),
  CONSTRAINT `fee_categories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.fee_payments
CREATE TABLE IF NOT EXISTS `fee_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `receipt_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_mode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `remarks` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_on` date NOT NULL,
  `collected_by` bigint unsigned DEFAULT NULL,
  `voided_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `void_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_payments_receipt_number_unique` (`receipt_number`),
  KEY `fee_payments_academic_year_id_foreign` (`academic_year_id`),
  KEY `fee_payments_collected_by_foreign` (`collected_by`),
  KEY `fee_payments_school_id_paid_on_index` (`school_id`,`paid_on`),
  KEY `fee_payments_student_id_academic_year_id_index` (`student_id`,`academic_year_id`),
  KEY `fee_payments_payment_mode_index` (`payment_mode`),
  KEY `fee_payments_paid_on_index` (`paid_on`),
  KEY `fee_payments_voided_by_foreign` (`voided_by`),
  KEY `fee_payments_status_index` (`status`),
  KEY `idx_fee_payments_school_paid` (`school_id`,`paid_on`),
  CONSTRAINT `fee_payments_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_payments_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fee_payments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_payments_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.fee_payment_items
CREATE TABLE IF NOT EXISTS `fee_payment_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `fee_payment_id` bigint unsigned NOT NULL,
  `student_fee_item_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fee_payment_items_student_fee_item_id_index` (`student_fee_item_id`),
  KEY `idx_fee_payment_items_payment_item` (`fee_payment_id`,`student_fee_item_id`),
  KEY `idx_fee_payment_items_school` (`school_id`),
  KEY `idx_fpi_student_fee_item` (`student_fee_item_id`),
  CONSTRAINT `fee_payment_items_fee_payment_id_foreign` FOREIGN KEY (`fee_payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_payment_items_student_fee_item_id_foreign` FOREIGN KEY (`student_fee_item_id`) REFERENCES `student_fee_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.fee_receipt_sequences
CREATE TABLE IF NOT EXISTS `fee_receipt_sequences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `last_number` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_receipt_sequences_school_id_academic_year_id_unique` (`school_id`,`academic_year_id`),
  KEY `fee_receipt_sequences_academic_year_id_foreign` (`academic_year_id`),
  KEY `idx_fee_receipt_school_year` (`school_id`,`academic_year_id`),
  CONSTRAINT `fee_receipt_sequences_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_receipt_sequences_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.fee_structures
CREATE TABLE IF NOT EXISTS `fee_structures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_structures_academic_year_id_class_section_id_unique` (`academic_year_id`,`class_section_id`),
  UNIQUE KEY `fs_school_year_section_unique` (`school_id`,`academic_year_id`,`class_section_id`),
  KEY `fee_structures_class_section_id_foreign` (`class_section_id`),
  KEY `fee_structures_status_index` (`status`),
  CONSTRAINT `fee_structures_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structures_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structures_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.fee_structure_items
CREATE TABLE IF NOT EXISTS `fee_structure_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fee_structure_id` bigint unsigned NOT NULL,
  `fee_category_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_structure_items_fee_structure_id_fee_category_id_unique` (`fee_structure_id`,`fee_category_id`),
  KEY `fee_structure_items_fee_category_id_foreign` (`fee_category_id`),
  CONSTRAINT `fee_structure_items_fee_category_id_foreign` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structure_items_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.grade_scales
CREATE TABLE IF NOT EXISTS `grade_scales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `grade_point` decimal(4,2) DEFAULT NULL,
  `is_fail` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grade_scales_school_id_status_index` (`school_id`,`status`),
  KEY `grade_scales_status_index` (`status`),
  CONSTRAINT `grade_scales_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.homework
CREATE TABLE IF NOT EXISTS `homework` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_date` date NOT NULL,
  `due_date` date NOT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `homework_academic_year_id_foreign` (`academic_year_id`),
  KEY `homework_subject_id_foreign` (`subject_id`),
  KEY `homework_school_id_class_section_id_index` (`school_id`,`class_section_id`),
  KEY `homework_school_id_subject_id_index` (`school_id`,`subject_id`),
  KEY `homework_school_id_status_index` (`school_id`,`status`),
  KEY `homework_status_index` (`status`),
  KEY `idx_homework_class_academic_status_due` (`class_section_id`,`academic_year_id`,`status`,`due_date`),
  CONSTRAINT `homework_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.leave_requests
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `leave_type_id` bigint unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `days` tinyint unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_user_id_foreign` (`user_id`),
  KEY `leave_requests_student_id_foreign` (`student_id`),
  KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`),
  KEY `leave_requests_approved_by_foreign` (`approved_by`),
  KEY `leave_requests_school_id_status_index` (`school_id`,`status`),
  KEY `leave_requests_school_id_student_id_index` (`school_id`,`student_id`),
  KEY `leave_requests_school_id_from_date_to_date_index` (`school_id`,`from_date`,`to_date`),
  KEY `leave_requests_status_index` (`status`),
  CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_requests_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.leave_types
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_types_school_id_name_unique` (`school_id`,`name`),
  KEY `leave_types_is_active_index` (`is_active`),
  CONSTRAINT `leave_types_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.library_authors
CREATE TABLE IF NOT EXISTS `library_authors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `library_authors_school_id_status_index` (`school_id`,`status`),
  KEY `library_authors_status_index` (`status`),
  CONSTRAINT `library_authors_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.library_books
CREATE TABLE IF NOT EXISTS `library_books` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `isbn` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  `publisher_id` bigint unsigned DEFAULT NULL,
  `edition` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'English',
  `rack_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `available_copies` int NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `library_books_category_id_foreign` (`category_id`),
  KEY `library_books_author_id_foreign` (`author_id`),
  KEY `library_books_publisher_id_foreign` (`publisher_id`),
  KEY `library_books_school_id_status_index` (`school_id`,`status`),
  KEY `library_books_title_index` (`title`),
  KEY `library_books_isbn_index` (`isbn`),
  KEY `library_books_status_index` (`status`),
  CONSTRAINT `library_books_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `library_authors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `library_books_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `library_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `library_books_publisher_id_foreign` FOREIGN KEY (`publisher_id`) REFERENCES `library_publishers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `library_books_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.library_categories
CREATE TABLE IF NOT EXISTS `library_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `library_categories_school_id_status_index` (`school_id`,`status`),
  KEY `library_categories_status_index` (`status`),
  CONSTRAINT `library_categories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.library_fine_settings
CREATE TABLE IF NOT EXISTS `library_fine_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `fine_per_day` decimal(10,2) NOT NULL DEFAULT '1.00',
  `max_fine` decimal(10,2) DEFAULT NULL,
  `grace_period_days` int NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `library_fine_settings_school_id_status_index` (`school_id`,`status`),
  KEY `library_fine_settings_status_index` (`status`),
  CONSTRAINT `library_fine_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.library_issues
CREATE TABLE IF NOT EXISTS `library_issues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `book_id` bigint unsigned NOT NULL,
  `issueable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issueable_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fine_paid` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'issued',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `library_issues_book_id_foreign` (`book_id`),
  KEY `library_issues_issueable_type_issueable_id_index` (`issueable_type`,`issueable_id`),
  KEY `library_issues_school_id_status_index` (`school_id`,`status`),
  KEY `library_issues_due_date_index` (`due_date`),
  KEY `library_issues_status_index` (`status`),
  CONSTRAINT `library_issues_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `library_issues_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.library_publishers
CREATE TABLE IF NOT EXISTS `library_publishers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `contact` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `library_publishers_school_id_status_index` (`school_id`,`status`),
  KEY `library_publishers_status_index` (`status`),
  CONSTRAINT `library_publishers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.login_activities
CREATE TABLE IF NOT EXISTS `login_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logged_in_at` timestamp NULL DEFAULT NULL,
  `logged_out_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_activities_user_id_foreign` (`user_id`),
  KEY `login_activities_school_id_user_id_created_at_index` (`school_id`,`user_id`,`created_at`),
  KEY `login_activities_email_index` (`email`),
  KEY `login_activities_status_index` (`status`),
  KEY `idx_login_activities_created_at` (`created_at`),
  CONSTRAINT `login_activities_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `login_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`school_id`,`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `model_has_permissions_permission_id_foreign` (`permission_id`),
  KEY `model_has_permissions_team_foreign_key_index` (`school_id`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`school_id`,`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `model_has_roles_role_id_foreign` (`role_id`),
  KEY `model_has_roles_team_foreign_key_index` (`school_id`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'attendance_alert, fee_reminder, exam_result_alert, announcement, timetable_update',
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium' COMMENT 'low, medium, high, urgent',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft, sent, failed',
  `target_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all' COMMENT 'all, students, parents, teachers, admins',
  `channel` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_app' COMMENT 'in_app, email, sms',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_school_id_foreign` (`school_id`),
  KEY `notifications_created_by_foreign` (`created_by`),
  KEY `notifications_updated_by_foreign` (`updated_by`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_status_index` (`status`),
  KEY `idx_notifications_target_status` (`target_type`,`status`),
  CONSTRAINT `notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.notification_user
CREATE TABLE IF NOT EXISTS `notification_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `delivery_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending, delivered, failed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_user_notification_id_user_id_unique` (`notification_id`,`user_id`),
  KEY `notification_user_user_id_foreign` (`user_id`),
  CONSTRAINT `notification_user_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.parents
CREATE TABLE IF NOT EXISTS `parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parents_uuid_unique` (`uuid`),
  UNIQUE KEY `parents_email_unique` (`email`),
  KEY `parents_user_id_foreign` (`user_id`),
  KEY `parents_created_by_foreign` (`created_by`),
  KEY `parents_updated_by_foreign` (`updated_by`),
  KEY `parents_school_id_status_index` (`school_id`,`status`),
  KEY `parents_email_index` (`email`),
  CONSTRAINT `parents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `parents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `parents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.parent_notifications
CREATE TABLE IF NOT EXISTS `parent_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('announcement','attendance_alert','fee_reminder','exam_result','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `target_parents` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_notifications_created_by_foreign` (`created_by`),
  KEY `parent_notifications_school_id_type_index` (`school_id`,`type`),
  KEY `parent_notifications_sent_at_index` (`sent_at`),
  CONSTRAINT `parent_notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `parent_notifications_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.parent_student
CREATE TABLE IF NOT EXISTS `parent_student` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `relationship` enum('father','mother','guardian','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guardian',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_student_parent_id_student_id_unique` (`parent_id`,`student_id`),
  KEY `parent_student_student_id_foreign` (`student_id`),
  KEY `parent_student_parent_id_is_primary_index` (`parent_id`,`is_primary`),
  CONSTRAINT `parent_student_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.payroll_departments
CREATE TABLE IF NOT EXISTS `payroll_departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_departments_school_id_status_index` (`school_id`,`status`),
  KEY `payroll_departments_status_index` (`status`),
  CONSTRAINT `payroll_departments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.payroll_designations
CREATE TABLE IF NOT EXISTS `payroll_designations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_designations_department_id_foreign` (`department_id`),
  KEY `payroll_designations_school_id_status_index` (`school_id`,`status`),
  KEY `payroll_designations_status_index` (`status`),
  CONSTRAINT `payroll_designations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `payroll_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_designations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.payroll_items
CREATE TABLE IF NOT EXISTS `payroll_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `payroll_run_id` bigint unsigned NOT NULL,
  `employee_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_items_payroll_run_id_index` (`payroll_run_id`),
  KEY `payroll_items_school_id_status_index` (`school_id`,`status`),
  KEY `payroll_items_status_index` (`status`),
  CONSTRAINT `payroll_items_payroll_run_id_foreign` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_items_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.payroll_runs
CREATE TABLE IF NOT EXISTS `payroll_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `month` int NOT NULL,
  `year` int NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `generated_by` bigint unsigned DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_runs_school_id_month_year_unique` (`school_id`,`month`,`year`),
  KEY `payroll_runs_generated_by_foreign` (`generated_by`),
  KEY `payroll_runs_school_id_status_index` (`school_id`,`status`),
  KEY `payroll_runs_status_index` (`status`),
  CONSTRAINT `payroll_runs_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_runs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.payroll_settings
CREATE TABLE IF NOT EXISTS `payroll_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `payroll_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR',
  `salary_day` int NOT NULL DEFAULT '1',
  `enable_professional_tax` tinyint(1) NOT NULL DEFAULT '1',
  `enable_provident_fund` tinyint(1) NOT NULL DEFAULT '1',
  `enable_esi` tinyint(1) NOT NULL DEFAULT '0',
  `pf_employee_share` decimal(5,2) NOT NULL DEFAULT '12.00',
  `pf_employer_share` decimal(5,2) NOT NULL DEFAULT '12.00',
  `esi_employee_share` decimal(5,2) NOT NULL DEFAULT '0.75',
  `esi_employer_share` decimal(5,2) NOT NULL DEFAULT '3.25',
  `professional_tax_monthly` decimal(10,2) NOT NULL DEFAULT '200.00',
  `overtime_rate_multiplier` decimal(5,2) NOT NULL DEFAULT '1.50',
  `pay_period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_settings_school_id_foreign` (`school_id`),
  KEY `payroll_settings_created_by_foreign` (`created_by`),
  KEY `payroll_settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `payroll_settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.pay_grades
CREATE TABLE IF NOT EXISTS `pay_grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `min_salary` decimal(12,2) DEFAULT NULL,
  `max_salary` decimal(12,2) DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pay_grades_school_id_status_index` (`school_id`,`status`),
  KEY `pay_grades_status_index` (`status`),
  CONSTRAINT `pay_grades_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_school_id_name_guard_name_unique` (`school_id`,`name`,`guard_name`),
  KEY `roles_team_foreign_key_index` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.routes
CREATE TABLE IF NOT EXISTS `routes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `route_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_point` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_point` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `distance` decimal(8,2) DEFAULT NULL,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `routes_vehicle_id_foreign` (`vehicle_id`),
  KEY `routes_driver_id_foreign` (`driver_id`),
  KEY `routes_school_id_status_index` (`school_id`,`status`),
  KEY `routes_status_index` (`status`),
  KEY `idx_routes_school_name` (`school_id`,`route_name`),
  KEY `idx_routes_school_start` (`school_id`,`start_point`),
  KEY `idx_routes_school_end` (`school_id`,`end_point`),
  CONSTRAINT `routes_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `routes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `routes_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.route_stops
CREATE TABLE IF NOT EXISTS `route_stops` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `route_id` bigint unsigned NOT NULL,
  `stop_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `drop_time` time DEFAULT NULL,
  `sequence` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `route_stops_school_id_foreign` (`school_id`),
  KEY `route_stops_route_id_sequence_index` (`route_id`,`sequence`),
  CONSTRAINT `route_stops_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `route_stops_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.salary_components
CREATE TABLE IF NOT EXISTS `salary_components` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_display` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'earning',
  `calculation_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_components_school_id_status_index` (`school_id`,`status`),
  KEY `salary_components_status_index` (`status`),
  CONSTRAINT `salary_components_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.schools
CREATE TABLE IF NOT EXISTS `schools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Kolkata',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR',
  `date_format` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'd-m-Y',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_uuid_unique` (`uuid`),
  UNIQUE KEY `schools_code_unique` (`code`),
  UNIQUE KEY `schools_slug_unique` (`slug`),
  KEY `schools_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.school_user
CREATE TABLE IF NOT EXISTS `school_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joined_at` date DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_user_school_id_user_id_unique` (`school_id`,`user_id`),
  KEY `school_user_user_id_foreign` (`user_id`),
  KEY `school_user_school_id_status_index` (`school_id`,`status`),
  KEY `school_user_status_index` (`status`),
  CONSTRAINT `school_user_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.sections
CREATE TABLE IF NOT EXISTS `sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` smallint unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sections_school_id_code_unique` (`school_id`,`code`),
  KEY `sections_school_id_status_index` (`school_id`,`status`),
  KEY `sections_status_index` (`status`),
  CONSTRAINT `sections_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.students
CREATE TABLE IF NOT EXISTS `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `admission_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admission_date` date DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `blood_group` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caste` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Indian',
  `mother_tongue` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aadhar_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_address` text COLLATE utf8mb4_unicode_ci,
  `permanent_address` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_school_id_admission_no_unique` (`school_id`,`admission_no`),
  UNIQUE KEY `students_uuid_unique` (`uuid`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_created_by_foreign` (`created_by`),
  KEY `students_updated_by_foreign` (`updated_by`),
  KEY `students_school_id_status_index` (`school_id`,`status`),
  KEY `students_school_id_first_name_last_name_index` (`school_id`,`first_name`,`last_name`),
  KEY `students_status_index` (`status`),
  KEY `idx_students_school_status` (`school_id`,`status`),
  FULLTEXT KEY `idx_students_name_fulltext` (`first_name`,`middle_name`,`last_name`),
  CONSTRAINT `students_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.student_documents
CREATE TABLE IF NOT EXISTS `student_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `document_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `file_size` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_documents_student_id_foreign` (`student_id`),
  KEY `student_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `student_documents_school_id_student_id_type_index` (`school_id`,`student_id`,`document_type`),
  KEY `student_documents_updated_by_foreign` (`updated_by`),
  KEY `student_documents_verified_by_foreign` (`verified_by`),
  CONSTRAINT `student_documents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_documents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.student_fees
CREATE TABLE IF NOT EXISTS `student_fees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `fee_structure_id` bigint unsigned NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_fees_student_id_academic_year_id_unique` (`student_id`,`academic_year_id`),
  UNIQUE KEY `student_fees_school_id_student_id_academic_year_id_unique` (`school_id`,`student_id`,`academic_year_id`),
  KEY `student_fees_academic_year_id_foreign` (`academic_year_id`),
  KEY `student_fees_fee_structure_id_foreign` (`fee_structure_id`),
  KEY `student_fees_status_index` (`status`),
  KEY `idx_student_fees_school_status` (`school_id`,`status`),
  CONSTRAINT `student_fees_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.student_fee_items
CREATE TABLE IF NOT EXISTS `student_fee_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `student_fee_id` bigint unsigned NOT NULL,
  `fee_category_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_fee_items_student_fee_id_fee_category_id_unique` (`student_fee_id`,`fee_category_id`),
  KEY `student_fee_items_fee_category_id_foreign` (`fee_category_id`),
  KEY `student_fee_items_due_date_index` (`due_date`),
  KEY `idx_student_fee_items_fee_id_due` (`student_fee_id`,`due_date`),
  KEY `idx_student_fee_items_school_fee` (`school_id`,`student_fee_id`),
  CONSTRAINT `student_fee_items_fee_category_id_foreign` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fee_items_student_fee_id_foreign` FOREIGN KEY (`student_fee_id`) REFERENCES `student_fees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.student_guardians
CREATE TABLE IF NOT EXISTS `student_guardians` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `relation` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `can_pickup` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_guardians_student_id_foreign` (`student_id`),
  KEY `student_guardians_user_id_foreign` (`user_id`),
  KEY `student_guardians_school_id_student_id_index` (`school_id`,`student_id`),
  KEY `student_guardians_phone_email_index` (`phone`,`email`),
  CONSTRAINT `student_guardians_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_guardians_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_guardians_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.student_sessions
CREATE TABLE IF NOT EXISTS `student_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `roll_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joined_on` date DEFAULT NULL,
  `left_on` date DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_sessions_school_id_academic_year_id_student_id_unique` (`school_id`,`academic_year_id`,`student_id`),
  UNIQUE KEY `student_sessions_roll_unique` (`school_id`,`academic_year_id`,`class_section_id`,`roll_no`),
  KEY `student_sessions_academic_year_id_foreign` (`academic_year_id`),
  KEY `student_sessions_school_year_class_status_idx` (`school_id`,`academic_year_id`,`class_section_id`,`status`),
  KEY `student_sessions_status_index` (`status`),
  KEY `idx_student_sessions_student_status` (`student_id`,`status`),
  KEY `idx_student_sessions_class_status` (`class_section_id`,`status`),
  CONSTRAINT `student_sessions_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_sessions_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_sessions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_sessions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.student_transfers
CREATE TABLE IF NOT EXISTS `student_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `transfer_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transfer',
  `from_class_section_id` bigint unsigned DEFAULT NULL,
  `to_class_section_id` bigint unsigned DEFAULT NULL,
  `from_academic_year_id` bigint unsigned DEFAULT NULL,
  `to_academic_year_id` bigint unsigned DEFAULT NULL,
  `transferred_on` date DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `tc_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tc_issued_on` date DEFAULT NULL,
  `conduct` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_school` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'issued',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_transfers_student_id_foreign` (`student_id`),
  KEY `student_transfers_from_class_section_id_foreign` (`from_class_section_id`),
  KEY `student_transfers_to_class_section_id_foreign` (`to_class_section_id`),
  KEY `student_transfers_from_academic_year_id_foreign` (`from_academic_year_id`),
  KEY `student_transfers_to_academic_year_id_foreign` (`to_academic_year_id`),
  KEY `student_transfers_created_by_foreign` (`created_by`),
  KEY `student_transfers_updated_by_foreign` (`updated_by`),
  KEY `student_transfers_school_id_transfer_type_index` (`school_id`,`transfer_type`),
  KEY `student_transfers_school_id_status_index` (`school_id`,`status`),
  KEY `student_transfers_school_id_student_id_index` (`school_id`,`student_id`),
  KEY `student_transfers_transfer_type_index` (`transfer_type`),
  KEY `student_transfers_tc_no_index` (`tc_no`),
  KEY `student_transfers_status_index` (`status`),
  CONSTRAINT `student_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_transfers_from_academic_year_id_foreign` FOREIGN KEY (`from_academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_transfers_from_class_section_id_foreign` FOREIGN KEY (`from_class_section_id`) REFERENCES `class_section` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_transfers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_transfers_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_transfers_to_academic_year_id_foreign` FOREIGN KEY (`to_academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_transfers_to_class_section_id_foreign` FOREIGN KEY (`to_class_section_id`) REFERENCES `class_section` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_transfers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'core',
  `credit_hours` smallint unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_school_id_code_unique` (`school_id`,`code`),
  KEY `subjects_school_id_status_index` (`school_id`,`status`),
  KEY `subjects_type_index` (`type`),
  KEY `subjects_status_index` (`status`),
  CONSTRAINT `subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teachers
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `qualification` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` tinyint unsigned DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teachers_school_id_employee_id_unique` (`school_id`,`employee_id`),
  UNIQUE KEY `teachers_uuid_unique` (`uuid`),
  KEY `teachers_user_id_foreign` (`user_id`),
  KEY `teachers_school_id_status_index` (`school_id`,`status`),
  KEY `teachers_status_index` (`status`),
  KEY `idx_teachers_school_status` (`school_id`,`status`),
  FULLTEXT KEY `idx_teachers_name_fulltext` (`first_name`,`middle_name`,`last_name`),
  CONSTRAINT `teachers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teacher_attendances
CREATE TABLE IF NOT EXISTS `teacher_attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `marked_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_attendances_teacher_id_attendance_date_unique` (`teacher_id`,`attendance_date`),
  KEY `teacher_attendances_marked_by_foreign` (`marked_by`),
  KEY `teacher_attendances_attendance_date_index` (`attendance_date`),
  KEY `teacher_attendances_status_index` (`status`),
  KEY `idx_teacher_attendances_teacher_date_status` (`teacher_id`,`attendance_date`,`status`),
  CONSTRAINT `teacher_attendances_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_attendances_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teacher_class_section
CREATE TABLE IF NOT EXISTS `teacher_class_section` (
  `teacher_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `is_class_teacher` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`teacher_id`,`class_section_id`),
  KEY `teacher_class_section_class_section_id_is_class_teacher_index` (`class_section_id`,`is_class_teacher`),
  KEY `teacher_class_section_is_class_teacher_index` (`is_class_teacher`),
  KEY `teacher_class_section_school_id_index` (`school_id`),
  CONSTRAINT `teacher_class_section_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_section_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_section_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teacher_documents
CREATE TABLE IF NOT EXISTS `teacher_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `document_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `teacher_documents_teacher_id_document_type_index` (`teacher_id`,`document_type`),
  CONSTRAINT `teacher_documents_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teacher_leaves
CREATE TABLE IF NOT EXISTS `teacher_leaves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `leave_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_leaves_approved_by_foreign` (`approved_by`),
  KEY `teacher_leaves_teacher_id_status_index` (`teacher_id`,`status`),
  KEY `teacher_leaves_status_index` (`status`),
  KEY `idx_teacher_leaves_teacher_status` (`teacher_id`,`status`),
  CONSTRAINT `teacher_leaves_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_leaves_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teacher_subject
CREATE TABLE IF NOT EXISTS `teacher_subject` (
  `teacher_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`teacher_id`,`subject_id`),
  KEY `teacher_subject_subject_id_foreign` (`subject_id`),
  KEY `teacher_subject_school_id_index` (`school_id`),
  CONSTRAINT `teacher_subject_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.teacher_timetable_slots
CREATE TABLE IF NOT EXISTS `teacher_timetable_slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `class_section_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `day_of_week` tinyint unsigned NOT NULL,
  `period_number` tinyint unsigned DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `period_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_timetable_unique` (`teacher_id`,`class_section_id`,`subject_id`,`day_of_week`,`period_label`),
  KEY `teacher_timetable_slots_class_section_id_foreign` (`class_section_id`),
  KEY `teacher_timetable_slots_subject_id_foreign` (`subject_id`),
  KEY `teacher_timetable_slots_day_of_week_index` (`day_of_week`),
  KEY `teacher_timetable_slots_status_index` (`status`),
  KEY `teacher_timetable_slots_created_by_foreign` (`created_by`),
  KEY `teacher_timetable_slots_updated_by_foreign` (`updated_by`),
  KEY `timetable_period_lookup` (`academic_year_id`,`class_section_id`,`day_of_week`,`period_number`),
  KEY `teacher_timetable_slots_school_id_foreign` (`school_id`),
  CONSTRAINT `teacher_timetable_slots_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_timetable_slots_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_timetable_slots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_timetable_slots_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_timetable_slots_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_timetable_slots_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_timetable_slots_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.transport_assignments
CREATE TABLE IF NOT EXISTS `transport_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `route_id` bigint unsigned DEFAULT NULL,
  `route_stop_id` bigint unsigned DEFAULT NULL,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `pickup_point` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transport_assignments_school_id_student_id_unique` (`school_id`,`student_id`),
  KEY `transport_assignments_student_id_foreign` (`student_id`),
  KEY `transport_assignments_route_id_foreign` (`route_id`),
  KEY `transport_assignments_route_stop_id_foreign` (`route_stop_id`),
  KEY `transport_assignments_vehicle_id_foreign` (`vehicle_id`),
  KEY `transport_assignments_school_id_status_index` (`school_id`,`status`),
  KEY `transport_assignments_status_index` (`status`),
  CONSTRAINT `transport_assignments_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transport_assignments_route_stop_id_foreign` FOREIGN KEY (`route_stop_id`) REFERENCES `route_stops` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transport_assignments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transport_assignments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transport_assignments_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.trips
CREATE TABLE IF NOT EXISTS `trips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `driver_id` bigint unsigned NOT NULL,
  `vehicle_id` bigint unsigned NOT NULL,
  `route_id` bigint unsigned NOT NULL,
  `type` enum('pickup','drop','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'both',
  `status` enum('scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `trip_date` date NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_students` smallint unsigned NOT NULL DEFAULT '0',
  `picked_up_count` smallint unsigned NOT NULL DEFAULT '0',
  `dropped_off_count` smallint unsigned NOT NULL DEFAULT '0',
  `total_distance` decimal(8,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trips_driver_id_foreign` (`driver_id`),
  KEY `trips_vehicle_id_foreign` (`vehicle_id`),
  KEY `trips_route_id_foreign` (`route_id`),
  KEY `trips_school_id_driver_id_trip_date_index` (`school_id`,`driver_id`,`trip_date`),
  KEY `trips_school_id_status_trip_date_index` (`school_id`,`status`,`trip_date`),
  CONSTRAINT `trips_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trips_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trips_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.trip_events
CREATE TABLE IF NOT EXISTS `trip_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `trip_id` bigint unsigned DEFAULT NULL,
  `trip_student_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trip_events_school_id_foreign` (`school_id`),
  KEY `trip_events_trip_student_id_foreign` (`trip_student_id`),
  KEY `trip_events_trip_id_event_type_index` (`trip_id`,`event_type`),
  KEY `trip_events_trip_id_created_at_index` (`trip_id`,`created_at`),
  CONSTRAINT `trip_events_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_events_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_events_trip_student_id_foreign` FOREIGN KEY (`trip_student_id`) REFERENCES `trip_students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.trip_students
CREATE TABLE IF NOT EXISTS `trip_students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `trip_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `route_stop_id` bigint unsigned NOT NULL,
  `pickup_status` enum('pending','picked_up','missed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `drop_status` enum('pending','dropped_off','missed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `dropped_off_at` timestamp NULL DEFAULT NULL,
  `pickup_latitude` decimal(10,7) DEFAULT NULL,
  `pickup_longitude` decimal(10,7) DEFAULT NULL,
  `drop_latitude` decimal(10,7) DEFAULT NULL,
  `drop_longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_students_trip_id_student_id_unique` (`trip_id`,`student_id`),
  KEY `trip_students_school_id_foreign` (`school_id`),
  KEY `trip_students_student_id_foreign` (`student_id`),
  KEY `trip_students_route_stop_id_foreign` (`route_stop_id`),
  KEY `trip_students_trip_id_route_stop_id_index` (`trip_id`,`route_stop_id`),
  CONSTRAINT `trip_students_route_stop_id_foreign` FOREIGN KEY (`route_stop_id`) REFERENCES `route_stops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_students_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_students_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `current_school_id` bigint unsigned DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `force_password_change` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_uuid_unique` (`uuid`),
  KEY `users_current_school_id_foreign` (`current_school_id`),
  KEY `users_status_index` (`status`),
  KEY `users_is_super_admin_index` (`is_super_admin`),
  CONSTRAINT `users_current_school_id_foreign` FOREIGN KEY (`current_school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.user_devices
CREATE TABLE IF NOT EXISTS `user_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_token` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_devices_user_id_device_token_unique` (`user_id`,`device_token`),
  KEY `user_devices_user_id_last_seen_at_index` (`user_id`,`last_seen_at`),
  KEY `user_devices_device_token_index` (`device_token`),
  CONSTRAINT `user_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.vehicles
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `vehicle_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bus',
  `capacity` smallint unsigned NOT NULL DEFAULT '0',
  `driver_id` bigint unsigned DEFAULT NULL,
  `attendant` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_school_id_vehicle_number_unique` (`school_id`,`vehicle_number`),
  KEY `vehicles_driver_id_foreign` (`driver_id`),
  KEY `vehicles_school_id_status_index` (`school_id`,`status`),
  KEY `vehicles_status_index` (`status`),
  CONSTRAINT `vehicles_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicles_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table school_erp.vehicle_locations
CREATE TABLE IF NOT EXISTS `vehicle_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `speed` decimal(8,2) DEFAULT NULL,
  `heading` decimal(5,2) DEFAULT NULL,
  `captured_at` timestamp NOT NULL,
  `source` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'driver_app, gps_device, manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_locations_vehicle_id_captured_at_index` (`vehicle_id`,`captured_at`),
  KEY `vehicle_locations_captured_at_index` (`captured_at`),
  CONSTRAINT `vehicle_locations_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
