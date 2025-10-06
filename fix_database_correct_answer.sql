-- Fix for correct_answer column issue
-- This script safely adds the correct_answer column to the questions table

-- First, check if the column already exists to prevent errors
SELECT COUNT(*) AS column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'questions' 
AND COLUMN_NAME = 'correct_answer';

-- Add the correct_answer column if it doesn't exist
-- This column is needed for storing correct answers for true/false and other question types
ALTER TABLE `questions` 
ADD COLUMN IF NOT EXISTS `correct_answer` TEXT NULL 
COMMENT 'Stores correct answer for true/false, short_answer, and other non-multiple-choice questions'
AFTER `explanation`;

-- Add index for better performance on correct_answer queries
CREATE INDEX IF NOT EXISTS `idx_questions_correct_answer` ON `questions` (`correct_answer`(100));

-- Update any existing true/false questions to have proper correct_answer values
-- This sets a default value if none exists
UPDATE `questions` 
SET `correct_answer` = 'true' 
WHERE `question_type` = 'true_false' 
AND (`correct_answer` IS NULL OR `correct_answer` = '');

-- Verify the column was added successfully
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'questions' 
AND COLUMN_NAME = 'correct_answer';

-- Show success message
SELECT 'SUCCESS: correct_answer column has been added/verified in questions table' AS result;
