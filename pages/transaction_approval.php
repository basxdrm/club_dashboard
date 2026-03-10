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
requireRole(['admin', 'board', 'advisor']);

$page_title = 'อนุมัติรายรับ-รายจ่าย';
$include_datatables = true;

try {
    $pdo = getDatabaseConnection();

    // Get pending transactions
    $stmt = $pdo->query("SELECT t.*, c.name as category_name, c.type as category_type, tk.title as task_name,
            CONCAT(up.prefix, ' ', up.first_name_th, ' ', up.last_name_th) as recorded_by_name
        FROM transactions t
        LEFT JOIN transaction_categories c ON t.category_id = c.id
        LEFT JOIN tasks tk ON t.task_id = tk.id
        LEFT JOIN users u ON t.recorded_by = u.id
        LEFT JOIN profiles up ON u.id = up.user_id
        WHERE t.status = 'pending'
        ORDER BY t.transaction_date DESC");
    $pending_transactions = $stmt->fetchAll();

    // Get approved transactions
    $stmt = $pdo->query("SELECT t.*, c.name as category_name, c.type as category_type, tk.title as task_name,
            CONCAT(up.prefix, ' ', up.first_name_th, ' ', up.last_name_th) as recorded_by_name,
            CONCAT(ua.prefix, ' ', ua.first_name_th, ' ', ua.last_name_th) as approved_by_name
        FROM transactions t
        LEFT JOIN transaction_categories c ON t.category_id = c.id
        LEFT JOIN tasks tk ON t.task_id = tk.id
        LEFT JOIN users u ON t.recorded_by = u.id
        LEFT JOIN profiles up ON u.id = up.user_id
        LEFT JOIN users ub ON t.approved_by = ub.id
        LEFT JOIN profiles ua ON ub.id = ua.user_id
        WHERE t.status IN ('approved', 'rejected')
        ORDER BY t.updated_at DESC
        LIMIT 20");
    $processed_transactions = $stmt->fetchAll();

    // Statistics
    $stats = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN status = 'pending' AND type = 'income' THEN amount ELSE 0 END) as pending_income,
        SUM(CASE WHEN status = 'pending' AND type = 'expense' THEN amount ELSE 0 END) as pending_expense
        FROM transactions")->fetch();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "เกิดข้อผิดพลาดในการดึงข้อมูล";
}

include '../includes/header.php';
include '../includes/topbar.php';
include '../includes/sidebar.php';
?>

<!-- ============================================================== -->
<!-- Start Page Content here -->
<!-- ============================================================== -->

<div class="content-page">
    <div class="content">

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title"><?php echo htmlspecialchars($page_title); ?></h4>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-clock-outline widget-icon bg-warning-lighten text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Pending">รออนุมัติ</h5>
                            <h3 class="mt-3 mb-3"><?= number_format($stats['pending_count']) ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">รอการอนุมัติ</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-check-circle widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Approved">อนุมัติแล้ว</h5>
                            <h3 class="mt-3 mb-3"><?= number_format($stats['approved_count']) ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">ได้รับการอนุมัติ</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-cash-plus widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Pending Income">รายรับรออนุมัติ</h5>
                            <h3 class="mt-3 mb-3">฿<?= number_format($stats['pending_income'], 2) ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">รายรับที่รออนุมัติ</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-cash-minus widget-icon bg-danger-lighten text-danger"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Pending Expense">รายจ่ายรออนุมัติ</h5>
                            <h3 class="mt-3 mb-3">฿<?= number_format($stats['pending_expense'], 2) ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">รายจ่ายที่รออนุมัติ</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Transactions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="mdi mdi-clock-outline text-warning me-2"></i>รายการรออนุมัติ
                                <span class="badge bg-warning ms-2"><?= count($pending_transactions) ?></span>
                            </h5>

                            <?php if (empty($pending_transactions)): ?>
                                <div class="text-center py-5">
                                    <i class="mdi mdi-check-all text-success" style="font-size: 48px;"></i>
                                    <p class="text-muted mt-3">ไม่มีรายการรออนุมัติ</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>วันที่</th>
                                                <th>ประเภท</th>
                                                <th>หมวดหมู่</th>
                                                <th>รายละเอียด</th>
                                                <th>งาน</th>
                                                <th class="text-end">จำนวนเงิน</th>
                                                <th>ผู้บันทึก</th>
                                                <th class="text-center">การดำเนินการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pending_transactions as $trans): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($trans['transaction_date'])) ?></td>
                                                    <td>
                                                        <?php if ($trans['type'] === 'income'): ?>
                                                            <span class="badge bg-success">รายรับ</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">รายจ่าย</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($trans['category_name']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($trans['description']) ?>
                                                        <?php if ($trans['receipt_image']): ?>
                                                            <a href="<?= htmlspecialchars($trans['receipt_image']) ?>" target="_blank" class="text-primary ms-2">
                                                                <i class="mdi mdi-image"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($trans['task_name']): ?>
                                                            <span class="badge bg-info"><?= htmlspecialchars($trans['task_name']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="<?= $trans['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                                            <?= $trans['type'] === 'income' ? '+' : '-' ?>฿<?= number_format($trans['amount'], 2) ?>
                                                        </strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($trans['recorded_by_name']) ?></td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-success me-1" onclick="approveTransaction(<?= $trans['id'] ?>)">
                                                            <i class="mdi mdi-check"></i> อนุมัติ
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="rejectTransaction(<?= $trans['id'] ?>)">
                                                            <i class="mdi mdi-close"></i> ปฏิเสธ
                                                        </button>
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

            <!-- Processed Transactions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="mdi mdi-history me-2"></i>ประวัติการอนุมัติ (20 รายการล่าสุด)
                            </h5>

                            <div class="table-responsive">
                                <table id="processedTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>วันที่</th>
                                            <th>ประเภท</th>
                                            <th>รายละเอียด</th>
                                            <th class="text-end">จำนวนเงิน</th>
                                            <th>สถานะ</th>
                                            <th>ผู้อนุมัติ</th>
                                            <th>วันที่อนุมัติ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($processed_transactions as $trans): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($trans['transaction_date'])) ?></td>
                                                <td>
                                                    <?php if ($trans['type'] === 'income'): ?>
                                                        <span class="badge bg-success">รายรับ</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">รายจ่าย</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($trans['description']) ?></td>
                                                <td class="text-end">
                                                    <strong class="<?= $trans['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                                        <?= $trans['type'] === 'income' ? '+' : '-' ?>฿<?= number_format($trans['amount'], 2) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php if ($trans['status'] === 'approved'): ?>
                                                        <span class="badge bg-success"><i class="mdi mdi-check"></i> อนุมัติ</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="mdi mdi-close"></i> ปฏิเสธ</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($trans['approved_by_name'] ?? '-') ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($trans['updated_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#processedTable').DataTable({
                    order: [
                        [7, 'desc']
                    ],
                    pageLength: 10,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                    }
                });
            });

            function approveTransaction(id) {
                Swal.fire({
                    title: 'ยืนยันการอนุมัติ',
                    text: 'คุณต้องการอนุมัติรายการนี้ใช่หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ใช่, อนุมัติ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processTransaction(id, 'approve');
                    }
                });
            }

            function rejectTransaction(id) {
                Swal.fire({
                    title: 'ยืนยันการปฏิเสธ',
                    text: 'คุณต้องการปฏิเสธรายการนี้ใช่หรือไม่?',
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'ระบุเหตุผล (ถ้ามี)',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ใช่, ปฏิเสธ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processTransaction(id, 'reject', result.value);
                    }
                });
            }

            function processTransaction(id, action, notes = '') {
                $.ajax({
                    url: '../api/transaction_approval_action.php',
                    type: 'POST',
                    data: {
                        transaction_id: id,
                        action: action,
                        notes: notes
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่อีกครั้ง'
                        });
                    }
                });
            }
        </script>

    </div> <!-- content -->
</div> <!-- content-page -->

<?php include '../includes/footer.php'; ?>