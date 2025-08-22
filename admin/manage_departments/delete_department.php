<?php
// require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
if(!$user || $user['role'] !== 'admin') {
    file_exists('../index.php') ?  header('Location: ../index.php') : header('Location: ../../index.php');
    exit();
}

$dept_id = $_GET['id'] ?? null;
if (!$dept_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();
    // Optionally: check for dependent courses and prevent deletion or reassign
    db_query("DELETE FROM departments WHERE dept_id = ?", [$dept_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Department deleted successfully';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting department: ' . $e->getMessage();
}

header('Location: index.php');
exit();
