<?php
/**
 * Reset Password Page
 * หน้ารีเซ็ตรหัสผ่าน
 */

define('APP_ACCESS', true);

// ตั้งค่า Session ก่อน session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); 
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

session_start();

// Load Configuration
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'includes/auth.php';

// ถ้า Login แล้ว ให้ไปหน้า Dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';
$valid_token = false;
$user_data = null;

// ตรวจสอบ token
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $pdo = getDatabaseConnection();
        
        // ตรวจสอบ token
        $stmt = $pdo->prepare("
            SELECT id, email 
            FROM users 
            WHERE reset_token = ? 
            AND reset_token_expires > NOW() 
            AND status = 1
        ");
        $stmt->execute([$token]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $valid_token = true;
        } else {
            $error = 'ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุแล้ว';
        }
    } catch (Exception $e) {
        error_log("Reset password token check error: " . $e->getMessage());
        $error = 'เกิดข้อผิดพลาดในการตรวจสอบลิงก์';
    }
} else {
    $error = 'ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง';
}

// ประมวลผลฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    // ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $token = $_POST['token'] ?? '';
        
        // ตรวจสอบรหัสผ่าน
        if (empty($password)) {
            $error = 'กรุณากรอกรหัสผ่านใหม่';
        } elseif (strlen($password) < 8) {
            $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
        } elseif ($password !== $confirm_password) {
            $error = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
        } else {
            try {
                $pdo = getDatabaseConnection();
                
                // ตรวจสอบ token อีกครั้ง
                $stmt = $pdo->prepare("
                    SELECT id 
                    FROM users 
                    WHERE reset_token = ? 
                    AND reset_token_expires > NOW() 
                    AND status = 1
                ");
                $stmt->execute([$token]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Hash รหัสผ่านใหม่
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // อัปเดตรหัสผ่านและลบ reset token
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET password = ?, 
                            reset_token = NULL, 
                            reset_token_expires = NULL,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$hashed_password, $user['id']]);
                    
                    // บันทึก Activity Log
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
                            VALUES (?, 'password_reset', 'รีเซ็ตรหัสผ่านสำเร็จ', ?, ?)
                        ");
                        $stmt->execute([
                            $user['id'],
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        ]);
                    } catch (Exception $e) {
                        error_log("Activity log error: " . $e->getMessage());
                    }
                    
                    // ลบ remember tokens ทั้งหมดของ user นี้ (บังคับ logout ทุกอุปกรณ์)
                    try {
                        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        
                        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                    } catch (Exception $e) {
                        error_log("Clear sessions error: " . $e->getMessage());
                    }
                    
                    $success = 'รีเซ็ตรหัสผ่านสำเร็จ! คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้แล้ว';
                    $valid_token = false; // ซ่อนฟอร์ม
                } else {
                    $error = 'ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุแล้ว';
                    $valid_token = false;
                }
            } catch (Exception $e) {
                error_log("Reset password error: " . $e->getMessage());
                $error = 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน';
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// อ่านค่า background
$login_bg = '';
if (file_exists('config/login_bg.txt')) {
    $login_bg = trim(file_get_contents('config/login_bg.txt'));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8" />
    <title>รีเซ็ตรหัสผ่าน | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    
    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    
    <?php if ($login_bg): ?>
    <style>
        body.authentication-bg {
            background-image: url('<?php echo htmlspecialchars($login_bg); ?>') !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            position: relative;
            min-height: 100vh;
        }
        body.authentication-bg::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.35) !important;
            z-index: 0;
        }
        .account-pages {
            position: relative;
            z-index: 1;
        }
        .account-pages .card {
            box-shadow: 0 0 35px 0 rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(2px);
        }
        .account-pages .card-header {
            background: rgba(var(--ct-primary-rgb), 0.95) !important;
        }
    </style>
    <?php endif; ?>

    <style>
        .password-strength {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>

<body class="loading authentication-bg" data-layout-config='{"darkMode":false}'>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header pt-4 pb-4 text-center bg-primary">
                            <a href="login.php">
                                <span><img src="assets/images/logo.png" alt="" height="18"></span>
                            </a>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">รีเซ็ตรหัสผ่าน</h4>
                                <?php if ($valid_token && $user_data): ?>
                                    <p class="text-muted mb-4">สร้างรหัสผ่านใหม่สำหรับ <strong><?php echo htmlspecialchars($user_data['email']); ?></strong></p>
                                <?php else: ?>
                                    <p class="text-muted mb-4">ตั้งค่าข้อมูลรหัสผ่านใหม่</p>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-alert-circle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="mdi mdi-login me-1"></i>เข้าสู่ระบบ
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if ($valid_token && empty($success)): ?>
                            <form method="POST" action="reset-password.php" id="reset-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

                                <div class="mb-3">
                                    <label for="password" class="form-label">รหัสผ่านใหม่</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" name="password" class="form-control" 
                                               required placeholder="กรอกรหัสผ่านใหม่" minlength="8"
                                               autocomplete="new-password">
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                    <div id="password-strength" class="password-strength"></div>
                                    <small class="text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</small>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                               required placeholder="กรอกรหัสผ่านใหม่อีกครั้ง"
                                               autocomplete="new-password">
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                    <div id="password-match" class="password-strength"></div>
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit" id="submit-btn">
                                        <i class="mdi mdi-lock-reset me-1"></i> บันทึกรหัสผ่านใหม่
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>

                            <?php if (!$valid_token && empty($success)): ?>
                            <div class="text-center">
                                <p class="text-muted">ลิงก์ไม่ถูกต้องหรือหมดอายุแล้ว</p>
                                <a href="forgot-password.php" class="btn btn-outline-primary">
                                    <i class="mdi mdi-email me-1"></i>ขอลิงก์ใหม่
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted">
                                <a href="login.php" class="text-muted ms-1">
                                    <i class="mdi mdi-arrow-left me-1"></i>กลับไปหน้าเข้าสู่ระบบ
                                </a>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt">
        <script>document.write(new Date().getFullYear())</script> © Dashboard - Secure System
    </footer>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthDiv = document.getElementById('password-strength');
        const matchDiv = document.getElementById('password-match');
        const submitBtn = document.getElementById('submit-btn');
        
        function checkPasswordStrength(password) {
            let score = 0;
            let feedback = [];
            
            if (password.length >= 8) score++;
            if (password.match(/[a-z]/)) score++;
            if (password.match(/[A-Z]/)) score++;
            if (password.match(/[0-9]/)) score++;
            if (password.match(/[^a-zA-Z0-9]/)) score++;
            
            if (score <= 2) {
                return { class: 'strength-weak', text: 'รหัสผ่านไม่ปลอดภัย' };
            } else if (score <= 3) {
                return { class: 'strength-medium', text: 'รหัสผ่านปานกลาง' };
            } else {
                return { class: 'strength-strong', text: 'รหัสผ่านปลอดภัย' };
            }
        }
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (confirm === '') {
                matchDiv.textContent = '';
                return false;
            }
            
            if (password === confirm) {
                matchDiv.innerHTML = '<span class="strength-strong">รหัสผ่านตรงกัน</span>';
                return true;
            } else {
                matchDiv.innerHTML = '<span class="strength-weak">รหัสผ่านไม่ตรงกัน</span>';
                return false;
            }
        }
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            if (password.length > 0) {
                const strength = checkPasswordStrength(password);
                strengthDiv.innerHTML = `<span class="${strength.class}">${strength.text}</span>`;
            } else {
                strengthDiv.textContent = '';
            }
            checkPasswordMatch();
        });
        
        confirmInput.addEventListener('input', checkPasswordMatch);
        
        // ป้องกันการ submit ถ้ารหัสผ่านไม่ตรงกัน
        document.getElementById('reset-form').addEventListener('submit', function(e) {
            if (!checkPasswordMatch() || passwordInput.value.length < 8) {
                e.preventDefault();
                alert('กรุณาตรวจสอบรหัสผ่านให้ถูกต้อง');
            }
        });
    });
    </script>

</body>
</html>