<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

// ต้อง login ก่อน ถ้ายังไม่ login ให้ไปหน้า login
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'ไม่มีสิทธิ์เข้าถึง';
?>
<?php include '../includes/header.php'; ?>

        <?php include '../includes/sidebar.php'; ?>
        
        <div class="content-page">
            <div class="content">
                <?php include '../includes/topbar.php'; ?>
                
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-6 col-md-8">
                            <div class="text-center mt-5">
                                <div class="mb-4">
                                    <i class="mdi mdi-shield-lock-outline text-danger" style="font-size: 120px;"></i>
                                </div>
                                
                                <h1 class="text-danger mb-3">ไม่มีสิทธิ์เข้าถึง</h1>
                                <h4 class="text-muted mb-4">คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้</h4>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div class="alert alert-warning" role="alert">
                                            <h5 class="alert-heading"><i class="mdi mdi-alert-circle-outline me-2"></i>ข้อมูลการเข้าถึง</h5>
                                            <hr>
                                            <p class="mb-2"><strong>ผู้ใช้งาน:</strong> <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['email']) ?></p>
                                            <p class="mb-2"><strong>บทบาท:</strong>
                                                <span class="badge bg-info"><?= htmlspecialchars(getRoleText($_SESSION['role'] ?? '')) ?></span>
                                            </p>
                                            <p class="mb-0"><strong>เหตุผล:</strong> คุณไม่มีสิทธิ์เข้าถึงหน้านี้ กรุณาติดต่อผู้ดูแลระบบหากคุณคิดว่านี่เป็นความผิดพลาด</p>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <p class="text-muted mb-3">คุณจะถูกนำกลับไปยังหน้าหลักใน <span id="countdown" class="fw-bold text-primary">10</span> วินาที</p>
                                            <a href="../index.php" class="btn btn-primary">
                                                <i class="mdi mdi-home me-1"></i> กลับสู่หน้าหลัก
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <script>
        // Countdown timer
        let seconds = 10;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = '../index.php';
            }
        }, 1000);
    </script>
</body>
</html>
