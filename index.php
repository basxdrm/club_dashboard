<?php

/**
 * Dashboard Page
 * หน้า Dashboard หลัก - ต้อง Login ก่อน
 */

define('APP_ACCESS', true);

// ตั้งค่า Session ก่อน session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// เริ่ม Session
session_start();

// ตรวจสอบ Remember Me ถ้ายังไม่ล็อกอินหรือ session หมดอายุ
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    require_once 'config/database.php';
    require_once 'config/security.php';
    require_once 'includes/auth.php';

    $pdo = getDatabaseConnection();
    if (checkRememberMeToken($pdo)) {
        // Auto login สำเร็จ ให้รีเฟรชหน้า
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        // ไม่มี remember token หรือไม่ถูกต้อง ไป login
        header('Location: login.php');
        exit;
    }
} else {
    // มี session แต่ตรวจสอบว่า timeout หรือยัง
    require_once 'config/database.php';
    require_once 'config/security.php';
    require_once 'includes/auth.php';

    if (!isLoggedIn()) {
        // Session timeout - ลองใช้ remember token
        $pdo = getDatabaseConnection();
        if (checkRememberMeToken($pdo)) {
            // Auto login สำเร็จ ให้รีเฟรชหน้า
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            // ไม่มี remember token ไป login
            header('Location: login.php');
            exit;
        }
    }
}

// Load Configuration
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'includes/auth.php';

// ต้อง Login ก่อน (double check)
requireLogin();

// กำหนดค่าสำหรับหน้านี้
$page_title = "Dashboard";

// กำหนด vendor libraries ที่ต้องการใช้
$include_apexcharts = true;
$include_fullcalendar = true;
$include_select2 = true;

// Get statistics
$db = new Database();
$conn = $db->getConnection();

// Member stats
$sql = "SELECT COUNT(*) as total FROM users WHERE status = 1 AND role != 'advisor'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$total_members = $stmt->fetch()['total'];

// Project stats (filtered by academic year)
$year_filter = $_SESSION['selected_academic_year'] ?? null;
$sql = "SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'planning' THEN 1 ELSE 0 END) as planning,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'on_hold' THEN 1 ELSE 0 END) as on_hold
        FROM projects WHERE status != 'cancelled'";
if ($year_filter && $year_filter != 0) {
    $sql .= " AND academic_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$year_filter]);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}
$project_stats = $stmt->fetch();

// Task stats (filtered by academic year through projects)
$sql = "SELECT
        COUNT(*) as total,
        SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN t.status = 'under_review' THEN 1 ELSE 0 END) as review,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE 1=1";
