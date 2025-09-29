-- TDD ITERATION 2: Database Schema - CORRECT SEQUENCE
-- Tables must be created in dependency order to avoid foreign key errors

-- ================================================================
-- STEP 1: Create base tables WITHOUT foreign key dependencies
-- ================================================================

-- 1.1 Users table (no dependencies)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `school_id` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','faculty','student') NOT NULL,
  `year_level` INT(11) DEFAULT NULL,
  `section` VARCHAR(10) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 1.2 Subjects table (no dependencies)
CREATE TABLE IF NOT EXISTS `subjects` (
    `subject_id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_code` VARCHAR(20) NOT NULL UNIQUE,
    `subject_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `units` INT(11) NOT NULL DEFAULT 3,
    `year_level` VARCHAR(20) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- STEP 2: Insert base data (before creating dependent tables)
-- ================================================================

-- 2.1 Insert users with properly hashed passwords
INSERT INTO `users` (`user_id`, `school_id`, `full_name`, `password`, `role`, `year_level`, `section`) VALUES
(1, 'ADMIN001', 'Admin User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL),
(2, 'FAC001', 'Dr. John Smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(3, 'FAC002', 'Dr. Jane Doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(4, '2020-001', 'Student One', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2, 'A'),
(5, '2020-002', 'Student Two', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2, 'A'),
(6, '2021-001', 'Student Three', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3, 'A'),
(7, '2021-002', 'Student Four', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3, 'A')
ON DUPLICATE KEY UPDATE 
    `full_name` = VALUES(`full_name`),
    `password` = VALUES(`password`),
    `role` = VALUES(`role`),
    `year_level` = VALUES(`year_level`),
    `section` = VALUES(`section`);

-- 2.2 Insert subjects data
INSERT INTO `subjects` (`subject_code`, `subject_name`, `description`, `units`, `year_level`, `semester`) VALUES
('CS101', 'Introduction to Computer Science', 'Basic computer science concepts', 3, '1st Year', '1st Semester'),
('MATH101', 'College Algebra', 'Fundamental algebraic concepts', 3, '1st Year', '1st Semester'),
('ENG101', 'English Communication', 'Basic English communication skills', 3, '1st Year', '1st Semester')
ON DUPLICATE KEY UPDATE 
    `subject_name` = VALUES(`subject_name`),
    `description` = VALUES(`description`);

-- ================================================================
-- STEP 3: Create tables with foreign key dependencies
-- ================================================================

-- 3.1 Subject-Faculty assignment table (depends on users + subjects)
CREATE TABLE IF NOT EXISTS `subject_faculty` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_id` INT(11) NOT NULL,
    `faculty_id` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_assignment` (`subject_id`, `faculty_id`),
    CONSTRAINT `fk_subject_faculty_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subject_faculty_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3.2 Enhanced Exams table (depends on subjects + users)
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
    `time_limit` INT(11) DEFAULT 60, -- Time limit in minutes
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
    CONSTRAINT `fk_exams_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 4: Create tables that depend on exams
-- ================================================================

-- 4.1 Enhanced Questions table (depends on exams)
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
    CONSTRAINT `fk_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2 Question Options table (for multiple choice questions)
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
    CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.3 Exam Attempts table (for tracking student attempts)
CREATE TABLE IF NOT EXISTS `exam_attempts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL,
    `student_id` INT(11) NOT NULL,
    `attempt_number` INT(11) NOT NULL DEFAULT 1,
    `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `end_time` TIMESTAMP NULL DEFAULT NULL,
    `score` DECIMAL(5,2) DEFAULT NULL,
    `total_points` INT(11) DEFAULT 0,
    `status` ENUM('in_progress','completed','abandoned','graded') DEFAULT 'in_progress',
    `time_taken` INT(11) DEFAULT NULL, -- Time taken in seconds
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_attempt` (`exam_id`,`student_id`,`attempt_number`),
    KEY `idx_exam_id` (`exam_id`),
    KEY `idx_student_id` (`student_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_attempts_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attempts_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.4 Student Answers table (for storing student responses)
CREATE TABLE IF NOT EXISTS `student_answers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `attempt_id` INT(11) NOT NULL,
    `question_id` INT(11) NOT NULL,
    `answer_text` TEXT,
    `selected_option_id` INT(11) DEFAULT NULL,
    `is_correct` TINYINT(1) DEFAULT NULL,
    `points_earned` DECIMAL(5,2) DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_answer` (`attempt_id`,`question_id`),
    KEY `idx_attempt_id` (`attempt_id`),
    KEY `idx_question_id` (`question_id`),
    KEY `idx_selected_option` (`selected_option_id`),
    CONSTRAINT `fk_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_option` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STEP 5: Insert sample data for dependent tables
-- ================================================================

-- 5.1 Sample exam data (updated for new structure)
INSERT INTO `exams` (`title`, `description`, `subject_id`, `faculty_id`, `year_level`, `section`, `academic_year`, `semester`, `exam_type`, `time_limit`, `total_points`, `instructions`, `is_active`) VALUES
('Midterm Examination - CS101', 'Comprehensive midterm covering programming fundamentals', 1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'midterm', 120, 100, 'Read all questions carefully. Choose the best answer. You have 2 hours to complete this exam.', 1),
('Final Examination - CS101', 'Final exam covering all course material', 1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'final', 180, 150, 'This is the final exam. Good luck! Make sure to manage your time wisely.', 0),
('Quiz 1 - Introduction to Programming', 'Quick quiz on basic programming concepts', 1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'quiz', 30, 25, 'Short quiz covering the first three chapters.', 1)
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `is_active` = VALUES(`is_active`);

-- 5.2 Sample questions (updated for new structure)
INSERT INTO `questions` (`exam_id`, `question_text`, `question_type`, `points`, `order_index`, `explanation`) VALUES
(1, 'What is the capital of France?', 'multiple_choice', 5, 1, 'Paris is the capital and largest city of France.'),
(1, 'PHP stands for PHP: Hypertext Preprocessor', 'true_false', 3, 2, 'This is correct. PHP is a recursive acronym.'),
(1, 'What does MVC stand for in software architecture?', 'multiple_choice', 7, 3, 'MVC is a software design pattern that separates application logic into three interconnected components.'),
(1, 'Explain the difference between a compiler and an interpreter.', 'short_answer', 10, 4, 'A compiler translates source code into machine code before execution, while an interpreter executes code line by line at runtime.')
ON DUPLICATE KEY UPDATE 
    `question_text` = VALUES(`question_text`),
    `points` = VALUES(`points`),
    `explanation` = VALUES(`explanation`);

-- 5.3 Sample question options (new table)
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `order_index`) VALUES
-- Options for question 1 (Capital of France)
(1, 'London', 0, 1),
(1, 'Berlin', 0, 2),
(1, 'Paris', 1, 3),
(1, 'Madrid', 0, 4),
-- Options for question 3 (MVC)
(3, 'Model View Controller', 1, 1),
(3, 'Multiple View Control', 0, 2),
(3, 'Main Visual Component', 0, 3),
(3, 'Modern Version Control', 0, 4)
ON DUPLICATE KEY UPDATE 
    `option_text` = VALUES(`option_text`),
    `is_correct` = VALUES(`is_correct`);

