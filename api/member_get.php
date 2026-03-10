<?php
define('APP_ACCESS', true);
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $user_id = intval($_GET['id'] ?? 0);
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    $sql = "SELECT 
                u.id, u.email, u.role, u.status,
                p.student_id, p.prefix, p.first_name_th, p.last_name_th, p.nickname_th, 
                p.first_name_en, p.last_name_en, p.birth_date,
                e.academic_year_id, e.academic_grade, e.academic_room, e.academic_status,
                e.agama_grade, e.agama_room, e.agama_status,
                c.phone_number,
                ci.department_id, ci.position_id, ci.member_generation
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            LEFT JOIN member_education e ON u.id = e.user_id AND e.is_current = 1
            LEFT JOIN member_contacts c ON u.id = c.user_id
            LEFT JOIN member_club_info ci ON u.id = ci.user_id
            WHERE u.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $member = $stmt->fetch();
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลสมาชิก']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $member
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching member: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
