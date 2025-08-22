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




$result_id = $_GET['id'] ?? null;
if (!$result_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();
    db_query("DELETE FROM results WHERE result_id = ?", [$result_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Result deleted successfully';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting result: ' . $e->getMessage();
}

header('Location: index.php');
exit();
