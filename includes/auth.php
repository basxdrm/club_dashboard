<?php
/**
 * Authentication Functions
 * ฟังก์ชันสำหรับระบบ Authentication
 */

// ป้องกันการเข้าถึงไฟล์โดยตรง
defined('APP_ACCESS') or die('Direct access not permitted');

require_once __DIR__ . '/../config/database.php';

/**
 * ตรวจสอบว่า User ล็อกอินอยู่หรือไม่
 */
function isLoggedIn() {
    // ตรวจสอบ session variables พื้นฐาน
    if (!isset($_SESSION['user_id']) || 
        !isset($_SESSION['logged_in']) || 
        $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // ตรวจสอบ user agent (ป้องกัน session hijacking)
    if (isset($_SESSION['user_agent_hash'])) {
        $current_hash = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? ''));
        if ($current_hash !== $_SESSION['user_agent_hash']) {
            session_destroy();
            return false;
        }
    }
    
    // ตรวจสอบ session timeout แยกต่างหาก
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > SESSION_TIMEOUT) {
            // Session timeout แต่ไม่ logout ทันที ให้ return false และให้ระบบจัดการ
            return false;
        }
        $_SESSION['last_activity'] = time();
    }
    
    return true;
}

/**
 * ตรวจสอบ Session Timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_TIMEOUT) {
            // Session timeout - ล้าง PHP session แต่เก็บ remember token
            clearPHPSession();
            return false;
        }
        
        // อัปเดต last_activity ในฐานข้อมูลทุก 5 นาที
        if (isset($_SESSION['user_id']) && 
            (!isset($_SESSION['db_last_update']) || (time() - $_SESSION['db_last_update'] > 300))) {
            try {
                $pdo = getDatabaseConnection();
                $stmt = $pdo->prepare("
                    UPDATE user_sessions
                    SET last_activity = CURRENT_TIMESTAMP
                    WHERE session_id = ?
                ");
                $stmt->execute([session_id()]);
                $_SESSION['db_last_update'] = time();
            } catch (Exception $e) {
                error_log("Update session activity error: " . $e->getMessage());
            }
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * ล้าง PHP Session เท่านั้น (ไม่ลบ remember token)
 */
function clearPHPSession() {
    // ลบ session จากฐานข้อมูล
    if (isset($_SESSION['user_id'])) {
        try {
            $pdo = getDatabaseConnection();
            removeSessionFromDatabase($_SESSION['user_id'], $pdo);
        } catch (Exception $e) {
            error_log("Error removing session: " . $e->getMessage());
        }
    }
    
    // ล้าง PHP session
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}/**
 * ตรวจสอบ Session Hijacking
 */
function validateSession() {
    $current_hash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    
    if (!isset($_SESSION['user_agent_hash'])) {
        $_SESSION['user_agent_hash'] = $current_hash;
    }
    
    return $_SESSION['user_agent_hash'] === $current_hash;
}

/**
 * Login User พร้อมตรวจสอบ Remember Me
 */
function loginUser($email, $password, $pdo, $remember_me = false) {
    // ตรวจสอบ Rate Limiting
    if (isLoginLocked($email, $pdo)) {
        return [
            'success' => false,
            'message' => 'บัญชีถูกล็อคชั่วคราว กรุณาลองใหม่ภายหลัง'
        ];
    }
    
    // ตรวจสอบว่ามี User อยู่หรือไม่ (ไม่จำกัด status)
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, u.password, u.role, u.status,
               CONCAT(p.first_name_th, ' ', p.last_name_th) as full_name,
               p.prefix, p.nickname_th
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // ตรวจสอบว่ามี User หรือไม่
    if (!$user) {
        recordLoginAttempt($email, false, $pdo, null);
        return [
            'success' => false,
            'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
            'use_swal' => true
        ];
    }
    
    // ตรวจสอบ status = 0 (บัญชีถูกระงับ)
    if ($user['status'] == 0) {
        recordLoginAttempt($email, false, $pdo, $user['id']);
        return [
            'success' => false,
            'message' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            'use_swal' => true
        ];
    }
    
    // ตรวจสอบ status (เข้าได้เฉพาะ 1 หรือ 2)
    if (!in_array($user['status'], [1, 2])) {
        recordLoginAttempt($email, false, $pdo, $user['id']);
        return [
            'success' => false,
            'message' => 'ไม่สามารถเข้าสู่ระบบได้ กรุณาติดต่อผู้ดูแลระบบ',
            'use_swal' => true
        ];
    }
    
    // ตรวจสอบ Password
    if (!verifyPassword($password, $user['password'])) {
        recordLoginAttempt($email, false, $pdo, $user['id']);
        return [
            'success' => false,
            'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
            'use_swal' => true
        ];
    }
    
    // Rehash password ถ้าจำเป็น
    if (needsRehash($user['password'])) {
        $new_hash = hashPassword($password);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_hash, $user['id']]);
    }
    
    // สร้าง Session
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    
    // บันทึก session ลงฐานข้อมูล
    saveSessionToDatabase($user['id'], $pdo);
    
    // ตั้งค่า Remember Me Cookie ถ้าเลือก
    if ($remember_me) {
        setRememberMeCookie($user['id'], $pdo);
    }
    
    // บันทึกการ Login สำเร็จ
    recordLoginAttempt($email, true, $pdo, $user['id']);
    updateLastLogin($user['id'], $pdo);
    
    // บันทึก Activity Log
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
            VALUES (?, 'login', 'เข้าสู่ระบบสำเร็จ', ?, ?)
        ");
        $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
    
    return [
        'success' => true,
        'message' => 'เข้าสู่ระบบสำเร็จ',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ];
}

