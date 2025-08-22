-- db_schema.sql
-- SQL schema to recreate the `department_management` database used by the project.
-- NOTES / ASSUMPTIONS:
-- 1) This file is a reasonable schema inferred from the app code. Adjust types/columns as needed.
-- 2) The `users` table contains fields referenced by the app: id, name, email, password, role, dept_id, extra_info (JSON), dob, contact, address.
-- 3) The file includes DROP TABLE IF EXISTS statements so it can be re-run safely (will remove existing data).
-- 4) Replace the placeholder password hash in the sample admin INSERT with a real hashed password (bcrypt recommended).

-- Create database
DROP DATABASE IF EXISTS `department_management`;
CREATE DATABASE `department_management` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE `department_management`;

-- Departments
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_departments_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users (students, admins, staff)
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'student',
  `dept_id` INT UNSIGNED NULL,
  `extra_info` JSON NULL,
  `dob` DATE NULL,
  `contact` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_users_email` (`email`),
  KEY `idx_users_dept_id` (`dept_id`),
  CONSTRAINT `fk_users_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Announcements
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NULL,
  `author_id` INT UNSIGNED NOT NULL,
  `dept_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_announcements_author` (`author_id`),
  KEY `idx_announcements_dept` (`dept_id`),
  CONSTRAINT `fk_announcements_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_announcements_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NULL,
  `location` VARCHAR(255) NULL,
  `picture` VARCHAR(255) NULL,
  `created_by` INT UNSIGNED NULL,
  `dept_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_events_created_by` (`created_by`),
  KEY `idx_events_dept` (`dept_id`),
  CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_events_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Results (student results)
DROP TABLE IF EXISTS `results`;
CREATE TABLE `results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `course_code` VARCHAR(80) NULL,
  `course_title` VARCHAR(255) NULL,
  `grade` VARCHAR(30) NULL,
  `uploaded_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_results_student` (`student_id`),
  CONSTRAINT `fk_results_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Timetables
DROP TABLE IF EXISTS `timetables`;
CREATE TABLE `timetables` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year_of_study` TINYINT UNSIGNED NOT NULL,
  `day_of_week` VARCHAR(16) NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NULL,
  `course` VARCHAR(255) NOT NULL,
  `lecturer` VARCHAR(255) NULL,
  `room` VARCHAR(64) NULL,
  `dept_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `idx_timetables_dept` (`dept_id`),
  CONSTRAINT `fk_timetables_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: simple audit_logs table (useful for admin actions)
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(120) NOT NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample seed data (replace password with your own hash)
INSERT INTO `departments` (`name`, `description`) VALUES
  ('Computer Science', 'Computer Science Department'),
  ('Mathematics', 'Mathematics Department');

-- NOTE: set a proper hashed password before using this insert. Example uses a placeholder.
INSERT INTO `users` (`name`, `email`, `password`, `role`, `dept_id`) VALUES
  ('Site Admin', 'admin@example.com', '<REPLACE_WITH_BCRYPT_HASH>', 'admin', 1),
  ('Alice Student', 'alice@example.com', '<REPLACE_WITH_BCRYPT_HASH>', 'student', 1);

-- Example announcement and event
INSERT INTO `announcements` (`title`, `body`, `author_id`, `dept_id`) VALUES
  ('Welcome', 'Welcome to the department portal.', 1, NULL);

INSERT INTO `events` (`title`, `description`, `start_time`, `end_time`, `location`, `created_by`, `dept_id`) VALUES
  ('Orientation', 'Freshers orientation session', '2025-09-01 09:00:00', '2025-09-01 12:00:00', 'Main Hall', 1, 1);

-- End of file
