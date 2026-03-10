<?php
header('Content-Type: application/json');

define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

// Check if user is admin or board
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'board', 'advisor'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id']);
$action = $input['action'];
$notes = isset($input['notes']) ? trim($input['notes']) : '';

try {
    $conn->beginTransaction();

    // Get borrowing details
    $sql = "SELECT eb.*, e.status as equipment_status
            FROM equipment_borrowing eb
            JOIN equipment e ON eb.equipment_id = e.id
            WHERE eb.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $borrowing = $stmt->fetch();

    if (!$borrowing) {
        throw new Exception('ไม่พบข้อมูลการยืม');
    }

    $admin_id = $_SESSION['user_id'];
    $message = '';

    switch ($action) {
        case 'approve':
            if ($borrowing['status'] === 'pending') {
                // Approve borrowing request
                if ($borrowing['equipment_status'] !== 'available') {
                    throw new Exception('อุปกรณ์ไม่พร้อมใช้งาน');
                }

                // Update borrowing status to borrowed directly and change equipment status
                $sql = "UPDATE equipment_borrowing 
                        SET status = 'borrowed', 
                            approved_by = ?,
                            approved_at = NOW()
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$admin_id, $id]);

                // Update equipment status to borrowed
                $sql = "UPDATE equipment SET status = 'borrowed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$borrowing['equipment_id']]);

                $message = 'อนุมัติคำขอยืมและส่งมอบอุปกรณ์แล้ว';
                
            } elseif ($borrowing['status'] === 'request_return') {
                // Approve return request
                $sql = "UPDATE equipment_borrowing 
                        SET status = 'returned', 
                            approved_by = ?,
                            approved_at = NOW(),
                            return_date = NOW(),
                            notes = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$admin_id, $notes ?: 'อนุมัติการคืน', $id]);

                // Update equipment status to available
                $sql = "UPDATE equipment SET status = 'available' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$borrowing['equipment_id']]);

                $message = 'อนุมัติการคืนอุปกรณ์แล้ว';
            } else {
                throw new Exception('สถานะไม่ถูกต้อง');
            }
            break;

        case 'reject':
            if ($borrowing['status'] === 'pending') {
                // Reject borrowing request
                $sql = "UPDATE equipment_borrowing 
                        SET status = 'cancelled',
                            approved_by = ?,
                            approved_at = NOW(),
                            notes = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$admin_id, 'ปฏิเสธ: ' . $notes, $id]);

                $message = 'ปฏิเสธคำขอยืมสำเร็จ';
                
            } elseif ($borrowing['status'] === 'request_return') {
                // Reject return request (keep as borrowed)
                $sql = "UPDATE equipment_borrowing 
                        SET status = 'borrowed',
                            approved_by = ?,
                            approved_at = NOW(),
                            notes = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$admin_id, 'ปฏิเสธการคืน: ' . $notes, $id]);

                $message = 'ปฏิเสธคำขอคืนแล้ว';
            } else {
                throw new Exception('สถานะไม่ถูกต้อง');
            }
            break;

        case 'return':
            if ($borrowing['status'] !== 'borrowed') {
                throw new Exception('สถานะไม่ถูกต้อง');
            }

            // Update equipment status to available
            $sql = "UPDATE equipment SET status = 'available' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$borrowing['equipment_id']]);

            // Update borrowing status
            $sql = "UPDATE equipment_borrowing 
                    SET status = 'returned',
                        return_date = NOW(),
                        notes = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$notes, $id]);

            $message = 'บันทึกการคืนสำเร็จ';
            break;

        default:
            throw new Exception('Action ไม่ถูกต้อง');
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
