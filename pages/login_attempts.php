<?php
/**
 * Login Attempts Page
 * หน้าสำหรับดู Login Attempts (Admin Only)
 */

define('APP_ACCESS', true);
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

// ตรวจสอบ Login และสิทธิ์ Admin
requireLogin();
requireRole('admin');

$pdo = getDatabaseConnection();

// ดึงสถิติ
$stats_query = "
    SELECT 
        COUNT(*) as total_attempts,
        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed,
        COUNT(DISTINCT email) as unique_users,
        COUNT(DISTINCT ip_address) as unique_ips
    FROM login_attempts
";
$stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);

$page_title = 'Login Attempts';
$include_datatables = true;
require_once '../includes/header.php';
?>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

<!-- ========== Left Sidebar Start ========== -->
<?php include_once('../includes/sidebar.php'); ?>
<!-- Left Sidebar End -->

<div class="content-page">
    <div class="content">

        <!-- Topbar Start -->
        <?php include_once('../includes/topbar.php'); ?>
        <!-- end Topbar -->
                
                <div class="container-fluid">
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                
                                <h4 class="page-title">
                                    <i class="mdi mdi-shield-lock-outline me-1"></i>
                                    <?= htmlspecialchars($page_title) ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0" title="Total Attempts">Total Attempts</h5>
                                            <h3 class="mt-3 mb-3"><?php echo number_format($stats['total_attempts']); ?></h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-primary-lighten rounded">
                                                <i class="mdi mdi-account-multiple-check font-20 text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-check-circle widget-icon bg-success-lighten text-success"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="Successful">สำเร็จ</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['successful']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i></span>
                                        <span class="text-nowrap">เข้าสู่ระบบสำเร็จ</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-close-circle widget-icon bg-danger-lighten text-danger"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="Failed">ล้มเหลว</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['failed']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i></span>
                                        <span class="text-nowrap">พยายามเข้าสู่ระบบล้มเหลว</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-ip-network widget-icon bg-info-lighten text-info"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="Unique IPs">Unique IPs</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['unique_ips']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">จำนวน IP ที่แตกต่างกัน</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Login Attempts Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title mb-0">Login Attempts Log</h4>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearLogs()">
                                            <i class="mdi mdi-delete-sweep me-1"></i>ล้างข้อมูล
                                        </button>
                                    </div>
                                    <p class="text-muted font-13 mb-4">
                                        รายการพยายามเข้าสู่ระบบทั้งหมด สามารถ export และล้างข้อมูลได้
                                    </p>
                                    
                                    <table id="login-attempts-table" class="table table-striped dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>เวลา</th>
                                                <th>ผู้ใช้งาน</th>
                                                <th>Username</th>
                                                <th>IP Address</th>
                                                <th>สถานะ</th>
                                                <th>สาเหตุ</th>
                                                <th>รายละเอียด</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "
                                                SELECT la.*,
                                                    u.id as user_id,
                                                    CONCAT(p.prefix, p.first_name_th, ' ', p.last_name_th) as full_name,
                                                    p.profile_picture
                                                FROM login_attempts la
                                                LEFT JOIN users u ON la.email = u.email
                                                LEFT JOIN profiles p ON u.id = p.user_id
                                                ORDER BY la.attempted_at DESC
                                            ";
                                            $stmt = $pdo->query($query);
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $status_class = $row['success'] == 1 ? 'success' : 'danger';
                                                $status_icon = $row['success'] == 1 ? 'check-circle' : 'close-circle';
                                                $status_text = $row['success'] == 1 ? 'สำเร็จ' : 'ล้มเหลว';
                                                
                                                $display_name = $row['full_name'] ?? $row['email'];
                                            ?>
                                            <tr>
                                                <td data-order="<?php echo strtotime($row['attempted_at']); ?>">
                                                    <div><?php echo date('d/m/Y', strtotime($row['attempted_at'])); ?></div>
                                                    <small class="text-muted"><?php echo date('H:i:s', strtotime($row['attempted_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($display_name); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><code><?php echo htmlspecialchars($row['ip_address']); ?></code></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                                        <i class="mdi mdi-<?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (!empty($row['user_agent'])) {
                                                        $user_agent = htmlspecialchars($row['user_agent']);
                                                        $short_agent = strlen($user_agent) > 50 ? substr($user_agent, 0, 50) . '...' : $user_agent;
                                                        echo '<span class="text-muted" title="' . $user_agent . '">' . $short_agent . '</span>';
                                                    } else {
                                                        echo '<span class="text-muted">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="showDetails(<?php echo $row['id']; ?>)">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
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
        <!-- content-page -->

    </div>
    <!-- end wrapper -->
    
<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียด Login Attempt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
            // Initialize DataTable
            $('#login-attempts-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                },
                pageLength: 25,
                order: [[0, 'desc']],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12 col-md-6"B>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="mdi mdi-content-copy me-1"></i> Copy',
                        className: 'btn btn-sm btn-outline-secondary',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="mdi mdi-file-delimited me-1"></i> CSV',
                        className: 'btn btn-sm btn-outline-secondary',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="mdi mdi-file-excel me-1"></i> Excel',
                        className: 'btn btn-sm btn-outline-success',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf me-1"></i> PDF',
                        className: 'btn btn-sm btn-outline-danger',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="mdi mdi-printer me-1"></i> Print',
                        className: 'btn btn-sm btn-outline-primary',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    }
                ],
                responsive: true
            });
        });
        
        function showDetails(id) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
            
            // Load details
            fetch('../api/login_attempt_detail.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('detailContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('detailContent').innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
                });
        }
        
        function clearLogs() {
            Swal.fire({
                title: 'ยืนยันการล้างข้อมูล?',
                text: "คุณจะไม่สามารถกู้คืนข้อมูลได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ล้างข้อมูล!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/login_attempts_clear.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
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
                                'ล้างข้อมูลสำเร็จ!',
                                data.message,
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'เกิดข้อผิดพลาด!',
                                data.message,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้: ' + error.message,
                            'error'
                        );
                    });
                }
            });
        }
</script>

<?php require_once '../includes/footer.php'; ?>
