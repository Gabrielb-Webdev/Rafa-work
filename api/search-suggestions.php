<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = executeQuery(
        "SELECT name FROM products WHERE is_active = 1 AND name LIKE ? ORDER BY name ASC LIMIT 6",
        ['%' . $q . '%']
    );
    $results = array_column($stmt->fetchAll(), 'name');
    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode([]);
}
