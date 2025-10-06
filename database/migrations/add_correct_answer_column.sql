-- Add correct_answer column to questions table
-- This column is needed for storing correct answers for true/false and other question types

-- Add the correct_answer column if it doesn't exist
ALTER TABLE `questions` 
ADD COLUMN IF NOT EXISTS `correct_answer` TEXT NULL AFTER `explanation`;

-- Add index for better performance on correct_answer queries
CREATE INDEX IF NOT EXISTS `idx_questions_correct_answer` ON `questions` (`correct_answer`(100));

-- Update any existing true/false questions to have proper correct_answer values
-- This is safe to run multiple times
UPDATE `questions` 
SET `correct_answer` = 'true' 
WHERE `question_type` = 'true_false' 
AND (`correct_answer` IS NULL OR `correct_answer` = '');

-- Add comment to document the column purpose
ALTER TABLE `questions` 
MODIFY COLUMN `correct_answer` TEXT NULL 
COMMENT 'Stores correct answer for true/false, short_answer, and other non-multiple-choice questions';
