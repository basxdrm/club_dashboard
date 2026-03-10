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

    $type = isset($_POST['type']) ? htmlspecialchars(trim($_POST['type']), ENT_QUOTES, 'UTF-8') : '';
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $transaction_date = isset($_POST['transaction_date']) ? htmlspecialchars(trim($_POST['transaction_date']), ENT_QUOTES, 'UTF-8') : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') : '';
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT) ?: null;
    $notes = isset($_POST['notes']) ? htmlspecialchars(trim($_POST['notes']), ENT_QUOTES, 'UTF-8') : '';

    // Validation
    if (!in_array($type, ['income', 'expense'])) {
        throw new Exception('กรุณาเลือกประเภทรายการ');
    }
    if (empty($category_id)) {
        throw new Exception('กรุณาเลือกหมวดหมู่');
    }
    if (!$amount || $amount <= 0) {
        throw new Exception('จำนวนเงินต้องมากกว่า 0');
    }
    if (empty($transaction_date)) {
        throw new Exception('กรุณาเลือกวันที่');
    }
    if (empty($description)) {
        throw new Exception('กรุณากรอกรายละเอียด');
    }

    // Handle file upload
    $receipt_path = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($_FILES['receipt']['type'], $allowed_types)) {
            throw new Exception('ไฟล์ต้องเป็นรูปภาพหรือ PDF เท่านั้น');
        }

        $file_extension = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $file_name = 'receipt_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_file)) {
            $receipt_path = $file_name;
        }
    }

    // Check user role - if admin/board, auto-approve, else pending
    $role = $_SESSION['role'] ?? 'member';
    $status = in_array($role, ['admin', 'board', 'advisor']) ? 'approved' : 'pending';
    $approved_by = ($status === 'approved') ? $_SESSION['user_id'] : null;

    // Generate unique transaction code
    $prefix = $type === 'income' ? 'IN' : 'EX';
    $date_code = date('Ymd');

    // Get next sequence number for today
    $stmt = $pdo->prepare("SELECT COUNT(*) + 1 as next_seq FROM transactions 
                           WHERE DATE(created_at) = CURDATE() AND type = ?");
    $stmt->execute([$type]);
    $seq = str_pad($stmt->fetchColumn(), 4, '0', STR_PAD_LEFT);

    $transaction_code = $prefix . $date_code . $seq;

    // Insert transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (
        type, category_id, amount, transaction_date,
        description, task_id, receipt_image, notes, status, recorded_by, approved_by, 
        transaction_code, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $type,
        $category_id,
        $amount,
        $transaction_date,
        $description,
        $task_id,
        $receipt_path,
        $notes,
        $status,
        $_SESSION['user_id'],
        $approved_by,
        $transaction_code
    ]);

    // Log activity
    $action_text = ($status === 'approved') ? 'เพิ่มรายการ' : 'ส่งรายการรอการอนุมัติ';
    $type_text = $type === 'income' ? 'รายรับ' : 'รายจ่าย';

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $action_text,
        "{$action_text}{$type_text}: (฿" . number_format($amount, 2) . ")",
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);

    $message = ($status === 'approved')
        ? 'บันทึกรายการเรียบร้อยแล้ว'
        : 'ส่งรายการเรียบร้อยแล้ว รอการอนุมัติจาก Admin/Board';

    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Transaction add error: " . $e->getMessage());
}
