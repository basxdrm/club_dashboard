<?php
/**
 * Update Registration Link API
 */

define('APP_ACCESS', true);
session_start();

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

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

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$expires_at = $_POST['expires_at'] ?? null;
$max_uses = $_POST['max_uses'] ?? null;
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อลิงก์']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Update
    $stmt = $conn->prepare("
        UPDATE registration_links 
        SET name = ?, expires_at = ?, max_uses = ?, is_active = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$name, $expires_at, $max_uses, $is_active, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทลิงก์สำเร็จ'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