/**
 * Logout User
 */
function logout() {
    // ลบ session จากฐานข้อมูลก่อน
    if (isset($_SESSION['user_id'])) {
        try {
            $pdo = getDatabaseConnection();
            removeSessionFromDatabase($_SESSION['user_id'], $pdo);
            
            // ลบ remember me token ด้วย
            clearRememberMeCookie($_SESSION['user_id'], $pdo);
        } catch (Exception $e) {
            error_log("Error removing session during logout: " . $e->getMessage());
        }
    }
    
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
    
    header('Location: login.php');
    exit();
}

/**
 * ตรวจสอบว่า Login ถูกล็อคหรือไม่
 */
function isLoginLocked($email, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempts, MAX(attempted_at) as last_attempt
        FROM login_attempts
        WHERE email = ?
        AND success = 0
        AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->execute([$email, LOGIN_LOCKOUT_TIME]);
    $result = $stmt->fetch();
    
    return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
}

/**
 * บันทึกการพยายาม Login
 */
function recordLoginAttempt($email, $success, $pdo, $user_id = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // ถ้าไม่มี user_id ให้ลองหาจาก email
    if (!$user_id && $success) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $user_id = $user['id'];
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (email, ip_address, user_agent, success)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $email,
        $ip_address,
        $user_agent,
        $success ? 1 : 0
    ]);
}

/**
 * อัพเดทเวลา Login ล่าสุด
 */
function updateLastLogin($user_id, $pdo) {
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);
}

/**
 * ตรวจสอบสิทธิ์การเข้าถึง
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // ตรวจสอบว่าอยู่ใน folder pages หรือไม่
        $script_path = $_SERVER['SCRIPT_NAME'];
        if (strpos($script_path, '/pages/') !== false) {
            header('Location: ../login.php');
        } else {
            header('Location: login.php');
        }
        exit();
    }
    
    // โหลดข้อมูล User ถ้ายังไม่มีใน Session
    if (!isset($_SESSION['user']) && isset($_SESSION['user_id'])) {
        loadUserData($_SESSION['user_id']);
    }
}

/**
 * โหลดข้อมูล User เข้า Session
 */
function loadUserData($user_id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT u.id, u.email, u.role, u.status,
               p.prefix, p.first_name_th, p.last_name_th, p.nickname_th,
               p.profile_picture, p.student_id
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ? AND u.status IN (1, 2)
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // แปลง profile_picture ให้เป็นชื่อไฟล์เท่านั้น (กรณีข้อมูลเก่ามี path)
        if (!empty($user['profile_picture']) && str_contains($user['profile_picture'], '/')) {
            $user['profile_picture'] = basename($user['profile_picture']);
        }
        $_SESSION['user'] = $user;
    }
}

/**
 * ตรวจสอบว่ามี Role ที่ต้องการหรือไม่
 */
function hasRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['role'] ?? '';
    
    $roles = ['member' => 1, 'board' => 2, 'advisor' => 2, 'admin' => 3];
    
    // ถ้าเป็น array ให้เช็คว่า user_role อยู่ในรายการหรือไม่
    if (is_array($required_role)) {
        return in_array($user_role, $required_role);
    }
    
    // ถ้าเป็น string ให้เช็คตาม level
    return ($roles[$user_role] ?? 0) >= ($roles[$required_role] ?? 999);
}

/**
 * แปลง Status เป็นข้อความ
 */
function getStatusText($status) {
    $statuses = [
        0 => 'ลาออก',
        1 => 'สมาชิก',
        2 => 'จบการศึกษา'
    ];
    return $statuses[$status] ?? 'ไม่ทราบสถานะ';
}

/**
 * แปลง Role เป็นข้อความ
 */
