<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit();
}

$id = intval($_GET['id']);

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT al.*, 
        CONCAT(p.first_name_th, ' ', p.last_name_th) as user_name,
        p.profile_picture,
        u.email,
        u.role
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE al.id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$log = $stmt->fetch();

if (!$log) {
    echo '<div class="alert alert-danger">Activity log not found</div>';
    exit();
}
?>

<div class="row">
    <div class="col-md-6">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">User Information</h5>
        <?php if ($log['user_id']): ?>
        <div class="d-flex align-items-center mb-3">
            <img src="<?php echo $log['profile_picture'] ? '../assets/images/users/' . $log['profile_picture'] : '../assets/images/users/avatar-1.jpg'; ?>"
                 class="rounded-circle avatar-lg me-3" alt="">
            <div>
                <h4 class="mb-1"><?php echo htmlspecialchars($log['user_name'] ?: 'Unknown'); ?></h4>
                <p class="text-muted mb-0">
                    <i class="mdi mdi-email-outline"></i> <?php echo htmlspecialchars($log['email']); ?>
                </p>
                <p class="text-muted mb-0">
                    <i class="mdi mdi-shield-account"></i> <?php echo ucfirst($log['role']); ?>
                </p>
            </div>
        </div>
        <?php else: ?>
        <p class="text-muted"><i class="mdi mdi-robot"></i> System Action</p>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Activity Information</h5>
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <th style="width: 120px;">Action:</th>
                <td>
                    <?php
                    $actionColors = [
                        'login' => 'success',
                        'logout' => 'secondary',
                        'create' => 'primary',
                        'update' => 'info',
                        'delete' => 'danger',
                        'approve' => 'success',
                        'reject' => 'warning'
                    ];
                    
                    $color = 'secondary';
                    foreach ($actionColors as $key => $val) {
                        if (stripos($log['action'], $key) !== false) {
                            $color = $val;
                            break;
                        }
                    }
                    ?>
                    <span class="badge bg-<?php echo $color; ?> fs-6"><?php echo htmlspecialchars($log['action']); ?></span>
                </td>
            </tr>
            <tr>
                <th>Date/Time:</th>
                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
            </tr>
            <tr>
                <th>IP Address:</th>
                <td><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Description</h5>
        <p><?php echo nl2br(htmlspecialchars($log['description'])); ?></p>
    </div>
</div>

<?php if ($log['user_agent']): ?>
<div class="row mt-3">
    <div class="col-12">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">User Agent</h5>
        <div class="alert alert-info">
            <small><code><?php echo htmlspecialchars($log['user_agent']); ?></code></small>
        </div>
        
        <?php
        // Parse user agent for better display
        $ua = $log['user_agent'];
        $browser = 'Unknown';
        $os = 'Unknown';
        
        // Detect browser
        if (preg_match('/MSIE|Trident/i', $ua)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edge/i', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Safari/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $ua)) {
            $browser = 'Opera';
        }
        
        // Detect OS
        if (preg_match('/Windows NT 10/i', $ua)) {
            $os = 'Windows 10';
        } elseif (preg_match('/Windows NT 6.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.2/i', $ua)) {
            $os = 'Windows 8';
        } elseif (preg_match('/Windows NT 6.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad/i', $ua)) {
            $os = 'iOS';
        }
        ?>
        
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1"><strong>Browser:</strong> <i class="mdi mdi-web"></i> <?php echo $browser; ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Operating System:</strong> <i class="mdi mdi-laptop"></i> <?php echo $os; ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
