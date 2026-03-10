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
requireRole(['admin', 'board', 'advisor']);

$db = new Database();
$conn = $db->getConnection();

// Enable DataTables
$include_datatables = true;

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$sql = "SELECT eb.*,
        e.name as equipment_name,
        e.serial_number,
        ec.name as category_name,
        CONCAT(prof_borrower.first_name_th, ' ', prof_borrower.last_name_th) as borrower_name,
        prof_borrower.profile_picture as borrower_picture,
        u_borrower.email as borrower_email,
        t.title as task_name,
        CONCAT(prof_approved.first_name_th, ' ', prof_approved.last_name_th) as approved_by_name,
        prof_approved.profile_picture as approved_picture
        FROM equipment_borrowing eb
        JOIN equipment e ON eb.equipment_id = e.id
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        JOIN users u_borrower ON eb.borrower_id = u_borrower.id
        JOIN profiles prof_borrower ON u_borrower.id = prof_borrower.user_id
        LEFT JOIN tasks t ON eb.task_id = t.id
        LEFT JOIN users u_approved ON eb.approved_by = u_approved.id
        LEFT JOIN profiles prof_approved ON u_approved.id = prof_approved.user_id
        WHERE eb.status IN ('pending', 'request_return')";

if (!empty($status_filter)) {
    $sql .= " AND eb.status = :status";
}
if (!empty($search)) {
    $sql .= " AND (e.name LIKE :search OR e.serial_number LIKE :search OR CONCAT(prof_borrower.first_name_th, ' ', prof_borrower.last_name_th) LIKE :search)";
}

$sql .= " ORDER BY
    CASE
        WHEN eb.status = 'pending' THEN 1
        WHEN eb.status = 'request_return' THEN 2
        ELSE 3
    END,
    eb.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($status_filter)) {
    $stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
}
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$borrowings = $stmt->fetchAll();

// Get counts by status
$sql = "SELECT status, COUNT(*) as count FROM equipment_borrowing GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->execute();
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}