function getRoleText($role) {
    $roles = [
        'member'  => 'สมาชิกทั่วไป',
        'board'   => 'บอร์ดบริหาร',
        'advisor' => 'อาจารย์ที่ปรึกษา',
        'admin'   => 'ผู้ดูแลระบบ'
    ];
    return $roles[$role] ?? 'ไม่ทราบตำแหน่ง';
}

/**
 * ต้องการ Role
 */
function requireRole($required_role) {
    requireLogin();
    
    $user_role = $_SESSION['role'] ?? '';
    
    // ถ้าเป็น array ให้เช็คว่า user_role ต้องอยู่ในรายการที่กำหนด
    if (is_array($required_role)) {
        if (!in_array($user_role, $required_role)) {
            http_response_code(403);
            header('Location: ../pages/access_denied.php');
            exit();
        }
    } else {
        // ถ้าเป็น string ให้เช็คว่าต้องตรงกันพอดี
        if ($user_role !== $required_role) {
            http_response_code(403);
            header('Location: ../pages/access_denied.php');
            exit();
        }
    }
}

/**
 * บันทึก Session ลงฐานข้อมูล
 */
function saveSessionToDatabase($user_id, $pdo) {
    try {
        $session_id = session_id();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // ลบ session เก่าของ user นี้
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // เพิ่ม session ใหม่
        $stmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $session_id, $ip_address, $user_agent]);
        
        return true;
    } catch (Exception $e) {
        error_log("Save session error: " . $e->getMessage());
        return false;
    }
}

/**
 * ลบ Session จากฐานข้อมูล
 */
function removeSessionFromDatabase($user_id = null, $pdo = null) {
    try {
        if (!$pdo) {
            $pdo = getDatabaseConnection();
        }
        
        if ($user_id) {
            // ลบ session ของ user เฉพาะคน
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } else {
            // ลบ session ปัจจุบัน
            $session_id = session_id();
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_id = ?");
            $stmt->execute([$session_id]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Remove session error: " . $e->getMessage());
        return false;
    }
}

/**
 * ดึงรายการ Session ทั้งหมดของ User
 */
function getUserSessions($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT us.*,
                   IF(us.session_id = ?, 1, 0) as is_current
            FROM user_sessions us
            WHERE us.user_id = ?
            ORDER BY us.last_activity DESC
        ");
        $stmt->execute([session_id(), $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get user sessions error: " . $e->getMessage());
        return [];
    }
}

/**
 * สร้าง Remember Me Cookie
 */
function setRememberMeCookie($user_id, $pdo) {
    try {
        // สร้าง token แบบสุ่ม
        $token = bin2hex(random_bytes(32));
        $expires = new DateTime('+30 days');
        
        // ลบ token เก่าของ user
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // เพิ่ม token ใหม่
        $stmt = $pdo->prepare("
            INSERT INTO remember_tokens (user_id, token, expires_at) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $token, $expires->format('Y-m-d H:i:s')]);
        
        // ตั้งค่า cookie (30 วัน)
        setcookie('remember_token', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => false, // เปลี่ยนเป็น true ถ้าใช้ HTTPS
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Set remember me cookie error: " . $e->getMessage());
        return false;
    }
}

/**
 * ตรวจสอบ Remember Me Token
 */
function checkRememberMeToken($pdo) {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    try {
        $token = $_COOKIE['remember_token'];
        
        $stmt = $pdo->prepare("
            SELECT rt.user_id, u.email, u.role, p.first_name_th, p.last_name_th
            FROM remember_tokens rt
            JOIN users u ON rt.user_id = u.id
            LEFT JOIN profiles p ON u.id = p.user_id
            WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // ล็อกอินอัตโนมัติ
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = ($user['first_name_th'] ?? '') . ' ' . ($user['last_name_th'] ?? '');
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
            $_SESSION['auto_login'] = true; // ระบุว่าเป็น auto login
            
            // บันทึก session ใหม่
            saveSessionToDatabase($user['user_id'], $pdo);
            
            // อัปเดต last login
            updateLastLogin($user['user_id'], $pdo);
            
            return true;
        } else {
            // Token ไม่ถูกต้อง ลบ cookie
            clearRememberMeCookie();
            return false;
        }
    } catch (Exception $e) {
        error_log("Check remember me token error: " . $e->getMessage());
        clearRememberMeCookie();
        return false;
    }
}

/**
 * ลบ Remember Me Cookie และ Token
 */
function clearRememberMeCookie($user_id = null, $pdo = null) {
    // ลบ cookie
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true
    ]);
    
    // ลบ token จากฐานข้อมูล
    if ($user_id && $pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Clear remember me token error: " . $e->getMessage());
        }
    } elseif (isset($_COOKIE['remember_token']) && $pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->execute([$_COOKIE['remember_token']]);
        } catch (Exception $e) {
            error_log("Clear remember me token error: " . $e->getMessage());
        }
    }
}