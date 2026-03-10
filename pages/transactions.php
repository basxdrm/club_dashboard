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

$page_title = 'รายรับ-รายจ่าย';
$include_datatables = true;

try {
    $pdo = getDatabaseConnection();
    $type_filter = $_GET['type'] ?? '';
    $category_filter = $_GET['category'] ?? '';

    $sql = "SELECT t.*, c.name as category_name, 
                tk.title as task_title, 
                p.name as project_name,
                CONCAT(up.prefix, ' ', up.first_name_th, ' ', up.last_name_th) as recorded_by_name
            FROM transactions t
            LEFT JOIN transaction_categories c ON t.category_id = c.id
            LEFT JOIN tasks tk ON t.task_id = tk.id
            LEFT JOIN projects p ON tk.project_id = p.id
            LEFT JOIN users u ON t.recorded_by = u.id
            LEFT JOIN profiles up ON u.id = up.user_id
            WHERE 1=1";

    $params = [];
    if ($type_filter) {
        $sql .= " AND t.type = ?";
        $params[] = $type_filter;
    }
    if ($category_filter) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_filter;
    }

    $sql .= " ORDER BY t.transaction_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // Summary
    $summary = $pdo->query("SELECT 
        SUM(CASE WHEN type = 'income' AND status = 'approved' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' AND status = 'approved' THEN amount ELSE 0 END) as total_expense,
        COUNT(CASE WHEN type = 'income' THEN 1 END) as income_count,
        COUNT(CASE WHEN type = 'expense' THEN 1 END) as expense_count,
        COUNT(*) as total_count
        FROM transactions")->fetch();

    $categories = $pdo->query("SELECT * FROM transaction_categories WHERE is_active = 1 ORDER BY type, name")->fetchAll();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $transactions = [];
    $summary = ['total_income' => 0, 'total_expense' => 0, 'income_count' => 0, 'expense_count' => 0, 'total_count' => 0];
    $categories = [];
}
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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="mdi mdi-plus-circle me-1"></i> บันทึกรายการ
                            </button>
                        </div>
                        <h4 class="page-title"><?php echo $page_title; ?></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-arrow-up-bold widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">รายรับทั้งหมด</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($summary['total_income'], 2); ?> ฿</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i></span>
                                <span class="text-nowrap">เงินเข้า</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-arrow-down-bold widget-icon bg-danger-lighten text-danger"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">รายจ่ายทั้งหมด</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($summary['total_expense'], 2); ?> ฿</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i></span>
                                <span class="text-nowrap">เงินออก</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-wallet widget-icon bg-primary-lighten text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">คงเหลือ</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($summary['total_income'] - $summary['total_expense'], 2); ?> ฿</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">ยอดคงเหลือสุทธิ</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-format-list-numbered widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">รายการทั้งหมด</h5>
                            <h3 class="mt-3 mb-3"><?php echo number_format($summary['total_count']); ?> รายการ</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">จำนวนธุรกรรม</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <select class="form-select" name="type">
                                        <option value="">ทุกประเภท</option>
                                        <option value="income" <?php echo $type_filter == 'income' ? 'selected' : ''; ?>>รายรับ</option>
                                        <option value="expense" <?php echo $type_filter == 'expense' ? 'selected' : ''; ?>>รายจ่าย</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="category">
                                        <option value="">ทุกหมวดหมู่</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo $cat['type'] == 'income' ? '📥' : '📤'; ?> <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-magnify"></i> ค้นหา</button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-centered table-striped dt-responsive nowrap w-100" id="transactions-table">
                                    <thead>
                                        <tr>
                                            <th>วันที่</th>
                                            <th>ประเภท</th>
                                            <th>หมวดหมู่</th>
                                            <th>รายละเอียด</th>
                                            <th>งาน</th>
                                            <th>จำนวนเงิน</th>
                                            <th>สถานะ</th>
                                            <th>ผู้บันทึก</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $trans):
                                            $statusClass = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                            $statusText = ['pending' => 'รออนุมัติ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ปฏิเสธ'];
                                        ?>
                                            <tr>
                                                <td data-order="<?php echo strtotime($trans['transaction_date']); ?>">
                                                    <?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $trans['type'] == 'income' ? 'success' : 'danger'; ?>">
                                                        <?php echo $trans['type'] == 'income' ? 'รายรับ' : 'รายจ่าย'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($trans['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars(mb_substr($trans['description'], 0, 50)); ?></td>
                                                <td><?php echo htmlspecialchars($trans['task_title'] ?? '-'); ?></td>
                                                <td class="text-<?php echo $trans['type'] == 'income' ? 'success' : 'danger'; ?> fw-bold">
                                                    <?php echo $trans['type'] == 'income' ? '+' : '-'; ?><?php echo number_format($trans['amount'], 2); ?> ฿
                                                </td>
                                                <td><span class="badge bg-<?php echo $statusClass[$trans['status']]; ?>"><?php echo $statusText[$trans['status']]; ?></span></td>
                                                <td><?php echo htmlspecialchars($trans['recorded_by_name']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick='viewTransaction(<?= json_encode($trans, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="ดูรายละเอียด">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                    <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                                        <button class="btn btn-sm btn-outline-warning" onclick='editTransaction(<?= json_encode($trans, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="แก้ไข">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTransaction(<?php echo $trans['id']; ?>)" title="ลบ">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
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
    </div>
    <?php include '../includes/footer.php'; ?>
</div>
</div>


<?php include '../includes/modals/transaction_add_modal.php'; ?>
<?php include '../includes/modals/transaction_edit_modal.php'; ?>
<?php include '../includes/modals/transaction_view_modal.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#transactions-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
            },
            order: [
                [0, 'desc']
            ]
        });
    });

    function deleteTransaction(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../api/transaction_delete.php',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: response.message,
                                confirmButtonText: 'ตกลง'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบรายการได้', 'error');
                    }
                });
            }
        });
    }
</script>
</body>

</html>