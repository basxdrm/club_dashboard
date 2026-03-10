<?php
define('APP_ACCESS', true);
header('Content-Type: application/json; charset=utf-8');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $purchase_date = trim($_POST['purchase_date'] ?? '');
    $purchase_price = floatval($_POST['purchase_price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'available');
    
    // Handle image upload
    $image_filename = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'ไฟล์รูปภาพต้องเป็น JPG, PNG หรือ GIF เท่านั้น']);
            exit;
        }
        
        if ($_FILES['image']['size'] > $max_size) {
            echo json_encode(['success' => false, 'message' => 'ไฟล์รูปภาพต้องมีขนาดไม่เกิน 5MB']);
            exit;
        }
        
        $upload_dir = __DIR__ . '/../assets/images/equipment/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_filename = 'equipment_' . time() . '_' . uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $image_filename;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปโหลดรูปภาพได้']);
            exit;
        }
    }
    
    // Validate required fields
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่ออุปกรณ์']);
        exit;
    }
    
    if ($category_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเลือกหมวดหมู่']);
        exit;
    }
    
    // Insert equipment
    $user_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO equipment (
                name, category_id, brand, model, serial_number,
                purchase_date, purchase_price,
                location, description, status, image, created_by
            ) VALUES (
                :name, :category_id, :brand, :model, :serial_number,
                :purchase_date, :purchase_price,
                :location, :description, :status, :image, :created_by
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':category_id' => $category_id,
        ':brand' => $brand,
        ':model' => $model,
        ':serial_number' => $serial_number,
        ':purchase_date' => $purchase_date ?: null,
        ':purchase_price' => $purchase_price > 0 ? $purchase_price : null,
        ':location' => $location,
        ':description' => $description,
        ':status' => $status,
        ':image' => $image_filename,
        ':created_by' => $user_id
    ]);
    
    $equipment_id = $pdo->lastInsertId();
    
    // Log activity
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $log_sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :description, :ip_address, :user_agent, NOW())";
    $log_stmt = $pdo->prepare($log_sql);
    $log_stmt->execute([
        ':user_id' => $user_id,
        ':action' => 'create',
        ':description' => "เพิ่มอุปกรณ์: $name (ID: $equipment_id)",
        ':ip_address' => $ip_address,
        ':user_agent' => $user_agent
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มอุปกรณ์เรียบร้อยแล้ว',
        'equipment_id' => $equipment_id
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in equipment_create.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in equipment_create.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
