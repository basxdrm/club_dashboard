<?php
define('APP_ACCESS', true);
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $project_id = intval($_POST['id'] ?? 0);
    
    if ($project_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
        exit;
    }
    
    // Check if project exists
    $stmt = $conn->prepare("SELECT id, name FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบโปรเจคนี้']);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Delete related records
    $conn->prepare("DELETE FROM project_members WHERE project_id = ?")->execute([$project_id]);
    $conn->prepare("DELETE FROM task_assignments WHERE task_id IN (SELECT id FROM tasks WHERE project_id = ?)")->execute([$project_id]);
    $conn->prepare("DELETE FROM task_activity_logs WHERE task_id IN (SELECT id FROM tasks WHERE project_id = ?)")->execute([$project_id]);
    $conn->prepare("DELETE FROM tasks WHERE project_id = ?")->execute([$project_id]);
    $conn->prepare("DELETE FROM projects WHERE id = ?")->execute([$project_id]);
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], 'ลบโปรเจค', 'ลบโปรเจค: ' . $project['name'] . ' (ID: ' . $project_id . ')', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบโปรเจคเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error deleting project: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
