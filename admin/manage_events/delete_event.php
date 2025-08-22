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


$event_id = $_GET['id'] ?? null;
if (!$event_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();
    // delete event
    db_query("DELETE FROM events WHERE event_id = ?", [$event_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Event deleted successfully';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting event: ' . $e->getMessage();
}

header('Location: index.php');
exit();