$page_title = 'อนุมัติการยืม-คืนอุปกรณ์';
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
                        <h4 class="page-title">อนุมัติการยืม-คืนอุปกรณ์</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle me-2"></i>
                <?php
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle me-2"></i>
                <?php
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-clock-outline widget-icon bg-warning-lighten text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Pending">รอดำเนินการ</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['pending'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">รอการอนุมัติ</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-check-circle widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Approved">อนุมัติแล้ว</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['approved'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">ได้รับการอนุมัติ</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-package-variant widget-icon bg-primary-lighten text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Borrowed">กำลังยืม</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['borrowed'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">อุปกรณ์ที่ถูกยืม</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-check-all widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Returned">คืนแล้ว</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['returned'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-nowrap">คืนเรียบร้อยแล้ว</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">จัดการคำขอยืม-คืนอุปกรณ์</h4>

                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="statusFilter">
                                        <option value="">ทุกสถานะ</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                        <option value="request_return" <?php echo $status_filter === 'request_return' ? 'selected' : ''; ?>>ขอคืน</option>
                                        <option value="borrowed" <?php echo $status_filter === 'borrowed' ? 'selected' : ''; ?>>กำลังยืม</option>
                                        <option value="returned" <?php echo $status_filter === 'returned' ? 'selected' : ''; ?>>คืนแล้ว</option>
                                        <option value="overdue" <?php echo $status_filter === 'overdue' ? 'selected' : ''; ?>>เกินกำหนด</option>
                                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="searchInput"
                                           placeholder="ค้นหาชื่ออุปกรณ์, S/N, ชื่อผู้ยืม..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary" onclick="applyFilters()">
                                        <i class="mdi mdi-magnify"></i> ค้นหา
                                    </button>
                                </div>
                            </div>

                            <!-- Borrowing List -->
                            <div class="table-responsive">
                                <table class="table table-centered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ผู้ยืม</th>
                                            <th>อุปกรณ์</th>
                                            <th>วัตถุประสงค์</th>
                                            <th>วันที่ยืม</th>
                                            <th>กำหนดคืน</th>
                                            <th>สถานะ</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowings as $borrow): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/users/<?php echo $borrow['borrower_picture'] ?: 'avatar-1.jpg'; ?>"
                                                         class="rounded-circle avatar-xs me-2" alt="">
                                                    <div>
                                                        <h5 class="my-0 fw-normal"><?php echo htmlspecialchars($borrow['borrower_name']); ?></h5>
                                                        <small class="text-muted"><?php echo htmlspecialchars($borrow['borrower_email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($borrow['equipment_name']); ?></strong><br>
                                                <?php if ($borrow['serial_number']): ?>
                                                <small class="text-muted">S/N: <?php echo htmlspecialchars($borrow['serial_number']); ?></small><br>
                                                <?php endif; ?>
                                                <small class="badge bg-secondary"><?php echo htmlspecialchars($borrow['category_name']); ?></small>
                                            </td>
                                            <td>
                                                <div style="max-width: 200px;">
                                                    <?php echo htmlspecialchars($borrow['purpose']); ?>
                                                    <?php if ($borrow['task_name']): ?>
                                                    <br><small class="text-muted"><i class="mdi mdi-checkbox-marked-outline"></i> <?php echo htmlspecialchars($borrow['task_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($borrow['borrow_date'])); ?></td>
                                            <td>
                                                <?php
                                                $due_date = strtotime($borrow['due_date']);
                                                $today = strtotime(date('Y-m-d'));
                                                $is_overdue = ($due_date < $today && $borrow['status'] === 'borrowed');
                                                ?>
                                                <span class="<?php echo $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo date('d/m/Y', $due_date); ?>
                                                </span>
                                                <?php if ($is_overdue): ?>
                                                <br><small class="badge bg-danger">เกินกำหนด</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusBadges = [
                                                    'pending' => ['bg-warning text-dark', 'รอดำเนินการ'],
                                                    'approved' => ['bg-info', 'อนุมัติแล้ว'],
                                                    'borrowed' => ['bg-primary', 'กำลังยืม'],
                                                    'request_return' => ['bg-dark-lighten', 'ขอคืน'],
                                                    'returned' => ['bg-success', 'คืนแล้ว'],
                                                    'overdue' => ['bg-danger', 'เกินกำหนด'],
                                                    'cancelled' => ['bg-secondary', 'ยกเลิก']
                                                ];
                                                $badge = $statusBadges[$borrow['status']] ?? ['bg-secondary', 'ไม่ระบุ'];
                                                ?>
                                                <span class="badge <?php echo $badge[0]; ?>"><?php echo $badge[1]; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($borrow['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-success"
                                                            onclick="approveRequest(<?php echo $borrow['id']; ?>)"
                                                            title="อนุมัติ">
                                                        <i class="mdi mdi-check"></i> อนุมัติ
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                            onclick="rejectRequest(<?php echo $borrow['id']; ?>)"
                                                            title="ปฏิเสธ">
                                                        <i class="mdi mdi-close"></i> ปฏิเสธ
                                                    </button>
                                                    <?php elseif ($borrow['status'] === 'request_return'): ?>
                                                    <button class="btn btn-sm btn-success"
                                                            onclick="approveReturn(<?php echo $borrow['id']; ?>)"
                                                            title="อนุมัติการคืน">
                                                        <i class="mdi mdi-check-circle"></i> อนุมัติคืน
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                            onclick="rejectReturn(<?php echo $borrow['id']; ?>)"
                                                            title="ปฏิเสธการคืน">
                                                        <i class="mdi mdi-close-circle"></i> ปฏิเสธคืน
                                                    </button>
                                                    <?php elseif ($borrow['status'] === 'approved'): ?>
                                                    <button class="btn btn-sm btn-primary"
                                                            onclick="markAsBorrowed(<?php echo $borrow['id']; ?>)"
                                                            title="ส่งมอบอุปกรณ์">
                                                        <i class="mdi mdi-hand-extended"></i> ส่งมอบ
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <button class="btn btn-sm btn-outline-info"
                                                            onclick="viewDetails(<?php echo $borrow['id']; ?>)"
                                                            title="ดูรายละเอียด">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>

                                        <?php if (empty($borrowings)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="mdi mdi-information-outline me-2"></i>
                                                ไม่มีข้อมูล
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
    </div>
    <?php require_once '../includes/footer.php'; ?>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="mdi mdi-information-outline me-2"></i>รายละเอียดการยืม</h5>
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

<script>
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value;
    let url = 'equipment_approval.php?';
    if (status) url += 'status=' + status + '&';
    if (search) url += 'search=' + encodeURIComponent(search);
    window.location.href = url;
}

function approveRequest(id) {
    Swal.fire({
        title: 'ยืนยันการอนุมัติ?',
        text: 'คุณต้องการอนุมัติคำขอยืมอุปกรณ์นี้ใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ใช่, อนุมัติ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'approve');
        }
    });
}

function rejectRequest(id) {
    Swal.fire({
        title: 'ปฏิเสธคำขอ',
        text: 'กรุณาระบุเหตุผลในการปฏิเสธ:',
        input: 'textarea',
        inputPlaceholder: 'ระบุเหตุผล...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ปฏิเสธ',
        cancelButtonText: 'ยกเลิก',
        inputValidator: (value) => {
            if (!value) {
                return 'กรุณาระบุเหตุผล!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'reject', result.value);
        }
    });
}

function markAsBorrowed(id) {
    Swal.fire({
        title: 'ยืนยันการส่งมอบ?',
        text: 'คุณยืนยันว่าได้ส่งมอบอุปกรณ์ให้ผู้ยืมแล้ว?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ใช่, ส่งมอบแล้ว',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'borrow');
        }
    });
}

function approveReturn(id) {
    Swal.fire({
        title: 'อนุมัติการคืน?',
        text: 'คุณต้องการอนุมัติการคืนอุปกรณ์นี้ใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ใช่, อนุมัติการคืน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'approve');
        }
    });
}

function rejectReturn(id) {
    Swal.fire({
        title: 'ปฏิเสธการคืน',
        text: 'กรุณาระบุเหตุผลในการปฏิเสธการคืน:',
        input: 'textarea',
        inputPlaceholder: 'ระบุเหตุผล...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ปฏิเสธการคืน',
        cancelButtonText: 'ยกเลิก',
        inputValidator: (value) => {
            if (!value) {
                return 'กรุณาระบุเหตุผล!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'reject', result.value);
        }
    });
}

function markAsReturned(id) {
    if (confirm('ยืนยันการรับคืนอุปกรณ์?')) {
        updateBorrowingStatus(id, 'return');
    }
}

function updateBorrowingStatus(id, action, notes = '') {
    fetch('../api/equipment_approval_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: id,
            action: action,
            notes: notes
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message || 'ดำเนินการเรียบร้อยแล้ว',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: data.message || 'ไม่สามารถดำเนินการได้'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง'
        });
    });
}

function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    
    fetch('../api/equipment_borrowing_detail.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailModalBody').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('detailModalBody').innerHTML =
                '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
}

// Enter key to search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>

</body>
</html>
