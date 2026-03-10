<?php
/**
 * Delete Club Position API
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

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if position exists
    $stmt = $conn->prepare("SELECT name FROM club_positions WHERE id = ?");
    $stmt->execute([$id]);
    $position = $stmt->fetch();
    
    if (!$position) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        exit();
    }
    
    // Delete
    $stmt = $conn->prepare("DELETE FROM club_positions WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบตำแหน่งสำเร็จ'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
