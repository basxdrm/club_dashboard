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
    
    $task_id = intval($_POST['id'] ?? 0);
    
    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit;
    }
    
    // Check if task exists
    $stmt = $conn->prepare("SELECT id, title FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบงานนี้']);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Delete related records in proper order to avoid foreign key constraints
    // 1. Delete task activity logs first
    $stmt = $conn->prepare("DELETE FROM task_activity_logs WHERE task_id = ?");
    $stmt->execute([$task_id]);
    
    // 2. Delete task assignments
    $stmt = $conn->prepare("DELETE FROM task_assignments WHERE task_id = ?");
    $stmt->execute([$task_id]);
    
    // 3. Finally delete the task
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], 'ลบงาน', 'ลบงาน: ' . $task['title'] . ' (ID: ' . $task_id . ')', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบงานเรียบร้อยแล้ว'
    ]);
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Database error deleting task: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการลบงาน: ' . $e->getMessage(),
        'debug' => $e->getCode()
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("General error deleting task: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดทั่วไป: ' . $e->getMessage()
    ]);
}
