<?php
/**
 * Task Registration Links
 * หน้ารวมลิงก์ลงทะเบียนงานที่เปิดอยู่
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

// ดึงข้อมูลงานที่เปิดลงทะเบียนและยังไม่เสร็จสิ้น
$stmt = $conn->prepare("
    SELECT t.*, p.name as project_name, 
           CONCAT(pr.prefix, pr.first_name_th, ' ', pr.last_name_th) as creator_name,
           (SELECT COUNT(*) FROM task_assignments ta WHERE ta.task_id = t.id AND ta.status = 'approved') as current_assignees
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u ON t.created_by = u.id
    LEFT JOIN profiles pr ON u.id = pr.user_id
    WHERE t.assignment_mode IN ('registration', 'hybrid') 
    AND t.registration_link IS NOT NULL 
    AND t.status NOT IN ('cancelled', 'completed')
    ORDER BY t.created_at DESC
");
$stmt->execute();
$tasks = $stmt->fetchAll();

$page_title = 'งานรับลงทะเบียน';
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
                        <h4 class="page-title">งานรับลงทะเบียน</h4>
                        <p class="text-muted">รวมงานที่เปิดให้สมาชิกลงทะเบียนรับงานด้วยตนเอง</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">รายการงานที่รับลงทะเบียน</h4>
                            
                            <?php if (empty($tasks)): ?>
                                <div class="text-center py-5">
                                    <i class="mdi mdi-clipboard-text-off-outline text-muted" style="font-size: 48px;"></i>
                                    <h5 class="text-muted mt-3">ยังไม่มีงานที่เปิดลงทะเบียน</h5>
                                    <p class="text-muted">งานที่เปิดให้ลงทะเบียนจะแสดงที่นี่</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($tasks as $task): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card border">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($task['title']); ?></h5>
                                                    <span class="badge bg-<?php echo match($task['assignment_mode']) {
                                                        'registration' => 'primary',
                                                        'hybrid' => 'info',
                                                        default => 'secondary'
                                                    }; ?>">
                                                        <?php echo match($task['assignment_mode']) {
                                                            'registration' => 'ลงทะเบียน',
                                                            'hybrid' => 'ผสม',
                                                            default => $task['assignment_mode']
                                                        }; ?>
                                                    </span>
                                                </div>

                                                <p class="text-muted small mb-2">
                                                    <i class="mdi mdi-folder-outline"></i>
                                                    <?php echo htmlspecialchars($task['project_name'] ?? 'ไม่มีโปรเจค'); ?>
                                                </p>

                                                <p class="text-muted small mb-2">
                                                    <i class="mdi mdi-account-outline"></i>
                                                    โดย: <?php echo htmlspecialchars($task['creator_name']); ?>
                                                </p>

                                                <?php if ($task['description']): ?>
                                                <p class="text-muted small mb-3">
                                                    <?php echo htmlspecialchars(mb_substr($task['description'], 0, 80)) . (mb_strlen($task['description']) > 80 ? '...' : ''); ?>
                                                </p>
                                                <?php endif; ?>

                                                <div class="row text-center mb-3">
                                                    <div class="col-6">
                                                        <div class="text-muted small">ลงทะเบียนแล้ว</div>
                                                        <h6 class="mb-0"><?php echo $task['current_assignees']; ?></h6>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-muted small">จำนวนสูงสุด</div>
                                                        <h6 class="mb-0"><?php echo $task['max_assignees'] ?: '∞'; ?></h6>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small text-muted">ลิงก์ลงทะเบียน:</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control form-control-sm"
                                                               value="<?php
                                                                   $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                                                                   $host = $_SERVER['HTTP_HOST'];
                                                                   $base_path = str_replace('/pages/registration_link.php', '', $_SERVER['SCRIPT_NAME']);
                                                                   echo $protocol . $host . $base_path . '/pages/task_register.php?token=' . htmlspecialchars($task['registration_link']); 
                                                               ?>"
                                                               id="taskUrl-<?php echo $task['id']; ?>" readonly>
                                                        <button class="btn btn-sm btn-outline-primary" type="button"
                                                                onclick="copyTaskUrl(<?php echo $task['id']; ?>)">
                                                            <i class="mdi mdi-content-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a href="task_view.php?id=<?php echo $task['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info flex-fill">
                                                        <i class="mdi mdi-eye"></i> ดูงาน
                                                    </a>
                                                    <a href="task_register.php?token=<?php echo htmlspecialchars($task['registration_link']); ?>" 
                                                       class="btn btn-sm btn-outline-success flex-fill" target="_blank">
                                                        <i class="mdi mdi-open-in-new"></i> เปิดหน้า
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- container -->

    </div>
    <!-- content -->

    <?php include_once('../includes/footer.php'); ?>

</div>

<script>
// Function to copy task registration URL
function copyTaskUrl(taskId) {
    const urlInput = document.getElementById('taskUrl-' + taskId);
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        Swal.fire({
            icon: 'success',
            title: 'คัดลอกแล้ว!',
            text: 'คัดลอกลิงค์ลงทะเบียนไปยังคลิปบอร์ดแล้ว',
            showConfirmButton: false,
            timer: 1500
        });
    } catch (err) {
        Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถคัดลอกได้', 'error');
    }
}
</script>

</body>
</html>
