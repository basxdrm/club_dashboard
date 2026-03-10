<?php

/**
 * Login Page
 * หน้าเข้าสู่ระบบที่ปลอดภัย
 */

define('APP_ACCESS', true);

// ตั้งค่า Session ก่อน session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // เปลี่ยนเป็น 1 ถ้าใช้ HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// เริ่ม Session อย่างปลอดภัย
session_start();

// Load Configuration
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'includes/auth.php';

// ตรวจสอบ Remember Me Token ถ้ายังไม่ล็อกอิน
if (!isLoggedIn()) {
    $pdo = getDatabaseConnection();
    if (checkRememberMeToken($pdo)) {
        // Auto login สำเร็จ ให้ไปหน้า Dashboard
        header('Location: index.php');
        exit();
    }
} else {
    // ล็อกอินแล้ว ให้ไปหน้า Dashboard
    header('Location: index.php');
    exit();
}

// ประมวลผล Form
$error = '';
$success = '';

// ดึง error จาก session (สำหรับ PRG Pattern)
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['login_error'] = 'Invalid request. Please try again.';
        header('Location: login.php');
        exit();
    }

    // รับและทำความสะอาดข้อมูล
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Validate Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
        header('Location: login.php');
        exit();
    } elseif (empty($password)) {
        $_SESSION['login_error'] = 'กรุณากรอกรหัสผ่าน';
        header('Location: login.php');
        exit();
    }

    try {
        $pdo = getDatabaseConnection();
        $result = loginUser($email, $password, $pdo, $remember_me);

        if ($result['success']) {
            header('Location: index.php');
            exit();
        } else {
            if (isset($result['use_swal']) && $result['use_swal']) {
                $_SESSION['login_error'] = 'SWAL:' . $result['message'];
            } else {
                $_SESSION['login_error'] = $result['message'];
            }
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        $_SESSION['login_error'] = 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ กรุณาลองใหม่อีกครั้ง';
        header('Location: login.php');
        exit();
    }
}

// สร้าง CSRF Token
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
    <title>เข้าสู่ระบบ &#8211; MSJ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/x-icon" href="assets/images/logos/MSJ logo new 512.png">

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Custom CSS (สำหรับเปลี่ยนฟอนต์) -->
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">เข้าสู่ระบบ</h4>
                                <p class="text-muted mb-4">กรุณากรอกอีเมลและรหัสผ่านของคุณ</p>
                            </div>

                            <?php if (!empty($error) && strpos($error, 'SWAL:') === 0): ?>
                                <!-- SweetAlert Error -->
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'เข้าสู่ระบบไม่สำเร็จ',
                                            text: <?php echo json_encode(str_replace('SWAL:', '', $error)); ?>,
                                            confirmButtonText: 'ตกลง'
                                        });
                                    });
                                </script>
                            <?php elseif (!empty($error)): ?>
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
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="login.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <!-- Hidden fields เพื่อหลอกเบราว์เซอร์ไม่ให้ autofill -->
                                <div style="display: none;">
                                    <input type="text" name="fake_username" autocomplete="username">
                                    <input type="password" name="fake_password" autocomplete="current-password">
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">อีเมล</label>
                                    <input class="form-control" type="email" id="email" name="email"
                                        required placeholder="กรอกอีเมล" autocomplete="username"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">รหัสผ่าน</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" name="password" class="form-control"
                                            required placeholder="กรอกรหัสผ่าน" autocomplete="off">
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="checkbox-signin" name="remember_me" value="1">
                                        <label class="form-check-label" for="checkbox-signin">จดจำฉันไว้</label>
                                    </div>
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit"> เข้าสู่ระบบ </button>
                                </div>

                            </form>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <!-- <p class="text-muted"><a href="forgot-password.php" class="text-muted ms-1">ลืมรหัสผ่าน?</a></p> -->
                            <p class="text-muted"><small>ติดต่อผู้ดูแลระบบเพื่อสร้างบัญชี</small></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt">
        Copyright &copy;
        <script>
            document.write(new Date().getFullYear())
        </script> MSJ. All Rights Reserved
    </footer>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            const rememberCheckbox = document.getElementById('checkbox-signin');
            const loginForm = document.querySelector('form');

            // ตั้งค่าเริ่มต้น - ปิด autocomplete
            passwordField.setAttribute('autocomplete', 'off');

            // เมื่อติ๊ก remember me
            rememberCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // เปิดให้เบราว์เซอร์จดจำรหัสผ่าน
                    passwordField.setAttribute('autocomplete', 'current-password');
                    emailField.setAttribute('autocomplete', 'username');
                } else {
                    // ปิดการจดจำรหัสผ่าน
                    passwordField.setAttribute('autocomplete', 'off');
                    emailField.setAttribute('autocomplete', 'username');
                }
            });

            // เมื่อ submit form
            loginForm.addEventListener('submit', function(e) {
                if (!rememberCheckbox.checked) {
                    // ถ้าไม่ได้ติ๊ก remember me ให้ปิด autocomplete
                    passwordField.setAttribute('autocomplete', 'off');
                }
            });

            // ป้องกันเบราว์เซอร์ auto-fill password เมื่อโหลดหน้า
            setTimeout(function() {
                if (!rememberCheckbox.checked) {
                    passwordField.value = '';
                }
            }, 100);
        });
    </script>

</body>

</html>