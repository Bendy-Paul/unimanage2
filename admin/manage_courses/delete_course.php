<?php
// require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
if(!$user || $user['role'] !== 'admin') {
    file_exists('../index.php') ?  header('Location: ../index.php') : header('Location: ../../index.php');
    // header('Location: ../index.php');
    exit();
}




$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();
    db_query("DELETE FROM courses WHERE course_id = ?", [$course_id]);
    // Optionally: cleanup related timetable_slots or other references if desired
    $pdo->commit();
    $_SESSION['success'] = 'Course deleted successfully';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting course: ' . $e->getMessage();
}

header('Location: index.php');
exit();
