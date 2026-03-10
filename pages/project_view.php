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

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($project_id <= 0) {
    header('Location: projects.php');
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Get project details
$sql = "SELECT p.*,
        CONCAT(prof_creator.prefix, prof_creator.first_name_th, ' ', prof_creator.last_name_th) as creator_name,
        prof_creator.profile_picture as creator_picture
        FROM projects p
        LEFT JOIN users u_creator ON p.created_by = u_creator.id
        LEFT JOIN profiles prof_creator ON u_creator.id = prof_creator.user_id
        WHERE p.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: projects.php');
    exit;
}

// Get project tasks
$sql = "SELECT t.*,
        CONCAT(prof_creator.prefix, prof_creator.first_name_th, ' ', prof_creator.last_name_th) as creator_name
        FROM tasks t
        LEFT JOIN users u_creator ON t.created_by = u_creator.id
        LEFT JOIN profiles prof_creator ON u_creator.id = prof_creator.user_id
        WHERE t.project_id = ?
        ORDER BY t.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$project_id]);
$tasks = $stmt->fetchAll();

// Calculate task statistics
$total_tasks = count($tasks);
$completed_tasks = 0;
$in_progress_tasks = 0;

foreach ($tasks as $task) {
    if ($task['status'] === 'completed') {
        $completed_tasks++;
    } elseif ($task['status'] === 'กำลังดำเนินการ' || $task['status'] === 'รอตรวจสอบ') {
        $in_progress_tasks++;
    }
}

$progress_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 2) : 0;

// Get project expenses (รายจ่าย) from transactions of tasks in this project
$sql = "SELECT SUM(tr.amount) as total_expenses
        FROM transactions tr
        JOIN tasks t ON tr.task_id = t.id
        WHERE t.project_id = ? AND tr.type = 'expense'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$project_id]);
$total_expenses = $stmt->fetch()['total_expenses'] ?? 0;

// Calculate remaining budget
$remaining_budget = $project['budget'] - $total_expenses;

// Get detailed transactions from tasks in this project
$sql = "SELECT tr.*, tr.type, t.title as task_name,
        CONCAT(prof.prefix, prof.first_name_th, ' ', prof.last_name_th) as recorded_by_name
        FROM transactions tr
        JOIN tasks t ON tr.task_id = t.id
        LEFT JOIN users u ON tr.recorded_by = u.id
        LEFT JOIN profiles prof ON u.id = prof.user_id
        WHERE t.project_id = ?
        ORDER BY tr.transaction_date DESC, tr.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$project_id]);
$transactions = $stmt->fetchAll();

