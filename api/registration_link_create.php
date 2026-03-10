<?php
/**
 * Create Registration Link API
 */

define('APP_ACCESS', true);
session_start();

require_once '../config/security.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$name = trim($_POST['name'] ?? '');
$expires_at = $_POST['expires_at'] ?? null;
$max_uses = $_POST['max_uses'] ?? null;
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อลิงก์']);
    exit();
}

// Generate unique token
$token = bin2hex(random_bytes(16));

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Insert
    $stmt = $conn->prepare("
        INSERT INTO registration_links (name, token, expires_at, max_uses, is_active) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$name, $token, $expires_at, $max_uses, $is_active]);
    
    echo json_encode([
        'success' => true,
        'message' => 'สร้างลิงก์สำเร็จ',
        'id' => $conn->lastInsertId(),
        'token' => $token
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
