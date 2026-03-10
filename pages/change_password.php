<?php
/**
 * Change Password Page
 * หน้าเปลี่ยนรหัสผ่าน
 */

define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/security.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.email, p.prefix, p.first_name_th, p.last_name_th, p.profile_picture
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$page_title = 'เปลี่ยนรหัสผ่าน';
require_once '../includes/header.php';
?>

<!-- ========== Left Sidebar Start ========== -->
<?php include_once('../includes/sidebar.php'); ?>
<!-- Left Sidebar End -->

<div class="content-page">
    <div class="content">

        <!-- Topbar Start -->
        <?php include_once('../includes/topbar.php'); ?>
        <!-- end Topbar -->

        <!-- Start Content-->
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">เปลี่ยนรหัสผ่าน</h4>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-8">
                    <!-- Profile Info Card -->
                    <div class="card text-center">
                        <div class="card-body py-4">
                            <div class="avatar-container mb-2" style="width: 80px; height: 80px; margin: 0 auto;">
                                <?php 
                                $avatar = !empty($user['profile_picture']) 
                                    ? '../assets/images/users/' . $user['profile_picture'] 
                                    : '../assets/images/users/avatar-1.jpg';
                                ?>
                                <img src="<?php echo htmlspecialchars($avatar); ?>" 
                                     class="rounded-circle" 
                                     alt="profile"
                                     style="width: 100%; height: 100%; object-fit: cover; border: 3px solid #e9ecef;">
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($user['prefix'] . $user['first_name_th'] . ' ' . $user['last_name_th']); ?></h5>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>

                    <!-- Change Password Form -->
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="mdi mdi-lock-reset text-danger" style="font-size: 3rem;"></i>
                                <h4 class="mt-2">เปลี่ยนรหัสผ่าน</h4>
                                <p class="text-muted">กรุณากรอกข้อมูลด้านล่างเพื่อเปลี่ยนรหัสผ่านของคุณ</p>
                            </div>

                            <form id="changePasswordForm">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="กรอกรหัสผ่านปัจจุบัน">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="กรอกรหัสผ่านใหม่">
                                    </div>
                                    <small class="text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</small>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="mdi mdi-lock-check"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="mdi mdi-lock-reset me-1"></i>เปลี่ยนรหัสผ่าน
                                    </button>
                                    <a href="my_account.php" class="btn btn-light">
                                        <i class="mdi mdi-arrow-left me-1"></i>กลับ
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security Tips -->
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="mdi mdi-information-outline me-1"></i>คำแนะนำความปลอดภัย</h5>
                        <ul class="mb-0">
                            <li>ใช้รหัสผ่านที่มีความยาวอย่างน้อย 8 ตัวอักษร</li>
                            <li>ใช้ตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข และอักขระพิเศษ</li>
                            <li>ไม่ควรใช้รหัสผ่านเดียวกันกับเว็บไซต์อื่น</li>
                            <li>เปลี่ยนรหัสผ่านเป็นประจำทุก 3-6 เดือน</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <!-- container -->

    </div>
    <!-- content -->

    <?php include_once('../includes/footer.php'); ?>

</div>
<!-- content-page -->

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Change Password
$('#changePasswordForm').on('submit', function(e) {
    e.preventDefault();
    
    const newPassword = $('#new_password').val();
    const confirmPassword = $('#confirm_password').val();
    
    if (newPassword !== confirmPassword) {
        Swal.fire('ข้อผิดพลาด!', 'รหัสผ่านใหม่ไม่ตรงกัน', 'error');
        return;
    }
    
    const formData = $(this).serialize();
    
    $.ajax({
        url: '../api/account_change_password.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: response.message,
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    $('#changePasswordForm')[0].reset();
                });
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error, xhr.responseText);
            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
