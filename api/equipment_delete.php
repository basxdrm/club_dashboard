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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    $equipment_id = intval($_POST['id'] ?? 0);
    
    if ($equipment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid equipment ID']);
        exit;
    }
    
    // Get equipment name for logging
    $sql = "SELECT name FROM equipment WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $equipment_id]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$equipment) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลอุปกรณ์']);
        exit;
    }
    
    // Check if equipment is currently borrowed
    $check_sql = "SELECT COUNT(*) FROM equipment_borrowing 
                  WHERE equipment_id = :id AND status IN ('pending', 'approved')";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':id' => $equipment_id]);
    $borrowed_count = $check_stmt->fetchColumn();
    
    if ($borrowed_count > 0) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ มีการยืมอุปกรณ์นี้อยู่']);
        exit;
    }
    
    // Delete equipment
    $delete_sql = "DELETE FROM equipment WHERE id = :id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([':id' => $equipment_id]);
    
    // Log activity
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $log_sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) 
                VALUES (:user_id, :action, :description, :ip_address, :user_agent, NOW())";
    $log_stmt = $pdo->prepare($log_sql);
    $log_stmt->execute([
        ':user_id' => $user_id,
        ':action' => 'delete',
        ':description' => "ลบอุปกรณ์: {$equipment['name']} (ID: $equipment_id)",
        ':ip_address' => $ip_address,
        ':user_agent' => $user_agent
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบอุปกรณ์เรียบร้อยแล้ว'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in equipment_delete.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in equipment_delete.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
