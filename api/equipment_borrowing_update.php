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
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลที่ต้องการแก้ไข']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $id = $input['id'];
    $status = $input['status'] ?? null;
    $borrow_date = $input['borrow_date'] ?? null;
    $due_date = $input['due_date'] ?? null;
    $task_id = $input['task_id'] ?? null;
    $purpose = $input['purpose'] ?? null;

    // เริ่ม transaction
    $conn->beginTransaction();

    // อัปเดตข้อมูล
    $sql = "UPDATE equipment_borrowing SET 
            status = :status,
            borrow_date = :borrow_date,
            due_date = :due_date,
            task_id = :task_id,
            purpose = :purpose,
            updated_at = NOW()
            WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':borrow_date' => $borrow_date,
        ':due_date' => $due_date,
        ':task_id' => $task_id,
        ':purpose' => $purpose,
        ':id' => $id
    ]);

    // ถ้าเปลี่ยนสถานะเป็น returned ให้อัปเดตสถานะอุปกรณ์เป็น available
    if ($status === 'returned') {
        // ดึงข้อมูล equipment_id
        $stmt = $conn->prepare("SELECT equipment_id FROM equipment_borrowing WHERE id = ?");
        $stmt->execute([$id]);
        $borrow = $stmt->fetch();

        if ($borrow) {
            $stmt = $conn->prepare("UPDATE equipment SET status = 'available' WHERE id = ?");
            $stmt->execute([$borrow['equipment_id']]);
        }
    }
    // ถ้าเปลี่ยนสถานะเป็น borrowed ให้อัปเดตสถานะอุปกรณ์เป็น borrowed
    else if ($status === 'borrowed') {
        $stmt = $conn->prepare("SELECT equipment_id FROM equipment_borrowing WHERE id = ?");
        $stmt->execute([$id]);
        $borrow = $stmt->fetch();

        if ($borrow) {
            $stmt = $conn->prepare("UPDATE equipment SET status = 'borrowed' WHERE id = ?");
            $stmt->execute([$borrow['equipment_id']]);
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขข้อมูลเรียบร้อย'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error updating borrowing: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
