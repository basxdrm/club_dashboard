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

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $type = isset($_POST['type']) ? htmlspecialchars(trim($_POST['type']), ENT_QUOTES, 'UTF-8') : '';
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $transaction_date = isset($_POST['transaction_date']) ? htmlspecialchars(trim($_POST['transaction_date']), ENT_QUOTES, 'UTF-8') : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') : '';
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT) ?: null;
    $notes = isset($_POST['notes']) ? htmlspecialchars(trim($_POST['notes']), ENT_QUOTES, 'UTF-8') : '';

    // Validation
    if (!$id) {
        throw new Exception('ไม่พบรายการที่ต้องการแก้ไข');
    }
    if (!in_array($type, ['income', 'expense'])) {
        throw new Exception('กรุณาเลือกประเภทรายการ');
    }
    if (!$category_id) {
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

    // Check if user can edit this transaction
    $stmt = $pdo->prepare("SELECT recorded_by, status FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    $trans = $stmt->fetch();

    if (!$trans) {
        throw new Exception('ไม่พบรายการที่ต้องการแก้ไข');
    }

    $role = $_SESSION['role'] ?? 'member';
    // Only the creator or admin/board can edit
    if ($trans['recorded_by'] != $_SESSION['user_id'] && !in_array($role, ['admin', 'board', 'advisor'])) {
        throw new Exception('คุณไม่มีสิทธิ์แก้ไขรายการนี้');
    }

    // If status is approved and user is not admin/board, cannot edit
    if ($trans['status'] === 'approved' && !in_array($role, ['admin', 'board', 'advisor'])) {
        throw new Exception('ไม่สามารถแก้ไขรายการที่อนุมัติแล้ว');
    }

    // Handle file upload
    $receipt_path = null;
    $update_receipt = false;
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
            $update_receipt = true;
        }
    }

    // Update transaction
    if ($update_receipt) {
        $stmt = $pdo->prepare("UPDATE transactions SET 
            type = ?, category_id = ?, amount = ?, transaction_date = ?,
            description = ?, task_id = ?, receipt_image = ?, notes = ?, updated_at = NOW()
            WHERE id = ?");

        $stmt->execute([
            $type,
            $category_id,
            $amount,
            $transaction_date,
            $description,
            $task_id,
            $receipt_path,
            $notes,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE transactions SET 
            type = ?, category_id = ?, amount = ?, transaction_date = ?,
            description = ?, task_id = ?, notes = ?, updated_at = NOW()
            WHERE id = ?");

        $stmt->execute([
            $type,
            $category_id,
            $amount,
            $transaction_date,
            $description,
            $task_id,
            $notes,
            $id
        ]);
    }

    // Log activity
    $stmt = $pdo->prepare("SELECT transaction_code FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    $trans_code = $stmt->fetchColumn();

    $type_text = $type === 'income' ? 'รายรับ' : 'รายจ่าย';

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        'แก้ไขรายการ',
        "แก้ไขรายการ{$type_text}: {$trans_code} (฿" . number_format($amount, 2) . ")",
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);

    echo json_encode(['success' => true, 'message' => 'แก้ไขรายการเรียบร้อยแล้ว']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Transaction update error: " . $e->getMessage());
}
