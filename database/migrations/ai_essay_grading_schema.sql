-- AI Essay Grading and Faculty Override Database Schema
-- This file contains the database schema for AI-powered essay grading system

-- Table for storing faculty score overrides
CREATE TABLE IF NOT EXISTS `faculty_score_overrides` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `attempt_id` int(11) NOT NULL,
    `question_id` int(11) NOT NULL,
    `original_score` decimal(5,2) DEFAULT 0.00,
    `new_score` decimal(5,2) NOT NULL,
    `reason` text NOT NULL,
    `overridden_by` int(11) NOT NULL,
    `overridden_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_override` (`attempt_id`, `question_id`),
    KEY `idx_attempt_id` (`attempt_id`),
    KEY `idx_question_id` (`question_id`),
    KEY `idx_overridden_by` (`overridden_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing AI grading results
CREATE TABLE IF NOT EXISTS `ai_grading_results` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `attempt_id` int(11) NOT NULL,
    `question_id` int(11) NOT NULL,
    `ai_score` decimal(5,2) NOT NULL DEFAULT 0.00,
    `max_points` decimal(5,2) NOT NULL DEFAULT 10.00,
    `confidence` int(3) NOT NULL DEFAULT 0,
    `criterion_scores` json DEFAULT NULL,
    `overall_feedback` text DEFAULT NULL,
    `strengths` json DEFAULT NULL,
    `improvements` json DEFAULT NULL,
    `requires_manual_review` tinyint(1) NOT NULL DEFAULT 0,
    `review_reason` varchar(255) DEFAULT NULL,
    `graded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_ai_grading` (`attempt_id`, `question_id`),
    KEY `idx_attempt_id` (`attempt_id`),
    KEY `idx_question_id` (`question_id`),
    KEY `idx_requires_review` (`requires_manual_review`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add AI configuration column to questions table (if not exists)
ALTER TABLE `questions` 
ADD COLUMN IF NOT EXISTS `ai_config` json DEFAULT NULL AFTER `explanation`;

-- Add score column to student_answers table (if not exists)
ALTER TABLE `student_answers` 
ADD COLUMN IF NOT EXISTS `score` decimal(5,2) DEFAULT 0.00 AFTER `is_correct`;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_questions_ai_config` ON `questions` (`question_type`);
CREATE INDEX IF NOT EXISTS `idx_student_answers_score` ON `student_answers` (`score`);

-- Sample data for testing (optional - remove in production)
-- INSERT INTO `faculty_score_overrides` (`attempt_id`, `question_id`, `original_score`, `new_score`, `reason`, `overridden_by`) 
-- VALUES (1, 1, 7.5, 9.0, 'Student showed deeper understanding than AI detected', 1);

-- Views for easier data retrieval
CREATE OR REPLACE VIEW `v_exam_results_with_overrides` AS
SELECT 
    ea.id as attempt_id,
    ea.exam_id,
    ea.student_id,
    ea.score as total_score,
    ea.status,
    ea.start_time,
    ea.end_time,
    e.title as exam_title,
    u.full_name as student_name,
    u.school_id,
    COUNT(sa.id) as total_questions,
    COUNT(fso.id) as overridden_questions,
    COUNT(agr.id) as ai_graded_questions
FROM exam_attempts ea
INNER JOIN exams e ON ea.exam_id = e.id
INNER JOIN users u ON ea.student_id = u.user_id
LEFT JOIN student_answers sa ON ea.id = sa.attempt_id
LEFT JOIN faculty_score_overrides fso ON ea.id = fso.attempt_id
LEFT JOIN ai_grading_results agr ON ea.id = agr.attempt_id
GROUP BY ea.id;

-- View for detailed question analysis
CREATE OR REPLACE VIEW `v_question_analysis` AS
SELECT 
    sa.attempt_id,
    sa.question_id,
    q.question_text,
    q.question_type,
    q.points as max_points,
    sa.answer_text as student_answer,
    sa.is_correct,
    sa.score as current_score,
    agr.ai_score,
    agr.confidence as ai_confidence,
    agr.overall_feedback as ai_feedback,
    agr.requires_manual_review,
    fso.original_score as override_original_score,
    fso.new_score as override_new_score,
    fso.reason as override_reason,
    fso.overridden_at,
    u.full_name as overridden_by_name,
    CASE 
        WHEN fso.id IS NOT NULL THEN 'overridden'
        WHEN agr.id IS NOT NULL THEN 'ai_graded'
        ELSE 'manual'
    END as grading_type
FROM student_answers sa
INNER JOIN questions q ON sa.question_id = q.id
LEFT JOIN ai_grading_results agr ON sa.attempt_id = agr.attempt_id AND sa.question_id = agr.question_id
LEFT JOIN faculty_score_overrides fso ON sa.attempt_id = fso.attempt_id AND sa.question_id = fso.question_id
LEFT JOIN users u ON fso.overridden_by = u.user_id;

-- Trigger to automatically update exam attempt score when question scores change
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `update_exam_score_after_override` 
AFTER INSERT ON `faculty_score_overrides`
FOR EACH ROW
BEGIN
    DECLARE total_score DECIMAL(10,2) DEFAULT 0;
    DECLARE max_possible DECIMAL(10,2) DEFAULT 0;
    DECLARE final_percentage DECIMAL(5,2) DEFAULT 0;
    
    -- Calculate new total score
    SELECT 
        COALESCE(SUM(sa.score), 0),
        COALESCE(SUM(q.points), 0)
    INTO total_score, max_possible
    FROM student_answers sa
    INNER JOIN questions q ON sa.question_id = q.id
    WHERE sa.attempt_id = NEW.attempt_id;
    
    -- Calculate percentage
    IF max_possible > 0 THEN
        SET final_percentage = (total_score / max_possible) * 100;
    END IF;
    
    -- Update exam attempt
    UPDATE exam_attempts 
    SET 
        score = final_percentage,
        total_points = total_score,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.attempt_id;
END//

CREATE TRIGGER IF NOT EXISTS `update_exam_score_after_override_update` 
AFTER UPDATE ON `faculty_score_overrides`
FOR EACH ROW
BEGIN
    DECLARE total_score DECIMAL(10,2) DEFAULT 0;
    DECLARE max_possible DECIMAL(10,2) DEFAULT 0;
    DECLARE final_percentage DECIMAL(5,2) DEFAULT 0;
    
    -- Calculate new total score
    SELECT 
        COALESCE(SUM(sa.score), 0),
        COALESCE(SUM(q.points), 0)
    INTO total_score, max_possible
    FROM student_answers sa
    INNER JOIN questions q ON sa.question_id = q.id
    WHERE sa.attempt_id = NEW.attempt_id;
    
    -- Calculate percentage
    IF max_possible > 0 THEN
        SET final_percentage = (total_score / max_possible) * 100;
    END IF;
    
    -- Update exam attempt
    UPDATE exam_attempts 
    SET 
        score = final_percentage,
        total_points = total_score,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.attempt_id;
END//
DELIMITER ;

-- Add comments to tables for documentation
ALTER TABLE `faculty_score_overrides` COMMENT = 'Stores faculty overrides of AI-generated essay scores';
ALTER TABLE `ai_grading_results` COMMENT = 'Stores AI grading results for essay questions';

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE ON faculty_score_overrides TO 'exam_app_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE ON ai_grading_results TO 'exam_app_user'@'localhost';
