<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/security.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// No pagination needed - DataTable will handle it

// Build query
$where_conditions = ['1=1'];
$params = [];

if (!empty($action_filter)) {
    $where_conditions[] = "al.action = ?";
    $params[] = $action_filter;
}
if ($user_filter > 0) {
    $where_conditions[] = "al.user_id = ?";
    $params[] = $user_filter;
}
if (!empty($date_from)) {
    $where_conditions[] = "DATE(al.created_at) >= ?";
    $params[] = $date_from;
}
if (!empty($date_to)) {
    $where_conditions[] = "DATE(al.created_at) <= ?";
    $params[] = $date_to;
}
if (!empty($search)) {
    $where_conditions[] = "(al.description LIKE ? OR al.ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(' AND ', $where_conditions);

// Get logs
$sql = "SELECT al.*,
        CONCAT(p.first_name_th, ' ', p.last_name_th) as user_name,
        p.profile_picture,
        u.email
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE $where_clause
        ORDER BY al.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get unique actions for filter
$sql = "SELECT DISTINCT action FROM activity_logs ORDER BY action";
$stmt = $conn->prepare($sql);
$stmt->execute();
$actions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get users for filter
$sql = "SELECT u.id, CONCAT(p.first_name_th, ' ', p.last_name_th) as name
        FROM users u
        JOIN profiles p ON u.id = p.user_id
        ORDER BY name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();

// Get statistics
$sql = "SELECT
        COUNT(*) as total_logs,
        COUNT(DISTINCT user_id) as total_users,
        COUNT(DISTINCT DATE(created_at)) as total_days
        FROM activity_logs";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats = $stmt->fetch();

$page_title = 'Activity Logs';
$include_datatables = true;
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
                        <h4 class="page-title"><?= htmlspecialchars($page_title) ?></h4>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-6 col-xl-4">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-chart-timeline-variant widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Total Activities">กิจกรรมทั้งหมด</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($stats['total_logs']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">จำนวนกิจกรรมในระบบ</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-account-multiple widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Active Users">ผู้ใช้งาน</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($stats['total_users']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">จำนวนผู้ใช้ที่มีกิจกรรม</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-calendar-month widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Days with Activity">วันที่มีกิจกรรม</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($stats['total_days']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">จำนวนวันที่มีการใช้งาน</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0"><?= htmlspecialchars($page_title) ?></h4>
                                <div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearLogs()">
                                        <i class="mdi mdi-delete-sweep"></i> ล้าง Logs
                                    </button>
                                </div>
                            </div>

                            <p class="text-muted mb-3">Use the search box and export buttons below to manage logs.</p>

                            <!-- Activity Table -->
                            <div class="table-responsive">
                                <table id="activity-logs-table" class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>IP Address</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td data-order="<?php echo strtotime($log['created_at']); ?>">
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($log['created_at'])); ?><br>
                                                    <?php echo date('H:i:s', strtotime($log['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($log['user_id']): ?>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $log['profile_picture'] ? '../assets/images/users/' . $log['profile_picture'] : '../assets/images/users/avatar-1.jpg'; ?>"
                                                         class="rounded-circle avatar-xs me-2" alt="">
                                                    <div>
                                                        <h6 class="my-0 fw-normal"><?php echo htmlspecialchars($log['user_name'] ?: 'Unknown'); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($log['email']); ?></small>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
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
                                                <span class="badge bg-<?php echo $color; ?>">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    <?php echo htmlspecialchars($log['description']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info"
                                                        onclick="viewDetails(<?php echo $log['id']; ?>)"
                                                        title="View Details">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>

                                        <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="mdi mdi-information-outline me-2"></i>
                                                No activity logs found
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

        </div>
        <!-- container -->

    </div>
    <!-- content -->

    <?php require_once '../includes/footer.php'; ?>

</div>
<!-- content-page -->

</div>
<!-- end wrapper -->

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="mdi mdi-information-outline me-2"></i>Activity Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/sidebar.php'; ?>

<script src="../assets/js/vendor.min.js"></script>
<script src="../assets/js/app.min.js"></script>

<!-- DataTables JS -->
<script src="../assets/js/vendor/jquery.dataTables.min.js"></script>
<script src="../assets/js/vendor/dataTables.bootstrap5.js"></script>
<script src="../assets/js/vendor/dataTables.buttons.min.js"></script>
<script src="../assets/js/vendor/buttons.bootstrap5.min.js"></script>
<script src="../assets/js/vendor/buttons.html5.min.js"></script>
<script src="../assets/js/vendor/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="../assets/js/vendor/dataTables.responsive.min.js"></script>
<script src="../assets/js/vendor/responsive.bootstrap5.min.js"></script>

<script>
function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    
    fetch('../api/activity_log_detail.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailModalBody').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('detailModalBody').innerHTML =
                '<div class="alert alert-danger">Error loading details</div>';
        });
}

function clearLogs() {
    Swal.fire({
        title: 'ยืนยันการล้าง Logs?',
        text: "การกระทำนี้ไม่สามารถย้อนกลับได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ล้างเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/activity_logs_clear.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'ล้างเรียบร้อย!',
                        'Activity Logs ถูกล้างแล้ว',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('ผิดพลาด!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาด: ' + error.message, 'error');
            });
        }
    });
}

$(document).ready(function() {
    $('#activity-logs-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        columnDefs: [
            { orderable: false, targets: 5 }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12 col-md-6"B>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="mdi mdi-content-copy"></i> Copy',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'csv',
                text: '<i class="mdi mdi-file-delimited"></i> CSV',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'excel',
                text: '<i class="mdi mdi-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="mdi mdi-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                },
                customize: function(doc) {
                    doc.defaultStyle.font = 'THSarabunNew';
                }
            },
            {
                extend: 'print',
                text: '<i class="mdi mdi-printer"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }
        ],
        responsive: true
    });
});
</script>

</body>
</html>
