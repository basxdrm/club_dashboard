<?php
/**
 * Forgot Password Page
 * หน้าลืมรหัสผ่าน
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'รูปแบบอีเมลไม่ถูกต้อง';
        } else {
            try {
                $pdo = getDatabaseConnection();
                
                // ตรวจสอบว่าอีเมลมีอยู่ในระบบหรือไม่
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // สร้าง reset token
                    $reset_token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + (60 * 60)); // หมดอายุใน 1 ชั่วโมง
                    
                    // บันทึก token ลงฐานข้อมูล
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET reset_token = ?, reset_token_expires = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$reset_token, $expires, $user['id']]);
                    
                    // สร้าง reset link
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . 
                                  dirname($_SERVER['PHP_SELF']) . 
                                  "/reset-password.php?token=" . $reset_token;
                    
                    // ในระบบจริงควรส่งอีเมล ตอนนี้แสดง link เฉยๆ
                    $success = "ลิงก์สำหรับรีเซ็ตรหัสผ่านได้ถูกสร้างแล้ว:<br><br>
                              <strong>Reset Link:</strong><br>
                              <a href='{$reset_link}' class='text-primary'>{$reset_link}</a><br><br>
                              <small class='text-muted'>ลิงก์นี้จะหมดอายุใน 1 ชั่วโมง</small>";
                } else {
                    // แม้ไม่พบอีเมลก็แสดงข้อความเดียวกันเพื่อความปลอดภัย
                    $success = "หากอีเมลที่กรอกมีอยู่ในระบบ เราจะส่งลิงก์รีเซ็ตรหัสผ่านให้คุณ";
                }
            } catch (Exception $e) {
                error_log("Forgot password error: " . $e->getMessage());
                $error = 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง';
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
    <title>ลืมรหัสผ่าน | Dashboard</title>
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
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">ลืมรหัสผ่าน?</h4>
                                <p class="text-muted mb-4">กรอกอีเมลของคุณ เราจะส่งลิงก์สำหรับรีเซ็ตรหัสผ่านให้</p>
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
                                <?php echo $success; // อนุญาต HTML สำหรับลิงก์ ?>
                            </div>
                            <?php endif; ?>

                            <?php if (empty($success)): ?>
                            <form method="POST" action="forgot-password.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="mb-3">
                                    <label for="email" class="form-label">อีเมล</label>
                                    <input class="form-control" type="email" id="email" name="email" 
                                           required placeholder="กรอกอีเมลของคุณ" autocomplete="email"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-email me-1"></i> ส่งลิงก์รีเซ็ต
                                    </button>
                                </div>
                            </form>
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

</body>
</html>