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
    
    // Get POST data
    $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $assignment_mode = $_POST['assignment_mode'] ?? 'direct';
    $assignee_type = $_POST['assignee_type'] ?? 'individual';
    $max_assignees = !empty($_POST['max_assignees']) ? intval($_POST['max_assignees']) : 1;
    $created_by = $_SESSION['user_id'];
    
    // Get assignees (individuals or departments)
    $assignees = $_POST['assignees'] ?? [];
    $departments = $_POST['departments'] ?? [];
    
    // Validation
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่องาน']);
        exit;
    }
    
    if (empty($start_date) || empty($due_date)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาระบุวันเริ่มต้นและวันสิ้นสุด']);
        exit;
    }
    
    // Validate date range
    if (strtotime($start_date) > strtotime($due_date)) {
        echo json_encode(['success' => false, 'message' => 'วันเริ่มต้นต้องไม่เกินวันสิ้นสุด']);
        exit;
    }
    
    // Validate assignment mode specific fields
    if ($assignment_mode === 'direct') {
        if ($assignee_type === 'individual' && empty($assignees)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเลือกผู้รับผิดชอบ']);
            exit;
        }
        if ($assignee_type === 'department' && empty($departments)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเลือกฝ่ายที่รับผิดชอบ']);
            exit;
        }
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
    
    // Generate registration link if needed
    $registration_link = null;
    if ($assignment_mode === 'registration') {
        $registration_link = bin2hex(random_bytes(16));
    }
    
    $conn->beginTransaction();
    
    // Insert task
    $stmt = $conn->prepare("
        INSERT INTO tasks (
            project_id, title, description, priority, status,
            start_date, due_date, assignment_mode, max_assignees,
            registration_link, created_by, created_at
        ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $project_id, $title, $description, $priority,
        $start_date, $due_date, $assignment_mode, $max_assignees,
        $registration_link, $created_by
    ]);
    
    $task_id = $conn->lastInsertId();
    
    // If direct assignment mode, create task assignments
    if ($assignment_mode === 'direct') {
        $assignee_list = [];
        
        if ($assignee_type === 'individual') {
            // Direct individual assignment
            foreach ($assignees as $user_id) {
                $assignee_list[] = intval($user_id);
            }
        } elseif ($assignee_type === 'department') {
            // Get all members from selected departments
            $dept_ids = array_map('intval', $departments);
            $placeholders = implode(',', array_fill(0, count($dept_ids), '?'));
            
            $stmt = $conn->prepare("
                SELECT DISTINCT u.id
                FROM users u
                JOIN member_club_info mci ON u.id = mci.user_id
                WHERE mci.department_id IN ($placeholders) AND u.status = 1
            ");
            $stmt->execute($dept_ids);
            while ($row = $stmt->fetch()) {
                $assignee_list[] = $row['id'];
            }
        }
        
        // Insert task assignments
        $stmt = $conn->prepare("
            INSERT INTO task_assignments (task_id, user_id, assignment_type, assigned_by, status, assigned_at)
            VALUES (?, ?, 'direct_assign', ?, 'approved', NOW())
        ");
        
        foreach ($assignee_list as $user_id) {
            $stmt->execute([$task_id, $user_id, $created_by]);
        }
        
        // Update current_assignees count
        $stmt = $conn->prepare("UPDATE tasks SET current_assignees = ? WHERE id = ?");
        $stmt->execute([count($assignee_list), $task_id]);
    }
    
    $conn->commit();
    
    // Log in task_activity_logs
    $stmt = $conn->prepare("INSERT INTO task_activity_logs (task_id, user_id, action_type, review_message, created_at) VALUES (?, ?, 'created', 'สร้างงานใหม่', NOW())");
    $stmt->execute([$task_id, $created_by]);
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$created_by, 'สร้างงาน', 'สร้างงาน: ' . $title . ' (ID: ' . $task_id . ')', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    echo json_encode([
        'success' => true,
        'message' => 'สร้างงานเรียบร้อยแล้ว',
        'data' => [
            'task_id' => $task_id,
            'title' => $title,
            'registration_link' => $registration_link ? "task_register?code=$registration_link" : null
        ]
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error creating task: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
