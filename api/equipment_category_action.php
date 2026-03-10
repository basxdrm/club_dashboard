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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $db = new Database();
    $pdo = $db->getConnection();
    
    switch ($action) {
        case 'create':
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            
            if (empty($name)) {
                throw new Exception('กรุณากรอกชื่อหมวดหมู่');
            }
            
            $stmt = $pdo->prepare("INSERT INTO equipment_categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'สร้างหมวดหมู่อุปกรณ์',
                "สร้างหมวดหมู่: {$name}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'เพิ่มหมวดหมู่เรียบร้อยแล้ว']);
            break;
            
        case 'update':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            
            if (!$id || empty($name)) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            $stmt = $pdo->prepare("UPDATE equipment_categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'แก้ไขหมวดหมู่อุปกรณ์',
                "แก้ไขหมวดหมู่: {$name}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'แก้ไขหมวดหมู่เรียบร้อยแล้ว']);
            break;
            
        case 'delete':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            
            if (!$id) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE category_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('ไม่สามารถลบได้เนื่องจากมีอุปกรณ์ในหมวดหมู่นี้');
            }
            
            $stmt = $pdo->prepare("SELECT name FROM equipment_categories WHERE id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM equipment_categories WHERE id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                'ลบหมวดหมู่อุปกรณ์',
                "ลบหมวดหมู่: {$category['name']}",
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'ลบหมวดหมู่เรียบร้อยแล้ว']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Equipment category action error: " . $e->getMessage());
}
