<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = intval($_POST['equipment_id']);
    $purpose = trim($_POST['purpose']);
    $task_id = !empty($_POST['task_id']) ? intval($_POST['task_id']) : null;
    $borrow_date = $_POST['borrow_date'];
    $due_date = $_POST['due_date'];

    // Validation
    if ($equipment_id <= 0) {
        $errors[] = 'กรุณาเลือกอุปกรณ์';
    }
    if (empty($purpose)) {
        $errors[] = 'กรุณากรอกวัตถุประสงค์';
    }
    if (empty($borrow_date)) {
        $errors[] = 'กรุณาเลือกวันที่ยืม';
    }
    if (empty($due_date)) {
        $errors[] = 'กรุณาเลือกวันที่กำหนดคืน';
    }

    // Check equipment availability
    if (empty($errors)) {
        $sql = "SELECT id, status FROM equipment WHERE id = ? AND status = 'available'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$equipment_id]);
        $equipment = $stmt->fetch();

        if (!$equipment) {
            $errors[] = 'อุปกรณ์ไม่พร้อมใช้งาน';
        }
    }

    // Insert borrowing request
    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Generate unique borrowing code
            $borrowing_code = 'BOR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Check if code exists, regenerate if needed
            $check_stmt = $conn->prepare("SELECT id FROM equipment_borrowing WHERE borrowing_code = ?");
            $check_stmt->execute([$borrowing_code]);
            while ($check_stmt->fetch()) {
                $borrowing_code = 'BOR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $check_stmt->execute([$borrowing_code]);
            }

            // Check if user is admin or board - auto approve
            $user_role = $_SESSION['role'] ?? 'member';
            $is_admin_or_board = in_array($user_role, ['admin', 'board']);
            
            $status = $is_admin_or_board ? 'borrowed' : 'pending';
            $approved_by = $is_admin_or_board ? $_SESSION['user_id'] : null;
            $approved_at = $is_admin_or_board ? date('Y-m-d H:i:s') : null;

            $sql = "INSERT INTO equipment_borrowing 
                    (borrowing_code, equipment_id, borrower_id, purpose, task_id, borrow_date, due_date, status, approved_by, approved_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $borrowing_code,
                $equipment_id,
                $_SESSION['user_id'],
                $purpose,
                $task_id,
                $borrow_date,
                $due_date,
                $status,
                $approved_by,
                $approved_at
            ]);

            // If auto-approved, update equipment status to borrowed
            if ($is_admin_or_board) {
                $update_stmt = $conn->prepare("UPDATE equipment SET status = 'borrowed' WHERE id = ?");
                $update_stmt->execute([$equipment_id]);
            }

            $conn->commit();
            $success = true;
            
            $message = $is_admin_or_board ? 'ยืมอุปกรณ์สำเร็จ' : 'ส่งคำขอยืมอุปกรณ์สำเร็จ รอการอนุมัติ';
            $_SESSION['success_message'] = $message;
            header('Location: ../pages/equipment_borrowing.php');
            exit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: ../pages/equipment_borrowing.php');
    exit();
}
