<?php
require_once __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

$term = "%$q%";

// Detect which primary key column exists to avoid SQL errors on different schemas
$has_user_id = (bool) db_query("SHOW COLUMNS FROM users LIKE 'user_id'")->fetch();
$has_id = (bool) db_query("SHOW COLUMNS FROM users LIKE 'id'")->fetch();

if ($has_user_id) {
    $stmt = db_query("SELECT user_id, name FROM users WHERE role = 'student' AND (name LIKE ? OR user_id LIKE ?) LIMIT 20", [$term, $term]);
    $rows = $stmt->fetchAll();
    $out = array_map(function($r){ return ['user_id' => $r['user_id'], 'name' => $r['name'] ?? '']; }, $rows);
} elseif ($has_id) {
    $stmt = db_query("SELECT id AS user_id, name FROM users WHERE role = 'student' AND (name LIKE ? OR id LIKE ?) LIMIT 20", [$term, $term]);
    $rows = $stmt->fetchAll();
    $out = array_map(function($r){ return ['user_id' => $r['user_id'], 'name' => $r['name'] ?? '']; }, $rows);
} else {
    // Fallback: only name available
    $stmt = db_query("SELECT name FROM users WHERE role = 'student' AND name LIKE ? LIMIT 20", [$term]);
    $rows = $stmt->fetchAll();
    $out = array_map(function($r){ return ['user_id' => '', 'name' => $r['name'] ?? '']; }, $rows);
}

echo json_encode($out);
