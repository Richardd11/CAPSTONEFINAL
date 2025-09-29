<?php
// Simple test to trigger the modal
session_start();

// Set the modal data to test
$_SESSION['show_exam_completed_modal'] = [
    'exam_title' => 'Test Exam',
    'exam_id' => 1,
    'completed_date' => '2025-09-24 10:30:00'
];

// Redirect to student dashboard
header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/student-success');
exit;
?>
