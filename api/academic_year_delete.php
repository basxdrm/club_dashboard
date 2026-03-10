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
    
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบ ID']);
        exit;
    }
    
    // Check if it's current year
    $stmt = $pdo->prepare("SELECT is_current FROM academic_years WHERE id = ?");
    $stmt->execute([$id]);
    $year = $stmt->fetch();
    
    if ($year && $year['is_current']) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบปีการศึกษาปัจจุบันได้']);
        exit;
    }
    
    // Check if has projects
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE academic_year_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากมีโปรเจคใช้งานปีการศึกษานี้อยู่']);
        exit;
    }
    
    // Delete
    $stmt = $pdo->prepare("DELETE FROM academic_years WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'ลบปีการศึกษาเรียบร้อย']);
    
} catch (Exception $e) {
    error_log("Error in academic_year_delete.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
