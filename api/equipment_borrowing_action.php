<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

if ($id <= 0 || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Permission check based on action
if ($action === 'request_return') {
    // Members can request return of their own borrowing
    $sql = "SELECT borrower_id FROM equipment_borrowing WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $borrowing = $stmt->fetch();
    
    if (!$borrowing || $borrowing['borrower_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์ในการดำเนินการ']);
        exit;
    }
} else {
    // Other actions require admin/board role
    if (!in_array($_SESSION['role'], ['admin', 'board', 'advisor'])) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์ในการดำเนินการ']);
        exit;
    }
}

try {
    $conn->beginTransaction();

    // Get borrowing details  
    $sql = "SELECT * FROM equipment_borrowing WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $borrowing = $stmt->fetch();

    if (!$borrowing) {
        throw new Exception('ไม่พบรายการยืม');
    }

    $message = '';

    switch ($action) {
        case 'request_return':
            if ($borrowing['status'] !== 'borrowed') {
                throw new Exception('สถานะไม่ถูกต้อง');
            }
            
            // Check if user is admin/board - auto approve return
            $user_role = $_SESSION['role'] ?? 'member';
            $is_admin_or_board = in_array($user_role, ['admin', 'board', 'advisor']);
            
            if ($is_admin_or_board) {
                // Auto approve return for admin/board
                $sql = "UPDATE equipment SET status = 'available' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$borrowing['equipment_id']]);
                
                $sql = "UPDATE equipment_borrowing SET status = 'returned', return_date = NOW() WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);
                
                $message = 'คืนอุปกรณ์สำเร็จ';
            } else {
                // Regular member - request return
                $sql = "UPDATE equipment_borrowing SET status = 'request_return' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);
                
                $message = 'ส่งคำขอคืนอุปกรณ์สำเร็จ รอการอนุมัติ';
            }
            break;

        case 'approve':
            if ($borrowing['status'] !== 'pending') {
                throw new Exception('สถานะไม่ถูกต้อง');
            }
            $sql = "UPDATE equipment_borrowing SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $id]);
            $message = 'อนุมัติคำขอยืมแล้ว';
            break;

        case 'borrow':
            if ($borrowing['status'] !== 'approved') {
                throw new Exception('สถานะไม่ถูกต้อง');
            }
            
            // Update equipment status to borrowed
            $sql = "UPDATE equipment SET status = 'borrowed' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$borrowing['equipment_id']]);
            
            // Update borrowing status
            $new_status = 'borrowed';
            $sql = "UPDATE equipment_borrowing SET status = ?, actual_borrow_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$new_status, $id]);
            
            $message = 'ส่งมอบอุปกรณ์แล้ว';
            break;

        case 'return':
            if ($borrowing['status'] !== 'borrowed' && $borrowing['status'] !== 'overdue') {
                throw new Exception('สถานะไม่ถูกต้อง');
            }
            
            // Update equipment status to available
            $sql = "UPDATE equipment SET status = 'available' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$borrowing['equipment_id']]);
            
            // Update borrowing status
            $new_status = 'returned';
            $sql = "UPDATE equipment_borrowing SET status = ?, return_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['returned', $id]);
            
            $message = 'รับคืนอุปกรณ์แล้ว';
            break;

        default:
            throw new Exception('Invalid action');
    }

    // Log activity
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_SESSION['user_id'],
        'equipment_borrowing_' . $action,
        "การยืมอุปกรณ์ {$borrowing['borrowing_code']}: $message",
        $_SERVER['REMOTE_ADDR']
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
