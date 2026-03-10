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
requireLogin();
requireRole('admin');

$page_title = 'ตั้งค่าพื้นหลังหน้า Login';

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'upload') {
            // Handle file upload
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $filename = $_FILES['background_image']['name'];
                $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($filetype, $allowed)) {
                    $newname = 'login_bg_' . time() . '.' . $filetype;
                    $upload_path = '../assets/images/bg/' . $newname;
                    
                    // Create directory if not exists
                    if (!file_exists('../assets/images/bg/')) {
                        mkdir('../assets/images/bg/', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['background_image']['tmp_name'], $upload_path)) {
                        // Save to config file
                        file_put_contents('../config/login_bg.txt', 'assets/images/bg/' . $newname);
                        $success = 'อัปโหลดรูปพื้นหลังสำเร็จ';
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
                    }
                } else {
                    $error = 'กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น (jpg, png, gif, webp)';
                }
            }
        } elseif ($_POST['action'] === 'remove') {
            // Remove background
            if (file_exists('../config/login_bg.txt')) {
                unlink('../config/login_bg.txt');
                $success = 'ลบพื้นหลังสำเร็จ';
            }
        } elseif ($_POST['action'] === 'use_default') {
            // Use default background
            file_put_contents('../config/login_bg.txt', $_POST['default_bg']);
            $success = 'เปลี่ยนพื้นหลังสำเร็จ';
        }
    }
}

// Get current background
$current_bg = '';
if (file_exists('../config/login_bg.txt')) {
    $current_bg = file_get_contents('../config/login_bg.txt');
}

// Default backgrounds
$default_backgrounds = [
    'assets/images/bg/default1.jpg' => 'พื้นหลังเริ่มต้น 1',
    'assets/images/bg/default2.jpg' => 'พื้นหลังเริ่มต้น 2',
    'assets/images/bg/default3.jpg' => 'พื้นหลังเริ่มต้น 3',
];

include_once '../includes/header.php';
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

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">ตั้งค่าพื้นหลังหน้า Login</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">อัปโหลดรูปพื้นหลังใหม่</h4>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="upload">
                                    
                                    <div class="mb-3">
                                        <label for="background_image" class="form-label">เลือกรูปภาพ</label>
                                        <input type="file" class="form-control" id="background_image" name="background_image" accept="image/*" required>
                                        <small class="text-muted">รองรับไฟล์: JPG, PNG, GIF, WebP (แนะนำขนาด 1920x1080px)</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-upload me-1"></i> อัปโหลด
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">จัดการพื้นหลัง</h4>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('ต้องการลบพื้นหลังหรือไม่?')">
                                        <i class="mdi mdi-delete me-1"></i> ลบพื้นหลัง
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">ตัวอย่างพื้นหลังปัจจุบัน</h4>
                                
                                <?php if ($current_bg): ?>
                                <div class="preview-container mb-3">
                                    <img src="../<?php echo htmlspecialchars($current_bg); ?>" 
                                         class="img-fluid rounded" 
                                         alt="Current Background"
                                         style="max-height: 400px; width: 100%; object-fit: cover;">
                                </div>
                                <p class="text-muted mb-0">
                                    <i class="mdi mdi-image me-1"></i> 
                                    <?php echo htmlspecialchars($current_bg); ?>
                                </p>
                                <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="mdi mdi-image-off" style="font-size: 3rem;"></i>
                                    <p class="mt-2">ยังไม่มีพื้นหลัง</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom backgrounds from uploaded files -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">รูปพื้นหลังที่อัปโหลด</h4>
                                
                                <div class="row">
                                    <?php
                                    $bg_dir = '../assets/images/bg/';
                                    if (file_exists($bg_dir)) {
                                        $files = glob($bg_dir . 'login_bg_*.*');
                                        foreach ($files as $file) {
                                            $relative_path = str_replace('../', '', $file);
                                            $is_current = ($current_bg === $relative_path);
                                    ?>
                                    <div class="col-md-4 col-lg-3 mb-3">
                                        <div class="card <?php echo $is_current ? 'border-primary' : ''; ?>">
                                            <img src="../<?php echo $relative_path; ?>" 
                                                 class="card-img-top" 
                                                 alt="Background"
                                                 style="height: 150px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <?php if ($is_current): ?>
                                                <span class="badge bg-primary w-100">กำลังใช้งาน</span>
                                                <?php else: ?>
                                                <form method="POST" class="d-grid">
                                                    <input type="hidden" name="action" value="use_default">
                                                    <input type="hidden" name="default_bg" value="<?php echo htmlspecialchars($relative_path); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">ใช้พื้นหลังนี้</button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container -->

        </div> <!-- content -->

    <?php include '../includes/footer.php'; ?>