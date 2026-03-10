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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    // Get POST data
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'member';
    $status = intval($_POST['status'] ?? 1);
    $prefix = trim($_POST['prefix'] ?? '');
    $first_name_th = trim($_POST['first_name_th'] ?? '');
    $last_name_th = trim($_POST['last_name_th'] ?? '');
    $nickname_th = trim($_POST['nickname_th'] ?? '');
    $first_name_en = trim($_POST['first_name_en'] ?? '');
    $last_name_en = trim($_POST['last_name_en'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $birth_date = $_POST['birth_date'] ?? null;
    $phone_number = trim($_POST['phone_number'] ?? '');
    
    // Education data - เปลี่ยนจาก education_level เป็น academic_year_id และเพิ่มฟิลด์ใหม่
    $academic_year_id = !empty($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;
    $academic_status = $_POST['academic_status'] ?? 'studying';
    $academic_grade = !empty($_POST['academic_grade']) ? intval($_POST['academic_grade']) : null;
    $academic_room = trim($_POST['academic_room'] ?? '');
    $agama_status = $_POST['agama_status'] ?? 'studying';
    $agama_grade = !empty($_POST['agama_grade']) ? intval($_POST['agama_grade']) : null;
    $agama_room = trim($_POST['agama_room'] ?? '');
    
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $position_id = !empty($_POST['position_id']) ? intval($_POST['position_id']) : null;
    $member_generation = !empty($_POST['member_generation']) ? intval($_POST['member_generation']) : null;
    
    // Validation
    $isAdvisor = ($role === 'advisor');
    if (empty($email) || empty($first_name_th) || empty($last_name_th)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
        exit;
    }
    if (!$isAdvisor && empty($student_id)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสนักเรียน']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'รูปแบบอีเมลไม่ถูกต้อง']);
        exit;
    }
    
    // Check duplicate email (exclude current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'อีเมลนี้มีในระบบแล้ว']);
        exit;
    }
    
    // Check duplicate student_id (skip for advisor)
    if (!$isAdvisor && !empty($student_id)) {
        $stmt = $conn->prepare("SELECT user_id FROM profiles WHERE student_id = ? AND user_id != ?");
        $stmt->execute([$student_id, $user_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'รหัสนักเรียนนี้มีในระบบแล้ว']);
            exit;
        }
    }
    
    $conn->beginTransaction();
    
    // Update user
    if (!empty($password)) {
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร']);
            exit;
        }
        $hashed_password = hashPassword($password);
        $stmt = $conn->prepare("UPDATE users SET email = ?, password = ?, role = ?, status = ? WHERE id = ?");
        $stmt->execute([$email, $hashed_password, $role, $status, $user_id]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET email = ?, role = ?, status = ? WHERE id = ?");
        $stmt->execute([$email, $role, $status, $user_id]);
    }
    
    // Update profile (student_id = null for advisor)
    $stmt = $conn->prepare("UPDATE profiles SET student_id = ?, prefix = ?, first_name_th = ?, last_name_th = ?, nickname_th = ?, first_name_en = ?, last_name_en = ?, birth_date = ? WHERE user_id = ?");
    $stmt->execute([$isAdvisor ? null : $student_id, $prefix, $first_name_th, $last_name_th, $nickname_th, $first_name_en, $last_name_en, $birth_date, $user_id]);

    // Update education (skip for advisor)
    if (!$isAdvisor && ($academic_year_id || $academic_grade || $agama_grade)) {
        $stmt = $conn->prepare("SELECT id FROM member_education WHERE user_id = ? AND is_current = 1");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            $stmt = $conn->prepare("UPDATE member_education SET academic_year_id = ?, academic_grade = ?, academic_room = ?, academic_status = ?, agama_grade = ?, agama_room = ?, agama_status = ? WHERE user_id = ? AND is_current = 1");
            $stmt->execute([$academic_year_id, $academic_grade, $academic_room, $academic_status, $agama_grade, $agama_room, $agama_status, $user_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO member_education (user_id, academic_year_id, academic_grade, academic_room, academic_status, agama_grade, agama_room, agama_status, is_current) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$user_id, $academic_year_id, $academic_grade, $academic_room, $academic_status, $agama_grade, $agama_room, $agama_status]);
        }
    }
    
    // Update contact
    if ($phone_number) {
        $stmt = $conn->prepare("SELECT user_id FROM member_contacts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            $stmt = $conn->prepare("UPDATE member_contacts SET phone_number = ? WHERE user_id = ?");
            $stmt->execute([$phone_number, $user_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO member_contacts (user_id, phone_number) VALUES (?, ?)");
            $stmt->execute([$user_id, $phone_number]);
        }
    }
    
    // Update club info
    $stmt = $conn->prepare("SELECT user_id FROM member_club_info WHERE user_id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) {
        $stmt = $conn->prepare("UPDATE member_club_info SET department_id = ?, position_id = ?, member_generation = ? WHERE user_id = ?");
        $stmt->execute([$department_id, $position_id, $member_generation, $user_id]);
    } else if ($department_id || $position_id || $member_generation) {
        $stmt = $conn->prepare("INSERT INTO member_club_info (user_id, department_id, position_id, member_generation, joined_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $department_id, $position_id, $member_generation]);
    }
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], 'แก้ไขข้อมูลสมาชิก', 'แก้ไขข้อมูลสมาชิก: ' . $first_name_th . ' ' . $last_name_th . ' (ID: ' . $user_id . ')', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขข้อมูลสมาชิกเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error updating member: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
