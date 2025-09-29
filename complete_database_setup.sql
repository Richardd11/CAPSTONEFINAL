-- ================================================================
-- COMPLETE EXAM SYSTEM DATABASE SETUP
-- ================================================================
-- This script creates a comprehensive database for the exam management system
-- Run this script to set up all tables with proper relationships and sample data

-- ================================================================
-- STEP 1: Create Database (if needed)
-- ================================================================
CREATE DATABASE IF NOT EXISTS `pokenginang` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `pokenginang`;

-- ================================================================
-- STEP 2: Base Tables (No Dependencies)
-- ================================================================

-- Users table - Core user management
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `school_id` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','faculty','student') NOT NULL,
  `year_level` INT(11) DEFAULT NULL,
  `section` VARCHAR(10) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_school_id` (`school_id`),
  INDEX `idx_year_section` (`year_level`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects table - Course management
CREATE TABLE IF NOT EXISTS `subjects` (
    `subject_id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_code` VARCHAR(20) NOT NULL UNIQUE,
    `subject_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `units` INT(11) NOT NULL DEFAULT 3,
    `year_level` VARCHAR(20) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`subject_id`),
    INDEX `idx_subject_code` (`subject_code`),
    INDEX `idx_year_semester` (`year_level`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 3: Relationship Tables
-- ================================================================

-- Subject assignments - Faculty to subject assignments
CREATE TABLE IF NOT EXISTS `subject_assignments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_id` INT(11) NOT NULL,
    `faculty_id` INT(11) NOT NULL,
    `year_level` VARCHAR(20) NOT NULL,
    `section` VARCHAR(10) NOT NULL,
    `academic_year` VARCHAR(10) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `status` ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_assignment` (`subject_id`, `year_level`, `section`, `academic_year`, `semester`),
    INDEX `idx_faculty_year` (`faculty_id`, `academic_year`),
    INDEX `idx_subject_year` (`subject_id`, `academic_year`),
    CONSTRAINT `fk_subject_assignments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subject_assignments_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student enrollments
CREATE TABLE IF NOT EXISTS `student_enrollments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) NOT NULL,
    `subject_id` INT(11) NOT NULL,
    `academic_year` VARCHAR(10) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `status` ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
    `enrollment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_enrollment` (`student_id`, `subject_id`, `academic_year`, `semester`),
    CONSTRAINT `fk_enrollments_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 4: Exam System Tables
-- ================================================================

-- Exams table - Main exam configuration
CREATE TABLE IF NOT EXISTS `exams` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `subject_id` INT(11) NOT NULL,
    `faculty_id` INT(11) NOT NULL,
    `year_level` VARCHAR(20) NOT NULL,
    `section` VARCHAR(10) NOT NULL,
    `academic_year` VARCHAR(20) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `exam_type` ENUM('quiz','midterm','final','assignment','project') NOT NULL DEFAULT 'quiz',
    `time_limit` INT(11) DEFAULT 60,
    `total_points` INT(11) DEFAULT 0,
    `instructions` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `allow_retakes` TINYINT(1) DEFAULT 0,
    `max_attempts` INT(11) DEFAULT 1,
    `show_results` TINYINT(1) DEFAULT 1,
    `randomize_questions` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_faculty_id` (`faculty_id`),
    KEY `idx_subject_id` (`subject_id`),
    KEY `idx_academic_year` (`academic_year`),
    KEY `idx_exam_type` (`exam_type`),
    KEY `idx_active_dates` (`is_active`, `start_date`, `end_date`),
    CONSTRAINT `fk_exams_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questions table - Exam questions
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice','true_false','short_answer','essay','fill_blank','matching','dropdown') NOT NULL DEFAULT 'multiple_choice',
    `points` INT(11) NOT NULL DEFAULT 1,
    `order_index` INT(11) NOT NULL DEFAULT 0,
    `is_required` TINYINT(1) DEFAULT 1,
    `explanation` TEXT,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_exam_id` (`exam_id`),
    KEY `idx_order_index` (`order_index`),
    KEY `idx_question_type` (`question_type`),
    CONSTRAINT `fk_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Question options table - Multiple choice options
CREATE TABLE IF NOT EXISTS `question_options` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `question_id` INT(11) NOT NULL,
    `option_text` TEXT NOT NULL,
    `is_correct` TINYINT(1) DEFAULT 0,
    `order_index` INT(11) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_question_id` (`question_id`),
    KEY `idx_order_index` (`order_index`),
    KEY `idx_is_correct` (`is_correct`),
    CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exam attempts table - Student exam attempts
CREATE TABLE IF NOT EXISTS `exam_attempts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL,
    `student_id` INT(11) NOT NULL,
    `attempt_number` INT(11) NOT NULL DEFAULT 1,
    `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `end_time` TIMESTAMP NULL DEFAULT NULL,
    `score` DECIMAL(5,2) DEFAULT NULL,
    `total_points` INT(11) DEFAULT 0,
    `percentage` DECIMAL(5,2) DEFAULT NULL,
    `status` ENUM('in_progress','completed','abandoned','graded','submitted') DEFAULT 'in_progress',
    `time_taken` INT(11) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_attempt` (`exam_id`,`student_id`,`attempt_number`),
    KEY `idx_exam_id` (`exam_id`),
    KEY `idx_student_id` (`student_id`),
    KEY `idx_status` (`status`),
    KEY `idx_start_time` (`start_time`),
    CONSTRAINT `fk_attempts_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attempts_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student answers table - Individual question responses
CREATE TABLE IF NOT EXISTS `student_answers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `attempt_id` INT(11) NOT NULL,
    `question_id` INT(11) NOT NULL,
    `answer_text` TEXT,
    `selected_option_id` INT(11) DEFAULT NULL,
    `is_correct` TINYINT(1) DEFAULT NULL,
    `points_earned` DECIMAL(5,2) DEFAULT 0,
    `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_answer` (`attempt_id`,`question_id`),
    KEY `idx_attempt_id` (`attempt_id`),
    KEY `idx_question_id` (`question_id`),
    KEY `idx_selected_option` (`selected_option_id`),
    KEY `idx_is_correct` (`is_correct`),
    CONSTRAINT `fk_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_option` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 5: Additional System Tables
-- ================================================================

-- System settings table
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `description` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table for tracking important actions
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `table_name` VARCHAR(50) DEFAULT NULL,
    `record_id` INT(11) DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_table_record` (`table_name`, `record_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 6: Insert Sample Data
-- ================================================================

-- Insert sample users (password is 'password' hashed with bcrypt)
INSERT INTO `users` (`user_id`, `school_id`, `full_name`, `email`, `password`, `role`, `year_level`, `section`) VALUES
(1, 'ADMIN001', 'System Administrator', 'admin@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL),
(2, 'FAC001', 'Dr. John Smith', 'john.smith@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(3, 'FAC002', 'Dr. Jane Doe', 'jane.doe@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(4, 'FAC003', 'Prof. Michael Johnson', 'michael.johnson@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(5, '2020-001', 'Alice Johnson', 'alice.johnson@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2, 'A'),
(6, '2020-002', 'Bob Smith', 'bob.smith@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2, 'A'),
(7, '2021-001', 'Charlie Brown', 'charlie.brown@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3, 'A'),
(8, '2021-002', 'Diana Prince', 'diana.prince@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3, 'A'),
(9, '2022-001', 'Eve Wilson', 'eve.wilson@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1, 'A'),
(10, '2022-002', 'Frank Miller', 'frank.miller@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1, 'B')
ON DUPLICATE KEY UPDATE 
    `full_name` = VALUES(`full_name`),
    `email` = VALUES(`email`),
    `role` = VALUES(`role`),
    `year_level` = VALUES(`year_level`),
    `section` = VALUES(`section`);

-- Insert sample subjects
INSERT INTO `subjects` (`subject_code`, `subject_name`, `description`, `units`, `year_level`, `semester`) VALUES
('CS101', 'Introduction to Computer Science', 'Basic computer science concepts and programming fundamentals', 3, '1st Year', '1st Semester'),
('CS102', 'Programming Fundamentals', 'Introduction to programming using modern languages', 3, '1st Year', '2nd Semester'),
('MATH101', 'College Algebra', 'Fundamental algebraic concepts and mathematical reasoning', 3, '1st Year', '1st Semester'),
('MATH102', 'Calculus I', 'Introduction to differential and integral calculus', 4, '1st Year', '2nd Semester'),
('ENG101', 'English Communication', 'Basic English communication and writing skills', 3, '1st Year', '1st Semester'),
('CS201', 'Data Structures and Algorithms', 'Advanced programming concepts and algorithm design', 3, '2nd Year', '1st Semester'),
('CS202', 'Object-Oriented Programming', 'Object-oriented design and programming principles', 3, '2nd Year', '2nd Semester'),
('DB101', 'Database Management Systems', 'Introduction to database design and management', 3, '2nd Year', '1st Semester')
ON DUPLICATE KEY UPDATE 
    `subject_name` = VALUES(`subject_name`),
    `description` = VALUES(`description`),
    `units` = VALUES(`units`);

-- Insert subject assignments
INSERT INTO `subject_assignments` (`subject_id`, `faculty_id`, `year_level`, `section`, `academic_year`, `semester`, `status`, `notes`) VALUES
(1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'Primary instructor for CS101 Section A'),
(1, 3, '1st Year', 'B', '2024-2025', '1st Semester', 'active', 'Primary instructor for CS101 Section B'),
(2, 2, '1st Year', 'A', '2024-2025', '2nd Semester', 'active', 'Programming Fundamentals instructor'),
(3, 4, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'Mathematics instructor'),
(3, 4, '1st Year', 'B', '2024-2025', '1st Semester', 'active', 'Mathematics instructor'),
(5, 3, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'English communication instructor'),
(6, 2, '2nd Year', 'A', '2024-2025', '1st Semester', 'active', 'Data Structures instructor'),
(8, 3, '2nd Year', 'A', '2024-2025', '1st Semester', 'active', 'Database systems instructor')
ON DUPLICATE KEY UPDATE 
    `faculty_id` = VALUES(`faculty_id`),
    `status` = VALUES(`status`),
    `notes` = VALUES(`notes`);

-- Insert student enrollments
INSERT INTO `student_enrollments` (`student_id`, `subject_id`, `academic_year`, `semester`, `status`) VALUES
-- 1st Year Students
(9, 1, '2024-2025', '1st Semester', 'enrolled'),  -- Eve in CS101
(9, 3, '2024-2025', '1st Semester', 'enrolled'),  -- Eve in MATH101
(9, 5, '2024-2025', '1st Semester', 'enrolled'),  -- Eve in ENG101
(10, 1, '2024-2025', '1st Semester', 'enrolled'), -- Frank in CS101
(10, 3, '2024-2025', '1st Semester', 'enrolled'), -- Frank in MATH101
(10, 5, '2024-2025', '1st Semester', 'enrolled'), -- Frank in ENG101
-- 2nd Year Students
(5, 6, '2024-2025', '1st Semester', 'enrolled'),  -- Alice in Data Structures
(5, 8, '2024-2025', '1st Semester', 'enrolled'),  -- Alice in Database
(6, 6, '2024-2025', '1st Semester', 'enrolled'),  -- Bob in Data Structures
(6, 8, '2024-2025', '1st Semester', 'enrolled'),  -- Bob in Database
-- 3rd Year Students (previous courses completed)
(7, 1, '2023-2024', '1st Semester', 'completed'), -- Charlie completed CS101
(7, 3, '2023-2024', '1st Semester', 'completed'), -- Charlie completed MATH101
(8, 1, '2023-2024', '1st Semester', 'completed'), -- Diana completed CS101
(8, 3, '2023-2024', '1st Semester', 'completed')  -- Diana completed MATH101
ON DUPLICATE KEY UPDATE 
    `status` = VALUES(`status`);

-- Insert sample exams
INSERT INTO `exams` (`title`, `description`, `subject_id`, `faculty_id`, `year_level`, `section`, `academic_year`, `semester`, `exam_type`, `time_limit`, `total_points`, `instructions`, `is_active`, `start_date`, `end_date`) VALUES
('CS101 Midterm Examination', 'Comprehensive midterm covering programming fundamentals and basic computer science concepts', 1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'midterm', 120, 100, 'Read all questions carefully. Choose the best answer for multiple choice questions. Provide detailed explanations for essay questions. You have 2 hours to complete this exam.', 1, '2024-10-15 09:00:00', '2024-10-15 23:59:59'),
('CS101 Final Examination', 'Final exam covering all course material from the semester', 1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'final', 180, 150, 'This is the final exam covering all topics discussed this semester. Good luck! Make sure to manage your time wisely.', 0, '2024-12-10 09:00:00', '2024-12-10 23:59:59'),
('Programming Quiz 1', 'Quick quiz on basic programming concepts and syntax', 1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'quiz', 30, 25, 'Short quiz covering variables, data types, and basic control structures.', 1, '2024-09-20 10:00:00', '2024-09-20 23:59:59'),
('Math 101 Quiz - Algebra Basics', 'Quiz on fundamental algebraic operations and equations', 3, 4, '1st Year', 'A', '2024-2025', '1st Semester', 'quiz', 45, 30, 'Solve all problems showing your work. Partial credit will be given for correct methodology.', 1, '2024-09-25 14:00:00', '2024-09-25 23:59:59'),
('Data Structures Midterm', 'Midterm examination on arrays, linked lists, stacks, and queues', 6, 2, '2nd Year', 'A', '2024-2025', '1st Semester', 'midterm', 90, 80, 'Implement algorithms and analyze time complexity. Code examples should be clear and well-commented.', 1, '2024-10-20 13:00:00', '2024-10-20 23:59:59')
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `is_active` = VALUES(`is_active`);

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('system_name', 'Academic Exam Management System', 'Name of the examination system'),
('default_exam_time_limit', '60', 'Default time limit for exams in minutes'),
('max_exam_attempts', '3', 'Maximum number of attempts allowed per exam'),
('auto_grade_multiple_choice', '1', 'Automatically grade multiple choice questions'),
('show_results_immediately', '0', 'Show exam results immediately after completion'),
('allow_exam_retakes', '1', 'Allow students to retake exams'),
('academic_year', '2024-2025', 'Current academic year'),
('current_semester', '1st Semester', 'Current semester'),
('maintenance_mode', '0', 'System maintenance mode (0=off, 1=on)'),
('backup_frequency', 'daily', 'Database backup frequency')
ON DUPLICATE KEY UPDATE 
    `setting_value` = VALUES(`setting_value`),
    `description` = VALUES(`description`);

-- ================================================================
-- STEP 7: Create Views for Common Queries
-- ================================================================

-- View for active exams with subject and faculty information
CREATE OR REPLACE VIEW `active_exams_view` AS
SELECT 
    e.id,
    e.title,
    e.description,
    e.exam_type,
    e.time_limit,
    e.total_points,
    e.start_date,
    e.end_date,
    e.year_level,
    e.section,
    e.academic_year,
    e.semester,
    s.subject_code,
    s.subject_name,
    u.full_name as faculty_name,
    u.email as faculty_email,
    (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) as question_count
FROM exams e
JOIN subjects s ON e.subject_id = s.subject_id
JOIN users u ON e.faculty_id = u.user_id
WHERE e.is_active = 1
ORDER BY e.start_date DESC;

-- View for student exam results
CREATE OR REPLACE VIEW `student_results_view` AS
SELECT 
    ea.id as attempt_id,
    ea.exam_id,
    e.title as exam_title,
    e.exam_type,
    u.school_id,
    u.full_name as student_name,
    ea.attempt_number,
    ea.start_time,
    ea.end_time,
    ea.score,
    ea.total_points,
    ea.percentage,
    ea.status,
    ea.time_taken,
    s.subject_code,
    s.subject_name,
    f.full_name as faculty_name
FROM exam_attempts ea
JOIN exams e ON ea.exam_id = e.id
JOIN users u ON ea.student_id = u.user_id
JOIN subjects s ON e.subject_id = s.subject_id
JOIN users f ON e.faculty_id = f.user_id
ORDER BY ea.start_time DESC;

-- View for faculty exam statistics
CREATE OR REPLACE VIEW `faculty_exam_stats` AS
SELECT 
    u.user_id,
    u.full_name as faculty_name,
    s.subject_code,
    s.subject_name,
    COUNT(DISTINCT e.id) as total_exams,
    COUNT(DISTINCT ea.id) as total_attempts,
    AVG(ea.score) as average_score,
    COUNT(DISTINCT ea.student_id) as unique_students
FROM users u
JOIN exams e ON u.user_id = e.faculty_id
JOIN subjects s ON e.subject_id = s.subject_id
LEFT JOIN exam_attempts ea ON e.id = ea.exam_id AND ea.status = 'completed'
WHERE u.role = 'faculty'
GROUP BY u.user_id, s.subject_id
ORDER BY u.full_name, s.subject_code;

-- ================================================================
-- STEP 8: Create Stored Procedures
-- ================================================================

DELIMITER //

-- Procedure to calculate exam statistics
CREATE PROCEDURE GetExamStatistics(IN exam_id INT)
BEGIN
    SELECT 
        e.title,
        e.total_points,
        COUNT(ea.id) as total_attempts,
        COUNT(DISTINCT ea.student_id) as unique_students,
        AVG(ea.score) as average_score,
        MIN(ea.score) as min_score,
        MAX(ea.score) as max_score,
        AVG(ea.time_taken) as average_time_taken,
        COUNT(CASE WHEN ea.status = 'completed' THEN 1 END) as completed_attempts,
        COUNT(CASE WHEN ea.status = 'in_progress' THEN 1 END) as in_progress_attempts
    FROM exams e
    LEFT JOIN exam_attempts ea ON e.id = ea.exam_id
    WHERE e.id = exam_id
    GROUP BY e.id;
END //

-- Procedure to get student's exam history
CREATE PROCEDURE GetStudentExamHistory(IN student_id INT)
BEGIN
    SELECT 
        e.title,
        e.exam_type,
        s.subject_code,
        s.subject_name,
        ea.attempt_number,
        ea.start_time,
        ea.end_time,
        ea.score,
        ea.total_points,
        ea.percentage,
        ea.status,
        CASE 
            WHEN ea.percentage >= 90 THEN 'A'
            WHEN ea.percentage >= 80 THEN 'B'
            WHEN ea.percentage >= 70 THEN 'C'
            WHEN ea.percentage >= 60 THEN 'D'
            ELSE 'F'
        END as letter_grade
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    JOIN subjects s ON e.subject_id = s.subject_id
    WHERE ea.student_id = student_id
    ORDER BY ea.start_time DESC;
END //

DELIMITER ;

-- ================================================================
-- COMPLETION MESSAGE
-- ================================================================

SELECT 'Database setup completed successfully!' as message,
       'All tables, views, and procedures have been created.' as status,
       NOW() as completed_at;
