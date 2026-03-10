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

try {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบ ID รายการ']);
        exit;
    }

    $id = intval($_GET['id']);
    
    $pdo = getDatabaseConnection();
    
    $query = "SELECT * FROM transactions WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($transaction) {
        echo json_encode([
            'success' => true,
            'transaction' => $transaction
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบรายการ']);
    }
    
} catch (Exception $e) {
    error_log("Error in transaction_get.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
