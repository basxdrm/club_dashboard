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
    
    $task_id = intval($_POST['task_id'] ?? 0);
    
    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit;
    }
    
    // Log received data
    error_log("Task Update - Received data: " . json_encode($_POST));
    
    $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $assignment_mode = $_POST['assignment_mode'] ?? 'direct';
    $max_assignees = intval($_POST['max_assignees'] ?? 1);
    
    // Get assignee data for direct assignment
    $assignee_type = $_POST['edit_assignee_type'] ?? 'individual';
    $assignees = $_POST['assignees'] ?? [];
    $departments = $_POST['departments'] ?? [];
    
    error_log("Task Update - Parsed data: task_id=$task_id, title='$title', project_id=$project_id, priority='$priority', assignees=" . json_encode($assignees));
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่องาน']);
        exit;
    }
    
    if (empty($start_date) || empty($due_date)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาระบุวันเริ่มต้นและวันสิ้นสุด']);
        exit;
    }
    
    if (strtotime($start_date) > strtotime($due_date)) {
        echo json_encode(['success' => false, 'message' => 'วันเริ่มต้นต้องไม่เกินวันสิ้นสุด']);
        exit;
    }
    
    // Check if project exists (only if project_id is provided)
    if ($project_id) {
        $stmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบโปรเจคที่เลือก']);
            exit;
        }
    }
    
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("UPDATE tasks SET
        project_id = ?,
        title = ?,
        description = ?,
        priority = ?,
        start_date = ?,
        due_date = ?,
        assignment_mode = ?,
        max_assignees = ?
        WHERE id = ?");

    $stmt->execute([
        $project_id,
        $title,
        $description,
        $priority,
        $start_date,
        $due_date,
        $assignment_mode,
        $max_assignees,
        $task_id
    ]);

    $affected_rows = $stmt->rowCount();
    error_log("Task Update - SQL executed, affected rows: $affected_rows");

    // Handle assignment based on mode
    if ($assignment_mode === 'direct') {
        // Direct assignment mode
        if (!empty($assignees)) {
            // Remove existing assignments for this task
            $stmt = $conn->prepare("DELETE FROM task_assignments WHERE task_id = ?");
            $stmt->execute([$task_id]);
            
            // Add new assignments
            $stmt = $conn->prepare("
                INSERT INTO task_assignments (task_id, user_id, assignment_type, assigned_by, status, assigned_at)
                VALUES (?, ?, 'direct_assign', ?, 'approved', NOW())
            ");
            
            foreach ($assignees as $user_id) {
                if (is_numeric($user_id)) {
                    $stmt->execute([$task_id, intval($user_id), $_SESSION['user_id']]);
                }
            }
            
            // Update current_assignees count
            $stmt = $conn->prepare("UPDATE tasks SET current_assignees = ? WHERE id = ?");
            $stmt->execute([count($assignees), $task_id]);
            
            error_log("Task Update - Updated assignments: " . count($assignees) . " assignees");
        }
        
        // Clear registration link if switching from registration to direct
        $stmt = $conn->prepare("UPDATE tasks SET registration_link = NULL WHERE id = ?");
        $stmt->execute([$task_id]);
        
    } elseif ($assignment_mode === 'registration' || $assignment_mode === 'hybrid') {
        // Registration mode - create registration link if not exists
        $stmt = $conn->prepare("SELECT registration_link FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $current_link = $stmt->fetchColumn();
        
        if (empty($current_link)) {
            // Generate new registration token
            $registration_token = bin2hex(random_bytes(16));
            $stmt = $conn->prepare("UPDATE tasks SET registration_link = ? WHERE id = ?");
            $stmt->execute([$registration_token, $task_id]);
            error_log("Task Update - Created registration link: $registration_token");
        }
        
        // If switching from direct to registration, remove direct assignments
        if ($assignment_mode === 'registration') {
            $stmt = $conn->prepare("DELETE FROM task_assignments WHERE task_id = ? AND assignment_type = 'direct_assign'");
            $stmt->execute([$task_id]);
            
            $stmt = $conn->prepare("UPDATE tasks SET current_assignees = 0 WHERE id = ?");
            $stmt->execute([$task_id]);
            
            error_log("Task Update - Removed direct assignments, switched to registration mode");
        }
    }

    $conn->commit();

    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], 'แก้ไขงาน', 'แก้ไขงาน: ' . $title . ' (ID: ' . $task_id . ')', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    error_log("Task Update - Success for task ID: $task_id");
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขงานเรียบร้อยแล้ว',
        'debug_info' => [
            'task_id' => $task_id,
            'title' => $title,
            'affected_rows' => $affected_rows,
            'assignees_count' => count($assignees)
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error updating task: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