-- 5.3 Sample subject-faculty assignments
INSERT INTO `subject_faculty` (`subject_id`, `faculty_id`) VALUES
(1, 2), -- CS101 assigned to Dr. John Smith
(2, 3), -- MATH101 assigned to Dr. Jane Doe
(3, 2)  -- ENG101 assigned to Dr. John Smith
ON DUPLICATE KEY UPDATE 
    `updated_at` = CURRENT_TIMESTAMP;

-- ================================================================
-- STEP 6: Enhanced Subject Assignments System
-- ================================================================

-- 6.1 Enhanced subject assignments table (replaces subject_faculty)
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
    UNIQUE KEY `unique_assignment` (
        `subject_id`, 
        `year_level`, 
        `section`, 
        `academic_year`, 
        `semester`
    ),
    INDEX `idx_faculty_year` (`faculty_id`, `academic_year`),
    INDEX `idx_subject_year` (`subject_id`, `academic_year`),
    INDEX `idx_year_section` (`year_level`, `section`, `academic_year`),
    CONSTRAINT `fk_subject_assignments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subject_assignments_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6.2 Sample enhanced assignments data
INSERT INTO `subject_assignments` (`subject_id`, `faculty_id`, `year_level`, `section`, `academic_year`, `semester`, `status`, `notes`) VALUES
(1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'Primary instructor for Section A'),
(1, 3, '1st Year', 'B', '2024-2025', '1st Semester', 'active', 'Primary instructor for Section B'),
(2, 3, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'Mathematics instructor'),
(2, 2, '1st Year', 'B', '2024-2025', '1st Semester', 'active', 'Mathematics instructor'),
(3, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'English communication instructor'),
(3, 3, '1st Year', 'B', '2024-2025', '1st Semester', 'active', 'English communication instructor'),
(1, 2, '2nd Year', 'A', '2024-2025', '1st Semester', 'active', 'Advanced CS for 2nd year'),
(2, 3, '2nd Year', 'A', '2024-2025', '1st Semester', 'active', 'Advanced Math for 2nd year')
ON DUPLICATE KEY UPDATE 
    `faculty_id` = VALUES(`faculty_id`),
    `status` = VALUES(`status`),
    `notes` = VALUES(`notes`),
    `updated_at` = CURRENT_TIMESTAMP;