<?php
/**
 * Clear Login Attempts API
 * API สำหรับล้างข้อมูล Login Attempts (Admin Only)
 */

// Temporarily enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('APP_ACCESS', true);
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // ตรวจสอบ Login และสิทธิ์ Admin
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied'
        ]);
        exit();
    }

    $db = new Database();
    $pdo = $db->getConnection();
    
    // Clear login attempts table
    $stmt = $pdo->prepare("TRUNCATE TABLE login_attempts");
    $stmt->execute();
    
    // บันทึก Activity Log
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
            VALUES (?, 'admin_action', 'Cleared login attempts log', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'ล้างข้อมูล Login Attempts สำเร็จ'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
