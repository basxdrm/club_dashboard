<?php
define('APP_ACCESS', true);
session_start();

require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$year_id = intval($_POST['year_id'] ?? -1);

if ($year_id >= 0) {
    $_SESSION['selected_academic_year'] = $year_id;
    $message = $year_id == 0 ? 'แสดงข้อมูลทุกปีการศึกษา' : 'เปลี่ยนปีการศึกษาเรียบร้อย';
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
}
