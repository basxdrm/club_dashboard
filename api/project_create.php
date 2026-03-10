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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $budget = floatval($_POST['budget'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'planning';
    $created_by = $_SESSION['user_id'];
    // Remove manual academic_year_id assignment - let trigger handle it
    
    // Validation
    if (empty($name) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
        exit;
    }
    
    // Generate unique project code
    $year = date('Y', strtotime($start_date));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE YEAR(start_date) = ?");
    $stmt->execute([$year]);
    $count = $stmt->fetchColumn() + 1;
    $project_code = 'PROJ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    
    // Insert project (academic_year_id will be auto-assigned by trigger based on start_date)
    $stmt = $conn->prepare("
        INSERT INTO projects (
            project_code, name, description, start_date, end_date,
            budget, location, status, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $project_code, $name, $description, $start_date, $end_date,
        $budget, $location, $status, $created_by
    ]);
    
    $project_id = $conn->lastInsertId();
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$created_by, 'สร้างโปรเจค', 'สร้างโปรเจค: ' . $name, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    echo json_encode([
        'success' => true,
        'message' => 'สร้างโปรเจคเรียบร้อยแล้ว',
        'data' => [
            'project_id' => $project_id,
            'name' => $name
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error creating project: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
