<?php
define('APP_ACCESS', true);
header('Content-Type: application/json; charset=utf-8');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    $equipment_id = intval($_GET['id'] ?? 0);
    
    if ($equipment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid equipment ID']);
        exit;
    }
    
    $sql = "SELECT * FROM equipment WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $equipment_id]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$equipment) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลอุปกรณ์']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'equipment' => $equipment
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in equipment_get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'
    ]);
} catch (Exception $e) {
    error_log("Error in equipment_get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด'
    ]);
}