$page_title = 'รายละเอียดโปรเจค';
$include_datatables = true;
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
                                <li class="breadcrumb-item"><a href="projects.php">โปรเจค</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($page_title) ?></li>
                            </ol>
                        </div>
                        <h4 class="page-title"><?= htmlspecialchars($page_title) ?></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Project Details Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?= htmlspecialchars($project['name']) ?></h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>สถานะ:</strong>
                                        <span class="badge bg-<?php
                                                                echo $project['status'] === 'active' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'secondary');
                                                                ?>">
                                            <?php
                                            $status_map = [
                                                'active' => 'ดำเนินการ',
                                                'completed' => 'เสร็จสิ้น',
                                                'cancelled' => 'ยกเลิก'
                                            ];
                                            echo $status_map[$project['status']] ?? $project['status'];
                                            ?>
                                        </span>
                                    </p>

                                    <p class="mb-2"><strong>สร้างโดย:</strong>
                                        <?php echo htmlspecialchars($project['creator_name'] ?? '-'); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>วันที่เริ่ม:</strong>
                                        <?php echo $project['start_date'] ? date('d/m/Y', strtotime($project['start_date'])) : '-'; ?>
                                    </p>
                                    
                                    <p class="mb-2"><strong>วันที่สิ้นสุด:</strong>
                                        <?php echo $project['end_date'] ? date('d/m/Y', strtotime($project['end_date'])) : '-'; ?>
                                    </p>

                                    <p class="mb-2"><strong>สถานที่:</strong>
                                        <?php echo htmlspecialchars($project['location'] ?: '-'); ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($project['description']): ?>
                                <hr class="my-3">
                                <p class="mb-0"><strong>คำอธิบาย:</strong></p>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Project Tasks Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">งานในโปรเจค</h4>

                            <?php if (empty($tasks)): ?>
                                <p class="text-muted">ยังไม่มีงานในโปรเจคนี้</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($tasks as $task): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h5>
                                                    <div class="mb-2">
                                                        <span class="badge bg-<?php
                                                                                echo $task['status'] === 'เสร็จสิ้น' ? 'success' : ($task['status'] === 'กำลังดำเนินการ' ? 'primary' : ($task['status'] === 'รอตรวจสอบ' ? 'warning' : 'info'));
                                                                                ?>"><?php echo htmlspecialchars($task['status']); ?></span>

                                                        <span class="badge bg-<?php
                                                                                echo $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary');
                                                                                ?>"><?php
                                                                $priority_map = ['low' => 'ต่ำ', 'medium' => 'กลาง', 'high' => 'สูง', 'urgent' => 'ด่วนมาก'];
                                                                echo $priority_map[$task['priority']] ?? $task['priority'];
                                                                ?></span>
                                                    </div>
                                                    <p class="text-muted mb-0 small">
                                                        ผู้มอบหมาย: <?php echo htmlspecialchars($task['creator_name']); ?>
                                                    </p>
                                                </div>
                                                <div class="text-end ms-3">
                                                    <p class="text-muted mb-2 small">
                                                        ครบกำหนด: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                                    </p>
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <?php if (in_array($task['assignment_mode'], ['registration', 'hybrid']) && !empty($task['registration_link'])): ?>
                                                            <a href="task_register.php?token=<?php echo htmlspecialchars($task['registration_link']); ?>" class="btn btn-sm btn-outline-success" target="_blank" title="ลงทะเบียน">
                                                                <i class="mdi mdi-link"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="task_view.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($task['description']): ?>
                                                <p class="text-muted mb-0 small"><?php echo htmlspecialchars(mb_substr($task['description'], 0, 100)); ?><?php echo mb_strlen($task['description']) > 100 ? '...' : ''; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Progress Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">ความคืบหน้า</h4>

                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <p class="text-muted mb-1 text-truncate">งานทั้งหมด</p>
                                    <h3 class="mb-0"><?php echo $total_tasks; ?></h3>
                                </div>
                                <div class="col-4">
                                    <p class="text-muted mb-1 text-truncate">เสร็จแล้ว</p>
                                    <h3 class="mb-0 text-success"><?php echo $completed_tasks; ?></h3>
                                </div>
                                <div class="col-4">
                                    <p class="text-muted mb-1 text-truncate">ยังไม่เสร็จ</p>
                                    <h3 class="mb-0 text-warning"><?php echo $total_tasks - $completed_tasks; ?></h3>
                                </div>
                            </div>

                            <h5 class="mb-2"><?php echo $progress_percentage; ?>% เสร็จสมบูรณ์</h5>
                            <div class="progress progress-md mb-0">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: <?php echo $progress_percentage; ?>%"
                                    aria-valuenow="<?php echo $progress_percentage; ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Summary Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">สรุปงบประมาณ</h4>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="mdi mdi-cash-multiple text-primary"></i> งบประมาณ</span>
                                    <h4 class="mb-0 text-primary">฿<?php echo number_format($project['budget'], 2); ?></h4>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="mdi mdi-arrow-up-circle text-danger"></i> รายจ่าย</span>
                                    <h4 class="mb-0 text-danger">฿<?php echo number_format($total_expenses, 2); ?></h4>
                                </div>

                                <hr class="my-2">

                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>คงเหลือ</strong>
                                    <h4 class="mb-0 text-<?php echo $remaining_budget >= 0 ? 'success' : 'danger'; ?>">
                                        ฿<?php echo number_format($remaining_budget, 2); ?>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions List Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">รายการรายรับรายจ่าย</h4>

                            <?php if (empty($transactions)): ?>
                                <p class="text-muted">ยังไม่มีรายการรายรับรายจ่าย</p>
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
                                                        <?php if ($trans['task_name']): ?>
                                                            <p class="text-muted mb-0">
                                                                <i class="mdi mdi-checkbox-marked-circle-outline"></i> 
                                                                งาน: <?php echo htmlspecialchars($trans['task_name']); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-end">
                                                        <h4 class="mb-0 text-<?php echo $type_color; ?>">
                                                            <?php echo $trans['type'] === 'income' ? '+' : '-'; ?>฿<?php echo number_format($trans['amount'], 2); ?>
                                                        </h4>
                                                        <small class="text-muted"><?php echo htmlspecialchars($trans['recorded_by_name']); ?></small>
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

</body>

</html>