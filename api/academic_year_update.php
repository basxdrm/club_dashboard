<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $id = intval($_POST['id'] ?? 0);
    $year = trim($_POST['year'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (!$id || empty($year) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit;
    }
    
    // Check duplicate (exclude current id)
    $stmt = $pdo->prepare("SELECT id FROM academic_years WHERE year = ? AND id != ?");
    $stmt->execute([$year, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'ปีการศึกษานี้มีอยู่แล้ว']);
        exit;
    }
    
    // Update
    $stmt = $pdo->prepare("UPDATE academic_years SET year = ?, start_date = ?, end_date = ? WHERE id = ?");
    $stmt->execute([$year, $start_date, $end_date, $id]);
    
    echo json_encode(['success' => true, 'message' => 'แก้ไขปีการศึกษาเรียบร้อย']);
    
} catch (Exception $e) {
    error_log("Error in academic_year_update.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
