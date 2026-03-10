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
require_once '../includes/status_helper.php';
requireLogin();

$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($task_id <= 0) {
    header('Location: tasks.php');
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Get task details
$sql = "SELECT t.*,
        p.name as project_name,
        CONCAT(prof_creator.prefix, prof_creator.first_name_th, ' ', prof_creator.last_name_th) as creator_name,
        prof_creator.profile_picture as creator_picture
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        LEFT JOIN users u_creator ON t.created_by = u_creator.id
        LEFT JOIN profiles prof_creator ON u_creator.id = prof_creator.user_id
        WHERE t.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: tasks.php');
    exit;
}

// Get assignees (ผู้รับผิดชอบ)
$sql = "SELECT ta.*,
        CONCAT(prof.prefix, prof.first_name_th, ' ', prof.last_name_th) as assignee_name,
        prof.profile_picture as assignee_picture,
        u.email as assignee_email,
        cd.name as department_name
        FROM task_assignments ta
        JOIN users u ON ta.user_id = u.id
        JOIN profiles prof ON u.id = prof.user_id
        LEFT JOIN member_club_info mci ON u.id = mci.user_id
        LEFT JOIN club_departments cd ON mci.department_id = cd.id
        WHERE ta.task_id = ? AND ta.status = 'approved'
        ORDER BY ta.assigned_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$task_id]);
$assignees = $stmt->fetchAll();

// Get activity logs with user info
$sql = "SELECT tal.*,
        CONCAT(prof.prefix, prof.first_name_th, ' ', prof.last_name_th) as user_name,
        prof.profile_picture
        FROM task_activity_logs tal
        JOIN users u ON tal.user_id = u.id
        JOIN profiles prof ON u.id = prof.user_id
        WHERE tal.task_id = ?
        ORDER BY tal.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$task_id]);
$activity_logs = $stmt->fetchAll();

// Get transactions for this task
$sql = "SELECT tr.*,
        CONCAT(prof.prefix, prof.first_name_th, ' ', prof.last_name_th) as recorded_by_name,
        tc.name as category_name
        FROM transactions tr
        JOIN users u ON tr.recorded_by = u.id
        JOIN profiles prof ON u.id = prof.user_id
        LEFT JOIN transaction_categories tc ON tr.category_id = tc.id
        WHERE tr.task_id = ?
        ORDER BY tr.transaction_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$task_id]);
$transactions = $stmt->fetchAll();

// Calculate total expenses (รายจ่าย)
$total_expenses = 0;
foreach ($transactions as $trans) {
    if ($trans['type'] === 'expense') {
        $total_expenses += $trans['amount'];
    }
}

// Get expense categories
$sql = "SELECT id, name FROM transaction_categories WHERE type = 'expense' ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$expense_categories = $stmt->fetchAll();

$page_title = 'รายละเอียดงาน';
$include_datatables = false;
?>
<?php include '../includes/header.php'; ?>


