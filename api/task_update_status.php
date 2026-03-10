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
    
    $task_id = intval($_POST['task_id'] ?? 0);
    $action_type = trim($_POST['action_type'] ?? '');
    $new_status = trim($_POST['status'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit;
    }
    
    if (empty($action_type) || empty($new_status)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาระบุข้อมูลให้ครบถ้วน']);
        exit;
    }
    
    // Get current task status
    $sql = "SELECT status, title FROM tasks WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลงาน']);
        exit;
    }
    
    $old_status = $task['status'];
    
    // Update task status
    $update_sql = "UPDATE tasks SET status = :status WHERE id = :id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([
        ':status' => $new_status,
        ':id' => $task_id
    ]);
    
    // Log activity in task_activity_logs
    $log_sql = "INSERT INTO task_activity_logs (task_id, user_id, action_type, status_from, status_to, review_message, created_at)
                VALUES (:task_id, :user_id, :action_type, :status_from, :status_to, :review_message, NOW())";
    $log_stmt = $pdo->prepare($log_sql);
    $log_stmt->execute([
        ':task_id' => $task_id,
        ':user_id' => $_SESSION['user_id'],
        ':action_type' => $action_type,
        ':status_from' => $old_status,
        ':status_to' => $new_status,
        ':review_message' => $message
    ]);
    
    // Log in general activity_logs
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $activity_sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) 
                     VALUES (:user_id, :action, :description, :ip_address, :user_agent, NOW())";
    $activity_stmt = $pdo->prepare($activity_sql);
    $activity_stmt->execute([
        ':user_id' => $user_id,
        ':action' => 'update',
        ':description' => "อัพเดทสถานะงาน: {$task['title']} จาก '$old_status' เป็น '$new_status'",
        ':ip_address' => $ip_address,
        ':user_agent' => $user_agent
    ]);
    
    $actionMessages = [
        'started' => 'เริ่มดำเนินการแล้ว',
        'submitted' => 'ส่งตรวจสอบแล้ว',
        'approved' => 'อนุมัติแล้ว',
        'rejected' => 'ส่งกลับแก้ไขแล้ว',
        'cancelled' => 'ยกเลิกงานแล้ว'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => $actionMessages[$action_type] ?? 'อัพเดทสถานะเรียบร้อยแล้ว'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in task_update_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in task_update_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
