<?php
/**
 * Session Management
 * จัดการ Session ของผู้ใช้ทั้งหมด
 */

session_start();

// กำหนดค่า APP_ACCESS
define('APP_ACCESS', true);

// รวมไฟล์ที่จำเป็น
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/security.php';

// ตรวจสอบสิทธิ์ (เฉพาะ admin/board)
requireRole(['admin', 'board', 'advisor']);

$pdo = getDatabaseConnection();

// จัดการการลบ session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'remove_session' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        
        try {
            removeSessionFromDatabase($user_id, $pdo);
            $success_message = "ลบ Session ของผู้ใช้ ID: {$user_id} สำเร็จ";
        } catch (Exception $e) {
            $error_message = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

// ดึงข้อมูล Session ทั้งหมด
try {
    $stmt = $pdo->prepare("
        SELECT 
            us.*,
            p.first_name_th,
            p.last_name_th,
            u.email,
            u.role
        FROM user_sessions us 
        LEFT JOIN users u ON us.user_id = u.id 
        LEFT JOIN profiles p ON u.id = p.user_id
        ORDER BY us.last_activity DESC
    ");
    $stmt->execute();
    $all_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "เกิดข้อผิดพลาดในการดึงข้อมูล Session: " . $e->getMessage();
    $all_sessions = [];
}

// ดึงสถิติ Session
try {
    $stats_stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_sessions,
            COUNT(DISTINCT user_id) as unique_users,
            MAX(last_activity) as latest_activity
        FROM user_sessions
    ");
    $stats_stmt->execute();
    $session_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $session_stats = [
        'total_sessions' => 0,
        'unique_users' => 0,
        'latest_activity' => null
    ];
}

$page_title = 'จัดการ Session';
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
                                <h4 class="page-title">จัดการ Session ผู้ใช้</h4>
                            </div>
                        </div>
                    </div>

                    <!-- แสดงข้อความแจ้งเตือน -->
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle-outline me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- สถิติ Session -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-account-multiple widget-icon bg-primary-lighten text-primary"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="Total Sessions">Session ทั้งหมด</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($session_stats['total_sessions']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">จำนวน Session ทั้งหมด</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-account-check widget-icon bg-success-lighten text-success"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="Unique Users">ผู้ใช้ที่เข้าระบบ</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($session_stats['unique_users']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">จำนวนผู้ใช้ที่เข้าสู่ระบบ</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-clock-outline widget-icon bg-info-lighten text-info"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="Latest Activity">กิจกรรมล่าสุด</h5>
                                    <h3 class="mt-3 mb-3">
                                        <?php 
                                        if ($session_stats['latest_activity']) {
                                            echo date('d/m/Y H:i', strtotime($session_stats['latest_activity']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">เวลาล่าสุดที่มีการใช้งาน</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ตาราง Session -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title">Session ทั้งหมด</h4>
                                        <button class="btn btn-warning" onclick="location.reload()">
                                            <i class="mdi mdi-refresh me-1"></i>รีเฟรช
                                        </button>
                                    </div>

                                    <?php if (empty($all_sessions)): ?>
                                        <div class="text-center py-4">
                                            <i class="mdi mdi-account-off h1 text-muted"></i>
                                            <p class="text-muted">ไม่มี Session ที่ใช้งานอยู่</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table id="sessions-table" class="table table-striped dt-responsive nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th>ผู้ใช้</th>
                                                        <th>อีเมล</th>
                                                        <th>บทบาท</th>
                                                        <th>IP Address</th>
                                                        <th>อุปกรณ์</th>
                                                        <th>เข้าระบบเมื่อ</th>
                                                        <th>ใช้งานล่าสุด</th>
                                                        <th>การจัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($all_sessions as $session): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars(($session['first_name_th'] ?? '') . ' ' . ($session['last_name_th'] ?? '')); ?></td>
                                                            <td><?php echo htmlspecialchars($session['email']); ?></td>
                                                            <td>
                                                                <?php
                                                                $role_colors = [
                                                                    'admin' => 'danger',
                                                                    'board' => 'warning',
                                                                    'member' => 'info'
                                                                ];
                                                                $color = $role_colors[$session['role']] ?? 'secondary';
                                                                ?>
                                                                <span class="badge bg-<?php echo $color; ?>">
                                                                    <?php echo ucfirst($session['role']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
                                                            <td>
                                                                <small title="<?php echo htmlspecialchars($session['user_agent']); ?>">
                                                                    <?php 
                                                                    $user_agent = $session['user_agent'];
                                                                    if (strpos($user_agent, 'Mobile') !== false) {
                                                                        echo '<i class="mdi mdi-cellphone"></i> มือถือ';
                                                                    } elseif (strpos($user_agent, 'Chrome') !== false) {
                                                                        echo '<i class="mdi mdi-google-chrome"></i> Chrome';
                                                                    } elseif (strpos($user_agent, 'Firefox') !== false) {
                                                                        echo '<i class="mdi mdi-firefox"></i> Firefox';
                                                                    } elseif (strpos($user_agent, 'Safari') !== false) {
                                                                        echo '<i class="mdi mdi-apple-safari"></i> Safari';
                                                                    } else {
                                                                        echo '<i class="mdi mdi-web"></i> อื่น ๆ';
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($session['created_at'])); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($session['last_activity'])); ?></td>
                                                            <td>
                                                                <?php if ($session['user_id'] != $_SESSION['user_id']): ?>
                                                                    <button class="btn btn-sm btn-danger" 
                                                                            onclick="removeUserSession(<?php echo $session['user_id']; ?>, '<?php echo htmlspecialchars(($session['first_name_th'] ?? '') . ' ' . ($session['last_name_th'] ?? '')); ?>')">
                                                                        <i class="mdi mdi-logout"></i> บังคับออก
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success">Session ปัจจุบัน</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    <!-- Form สำหรับลบ Session -->
    <form id="remove-session-form" method="POST" style="display: none;">
        <input type="hidden" name="action" value="remove_session">
        <input type="hidden" name="user_id" id="remove-user-id">
    </form>

    <?php include '../includes/footer.php'; ?>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#sessions-table').DataTable({
            "order": [[ 6, "desc" ]], // เรียงตาม last activity
            "pageLength": 25,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json"
            }
        });
    });

    function removeUserSession(userId, userName) {
        Swal.fire({
            title: 'ยืนยันการบังคับออก?',
            text: `คุณต้องการบังคับให้ ${userName} ออกจากระบบหรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, บังคับออก',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('remove-user-id').value = userId;
                document.getElementById('remove-session-form').submit();
            }
        });
    }
    </script>