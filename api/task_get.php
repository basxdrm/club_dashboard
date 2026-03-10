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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $task_id = intval($_GET['id'] ?? 0);
    
    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลงาน']);
        exit;
    }
    
    // Get current task assignments
    $stmt = $conn->prepare("
        SELECT ta.user_id, 
               CONCAT(p.prefix, p.first_name_th, ' ', p.last_name_th) as user_name,
               d.name as department_name
        FROM task_assignments ta
        JOIN users u ON ta.user_id = u.id
        JOIN profiles p ON u.id = p.user_id
        LEFT JOIN member_club_info mci ON u.id = mci.user_id
        LEFT JOIN club_departments d ON mci.department_id = d.id
        WHERE ta.task_id = ? AND ta.status IN ('approved', 'working', 'completed')
        ORDER BY p.first_name_th
    ");
    $stmt->execute([$task_id]);
    $assignments = $stmt->fetchAll();
    
    // Get assigned user IDs for easy selection
    $assigned_user_ids = array_column($assignments, 'user_id');
    
    $task['assignments'] = $assignments;
    $task['assigned_user_ids'] = $assigned_user_ids;
    
    echo json_encode([
        'success' => true,
        'data' => $task
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching task: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
