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

// Check authentication and authorization
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'board', 'advisor'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'คุณไม่มีสิทธิ์ในการอนุมัติรายการ'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING) ?? '';
    
    if (!$transaction_id || !in_array($action, ['approve', 'reject'])) {
        throw new Exception('ข้อมูลไม่ถูกต้อง');
    }
    
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();
    
    // Check if transaction exists and is pending
    $stmt = $pdo->prepare("SELECT id, status, amount, type FROM transactions WHERE id = ?");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        throw new Exception('ไม่พบรายการที่ต้องการอนุมัติ');
    }
    
    if ($transaction['status'] !== 'pending') {
        throw new Exception('รายการนี้ได้รับการดำเนินการแล้ว');
    }
    
    // Update transaction status
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE transactions 
        SET status = ?, approved_by = ?, updated_at = NOW() 
        WHERE id = ?");
    $stmt->execute([$new_status, $_SESSION['user_id'], $transaction_id]);
    
    // Log the activity
    $activity_description = ($action === 'approve') 
        ? "อนุมัติรายการ จำนวน ฿" . number_format($transaction['amount'], 2)
        : "ปฏิเสธรายการ จำนวน ฿" . number_format($transaction['amount'], 2);
    
    if ($notes) {
        $activity_description .= " (หมายเหตุ: {$notes})";
    }
    
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $action === 'approve' ? 'อนุมัติรายการเงิน' : 'ปฏิเสธรายการเงิน',
        $activity_description,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $action === 'approve' ? 'อนุมัติรายการเรียบร้อยแล้ว' : 'ปฏิเสธรายการเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    error_log("Transaction approval error: " . $e->getMessage());
}
