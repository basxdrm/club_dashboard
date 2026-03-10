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

$db = new Database();
$conn = $db->getConnection();

// Base path for modal
$base_path = '../';

// Enable DataTables
$include_datatables = true;

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT eb.*,
        e.name as equipment_name,
        ec.name as category_name,
        CONCAT(prof_borrower.first_name_th, ' ', prof_borrower.last_name_th) as borrower_name,
        prof_borrower.profile_picture as borrower_picture,
        t.title as task_name,
        CONCAT(prof_approved.first_name_th, ' ', prof_approved.last_name_th) as approved_by_name
        FROM equipment_borrowing eb
        JOIN equipment e ON eb.equipment_id = e.id
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        JOIN users u_borrower ON eb.borrower_id = u_borrower.id
        JOIN profiles prof_borrower ON u_borrower.id = prof_borrower.user_id
        LEFT JOIN tasks t ON eb.task_id = t.id
        LEFT JOIN users u_approved ON eb.approved_by = u_approved.id
        LEFT JOIN profiles prof_approved ON u_approved.id = prof_approved.user_id
        WHERE 1=1";

$params = [];

if ($status_filter) {
    $sql .= " AND eb.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $sql .= " AND (e.name LIKE ? OR CONCAT(prof_borrower.first_name_th, ' ', prof_borrower.last_name_th) LIKE ? OR eb.purpose LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY eb.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$borrowings = $stmt->fetchAll();

// Get counts by status
$sql = "SELECT status, COUNT(*) as count FROM equipment_borrowing GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->execute();
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}

// Get tasks for dropdown
$tasks_stmt = $conn->prepare("SELECT id, title FROM tasks WHERE status NOT IN ('completed', 'cancelled') ORDER BY title");
$tasks_stmt->execute();
$tasks = $tasks_stmt->fetchAll();

$page_title = 'รายการยืม-คืนอุปกรณ์';
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
                        <h4 class="page-title"><?php echo htmlspecialchars($page_title); ?></h4>
                    </div>
                </div>
            </div>



            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-clock-outline widget-icon bg-warning-lighten text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">รอดำเนินการ</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['pending'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-check-circle widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">อนุมัติแล้ว</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['approved'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-package-variant widget-icon bg-primary-lighten text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">กำลังยืม</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['borrowed'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-check-all widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">คืนแล้ว</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['returned'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-alert-circle widget-icon bg-danger-lighten text-danger"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">เกินกำหนด</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['overdue'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-close-circle widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0 text-truncate">ยกเลิก</h5>
                            <h3 class="mt-3 mb-3"><?php echo $status_counts['cancelled'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">รายการยืม-คืนอุปกรณ์</h4>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#borrowEquipmentModal">
                                    <i class="mdi mdi-hand-extended"></i> ขอยืมอุปกรณ์
                                </button>
                            </div>

                            <form method="GET" class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <select class="form-select" name="status" id="statusFilter">
                                        <option value="">ทุกสถานะ</option>
                                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>รอพิจารณา</option>
                                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>อนุมัติแล้ว</option>
                                        <option value="borrowed" <?php echo $status_filter == 'borrowed' ? 'selected' : ''; ?>>กำลังยืม</option>
                                        <option value="request_return" <?php echo $status_filter == 'request_return' ? 'selected' : ''; ?>>ขอคืน</option>
                                        <option value="returned" <?php echo $status_filter == 'returned' ? 'selected' : ''; ?>>คืนแล้ว</option>
                                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="search" id="searchInput" 
                                           placeholder="ค้นหา (ชื่ออุปกรณ์, ผู้ยืม, วัตถุประสงค์)" 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="mdi mdi-magnify"></i> กรอง
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-centered table-striped dt-responsive nowrap w-100" id="borrowingTable">
                                    <thead>
                                        <tr>
                                            <th>อุปกรณ์</th>
                                            <th>ผู้ยืม</th>
                                            <th>รายละเอียด</th>
                                            <th>วันที่ยืม</th>
                                            <th>กำหนดคืน</th>
                                            <th>สถานะ</th>
                                            <th>การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowings as $borrow): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($borrow['equipment_name']); ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/users/<?php echo $borrow['borrower_picture'] ?: 'avatar-1.jpg'; ?>"
                                                         alt="" class="rounded-circle me-2" width="32" height="32">
                                                    <?php echo htmlspecialchars($borrow['borrower_name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="max-width: 250px;">
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
                                                $now = time();
                                                $is_overdue = ($due_date < $now && $borrow['status'] === 'borrowed');
                                                ?>
                                                <span class="<?php echo $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo date('d/m/Y', strtotime($borrow['due_date'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_badges = [
                                                    'pending' => '<span class="badge bg-warning">รอดำเนินการ</span>',
                                                    'borrowed' => '<span class="badge bg-primary">กำลังยืม</span>',
                                                    'request_return' => '<span class="badge bg-info">ขอคืน</span>',
                                                    'returned' => '<span class="badge bg-success">คืนแล้ว</span>',
                                                    'cancelled' => '<span class="badge bg-secondary">ยกเลิก</span>'
                                                ];
                                                echo $status_badges[$borrow['status']] ?? '';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <?php if ($borrow['status'] === 'borrowed' && $borrow['borrower_id'] == $_SESSION['user_id']): ?>
                                                    <button class="btn btn-outline-warning btn-sm"
                                                            onclick="requestReturn(<?php echo $borrow['id']; ?>)"
                                                            title="คืนอุปกรณ์">
                                                        <i class="mdi mdi-keyboard-return"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <button class="btn btn-outline-info btn-sm"
                                                            onclick="viewDetails(<?php echo $borrow['id']; ?>)"
                                                            title="ดูรายละเอียด">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>

                                                    <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                                    <button class="btn btn-outline-primary btn-sm"
                                                            onclick="editBorrowing(<?php echo $borrow['id']; ?>)"
                                                            title="แก้ไข">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteBorrowing(<?php echo $borrow['id']; ?>)"
                                                            title="ลบ">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="mdi mdi-pencil me-2"></i>แก้ไขรายการยืม</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBorrowingForm">
                    <input type="hidden" id="edit_borrowing_id">
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select id="edit_status" class="form-select" required>
                            <option value="pending">รอดำเนินการ</option>
                            <option value="borrowed">กำลังยืม</option>
                            <option value="request_return">ขอคืน</option>
                            <option value="returned">คืนแล้ว</option>
                            <option value="cancelled">ยกเลิก</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่ยืม</label>
                        <input type="date" id="edit_borrow_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">กำหนดคืน</label>
                        <input type="date" id="edit_due_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">งานที่เกี่ยวข้อง (Task ID)</label>
                        <input type="number" id="edit_task_id" class="form-control" placeholder="ใส่ ID ของงาน (ถ้ามี)" onchange="loadTaskTitle(this.value)">
                        <small class="text-muted">ใส่เลข ID ของงาน หรือเว้นว่างถ้าไม่เกี่ยวข้องกับงาน</small>
                        <div id="task_title_display" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วัตถุประสงค์</label>
                        <textarea id="edit_purpose" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitEditBorrowing()">
                    <i class="mdi mdi-content-save"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/sidebar.php'; ?>

<script>
function requestReturn(id) {
    Swal.fire({
        title: 'ยืนยันการขอคืน?',
        text: 'คุณต้องการส่งคำขอคืนอุปกรณ์นี้ใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f1c40f',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ใช่, ขอคืนอุปกรณ์',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'request_return');
        }
    });
}

function markAsReturned(id) {
    Swal.fire({
        title: 'ยืนยันการรับคืน?',
        text: 'คุณยืนยันว่าได้รับคืนอุปกรณ์แล้ว?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ใช่, รับคืนแล้ว',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBorrowingStatus(id, 'return');
        }
    });
}

function updateBorrowingStatus(id, action) {
    fetch('../api/equipment_borrowing_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id, action: action })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.text(); // เปลี่ยนเป็น text ก่อนเพื่อดู error
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: data.message
                });
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                html: '<pre>' + text + '</pre>'
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

function editBorrowing(id) {
    // ดึงข้อมูลการยืมมาแสดงในฟอร์ม
    fetch('../api/equipment_borrowing_detail.php?id=' + id + '&format=json')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const borrow = data.borrowing;
                console.log('Borrowing data:', borrow); // Debug
                
                // เติมข้อมูลในฟอร์ม
                document.getElementById('edit_borrowing_id').value = id;
                document.getElementById('edit_status').value = borrow.status || '';
                
                // แปลงวันที่ให้เป็น format YYYY-MM-DD
                if (borrow.borrow_date) {
                    const borrowDate = new Date(borrow.borrow_date);
                    document.getElementById('edit_borrow_date').value = borrowDate.toISOString().split('T')[0];
                }
                
                if (borrow.due_date) {
                    const dueDate = new Date(borrow.due_date);
                    document.getElementById('edit_due_date').value = dueDate.toISOString().split('T')[0];
                }
                
                document.getElementById('edit_task_id').value = borrow.task_id || '';
                document.getElementById('edit_purpose').value = borrow.purpose || '';
                
                // โหลดชื่องานถ้ามี task_id
                if (borrow.task_id) {
                    loadTaskTitle(borrow.task_id);
                }
                
                // เปิด modal
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            } else {
                Swal.fire('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
        });
}

function submitEditBorrowing() {
    const data = {
        id: document.getElementById('edit_borrowing_id').value,
        status: document.getElementById('edit_status').value,
        borrow_date: document.getElementById('edit_borrow_date').value,
        due_date: document.getElementById('edit_due_date').value,
        task_id: document.getElementById('edit_task_id').value || null,
        purpose: document.getElementById('edit_purpose').value
    };
    
    fetch('../api/equipment_borrowing_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ปิด modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            modal.hide();
            
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('เกิดข้อผิดพลาด', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
    });
}

function loadTaskTitle(taskId) {
    const displayDiv = document.getElementById('task_title_display');
    
    if (!taskId || taskId == '') {
        displayDiv.innerHTML = '';
        return;
    }
    
    displayDiv.innerHTML = '<small class="text-muted"><i class="mdi mdi-loading mdi-spin"></i> กำลังโหลด...</small>';
    
    fetch('../api/task_get.php?id=' + taskId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const task = data.data;
                const statusText = {
                    'pending': 'รอดำเนินการ',
                    'in_progress': 'กำลังดำเนินการ',
                    'under_review': 'รอตรวจสอบ',
                    'completed': 'เสร็จสิ้น',
                    'cancelled': 'ยกเลิก'
                };
                const statusBadge = {
                    'pending': 'secondary',
                    'in_progress': 'primary',
                    'under_review': 'warning',
                    'completed': 'success',
                    'cancelled': 'danger'
                };
                displayDiv.innerHTML = `
                    <div class="alert alert-info py-2 mb-0">
                        <i class="mdi mdi-checkbox-marked-outline me-1"></i>
                        <strong>${task.title}</strong>
                        <span class="badge bg-${statusBadge[task.status] || 'secondary'} ms-2">${statusText[task.status] || task.status}</span>
                    </div>
                `;
            } else {
                displayDiv.innerHTML = '<small class="text-danger"><i class="mdi mdi-alert-circle-outline"></i> ไม่พบงานนี้</small>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayDiv.innerHTML = '<small class="text-danger"><i class="mdi mdi-alert-circle-outline"></i> เกิดข้อผิดพลาดในการโหลด</small>';
        });
}

function deleteBorrowing(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'คุณต้องการลบรายการยืมนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/equipment_borrowing_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบแล้ว!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถลบข้อมูลได้', 'error');
            });
        }
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

$(document).ready(function() {
    // Check if DataTable exists before using it
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#borrowingTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
            },
            order: [[4, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
    } else {
        console.warn('DataTables not loaded');
    }

    // Check if Swal exists before using it
    if (typeof Swal !== 'undefined') {
        // Show success/error messages with SweetAlert
        <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: '<?php echo addslashes($_SESSION['success_message']); ?>',
            confirmButtonText: 'ตรวจสอบ'
        });
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            html: '<?php echo addslashes($_SESSION['error_message']); ?>'
        });
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    } else {
        console.warn('SweetAlert2 not loaded');
        // Fallback to alert
        <?php if (isset($_SESSION['success_message'])): ?>
        alert('สำเร็จ: <?php echo addslashes($_SESSION['success_message']); ?>');
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
        alert('ข้อผิดพลาด: <?php echo addslashes($_SESSION['error_message']); ?>');
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    }
});
</script>

<?php include_once('../includes/modals/equipment_borrow_modal.php'); ?>

</body>
</html>
