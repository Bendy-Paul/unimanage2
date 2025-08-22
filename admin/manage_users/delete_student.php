<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
if(!$user || $user['role'] !== 'admin') {
    file_exists('../index.php') ?  header('Location: ../index.php') : header('Location: ../../index.php');
    // header('Location: ../index.php');
    exit();
}

require_once '../../includes/db_connect.php';
// require_once '../includes/auth.php';

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    $_SESSION['error'] = 'No student ID provided';
    header('Location: students.php');
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // First, get student name for notification
    $stmt = db_query("SELECT name FROM users WHERE user_id = ?", [$student_id]);
    $student = $stmt->fetch();
    $student_name = $student['name'] ?? 'Unknown';
    
    // Delete from students table
    db_query("DELETE FROM students WHERE user_id = ?", [$student_id]);
    
    // Delete from users table
    db_query("DELETE FROM users WHERE user_id = ?", [$student_id]);
    
    // Delete related results
    db_query("DELETE FROM results WHERE student_id = ?", [$student_id]);
    
    $pdo->commit();
    
    $_SESSION['success'] = "Student '$student_name' has been deleted successfully";
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting student: ' . $e->getMessage();
}

header('Location: view_students.php');
exit();