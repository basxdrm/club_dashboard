<?php
define('APP_ACCESS', true);
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/security.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT e.id, e.name,
        ec.name as category_name
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.status = 'available'
        ORDER BY e.name";

$stmt = $conn->prepare($sql);
$stmt->execute();
$equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($equipment);
