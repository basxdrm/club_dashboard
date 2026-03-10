<?php
/**
 * Logout Script
 * สคริปต์สำหรับออกจากระบบอย่างปลอดภัย
 */

define('APP_ACCESS', true);

// ตั้งค่า Session ก่อน session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

session_start();

require_once 'config/database.php';
require_once 'config/security.php';
require_once 'includes/auth.php';

// Logout
logout();
