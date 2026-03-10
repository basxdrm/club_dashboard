<?php
/**
 * Update Club Position API
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
$level = intval($_POST['level'] ?? 0);
$description = trim($_POST['description'] ?? '');
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อตำแหน่ง']);
    exit();
}

if ($level <= 0) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุลำดับที่ถูกต้อง']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check duplicate name
    $stmt = $conn->prepare("SELECT id FROM club_positions WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'ชื่อตำแหน่งนี้มีอยู่แล้ว']);
        exit();
    }
    
    // Update
    $stmt = $conn->prepare("
        UPDATE club_positions 
        SET name = ?, level = ?, description = ?, is_active = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$name, $level, $description, $is_active, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทตำแหน่งสำเร็จ'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
