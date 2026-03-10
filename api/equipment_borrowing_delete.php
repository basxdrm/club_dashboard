<?php
define('APP_ACCESS', true);
session_start();

require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// ตรวจสอบการล็อกอินและสิทธิ์
if (!isset($_SESSION['user_id']) || !hasRole(['admin', 'board'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit;
}

// รับข้อมูลจาก request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลที่ต้องการลบ']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $id = $input['id'];

    // เริ่ม transaction
    $conn->beginTransaction();

    // ดึงข้อมูลการยืมก่อนลบ เพื่อคืนสถานะอุปกรณ์
    $stmt = $conn->prepare("SELECT equipment_id, status FROM equipment_borrowing WHERE id = ?");
    $stmt->execute([$id]);
    $borrow = $stmt->fetch();

    if (!$borrow) {
        throw new Exception('ไม่พบรายการยืมที่ต้องการลบ');
    }

    // ถ้าสถานะเป็น borrowed ให้คืนสถานะอุปกรณ์เป็น available
    if ($borrow['status'] === 'borrowed') {
        $stmt = $conn->prepare("UPDATE equipment SET status = 'available' WHERE id = ?");
        $stmt->execute([$borrow['equipment_id']]);
    }

    // ลบรายการยืม
    $stmt = $conn->prepare("DELETE FROM equipment_borrowing WHERE id = ?");
    $stmt->execute([$id]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบรายการยืมเรียบร้อย'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error deleting borrowing: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
