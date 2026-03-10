<?php
define('APP_ACCESS', true);
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $user_id = intval($_POST['id'] ?? 0);
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบผู้ใช้งานนี้']);
        exit;
    }
    
    // Prevent self-deletion
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบบัญชีของตัวเองได้']);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Delete related records (cascade delete)
    $conn->prepare("DELETE FROM member_club_info WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM member_contacts WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM member_education WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM profiles WHERE user_id = ?")->execute([$user_id]);
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], 'ลบสมาชิก', 'ลบสมาชิก: ' . $user['email'] . ' (ID: ' . $user_id . ')', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบสมาชิกเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error deleting member: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
