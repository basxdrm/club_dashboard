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
    
    $year = trim($_POST['year'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $is_current = isset($_POST['is_current']) ? 1 : 0;
    
    if (empty($year) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit;
    }
    
    // Check duplicate
    $stmt = $pdo->prepare("SELECT id FROM academic_years WHERE year = ?");
    $stmt->execute([$year]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'ปีการศึกษานี้มีอยู่แล้ว']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    // If setting as current, unset other current years
    if ($is_current) {
        $pdo->exec("UPDATE academic_years SET is_current = 0");
    }
    
    // Insert new year
    $stmt = $pdo->prepare("INSERT INTO academic_years (year, start_date, end_date, is_current) VALUES (?, ?, ?, ?)");
    $stmt->execute([$year, $start_date, $end_date, $is_current]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'เพิ่มปีการศึกษาเรียบร้อย']);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in academic_year_create.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