if ($year_filter && $year_filter != 0) {
    $sql .= " AND t.academic_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$year_filter]);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}
$task_stats = $stmt->fetch();

// My assigned tasks (งานที่ได้รับมอบหมาย) - filtered by academic year
$user_id = $_SESSION['user_id'];
$sql = "SELECT t.*, p.name as project_name,
        CONCAT(creator.first_name_th, ' ', creator.last_name_th) as created_by_name
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        INNER JOIN task_assignments ta ON t.id = ta.task_id
        LEFT JOIN users u ON t.created_by = u.id
        LEFT JOIN profiles creator ON u.id = creator.user_id
        WHERE ta.user_id = ?
        AND t.status IN ('pending', 'in_progress')";
if ($year_filter && $year_filter != 0) {
    $sql .= " AND t.academic_year_id = ?";
}
$sql .= " ORDER BY t.due_date ASC LIMIT 10";
$stmt = $conn->prepare($sql);
if ($year_filter && $year_filter != 0) {
    $stmt->execute([$user_id, $year_filter]);
} else {
    $stmt->execute([$user_id]);
}
$my_assigned_tasks = $stmt->fetchAll();

// Equipment stats
$sql = "SELECT
        COUNT(*) as total,
        COUNT(*) as total_qty,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_qty,
        SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as borrowed_qty,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_qty
        FROM equipment";
$stmt = $conn->prepare($sql);
$stmt->execute();
$equipment_stats = $stmt->fetch();

// Finance stats
$sql = "SELECT
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions WHERE status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$finance_stats = $stmt->fetch();
$balance = $finance_stats['total_income'] - $finance_stats['total_expense'];

// Recent tasks (4 latest tasks)
$sql = "SELECT t.*, p.name as project_name,
        CONCAT(creator.first_name_th, ' ', creator.last_name_th) as created_by_name
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        LEFT JOIN users u ON t.created_by = u.id
        LEFT JOIN profiles creator ON u.id = creator.user_id
        ORDER BY t.created_at DESC
        LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->execute();
$recent_tasks = $stmt->fetchAll();

// Include header
include 'includes/header.php';

// Include sidebar
include 'includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">

        <?php include 'includes/topbar.php'; ?>

        <!-- Start Content-->
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Dashboard</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-account-group widget-icon bg-primary-lighten text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="สมาชิกทั้งหมด">สมาชิกทั้งหมด</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($total_members); ?></h3>
                            <p class="mb-0 text-muted">
                                <a href="pages/members.php" class="text-nowrap">ดูทั้งหมด</a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-folder-multiple widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="โปรเจค">โปรเจค</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($project_stats['total']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">
                                    <i class="mdi mdi-arrow-up-bold"></i> <?php echo $project_stats['in_progress']; ?>
                                </span>
                                <span class="text-nowrap">กำลังดำเนินการ</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-clipboard-check-outline widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="งาน">งานทั้งหมด</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($task_stats['total']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-warning me-2">
                                    <?php echo $task_stats['pending']; ?> รอทำ
                                </span>
                                <span class="text-success">
                                    <?php echo $task_stats['completed']; ?> เสร็จสิ้น
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-cash widget-icon bg-warning-lighten text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="การเงิน">งบประมาณคงเหลือ</h5>
                            <h3 class="mt-3 mb-3">฿<?php echo number_format($balance, 2); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">
                                    รายรับ ฿<?php echo number_format($finance_stats['total_income'], 0); ?>
                                </span>
                                <span class="text-danger">
                                    รายจ่าย ฿<?php echo number_format($finance_stats['total_expense'], 0); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <!-- My Assigned Tasks -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title"><i class="mdi mdi-account-check text-primary"></i> งานของฉัน</h4>
                                <a href="pages/tasks.php" class="btn btn-sm btn-link">ดูทั้งหมด</a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>งาน</th>
                                            <th>โปรเจค</th>
                                            <th>กำหนดส่ง</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($my_assigned_tasks)): ?>
                                            <?php foreach ($my_assigned_tasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <a href="pages/task_view.php?id=<?php echo $task['id']; ?>" class="text-dark fw-semibold">
                                                            <?php echo htmlspecialchars($task['title']); ?>
                                                        </a>
                                                        <?php if ($task['priority'] === 'high'): ?>
                                                            <span class="badge badge-danger-lighten badge-xs">ด่วน</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo htmlspecialchars($task['project_name'] ?? '-'); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $due_date = strtotime($task['due_date']);
                                                        $now = time();
                                                        $days_left = floor(($due_date - $now) / 86400);
                                                        $is_overdue = $days_left < 0;
                                                        $is_soon = $days_left >= 0 && $days_left <= 3;
                                                        ?>
                                                        <span class="<?php echo $is_overdue ? 'text-danger' : ($is_soon ? 'text-warning' : ''); ?>">
                                                            <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                                            <?php if ($is_overdue): ?>
                                                                <br><small>(เกิน <?php echo abs($days_left); ?> วัน)</small>
                                                            <?php elseif ($is_soon): ?>
                                                                <br><small>(เหลือ <?php echo $days_left; ?> วัน)</small>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_badges = [
                                                            'pending' => '<span class="badge bg-secondary">รอดำเนินการ</span>',
                                                            'in_progress' => '<span class="badge bg-primary">กำลังทำ</span>',
                                                            'under_review' => '<span class="badge bg-warning">รอตรวจ</span>'
                                                        ];
                                                        echo $status_badges[$task['status']] ?? '';
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline" style="font-size: 48px; opacity: 0.3;"></i>
                                                    <p class="mb-0 mt-2">ไม่มีงานที่ได้รับมอบหมาย</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Recent Tasks -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title"><i class="mdi mdi-new-box text-success"></i> งานที่เพิ่มล่าสุด</h4>
                                <a href="pages/tasks.php" class="btn btn-sm btn-link">ดูทั้งหมด</a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>งาน</th>
                                            <th>โปรเจค</th>
                                            <th>วันที่สร้าง</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_tasks)): ?>
                                            <?php foreach ($recent_tasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <a href="pages/task_view.php?id=<?php echo $task['id']; ?>" class="text-dark fw-semibold">
                                                            <?php echo htmlspecialchars($task['title']); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo htmlspecialchars($task['project_name'] ?? '-'); ?></small>
                                                    </td>
                                                    <td>
                                                        <small><?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_badges = [
                                                            'pending' => '<span class="badge bg-secondary">รอทำ</span>',
                                                            'in_progress' => '<span class="badge bg-primary">กำลังทำ</span>',
                                                            'under_review' => '<span class="badge bg-warning">รอตรวจ</span>',
                                                            'completed' => '<span class="badge bg-success">เสร็จสิ้น</span>'
                                                        ];
                                                        echo $status_badges[$task['status']] ?? '';
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="mdi mdi-clipboard-outline" style="font-size: 48px; opacity: 0.3;"></i>
                                                    <p class="mb-0 mt-2">ยังไม่มีงานในระบบ</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <!-- Task Status Bar Chart -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><i class="mdi mdi-chart-bar text-primary"></i> สรุปสถานะงาน</h4>
                            <div id="task-status-chart" class="apex-charts" style="min-height: 320px;"></div>
                            <div class="mt-3">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <p class="text-muted mb-1" style="font-size: 12px;">เสร็จสิ้น</p>
                                        <h4 class="mb-0" style="color: #0acf97;"><?php echo $task_stats['completed']; ?></h4>
                                        <small class="text-muted">
                                            <?php
                                            $completed_pct = $task_stats['total'] > 0 ? round(($task_stats['completed'] / $task_stats['total']) * 100, 1) : 0;
                                            echo $completed_pct . '%';
                                            ?>
                                        </small>
                                    </div>
                                    <div class="col-3">
                                        <p class="text-muted mb-1" style="font-size: 12px;">กำลังทำ</p>
                                        <h4 class="mb-0" style="color: #3688fc;"><?php echo $task_stats['in_progress']; ?></h4>
                                        <small class="text-muted">
                                            <?php
                                            $in_progress_pct = $task_stats['total'] > 0 ? round(($task_stats['in_progress'] / $task_stats['total']) * 100, 1) : 0;
                                            echo $in_progress_pct . '%';
                                            ?>
                                        </small>
                                    </div>
                                    <div class="col-3">
                                        <p class="text-muted mb-1" style="font-size: 12px;">รอตรวจ</p>
                                        <h4 class="mb-0" style="color: #ffc35a;"><?php echo $task_stats['review']; ?></h4>
                                        <small class="text-muted">
                                            <?php
                                            $review_pct = $task_stats['total'] > 0 ? round(($task_stats['review'] / $task_stats['total']) * 100, 1) : 0;
                                            echo $review_pct . '%';
                                            ?>
                                        </small>
                                    </div>
                                    <div class="col-3">
                                        <p class="text-muted mb-1" style="font-size: 12px;">รอดำเนินการ</p>
                                        <h4 class="mb-0" style="color: #fa5c7c;"><?php echo $task_stats['pending']; ?></h4>
                                        <small class="text-muted">
                                            <?php
                                            $pending_pct = $task_stats['total'] > 0 ? round(($task_stats['pending'] / $task_stats['total']) * 100, 1) : 0;
                                            echo $pending_pct . '%';
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <!-- Calendar with Monthly Tasks -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><i class="mdi mdi-calendar text-info"></i> ปฏิทินและงานของเดือน</h4>

                            <div class="row">
                                <div class="col-md-7">
                                    <div id="inline-calendar" class="datepicker-inline"></div>
                                </div>
                                <div class="col-md-5">
                                    <h6 class="text-muted mb-2">งานของเดือน <span id="current-month-name"></span></h6>
                                    <div style="max-height: 350px; overflow-y: auto;">
                                        <div id="monthly-task-list">
                                            <div class="text-center text-muted py-3">
                                                <small>กำลังโหลด...</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Stats -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><i class="mdi mdi-toolbox text-success"></i> สถิติอุปกรณ์</h4>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <i class="mdi mdi-package-variant text-info" style="font-size: 32px;"></i>
                                    <h3 class="mt-2"><?php echo number_format($equipment_stats['total']); ?></h3>
                                    <p class="text-muted mb-0">ประเภททั้งหมด</p>
                                </div>
                                <div class="col-md-3">
                                    <i class="mdi mdi-cube-outline text-primary" style="font-size: 32px;"></i>
                                    <h3 class="mt-2"><?php echo number_format($equipment_stats['total_qty']); ?></h3>
                                    <p class="text-muted mb-0">จำนวนทั้งหมด (ชิ้น)</p>
                                </div>
                                <div class="col-md-2">
                                    <i class="mdi mdi-check-circle text-success" style="font-size: 32px;"></i>
                                    <h3 class="mt-2 text-success"><?php echo number_format($equipment_stats['available_qty']); ?></h3>
                                    <p class="text-muted mb-0">พร้อมใช้งาน</p>
                                </div>
                                <div class="col-md-2">
                                    <i class="mdi mdi-account-clock text-warning" style="font-size: 32px;"></i>
                                    <h3 class="mt-2 text-warning"><?php echo number_format($equipment_stats['borrowed_qty']); ?></h3>
                                    <p class="text-muted mb-0">กำลังยืม</p>
                                </div>
                                <div class="col-md-2">
                                    <a href="pages/equipment.php" class="btn btn-primary mt-3">
                                        <i class="mdi mdi-eye"></i> ดูอุปกรณ์
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- container -->

    </div> <!-- content -->

    <?php include 'includes/footer.php'; ?>

</div>
<!-- END wrapper -->

<script>
    $(document).ready(function() {
        // Task Status Bar Chart
        var taskOptions = {
            series: [{
                name: 'จำนวนงาน',
                data: [
                    <?php echo $task_stats['completed']; ?>,
                    <?php echo $task_stats['in_progress']; ?>,
                    <?php echo $task_stats['review']; ?>,
                    <?php echo $task_stats['pending']; ?>
                ]
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    dataLabels: {
                        position: 'top'
                    },
                    distributed: true
                }
            },
            colors: ['#0acf97', '#3688fc', '#ffc35a', '#fa5c7c'],
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    const total = <?php echo $task_stats['total']; ?>;
                    const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                    return val + ' งาน (' + percentage + '%)';
                },
                offsetX: 0,
                style: {
                    fontSize: '13px',
                    fontWeight: 600,
                    colors: ['#fff']
                }
            },
            xaxis: {
                categories: ['เสร็จสิ้น', 'กำลังทำ', 'รอตรวจ', 'รอดำเนินการ'],
                labels: {
                    show: true,
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '13px',
                        fontWeight: 500
                    }
                }
            },
            grid: {
                borderColor: '#f1f3fa',
                padding: {
                    left: 10,
                    right: 15
                }
            },
            legend: {
                show: false
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        const total = <?php echo $task_stats['total']; ?>;
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return value + ' งาน (' + percentage + '%)';
                    }
                }
            }
        };

        var taskChart = new ApexCharts(document.querySelector("#task-status-chart"), taskOptions);
        taskChart.render();

        // Inline Calendar with Datepicker
        const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];

        // Load monthly tasks
        function loadMonthlyTasks(year, month) {
            $.ajax({
                url: 'api/calendar_api.php',
                method: 'GET',
                data: {
                    action: 'monthly',
                    year: year,
                    month: month
                },
                dataType: 'json',
                success: function(data) {
                    const monthName = thaiMonths[month - 1];
                    $('#current-month-name').text(monthName + ' ' + (year + 543));

                    if (data && data.length > 0) {
                        let html = '';
                        data.forEach(task => {
                            const statusClass = {
                                'pending': 'secondary',
                                'in_progress': 'primary',
                                'under_review': 'warning',
                                'completed': 'success'
                            } [task.status] || 'secondary';

                            const statusText = {
                                'pending': 'รอทำ',
                                'in_progress': 'กำลังทำ',
                                'under_review': 'รอตรวจ',
                                'completed': 'เสร็จสิ้น'
                            } [task.status] || task.status;

                            html += `
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="pages/task_view.php?id=${task.id}" class="text-dark">
                                                ${task.title}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="mdi mdi-calendar"></i> ${task.start_date ? task.start_date : task.end_date} - ${task.end_date}
                                        </small>
                                    </div>
                                    <span class="badge bg-${statusClass}">${statusText}</span>
                                </div>
                            </div>
                        `;
                        });
                        $('#monthly-task-list').html(html);
                    } else {
                        $('#monthly-task-list').html(`
                        <div class="text-center text-muted py-3">
                            <i class="mdi mdi-calendar-blank-outline" style="font-size: 32px; opacity: 0.3;"></i>
                            <p class="mb-0 mt-2 small">ไม่มีงานในเดือนนี้</p>
                        </div>
                    `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading monthly tasks:', error);
                    $('#monthly-task-list').html(`
                    <div class="text-center text-danger py-3">
                        <small>เกิดข้อผิดพลาดในการโหลดข้อมูล</small>
                    </div>
                `);
                }
            });
        }

        // Initialize datepicker
        $('#inline-calendar').datepicker({
            todayHighlight: true,
            format: "dd/mm/yyyy",
            language: "th",
            autoclose: false,
            todayBtn: false,
            clearBtn: false,
            defaultViewDate: {
                year: new Date().getFullYear(),
                month: new Date().getMonth()
            }
        }).on('changeMonth', function(e) {
            const viewDate = e.date || e.dates || new Date();
            const year = viewDate.getFullYear();
            const month = viewDate.getMonth() + 1;
            loadMonthlyTasks(year, month);
        });

        // Load current month tasks
        const now = new Date();
        loadMonthlyTasks(now.getFullYear(), now.getMonth() + 1);

        // Observer สำหรับดักจับการคลิกลูกศร prev/next
        const calendarElement = document.querySelector('#inline-calendar');
        if (calendarElement) {
            // ใช้ MutationObserver เพื่อดักจับการเปลี่ยนแปลง DOM
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'attributes') {
                        const switchButton = calendarElement.querySelector('.datepicker-switch');
                        if (switchButton) {
                            const monthYearText = switchButton.textContent.trim();
                            // ดึง year และ month จากข้อความ
                            const match = monthYearText.match(/(\w+)\s+(\d{4})/);
                            if (match) {
                                const monthName = match[1];
                                const year = parseInt(match[2]);
                                const monthMap = {
                                    'มกราคม': 1,
                                    'กุมภาพันธ์': 2,
                                    'มีนาคม': 3,
                                    'เมษายน': 4,
                                    'พฤษภาคม': 5,
                                    'มิถุนายน': 6,
                                    'กรกฎาคม': 7,
                                    'สิงหาคม': 8,
                                    'กันยายน': 9,
                                    'ตุลาคม': 10,
                                    'พฤศจิกายน': 11,
                                    'ธันวาคม': 12
                                };
                                const month = monthMap[monthName];
                                if (month && year) {
                                    loadMonthlyTasks(year, month);
                                }
                            }
                        }
                    }
                });
            });

            // เริ่ม observe
            setTimeout(() => {
                const datepickerDiv = calendarElement.querySelector('.datepicker');
                if (datepickerDiv) {
                    observer.observe(datepickerDiv, {
                        childList: true,
                        subtree: true,
                        attributes: false
                    });
                }
            }, 500);
        }
    });
</script>

</body>

</html>