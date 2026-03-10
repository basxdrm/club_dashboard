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
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if ($project_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $budget = floatval($_POST['budget'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'planning';
    
    if (empty($name) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE projects SET 
        name = ?, 
        description = ?, 
        start_date = ?, 
        end_date = ?, 
        budget = ?, 
        location = ?, 
        status = ? 
        WHERE id = ?");
    
    $stmt->execute([
        $name, 
        $description, 
        $start_date, 
        $end_date, 
        $budget, 
        $location, 
        $status,
        $project_id
    ]);
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], 'แก้ไขโปรเจค', 'แก้ไขโปรเจค: ' . $name, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขโปรเจคเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    error_log("Error updating project: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
