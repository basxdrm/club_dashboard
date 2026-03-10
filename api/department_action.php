<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการดำเนินการ']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $action = $_SERVER['REQUEST_METHOD'] === 'POST' 
        ? filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING)
        : filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
    
    switch ($action) {
        case 'create':
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $icon = filter_input(INPUT_POST, 'icon', FILTER_SANITIZE_STRING);
            $color = filter_input(INPUT_POST, 'color', FILTER_SANITIZE_STRING);
            
            if (empty($name)) {
                throw new Exception('กรุณากรอกชื่อฝ่าย');
            }
            
            $stmt = $pdo->prepare("INSERT INTO club_departments (name, description, icon, color) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $icon, $color]);
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'สร้างฝ่าย',
                "สร้างฝ่าย: {$name}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'เพิ่มฝ่ายเรียบร้อยแล้ว']);
            break;
            
        case 'update':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $icon = filter_input(INPUT_POST, 'icon', FILTER_SANITIZE_STRING);
            $color = filter_input(INPUT_POST, 'color', FILTER_SANITIZE_STRING);
            
            if (!$id || empty($name)) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            $stmt = $pdo->prepare("UPDATE club_departments SET name = ?, description = ?, icon = ?, color = ? WHERE id = ?");
            $stmt->execute([$name, $description, $icon, $color, $id]);
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'แก้ไขฝ่าย',
                "แก้ไขฝ่าย: {$name}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'แก้ไขฝ่ายเรียบร้อยแล้ว']);
            break;
            
        case 'delete':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            
            if (!$id) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM member_club_info WHERE department_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('ไม่สามารถลบได้เนื่องจากมีสมาชิกในฝ่ายนี้');
            }
            
            $stmt = $pdo->prepare("SELECT name FROM club_departments WHERE id = ?");
            $stmt->execute([$id]);
            $dept = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM club_departments WHERE id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'ลบฝ่าย',
                "ลบฝ่าย: {$dept['name']}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'ลบฝ่ายเรียบร้อยแล้ว']);
            break;
            
        case 'get_available_members':
            $dept_id = filter_input(INPUT_GET, 'department_id', FILTER_VALIDATE_INT);
            
            $stmt = $pdo->prepare("SELECT u.id, u.email,
                CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as full_name,
                p.student_id
                FROM users u
                JOIN profiles p ON u.id = p.user_id
                LEFT JOIN member_club_info ci ON u.id = ci.user_id
                WHERE u.status = 1 AND (ci.department_id IS NULL OR ci.department_id = 0 OR ci.department_id != ?)
                ORDER BY p.student_id ASC");
            $stmt->execute([$dept_id]);
            $members = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'members' => $members]);
            break;
            
        case 'assign_member':
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $dept_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
            
            if (!$user_id || !$dept_id) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            // Get user info
            $stmt = $pdo->prepare("SELECT CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as name FROM profiles p WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Get new department name
            $stmt = $pdo->prepare("SELECT name FROM club_departments WHERE id = ?");
            $stmt->execute([$dept_id]);
            $new_dept = $stmt->fetch();
            
            // Check if user has club info and current department
            $stmt = $pdo->prepare("SELECT ci.department_id, d.name as old_dept_name 
                FROM member_club_info ci
                LEFT JOIN club_departments d ON ci.department_id = d.id
                WHERE ci.user_id = ?");
            $stmt->execute([$user_id]);
            $current_info = $stmt->fetch();
            
            $message = '';
            $log_detail = '';
            
            if ($current_info) {
                if ($current_info['department_id'] && $current_info['old_dept_name']) {
                    // Moving from another department
                    $message = "ย้าย {$user['name']} จาก {$current_info['old_dept_name']} มา {$new_dept['name']} เรียบร้อยแล้ว";
                    $log_detail = "ย้าย {$user['name']} จาก {$current_info['old_dept_name']} มา {$new_dept['name']}";
                } else {
                    // Has record but no department
                    $message = "เพิ่ม {$user['name']} เข้าฝ่าย {$new_dept['name']} เรียบร้อยแล้ว";
                    $log_detail = "เพิ่ม {$user['name']} เข้าฝ่าย {$new_dept['name']}";
                }
                
                $stmt = $pdo->prepare("UPDATE member_club_info SET department_id = ?, is_department_head = 0 WHERE user_id = ?");
                $stmt->execute([$dept_id, $user_id]);
            } else {
                // New member
                $message = "เพิ่ม {$user['name']} เข้าฝ่าย {$new_dept['name']} เรียบร้อยแล้ว";
                $log_detail = "เพิ่ม {$user['name']} เข้าฝ่าย {$new_dept['name']}";
                
                $stmt = $pdo->prepare("INSERT INTO member_club_info (user_id, department_id, member_generation, joined_date) VALUES (?, ?, 1, CURDATE())");
                $stmt->execute([$user_id, $dept_id]);
            }
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'จัดการฝ่าย',
                $log_detail,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => $message]);
            break;
            
        case 'remove_member':
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            
            if (!$user_id) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            $stmt = $pdo->prepare("UPDATE member_club_info SET department_id = NULL, is_department_head = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $stmt = $pdo->prepare("SELECT CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as name FROM profiles p WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'ลบสมาชิกออกจากฝ่าย',
                "ลบ {$user['name']} ออกจากฝ่าย",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'ลบสมาชิกออกจากฝ่ายเรียบร้อยแล้ว']);
            break;
            
        case 'toggle_department_head':
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $dept_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
            $is_head = filter_input(INPUT_POST, 'is_head', FILTER_VALIDATE_INT);
            
            if (!$user_id || !$dept_id) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            if ($is_head) {
                // Remove other department heads in this department
                $stmt = $pdo->prepare("UPDATE member_club_info SET is_department_head = 0 WHERE department_id = ?");
                $stmt->execute([$dept_id]);
            }
            
            $stmt = $pdo->prepare("UPDATE member_club_info SET is_department_head = ? WHERE user_id = ? AND department_id = ?");
            $stmt->execute([$is_head, $user_id, $dept_id]);
            
            $stmt = $pdo->prepare("SELECT CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as name FROM profiles p WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            $action_text = $is_head ? 'ตั้งเป็นหัวหน้าฝ่าย' : 'ยกเลิกหัวหน้าฝ่าย';
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $action_text,
                "{$action_text}: {$user['name']}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => $is_head ? 'ตั้งเป็นหัวหน้าฝ่ายเรียบร้อยแล้ว' : 'ยกเลิกหัวหน้าฝ่ายเรียบร้อยแล้ว']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Department action error: " . $e->getMessage());
}
