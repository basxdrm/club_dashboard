<?php
/**
 * Reorder Club Positions API
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

$order = json_decode($_POST['order'] ?? '[]', true);

if (!is_array($order) || empty($order)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("UPDATE club_positions SET level = ? WHERE id = ?");
    
    foreach ($order as $item) {
        $stmt->execute([$item['level'], $item['id']]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทลำดับสำเร็จ'
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
