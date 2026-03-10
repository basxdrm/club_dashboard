<?php
// Temporarily enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

try {
    // Keep only the last 1000 records (optional)
    // Or delete all: TRUNCATE TABLE activity_logs
    
    // Option 1: Delete all
    $sql = "TRUNCATE TABLE activity_logs";
    
    // Option 2: Keep last 1000 records (uncomment if preferred)
    // $sql = "DELETE FROM activity_logs WHERE id NOT IN (
    //     SELECT id FROM (
    //         SELECT id FROM activity_logs ORDER BY created_at DESC LIMIT 1000
    //     ) temp
    // )";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Log this action
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) 
            VALUES (?, 'admin_action', 'Cleared activity logs', ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_SESSION['user_id'],
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Activity logs cleared successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
