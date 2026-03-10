<?php
/**
 * Security Configuration
 * การตั้งค่าความปลอดภัย
 */

// ป้องกันการเข้าถึงไฟล์โดยตรง
defined('APP_ACCESS') or die('Direct access not permitted');

// หมายเหตุ: การตั้งค่า Session ini_set() ต้องทำก่อน session_start() ในแต่ละไฟล์

// Password Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT);

// Login Security
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 นาที (วินาที)
// Session Security
define('SESSION_TIMEOUT', 3600); // 1 ชั่วโมง (วินาที)

// CSRF Token
define('CSRF_TOKEN_LENGTH', 32);

/**
 * สร้าง CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * ตรวจสอบ CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ทำความสะอาด Input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * ตรวจสอบความแข็งแรงของ Password
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "รหัสผ่านต้องมีอย่างน้อย " . PASSWORD_MIN_LENGTH . " ตัวอักษร";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "รหัสผ่านต้องมีตัวอักษรพิมพ์ใหญ่อย่างน้อย 1 ตัว";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "รหัสผ่านต้องมีตัวอักษรพิมพ์เล็กอย่างน้อย 1 ตัว";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว";
    }
    
    return $errors;
}

/**
 * เข้ารหัส Password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_HASH_ALGO);
}

/**
 * ตรวจสอบ Password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * ตรวจสอบว่าต้อง Rehash หรือไม่
 */
function needsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_HASH_ALGO);
}
