<?php
/**
 * Admin Categories Management
 * หน้าจัดการหมวดหมู่ (Admin Only)
 */

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

// ดึงข้อมูลหมวดหมู่อุปกรณ์
$equipment_stmt = $conn->prepare("SELECT * FROM equipment_categories ORDER BY name ASC");
$equipment_stmt->execute();
$equipment_categories = $equipment_stmt->fetchAll();

// ดึงข้อมูลหมวดหมู่รายรับรายจ่าย
$transaction_stmt = $conn->prepare("SELECT * FROM transaction_categories ORDER BY type ASC, name ASC");
$transaction_stmt->execute();
$transaction_categories = $transaction_stmt->fetchAll();

$page_title = 'จัดการหมวดหมู่';
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
                        <h4 class="page-title"><?= htmlspecialchars($page_title) ?></h4>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs nav-bordered mb-3">
                <li class="nav-item">
                    <a href="#equipment-categories" data-bs-toggle="tab" aria-expanded="false" class="nav-link active">
                        <i class="mdi mdi-package-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">หมวดหมู่อุปกรณ์</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#transaction-categories" data-bs-toggle="tab" aria-expanded="true" class="nav-link">
                        <i class="mdi mdi-cash-multiple d-md-none d-block"></i>
                        <span class="d-none d-md-block">หมวดหมู่รายรับรายจ่าย</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Equipment Categories Tab -->
                <div class="tab-pane show active" id="equipment-categories">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">หมวดหมู่อุปกรณ์</h5>
                                        <button class="btn btn-primary btn-sm" onclick="addEquipmentCategory()">
                                            <i class="mdi mdi-plus"></i> เพิ่มหมวดหมู่
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ชื่อหมวดหมู่</th>
                                                    <th>คำอธิบาย</th>
                                                    <th>จำนวนอุปกรณ์</th>
                                                    <th class="text-center">การดำเนินการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($equipment_categories)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">ไม่มีข้อมูล</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($equipment_categories as $cat): 
                                                        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM equipment WHERE category_id = ?");
                                                        $count_stmt->execute([$cat['id']]);
                                                        $equipment_count = $count_stmt->fetchColumn();
                                                    ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                                            <td><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                                                            <td>
                                                                <span class="badge bg-info"><?= $equipment_count ?> รายการ</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <button class="btn btn-sm btn-warning" onclick='editEquipmentCategory(<?= json_encode($cat) ?>)'>
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </button>
                                                                <?php if ($equipment_count == 0): ?>
                                                                    <button class="btn btn-sm btn-danger" onclick="deleteEquipmentCategory(<?= $cat['id'] ?>)">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="btn btn-sm btn-secondary" disabled title="ไม่สามารถลบได้เนื่องจากมีอุปกรณ์ในหมวดหมู่นี้">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Categories Tab -->
                <div class="tab-pane" id="transaction-categories">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">หมวดหมู่รายรับรายจ่าย</h5>
                                        <button class="btn btn-primary btn-sm" onclick="addTransactionCategory()">
                                            <i class="mdi mdi-plus"></i> เพิ่มหมวดหมู่
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ชื่อหมวดหมู่</th>
                                                    <th>ประเภท</th>
                                                    <th>คำอธิบาย</th>
                                                    <th>จำนวนรายการ</th>
                                                    <th class="text-center">การดำเนินการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($transaction_categories)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">ไม่มีข้อมูล</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($transaction_categories as $cat): 
                                                        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ?");
                                                        $count_stmt->execute([$cat['id']]);
                                                        $transaction_count = $count_stmt->fetchColumn();
                                                    ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                                            <td>
                                                                <?php if ($cat['type'] === 'income'): ?>
                                                                    <span class="badge bg-success">รายรับ</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">รายจ่าย</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                                                            <td>
                                                                <span class="badge bg-info"><?= $transaction_count ?> รายการ</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <button class="btn btn-sm btn-warning" onclick='editTransactionCategory(<?= json_encode($cat) ?>)'>
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </button>
                                                                <?php if ($transaction_count == 0): ?>
                                                                    <button class="btn btn-sm btn-danger" onclick="deleteTransactionCategory(<?= $cat['id'] ?>)">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="btn btn-sm btn-secondary" disabled title="ไม่สามารถลบได้เนื่องจากมีรายการในหมวดหมู่นี้">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
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
        </div>
    </div>
</div>

<?php 
include '../includes/modals/equipment_category_modal.php';
include '../includes/modals/transaction_category_modal.php';
include '../includes/footer.php';
?>

<script>
// Equipment Category Functions
function addEquipmentCategory() {
    $('#equipmentCategoryModalLabel').text('เพิ่มหมวดหมู่อุปกรณ์');
    $('#equipmentCategoryForm')[0].reset();
    $('#equipment_category_id').val('');
    $('#equipmentCategoryModal').modal('show');
}

function editEquipmentCategory(category) {
    $('#equipmentCategoryModalLabel').text('แก้ไขหมวดหมู่อุปกรณ์');
    $('#equipment_category_id').val(category.id);
    $('#equipment_category_name').val(category.name);
    $('#equipment_category_description').val(category.description);
    $('#equipmentCategoryModal').modal('show');
}

function deleteEquipmentCategory(id) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: 'คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../api/equipment_category_action.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดำเนินการได้', 'error');
                }
            });
        }
    });
}

$('#equipmentCategoryForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const action = $('#equipment_category_id').val() ? 'update' : 'create';
    
    $.ajax({
        url: '../api/equipment_category_action.php',
        type: 'POST',
        data: formData + '&action=' + action,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            let errorMsg = 'ไม่สามารถดำเนินการได้';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) errorMsg = response.message;
            } catch (e) {
                errorMsg = xhr.responseText || errorMsg;
            }
            Swal.fire('เกิดข้อผิดพลาด', errorMsg, 'error');
        }
    });
});

// Transaction Category Functions
function addTransactionCategory() {
    $('#transactionCategoryModalLabel').text('เพิ่มหมวดหมู่รายรับรายจ่าย');
    $('#transactionCategoryForm')[0].reset();
    $('#transaction_category_id').val('');
    $('#transactionCategoryModal').modal('show');
}

function editTransactionCategory(category) {
    $('#transactionCategoryModalLabel').text('แก้ไขหมวดหมู่รายรับรายจ่าย');
    $('#transaction_category_id').val(category.id);
    $('#transaction_category_name').val(category.name);
    $('#transaction_category_type').val(category.type);
    $('#transaction_category_description').val(category.description);
    $('#transactionCategoryModal').modal('show');
}

function deleteTransactionCategory(id) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: 'คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../api/transaction_category_action.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดำเนินการได้', 'error');
                }
            });
        }
    });
}

$('#transactionCategoryForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const action = $('#transaction_category_id').val() ? 'update' : 'create';
    
    $.ajax({
        url: '../api/transaction_category_action.php',
        type: 'POST',
        data: formData + '&action=' + action,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            let errorMsg = 'ไม่สามารถดำเนินการได้';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) errorMsg = response.message;
            } catch (e) {
                errorMsg = xhr.responseText || errorMsg;
            }
            Swal.fire('เกิดข้อผิดพลาด', errorMsg, 'error');
        }
    });
});
</script>
