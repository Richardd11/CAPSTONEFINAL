-- Exam System Database Schema
-- Run this SQL to create the necessary tables for the exam functionality

-- Create exams table
CREATE TABLE IF NOT EXISTS `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `subject_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `section` varchar(10) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `exam_type` enum('quiz','midterm','final','assignment','project') NOT NULL DEFAULT 'quiz',
  `time_limit` int(11) DEFAULT 60 COMMENT 'Time limit in minutes',
  `total_points` int(11) DEFAULT 0,
  `instructions` text,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `allow_retakes` tinyint(1) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 1,
  `show_results` tinyint(1) DEFAULT 1,
  `randomize_questions` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faculty_id` (`faculty_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_exam_type` (`exam_type`),
  CONSTRAINT `fk_exams_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create questions table
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','essay','fill_blank','matching','dropdown') NOT NULL DEFAULT 'multiple_choice',
  `points` int(11) NOT NULL DEFAULT 1,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `explanation` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exam_id` (`exam_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `fk_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create question_options table (for multiple choice questions)
CREATE TABLE IF NOT EXISTS `question_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_question_id` (`question_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create exam_attempts table (for tracking student attempts)
CREATE TABLE IF NOT EXISTS `exam_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attempt_number` int(11) NOT NULL DEFAULT 1,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `status` enum('in_progress','completed','abandoned','graded') DEFAULT 'in_progress',
  `time_taken` int(11) DEFAULT NULL COMMENT 'Time taken in seconds',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attempt` (`exam_id`,`student_id`,`attempt_number`),
  KEY `idx_exam_id` (`exam_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_attempts_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attempts_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create student_answers table (for storing student responses)
CREATE TABLE IF NOT EXISTS `student_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text,
  `selected_option_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_answer` (`attempt_id`,`question_id`),
  KEY `idx_attempt_id` (`attempt_id`),
  KEY `idx_question_id` (`question_id`),
  KEY `idx_selected_option` (`selected_option_id`),
  CONSTRAINT `fk_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_answers_option` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data for testing (optional)
-- You can uncomment these if you want some test data

/*
-- Sample exam
INSERT INTO `exams` (`title`, `description`, `subject_id`, `faculty_id`, `year_level`, `section`, `academic_year`, `semester`, `exam_type`, `time_limit`, `instructions`) 
VALUES 
('Sample Quiz - Introduction to Programming', 'A basic quiz covering programming fundamentals', 1, 1, '1st Year', 'A', '2024-2025', '1st Semester', 'quiz', 30, 'Please read each question carefully and select the best answer.');

-- Sample questions
INSERT INTO `questions` (`exam_id`, `question_text`, `question_type`, `points`, `order_index`) 
VALUES 
(1, 'What is a variable in programming?', 'multiple_choice', 2, 1),
(1, 'Programming languages are used to communicate with computers.', 'true_false', 1, 2),
(1, 'Explain the difference between a compiler and an interpreter.', 'short_answer', 5, 3);

-- Sample options for multiple choice question
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `order_index`) 
VALUES 
(1, 'A container that stores data values', 1, 1),
(1, 'A type of loop', 0, 2),
(1, 'A function that returns a value', 0, 3),
(1, 'A programming language', 0, 4);
*/