<?php include '../includes/sidebar.php'; ?>
<div class="content-page">
    <div class="content">
        <?php include '../includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="tasks.php">งาน</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($page_title) ?></li>
                            </ol>
                        </div>
                        <h4 class="page-title">รายละเอียดงาน #<?php echo $task['id']; ?></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column: Task Details -->
                <div class="col-lg-8">
                    <!-- Task Information Card -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-3"><?php echo htmlspecialchars($task['title']); ?></h3>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>โปรเจค:</strong>
                                        <?php echo htmlspecialchars($task['project_name'] ?? '-'); ?>
                                    </p>
                                    <p class="mb-2"><strong>สถานะ:</strong>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'secondary',
                                            'กำลังดำเนินการ' => 'primary',
                                            'รอตรวจสอบ' => 'warning',
                                            'completed' => 'success',
                                            'ยกเลิก' => 'danger'
                                        ];
                                        echo '<span class="badge bg-' . ($statusClass[$task['status']] ?? 'secondary') . '">' . htmlspecialchars($task['status']) . '</span>';
                                        ?>
                                    </p>
                                    <p class="mb-2"><strong>ความสำคัญ:</strong>
                                        <?php
                                        $priorityClass = ['low' => 'info', 'medium' => 'warning', 'high' => 'danger'];
                                        $priorityText = ['low' => 'ต่ำ', 'medium' => 'ปานกลาง', 'high' => 'สูง'];
                                        echo '<span class="badge bg-' . ($priorityClass[$task['priority']] ?? 'info') . '">' . ($priorityText[$task['priority']] ?? $task['priority']) . '</span>';
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>กำหนดส่ง:</strong>
                                        <?php echo $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-'; ?>
                                    </p>
                                    <p class="mb-2"><strong>สร้างโดย:</strong>
                                        <?php echo htmlspecialchars($task['creator_name']); ?>
                                    </p>
                                    <p class="mb-2"><strong>วันที่สร้าง:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong>รายละเอียด:</strong>
                                <p class="mt-2"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                            </div>

                            <!-- ผู้รับผิดชอบ -->
                            <div class="mb-0">
                                <strong>ผู้รับผิดชอบ:</strong>
                                <?php if (empty($assignees)): ?>
                                    <p class="mt-2 text-muted">ยังไม่มีผู้รับผิดชอบ</p>
                                <?php else: ?>
                                    <div class="mt-2">
                                        <div class="row g-2">
                                            <?php foreach ($assignees as $assignee): ?>
                                                <div class="col-auto">
                                                    <div class="border rounded p-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm me-2 flex-shrink-0">
                                                                <img src="../assets/images/users/<?php echo $assignee['assignee_picture'] ? $assignee['assignee_picture'] : 'avatar-1.jpg'; ?>"
                                                                    alt="avatar" class="rounded-circle img-fluid border">
                                                            </div>
                                                            <div>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($assignee['assignee_name']); ?></div>
                                                                <?php if ($assignee['department_name']): ?>
                                                                    <div class="text-muted small"><?php echo htmlspecialchars($assignee['department_name']); ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="mdi mdi-history"></i> ความคืบหน้าและหมายเหตุ
                            </h4>

                            <?php if (empty($activity_logs)): ?>
                                <div class="text-center py-4">
                                    <i class="mdi mdi-information-outline font-24 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">ยังไม่มีกิจกรรม</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline" style="max-height: 600px; overflow-y: auto;">
                                    <?php foreach ($activity_logs as $log):
                                        $actionColors = [
                                            'created' => 'info',
                                            'started' => 'warning',
                                            'submitted' => 'primary',
                                            'approved' => 'success',
                                            'completed' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'danger'
                                        ];
                                        $color = $actionColors[$log['action_type']] ?? 'secondary';
                                    ?>
                                        <div class="activity-item mb-3">
                                            <div class="card border-start border-3 border-<?php echo $color; ?> shadow-sm">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h5 class="mb-1 font-14">
                                                                <?php echo htmlspecialchars($log['user_name']); ?>
                                                                <span class="badge bg-<?php echo $color; ?> ms-2">
                                                                    <?php
                                                                    $actionText = [
                                                                        'created' => 'สร้างงาน',
                                                                        'started' => 'เริ่มดำเนินการ',
                                                                        'submitted' => 'ส่งตรวจสอบ',
                                                                        'approved' => 'อนุมัติ',
                                                                        'rejected' => 'ส่งกลับแก้ไข',
                                                                        'completed' => 'เสร็จสิ้น',
                                                                        'cancelled' => 'ยกเลิก'
                                                                    ];
                                                                    echo $actionText[$log['action_type']] ?? $log['action_type'];
                                                                    ?>
                                                                </span>
                                                            </h5>
                                                            <p class="text-muted mb-0 font-12">
                                                                <i class="mdi mdi-clock-outline"></i>
                                                                <?php echo date('d/m/Y H:i น.', strtotime($log['created_at'])); ?>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <?php if ($log['status_from'] || $log['status_to']): ?>
                                                        <div class="mb-2">
                                                            <small class="text-muted">เปลี่ยนสถานะ:</small>
                                                            <?php if ($log['status_from']): ?>
                                                                <span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($log['status_from']); ?></span>
                                                                <i class="mdi mdi-arrow-right text-muted mx-1"></i>
                                                            <?php endif; ?>
                                                            <?php if ($log['status_to']): ?>
                                                                <span class="badge bg-<?php echo $color; ?>-subtle text-<?php echo $color; ?>"><?php echo htmlspecialchars($log['status_to']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($log['review_message']): ?>
                                                        <div class="bg-light rounded p-2 mt-2">
                                                            <i class="mdi mdi-message-text-outline text-muted me-1"></i>
                                                            <small class="text-dark"><?php echo nl2br(htmlspecialchars($log['review_message'])); ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Status & Summary -->
                <div class="col-lg-4">
                    <!-- Status Actions Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">สถานะการดำเนินงาน</h4>

                            <div class="timeline-status">
                                <?php
                                $statuses = [
                                    'pending' => ['icon' => 'mdi-clock-outline', 'color' => 'info'],
                                    'in_progress' => ['icon' => 'mdi-cog-outline', 'color' => 'warning'],
                                    'under_review' => ['icon' => 'mdi-eye-check-outline', 'color' => 'primary'],
                                    'completed' => ['icon' => 'mdi-check-circle-outline', 'color' => 'success']
                                ];

                                $currentStatus = $task['status'];
                                $statusKeys = array_keys($statuses);
                                $currentIndex = array_search($currentStatus, $statusKeys);

                                foreach ($statuses as $status => $config):
                                    $index = array_search($status, $statusKeys);
                                    $isActive = ($index <= $currentIndex) || ($currentStatus === 'completed' && $status === 'completed');
                                    $isCurrent = ($status === $currentStatus);

                                    // แสดงเส้นเฉพาะเมื่อสถานะถัดไปก็ active ด้วย
                                    $nextIndex = $index + 1;
                                    $showLine = $isActive && isset($statusKeys[$nextIndex]) && ($nextIndex <= $currentIndex);
                                ?>
                                    <div class="timeline-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $isCurrent ? 'current' : ''; ?> <?php echo $showLine ? 'show-line' : ''; ?>">
                                        <div class="timeline-icon">
                                            <div class="icon-circle">
                                                <i class="mdi <?php echo $config['icon']; ?> text-<?php echo $isActive ? $config['color'] : 'muted'; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1 <?php echo $isActive ? 'text-' . $config['color'] : 'text-muted'; ?>">
                                                <?php echo getTaskStatusText($status); ?>
                                            </h6>
                                            <?php if ($isCurrent): ?>
                                                <small class="text-<?php echo $config['color']; ?>">• สถานะปัจจุบัน</small>
                                            <?php elseif ($isActive): ?>
                                                <small class="text-success">✓ เสร็จแล้ว</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-4">
                                <h6 class="mb-3">การดำเนินการ</h6>
                                <?php if ($task['status'] === 'pending'): ?>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" id="startTaskBtn">
                                            <i class="mdi mdi-play-circle-outline"></i> เริ่มดำเนินการ
                                        </button>
                                    </div>

                                <?php elseif ($task['status'] === 'in_progress'): ?>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary text-white flex-fill" id="submitTaskBtn">
                                            <i class="mdi mdi-send"></i> ส่งตรวจสอบ
                                        </button>
                                        <button class="btn btn-outline-warning flex-fill" id="pauseTaskBtn">
                                            <i class="mdi mdi-pause-circle-outline"></i> หยุดชั่วคราว
                                        </button>
                                    </div>
                                <?php elseif ($task['status'] === 'under_review'): ?>
                                    <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-success flex-fill" id="approveTaskBtn">
                                                <i class="mdi mdi-check-all"></i> อนุมัติ
                                            </button>
                                            <button class="btn btn-danger flex-fill" id="rejectTaskBtn">
                                                <i class="mdi mdi-close-circle"></i> ปฏิเสธ
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info" role="alert">
                                            <i class="mdi mdi-information-outline"></i> รอการอนุมัติจากผู้ดูแลระบบ
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($task['status'] === 'completed'): ?>
                                    <div class="alert alert-success" role="alert">
                                        <i class="mdi mdi-check-circle"></i> งานเสร็จสมบูรณ์แล้ว
                                    </div>

                                <?php else: ?>
                                    <!-- สถานะอื่นๆ -->
                                    <div class="list-group-item d-flex align-items-center border-0 px-0">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm rounded-circle bg-secondary">
                                                <i class="mdi mdi-circle font-18 text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="mt-0 mb-1"><?php echo htmlspecialchars($task['status']); ?></h5>
                                            <p class="mb-0 text-muted small">• สถานะปัจจุบัน</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($task['status'] !== 'cancelled' && $task['status'] !== 'completed'): ?>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-outline-danger" id="cancelTaskBtn">
                                        <i class="mdi mdi-cancel"></i> ยกเลิกงาน
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Registration Link Card -->
                    <?php if (($task['assignment_mode'] === 'registration' || $task['assignment_mode'] === 'hybrid') && $task['registration_link']): ?>
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">
                                    <i class="mdi mdi-link-variant"></i> ลิงค์ลงทะเบียน
                                </h4>

                                <div class="alert alert-info">
                                    <p class="mb-2">
                                        <i class="mdi mdi-information-outline"></i>
                                        <strong>แชร์ลิงค์นี้</strong> ให้สมาชิกสามารถลงทะเบียนรับงานได้
                                    </p>
                                    <?php if ($task['max_assignees']): ?>
                                        <p class="mb-0 text-muted small">
                                            รับได้สูงสุด: <?php echo $task['max_assignees']; ?> คน
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="input-group">
                                    <input type="text" class="form-control"
                                        value="<?php
                                                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                                                $host = $_SERVER['HTTP_HOST'];
                                                $base_path = str_replace('/pages/task_view.php', '', $_SERVER['SCRIPT_NAME']);
                                                echo $protocol . $host . $base_path . '/pages/task_register.php?token=' . htmlspecialchars($task['registration_link']);
                                                ?>"
                                        id="registrationUrl" readonly>
                                    <button class="btn btn-outline-primary" type="button" onclick="copyRegistrationUrl()">
                                        <i class="mdi mdi-content-copy"></i> คัดลอก
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <a href="task_register.php?token=<?php echo htmlspecialchars($task['registration_link']); ?>"
                                        class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="mdi mdi-eye"></i> ดูหน้าลงทะเบียน
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Expense Summary Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">สรุปรายจ่าย</h4>

                            <div class="text-center">
                                <h2 class="mb-0 text-primary">฿<?php echo number_format($total_expenses, 2); ?></h2>
                                <p class="text-muted mb-0">รวมค่าใช้จ่ายทั้งหมด</p>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between mb-2">
                                <span>จำนวนรายการ:</span>
                                <strong><?php echo count($transactions); ?> รายการ</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">รายการค่าใช้จ่าย</h4>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                    <i class="mdi mdi-plus-circle"></i> เพิ่มรายจ่าย
                                </button>
                            </div>

                            <?php if (empty($transactions)): ?>
                                <p class="text-muted">ยังไม่มีรายการรับ-จ่าย</p>
                            <?php else: ?>
                                <div class="transaction-list">
                                    <?php foreach ($transactions as $trans):
                                        $type_display = $trans['type'] === 'income' ? 'รายรับ' : 'รายจ่าย';
                                        $type_color = $trans['type'] === 'income' ? 'success' : 'danger';
                                        $type_icon = $trans['type'] === 'income' ? 'arrow-down' : 'arrow-up';
                                    ?>
                                        <div class="card mb-2 border">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-<?php echo $type_color; ?> me-2">
                                                                <i class="mdi mdi-<?php echo $type_icon; ?>-circle me-1"></i><?php echo $type_display; ?>
                                                            </span>
                                                            <small class="text-muted">
                                                                <i class="mdi mdi-calendar"></i> <?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?>
                                                            </small>
                                                        </div>
                                                        <h5 class="mb-1"><?php echo htmlspecialchars($trans['description']); ?></h5>
                                                        <p class="text-muted mb-0">
                                                            <i class="mdi mdi-tag-outline"></i>
                                                            หมวดหมู่: <?php echo htmlspecialchars($trans['category_name'] ?? 'ไม่ระบุ'); ?>
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="mdi mdi-account-circle"></i>
                                                            ผู้บันทึก: <?php echo htmlspecialchars($trans['recorded_by_name']); ?>
                                                        </small>
                                                    </div>
                                                    <div class="text-end ms-3">
                                                        <h4 class="mb-2 text-<?php echo $type_color; ?>">
                                                            <?php echo $trans['type'] === 'income' ? '+' : '-'; ?>฿<?php echo number_format($trans['amount'], 2); ?>
                                                        </h4>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-outline-warning"
                                                                onclick="editTransaction(<?php echo $trans['id']; ?>)"
                                                                title="แก้ไข">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteTransaction(<?php echo $trans['id']; ?>)"
                                                                title="ลบ">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </div>
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
    </div>
    <?php include '../includes/footer.php'; ?>
</div>
</div>

<?php include '../includes/modals/task_expense_add_modal.php'; ?>
<?php include '../includes/modals/task_expense_edit_modal.php'; ?>

<script>
    $(document).ready(function() {
        const taskId = <?php echo $task['id']; ?>;

        // Add expense
        $('#addExpenseForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '../api/transaction_add.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'บันทึกรายจ่ายเรียบร้อย',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                }
            });
        });

        // Edit expense form submit
        $('#editExpenseForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '../api/transaction_update.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editExpenseModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'แก้ไขรายจ่ายเรียบร้อย',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการแก้ไข', 'error');
                }
            });
        });

        // Task status actions
        $('#startTaskBtn').on('click', function() {
            updateTaskStatus('started', 'in_progress');
        });

        $('#submitTaskBtn').on('click', function() {
            Swal.fire({
                title: 'ส่งตรวจสอบงาน',
                input: 'textarea',
                inputPlaceholder: 'ระบุหมายเหตุในการส่งตรวจสอบ (ถ้ามี)',
                showCancelButton: true,
                confirmButtonText: 'ส่งตรวจสอบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateTaskStatus('submitted', 'under_review', result.value);
                }
            });
        });

        $('#approveTaskBtn').on('click', function() {
            Swal.fire({
                title: 'หมายเหตุการอนุมัติ',
                input: 'textarea',
                inputPlaceholder: 'ระบุหมายเหตุ (ถ้ามี)',
                showCancelButton: true,
                confirmButtonText: 'อนุมัติ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateTaskStatus('approved', 'completed', result.value);
                }
            });
        });

        $('#pauseTaskBtn').on('click', function() {
            Swal.fire({
                title: 'หยุดชั่วคราว',
                text: 'ต้องการหยุดงานชั่วคราวใช่หรือไม่?',
                icon: 'question',
                input: 'textarea',
                inputPlaceholder: 'ระบุเหตุผล (ถ้ามี)',
                showCancelButton: true,
                confirmButtonText: 'หยุดชั่วคราว',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateTaskStatus('paused', 'pending', result.value);
                }
            });
        });

        $('#rejectTaskBtn').on('click', function() {
            Swal.fire({
                title: 'เหตุผลในการส่งกลับแก้ไข',
                input: 'textarea',
                inputPlaceholder: 'ระบุเหตุผล',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาระบุเหตุผล';
                    }
                },
                showCancelButton: true,
                confirmButtonText: 'ส่งกลับแก้ไข',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateTaskStatus('rejected', 'in_progress', result.value);
                }
            });
        });

        $('#cancelTaskBtn').on('click', function() {
            Swal.fire({
                title: 'ยืนยันการยกเลิกงาน?',
                input: 'textarea',
                inputPlaceholder: 'ระบุเหตุผล',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'ยกเลิกงาน',
                cancelButtonText: 'ไม่ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateTaskStatus('cancelled', 'cancelled', result.value);
                }
            });
        });

        function updateTaskStatus(action, newStatus, message = '') {
            $.ajax({
                url: '../api/task_update_status.php',
                type: 'POST',
                data: {
                    task_id: taskId,
                    action_type: action,
                    status: newStatus,
                    message: message
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                }
            });
        }

        // Function to copy registration URL
        window.copyRegistrationUrl = function() {
            const urlInput = document.getElementById('registrationUrl');
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
        };

        // Load expense categories for edit modal
        window.expenseCategories = <?php echo json_encode($expense_categories); ?>;

        // Transaction functions
        window.editTransaction = function(transactionId) {
            // Fetch transaction data
            fetch('../api/transaction_get.php?id=' + transactionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.transaction) {
                        const trans = data.transaction;

                        // Populate modal
                        $('#edit_expense_id').val(trans.id);
                        $('#edit_expense_task_id').val(trans.task_id || '');
                        $('#edit_expense_description').val(trans.description);
                        $('#edit_expense_category').val(trans.category_id);
                        $('#edit_expense_amount').val(trans.amount);
                        $('#edit_expense_date').val(trans.transaction_date);
                        $('#edit_expense_notes').val(trans.notes || '');

                        // Show receipt info
                        if (trans.receipt_image) {
                            $('#edit_current_receipt').html('ไฟล์ปัจจุบัน: <a href="../assets/images/receipts/' + trans.receipt_image + '" target="_blank">' + trans.receipt_image + '</a>');
                        } else {
                            $('#edit_current_receipt').text('ยังไม่มีไฟล์แนบ');
                        }

                        // Show modal
                        $('#editExpenseModal').modal('show');
                    } else {
                        Swal.fire('ข้อผิดพลาด!', data.message || 'ไม่พบข้อมูล', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                });
        };

        window.deleteTransaction = function(transactionId) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/transaction_delete.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + transactionId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'ลบแล้ว!',
                                    text: 'รายการถูกลบเรียบร้อยแล้ว',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('ข้อผิดพลาด!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถลบได้', 'error');
                        });
                }
            });
        };
    });
</script>
</body>

</html>