<?php
// require_once '../../includes/sidebar.php';
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

$slot_id = $_GET['id'] ?? null;
if (!$slot_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();
    db_query("DELETE FROM timetable_slots WHERE slot_id = ?", [$slot_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Slot deleted successfully';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting slot: ' . $e->getMessage();
}

header('Location: index.php');
exit();
