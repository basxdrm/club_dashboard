<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

requireLogin();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$errors = [];
$success = false;
$success_message = '';
$task = null;
$registration_closed = false;
$close_reason = '';

if (empty($token)) {
    $errors[] = 'ลิงค์ไม่ถูกต้อง';
} else {
    $db = new Database();
    $conn = $db->getConnection();

    // Get task by registration link
    $sql = "SELECT t.*, p.name as project_name,
            COUNT(CASE WHEN ta.status = 'approved' THEN 1 END) as current_assignees
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN task_assignments ta ON t.id = ta.task_id
            WHERE t.registration_link = ?
            AND (t.assignment_mode = 'registration' OR t.assignment_mode = 'hybrid')
            GROUP BY t.id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$token]);
    $task = $stmt->fetch();

    if (!$task) {
        $errors[] = 'ไม่พบงานหรือลิงค์ไม่ถูกต้อง';
    } else {
        // Get registered users
        $sql_users = "SELECT ta.*, CONCAT(pr.prefix, pr.first_name_th, ' ', pr.last_name_th) as user_name,
                      pr.nickname_th, ta.assigned_at
                      FROM task_assignments ta
                      LEFT JOIN users u ON ta.user_id = u.id
                      LEFT JOIN profiles pr ON u.id = pr.user_id
                      WHERE ta.task_id = ? AND ta.status = 'approved'
                      ORDER BY ta.assigned_at ASC";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->execute([$task['id']]);
        $registered_users = $stmt_users->fetchAll();

        // Check if task is completed
        if ($task['status'] === 'completed') {
            $registration_closed = true;
            $close_reason = 'งานเสร็จสิ้นแล้ว';
        }

        // Check if max assignees reached
        if ($task['max_assignees'] && $task['current_assignees'] >= $task['max_assignees']) {
            $registration_closed = true;
            $close_reason = 'รับครบจำนวนแล้ว';
        }

        // Check if user already registered
        $sql = "SELECT id, status FROM task_assignments WHERE task_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$task['id'], $_SESSION['user_id']]);
        $existing = $stmt->fetch();

        $user_registration_status = null;
        if ($existing) {
            $user_registration_status = $existing['status'];
        }

        // Process registration or cancellation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $errors[] = 'Invalid CSRF token';
            } else {
                // Check if it's a cancellation request
                if (isset($_POST['cancel_registration'])) {
                    try {
                        // Delete the registration
                        $sql = "DELETE FROM task_assignments WHERE task_id = ? AND user_id = ? AND status IN ('pending', 'approved')";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$task['id'], $_SESSION['user_id']]);

                        // Update current assignees count
                        $sql = "UPDATE tasks SET current_assignees = GREATEST(0, current_assignees - 1) WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$task['id']]);

                        // Redirect with success parameter to prevent refresh loop
                        header("Location: " . $_SERVER['REQUEST_URI'] . "&cancelled=1");
                        exit();
                    } catch (Exception $e) {
                        $errors[] = 'เกิดข้อผิดพลาดในการยกเลิกการลงทะเบียน: ' . $e->getMessage();
                    }
                } else {
                    // Regular registration
                    if (!$user_registration_status && !$registration_closed) {
                        try {
                            $status = 'approved';

                            $sql = "INSERT INTO task_assignments (
                                task_id, user_id, assignment_type, status
                            ) VALUES (?, ?, 'self_register', ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$task['id'], $_SESSION['user_id'], $status]);

                            // Update current assignees count if approved
                            if ($status === 'approved') {
                                $sql = "UPDATE tasks SET current_assignees = current_assignees + 1 WHERE id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$task['id']]);
                            }

                            $success = true;
                            $success_message = 'ลงทะเบียนสำเร็จ คุณสามารถเริ่มทำงานได้แล้ว';

                            // Refresh the user status
                            $user_registration_status = $status;
                        } catch (PDOException $e) {
                            $errors[] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();
$page_title = 'ลงทะเบียนรับงาน';

// อ่านค่า background
$login_bg = '';
if (file_exists('../config/login_bg.txt')) {
    $login_bg = trim(file_get_contents('../config/login_bg.txt'));
}

// Get user profile information
$user_profile = null;
if (isset($_SESSION['user_id']) && $task) {
    $stmt_profile = $conn->prepare("SELECT CONCAT(pr.prefix, pr.first_name_th, ' ', pr.last_name_th) as full_name, pr.student_id, u.email FROM users u LEFT JOIN profiles pr ON u.id = pr.user_id WHERE u.id = ?");
    $stmt_profile->execute([$_SESSION['user_id']]);
    $user_profile = $stmt_profile->fetch();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8" />
    <title><?php echo $page_title; ?> &#8211; MSJ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="../assets/images/logos/MSJ logo new 512.png">

    <!-- App css -->
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../assets/css/custom.css" rel="stylesheet" type="text/css" />

    <?php if ($login_bg): ?>
        <style>
            body.authentication-bg {
                background-image: url('../<?php echo htmlspecialchars($login_bg); ?>') !important;
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

            .card {
                box-shadow: 0 0 35px 0 rgba(0, 0, 0, 0.4) !important;
                backdrop-filter: blur(2px);
            }
        </style>
    <?php else: ?>
        <style>
            body.authentication-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                min-height: 100vh;
            }

            .card {
                box-shadow: 0 0 35px 0 rgba(0, 0, 0, 0.4) !important;
                backdrop-filter: blur(2px);
            }
        </style>
    <?php endif; ?>
</head>

<body class="loading authentication-bg" data-layout-config='{"darkMode":false}'>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-6 col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">

                            <?php if ($task && empty($errors)): ?>

                                <!-- Event Details Section -->
                                <div class="mb-4">
                                    <h5 class="card-title text-center mb-3">
                                        <i class="mdi mdi-calendar-check text-primary me-2"></i>
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h5>

                                    <?php if ($task['description']): ?>
                                        <p class="text-center text-muted mb-3">
                                            <?php echo htmlspecialchars(mb_substr($task['description'], 0, 100)) . (mb_strlen($task['description']) > 100 ? '...' : ''); ?>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Progress Information -->
                                    <div class="row text-center mb-4">
                                        <div class="col-4">
                                            <div class="p-2">
                                                <h4 class="mt-0 text-primary"><?php echo $task['current_assignees'] ?: 0; ?></h4>
                                                <p class="text-muted mb-0">ลงทะเบียนแล้ว</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2">
                                                <h4 class="mt-0 text-info"><?php echo $task['max_assignees'] ?: '∞'; ?></h4>
                                                <p class="text-muted mb-0">จำนวนรับ</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2">
                                                <?php
                                                // Calculate status
                                                $status_text = 'เปิดลงทะเบียน';
                                                $status_color = 'success';

                                                if ($registration_closed) {
                                                    $status_text = $close_reason;
                                                    $status_color = 'danger';
                                                }
                                                ?>
                                                <h4 class="mt-0 text-<?php echo $status_color; ?>"><?php echo $status_text; ?></h4>
                                                <p class="text-muted mb-0">สถานะ</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="text-center p-2 border rounded">
                                                <i class="mdi mdi-calendar-start text-success"></i>
                                                <p class="mb-1 text-muted">วันที่เริ่มต้น</p>
                                                <strong><?php echo date('d/m/Y', strtotime($task['start_date'])); ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-center p-2 border rounded">
                                                <i class="mdi mdi-calendar-end text-danger"></i>
                                                <p class="mb-1 text-muted">วันที่สิ้นสุด</p>
                                                <strong><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Registration Section -->
                                <?php if ($user_registration_status === 'pending'): ?>
                                    <!-- Pending Approval -->
                                    <div class="mb-4">
                                        <div class="alert alert-warning">
                                            <h6 class="mb-3"><i class="mdi mdi-clock-outline me-2"></i>รอการอนุมัติ</h6>
                                            <div class="row small">
                                                <div class="col-md-4">
                                                    <strong>ชื่อ-นามสกุล:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['full_name'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>รหัสนักเรียน:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['student_id'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>อีเมล:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['email'] ?? $_SESSION['email'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <p class="text-muted mb-3">คุณได้ลงทะเบียนแล้ว กรุณารอการอนุมัติจากผู้ดูแล</p>

                                            <?php if ($task['status'] !== 'completed'): ?>
                                                <form method="POST" action="" id="cancelFormPending">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="cancel_registration" value="1">
                                                    <button type="button" class="btn btn-outline-danger" onclick="confirmCancelRegistration('cancelFormPending')">
                                                        <i class="mdi mdi-cancel me-2"></i>ยกเลิกการลงทะเบียน
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <?php elseif ($user_registration_status === 'approved' || $user_registration_status === 'in_progress'): ?>
                                    <!-- Already Approved -->
                                    <div class="mb-4">
                                        <div class="alert alert-success">
                                            <h6 class="mb-3"><i class="mdi mdi-check-circle me-2"></i>คุณได้รับงานนี้แล้ว</h6>
                                            <div class="row small">
                                                <div class="col-md-4">
                                                    <strong>ชื่อ-นามสกุล:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['full_name'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>รหัสนักเรียน:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['student_id'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>อีเมล:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['email'] ?? $_SESSION['email'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <p class="text-success mb-3">
                                                <i class="mdi mdi-check me-1"></i>
                                                คุณสามารถเริ่มทำงานได้แล้ว
                                            </p>
                                            <?php if ($task['status'] !== 'completed'): ?>
                                                <form method="POST" action="" id="cancelFormApproved">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="cancel_registration" value="1">
                                                    <button type="button" class="btn btn-outline-danger" onclick="confirmCancelRegistration('cancelFormApproved')">
                                                        <i class="mdi mdi-cancel me-2"></i>ยกเลิกการลงทะเบียน
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <?php elseif ($user_registration_status === 'rejected'): ?>
                                    <!-- Rejected -->
                                    <div class="mb-4">
                                        <div class="alert alert-danger">
                                            <h6 class="mb-2"><i class="mdi mdi-close-circle me-2"></i>การลงทะเบียนถูกปฏิเสธ</h6>
                                            <p class="mb-0">การลงทะเบียนของคุณถูกปฏิเสธ กรุณาติดต่อผู้ดูแลสำหรับข้อมูลเพิ่มเติม</p>
                                        </div>
                                    </div>

                                <?php elseif ($user_registration_status === 'completed'): ?>
                                    <!-- Completed -->
                                    <div class="mb-4">
                                        <div class="alert alert-info">
                                            <h6 class="mb-2"><i class="mdi mdi-check-all me-2"></i>คุณได้ทำงานนี้เสร็จแล้ว</h6>
                                            <p class="mb-0">ขอบคุณที่ร่วมทำงานนี้</p>
                                        </div>
                                    </div>

                                <?php elseif (!$user_registration_status && !$registration_closed): ?>
                                    <!-- Registration Form -->
                                    <div class="mb-4">
                                        <div class="alert alert-info">
                                            <h6 class="mb-3"><i class="mdi mdi-account me-2"></i>ข้อมูลผู้ลงทะเบียน</h6>
                                            <div class="row small">
                                                <div class="col-md-4">
                                                    <strong>ชื่อ-นามสกุล:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['full_name'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>รหัสนักเรียน:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['student_id'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>อีเมล:</strong><br>
                                                    <?php echo htmlspecialchars($user_profile['email'] ?? $_SESSION['email'] ?? 'ไม่พบข้อมูล'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <form method="POST" action="" id="registrationForm">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                            <div class="text-center">
                                                <button type="button" class="btn btn-success btn-lg" onclick="confirmRegistration()">
                                                    <i class="mdi mdi-calendar-check me-2"></i>ลงทะเบียนเข้าร่วม
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                <?php else: ?>
                                    <!-- Registration Closed -->
                                    <div class="text-center mb-4">
                                        <div class="alert alert-warning">
                                            <h5 class="alert-heading"><i class="mdi mdi-alert-circle-outline me-2"></i>การลงทะเบียนปิดแล้ว</h5>
                                            <p class="mb-0"><?php echo $close_reason; ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Participants List -->
                                <div class="mt-4">
                                    <h6 class="mb-3">
                                        <i class="mdi mdi-account-group me-1"></i>
                                        รายชื่อผู้ลงทะเบียน (<?php echo count($registered_users); ?> คน)
                                    </h6>

                                    <?php if (!empty($registered_users)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ชื่อ-นามสกุล</th>
                                                        <th>ชื่อเล่น</th>
                                                        <th>วันที่ลงทะเบียน</th>
                                                        <th class="text-center">สถานะ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($registered_users as $index => $user): ?>
                                                        <tr>
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['nickname_th'] ?? '-'); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($user['assigned_at'])); ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-success">
                                                                    <i class="mdi mdi-check"></i>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="mdi mdi-account-off" style="font-size: 48px;"></i>
                                            <p class="mt-2 mb-1">ยังไม่มีผู้ลงทะเบียน</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="../index.php" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left me-1"></i>กลับหน้าแรก
                                    </a>
                                </div>

                        </div> <!-- card-body -->
                    </div> <!-- card -->
                <?php endif; ?>

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
    <script src="../assets/js/vendor.min.js"></script>
    <script src="../assets/js/app.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // SweetAlert for cancel registration confirmation
        function confirmCancelRegistration(formId) {
            Swal.fire({
                title: 'ยืนยันการยกเลิก',
                text: 'คุณแน่ใจหรือไม่ที่จะยกเลิกการลงทะเบียน?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ยกเลิกการลงทะเบียน',
                cancelButtonText: 'ไม่ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form
                    document.getElementById(formId || 'cancelForm').submit();
                }
            });
            return false;
        }

        // SweetAlert for registration confirmation
        function confirmRegistration() {
            Swal.fire({
                title: 'ยืนยันการลงทะเบียน',
                text: 'คุณต้องการลงทะเบียนรับงานนี้หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลงทะเบียน',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show success toast
                    Swal.fire({
                        title: 'กำลังลงทะเบียน...',
                        text: 'โปรดรอสักครู่',
                        icon: 'info',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Use AJAX to submit form
                    const form = document.getElementById('registrationForm');
                    if (form) {
                        const formData = new FormData(form);

                        fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (response.ok) {
                                    Swal.fire({
                                        title: 'ลงทะเบียนสำเร็จ!',
                                        text: 'กำลังอัพเดทข้อมูล...',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Refresh once to show updated data
                                        window.location.reload();
                                    });
                                } else {
                                    throw new Error('Network response was not ok');
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: 'เกิดข้อผิดพลาด!',
                                    text: 'กรุณาลองใหม่อีกครั้ง',
                                    icon: 'error'
                                });
                            });
                    }
                }
            }).catch(error => {
                console.error('SweetAlert error:', error);
            });
            return false;
        }

        <?php if ($success): ?>
            // Registration successful
        <?php endif; ?>

        // Check for cancellation success from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('cancelled') === '1') {
            // Remove the parameter from URL
            urlParams.delete('cancelled');
            const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState({}, '', newUrl);

            // Show success message
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'ยกเลิกสำเร็จ!',
                    text: 'ยกเลิกการลงทะเบียนสำเร็จ',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }

        <?php if (!empty($errors)): ?>
            // Show error messages when page loads
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    html: <?php echo json_encode(implode("<br>", array_map("htmlspecialchars", $errors)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'ตกลง'
                });
            });
        <?php endif; ?>
    </script>

</body>

</html>