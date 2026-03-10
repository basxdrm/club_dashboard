<?php
/**
 * Update Profile API
 */

define('APP_ACCESS', true);
session_start();

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    try {
        $file = $_FILES['profile_image'];
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        
        // Validate file type
        if (!in_array($file['type'], $allowed)) {
            echo json_encode(['success' => false, 'message' => 'ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ JPG, PNG, GIF)']);
            exit();
        }
        
        // Validate file size (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'ขนาดไฟล์ต้องไม่เกิน 5MB']);
            exit();
        }
        
        // Create upload directory if not exists
        $upload_dir = '../assets/images/users/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Get student_id for filename
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT student_id FROM profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $student_id = $stmt->fetchColumn();
        
        // Generate filename with student_id and date (profile_51234_10012026_143052.jpg)
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $date_str = date('dmY'); // วันเดือนปี เช่น 10012026
        $time_str = date('His'); // ชั่วโมงนาทีวินาที เช่น 143052
        $filename = 'profile_' . $student_id . '_' . $date_str . '_' . $time_str . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            
            // Get old profile picture from profiles table
            $stmt = $conn->prepare("SELECT profile_picture FROM profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $old_picture = $stmt->fetchColumn();
            
            // Delete old file if exists and not default avatar
            if ($old_picture && !str_contains($old_picture, 'avatar-')) {
                $old_file = $upload_dir . $old_picture;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            // Update database - เก็บแค่ชื่อไฟล์เท่านั้น
            $stmt = $conn->prepare("UPDATE profiles SET profile_picture = ? WHERE user_id = ?");
            $stmt->execute([$filename, $user_id]);
            
            // Update session ให้รูปแสดงทันทีโดยไม่ต้อง logout
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['profile_picture'] = $filename;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'อัปโหลดรูปโปรไฟล์สำเร็จ',
                'profile_picture' => $filename
            ]);
            exit();
        } else {
            throw new Exception('ไม่สามารถย้ายไฟล์ได้');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        exit();
    }
}

// Handle profile data update
$prefix = trim($_POST['prefix'] ?? '');
$first_name_th = trim($_POST['first_name_th'] ?? '');
$last_name_th = trim($_POST['last_name_th'] ?? '');
$nickname_th = trim($_POST['nickname_th'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$line_id = trim($_POST['line_id'] ?? '');
$bio = trim($_POST['bio'] ?? '');

// Validation
if (empty($first_name_th) || empty($last_name_th) || empty($birth_date)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $conn->beginTransaction();
    
    // Update profiles
    $stmt = $conn->prepare("
        UPDATE profiles 
        SET prefix = ?, first_name_th = ?, last_name_th = ?, nickname_th = ?, birth_date = ?, bio = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$prefix, $first_name_th, $last_name_th, $nickname_th, $birth_date, $bio, $user_id]);
    
    // Update or insert contact
    if ($phone_number || $line_id) {
        $stmt = $conn->prepare("SELECT user_id FROM member_contacts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        if ($stmt->fetch()) {
            $stmt = $conn->prepare("UPDATE member_contacts SET phone_number = ?, line_id = ? WHERE user_id = ?");
            $stmt->execute([$phone_number, $line_id, $user_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO member_contacts (user_id, phone_number, line_id) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $phone_number, $line_id]);
        }
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกข้อมูลสำเร็จ'
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
