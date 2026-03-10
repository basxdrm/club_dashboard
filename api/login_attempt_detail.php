<?php
/**
 * Login Attempt Detail Modal Content
 */

define('APP_ACCESS', true);
session_start();

require_once '../config/database.php';
require_once '../includes/auth.php';

// ตรวจสอบ Login และสิทธิ์ Admin
requireLogin();
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo '<div class="alert alert-danger">Invalid ID</div>';
    exit();
}

$pdo = getDatabaseConnection();

$query = "
    SELECT
        la.*,
        u.email,
        u.role,
        CONCAT(p.first_name_th, ' ', p.last_name_th) as full_name,
        p.profile_picture
    FROM login_attempts la
    LEFT JOIN users u ON la.user_id = u.id
    LEFT JOIN profiles p ON u.id = p.user_id
    WHERE la.id = ?
    LIMIT 1
";$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    echo '<div class="alert alert-danger">ไม่พบข้อมูล</div>';
    exit();
}

// Parse User Agent
function parseUserAgent($user_agent) {
    $browser = 'Unknown';
    $os = 'Unknown';
    
    // Detect Browser
    if (preg_match('/Edge\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Edge ' . $matches[1];
    } elseif (preg_match('/Edg\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Edge ' . $matches[1];
    } elseif (preg_match('/Chrome\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Chrome ' . $matches[1];
    } elseif (preg_match('/Firefox\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Firefox ' . $matches[1];
    } elseif (preg_match('/Safari\/([0-9\.]+)/', $user_agent, $matches) && !preg_match('/Chrome/', $user_agent)) {
        $browser = 'Safari ' . $matches[1];
    } elseif (preg_match('/MSIE ([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'IE ' . $matches[1];
    } elseif (preg_match('/Opera\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Opera ' . $matches[1];
    }
    
    // Detect OS
    if (preg_match('/Windows NT 10/i', $user_agent)) {
        $os = 'Windows 10';
    } elseif (preg_match('/Windows NT 6.3/i', $user_agent)) {
        $os = 'Windows 8.1';
    } elseif (preg_match('/Windows NT 6.2/i', $user_agent)) {
        $os = 'Windows 8';
    } elseif (preg_match('/Windows NT 6.1/i', $user_agent)) {
        $os = 'Windows 7';
    } elseif (preg_match('/Mac OS X/i', $user_agent)) {
        $os = 'macOS';
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $os = 'Linux';
    } elseif (preg_match('/Android/i', $user_agent)) {
        $os = 'Android';
    } elseif (preg_match('/iPhone|iPad|iPod/i', $user_agent)) {
        $os = 'iOS';
    }
    
    return ['browser' => $browser, 'os' => $os];
}

$ua_info = parseUserAgent($attempt['user_agent']);
$avatar = !empty($attempt['profile_picture']) ? '../assets/images/users/' . $attempt['profile_picture'] : '../assets/images/users/avatar-1.jpg';
$status_class = $attempt['status'] === 'success' ? 'success' : 'danger';
$status_icon = $attempt['status'] === 'success' ? 'check-circle' : 'close-circle';
$status_text = $attempt['status'] === 'success' ? 'สำเร็จ' : 'ล้มเหลว';
?>

<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3">ข้อมูลผู้ใช้งาน</h5>
        <div class="d-flex align-items-center mb-3">
            <img src="<?php echo $avatar; ?>" alt="avatar" class="rounded-circle me-3" width="64" height="64">
            <div>
                <h5 class="mb-1"><?php echo htmlspecialchars($attempt['full_name'] ?: 'ไม่ระบุชื่อ'); ?></h5>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($attempt['email'] ?: '-'); ?></p>
                <?php if ($attempt['role']): ?>
                <span class="badge bg-info"><?php echo ucfirst($attempt['role']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="table table-sm">
            <tr>
                <th width="40%">Username:</th>
                <td><?php echo htmlspecialchars($attempt['username']); ?></td>
            </tr>
            <tr>
                <th>User ID:</th>
                <td><?php echo $attempt['user_id'] ?: '<span class="text-muted">N/A</span>'; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h5 class="mb-3">รายละเอียดการพยายาม Login</h5>
        <table class="table table-sm">
            <tr>
                <th width="40%">สถานะ:</th>
                <td>
                    <span class="badge bg-<?php echo $status_class; ?>">
                        <i class="mdi mdi-<?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>สาเหตุ:</th>
                <td>
                    <?php 
                    if ($attempt['failure_reason']) {
                        echo '<span class="text-danger">' . htmlspecialchars($attempt['failure_reason']) . '</span>';
                    } else {
                        echo '<span class="text-muted">-</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>เวลา:</th>
                <td><?php echo date('d/m/Y H:i:s', strtotime($attempt['created_at'])); ?></td>
            </tr>
            <tr>
                <th>IP Address:</th>
                <td><code><?php echo htmlspecialchars($attempt['ip_address']); ?></code></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5 class="mb-3">ข้อมูล User Agent</h5>
        <table class="table table-sm">
            <tr>
                <th width="20%">Browser:</th>
                <td>
                    <i class="mdi mdi-web me-1"></i>
                    <?php echo htmlspecialchars($ua_info['browser']); ?>
                </td>
            </tr>
            <tr>
                <th>Operating System:</th>
                <td>
                    <i class="mdi mdi-laptop me-1"></i>
                    <?php echo htmlspecialchars($ua_info['os']); ?>
                </td>
            </tr>
            <tr>
                <th>Full User Agent:</th>
                <td><small class="text-muted"><?php echo htmlspecialchars($attempt['user_agent']); ?></small></td>
            </tr>
        </table>
    </div>
</div>
