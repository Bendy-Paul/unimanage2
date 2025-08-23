<?php
require_once '../../includes/db_connect.php';
require_once '../../includes/auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['error'] = 'No announcement specified for deletion.';
    header('Location: view_announcements.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Only allow deleting announcements belonging to the user's department (extra safety)
    $stmt = db_query("DELETE FROM announcements WHERE annc_id = ? AND dept_id = ?", [$id, $_SESSION['user']['dept_id']]);

    $pdo->commit();
    $_SESSION['success'] = 'Announcement deleted successfully.';
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = 'Failed to delete announcement: ' . $e->getMessage();
}

header('Location: view_announcements.php');
exit();
