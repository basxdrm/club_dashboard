<?php
/**
 * Admin Club Positions Management
 * หน้าจัดการตำแหน่งบอร์ดบริหาร (Admin Only)
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

// ดึงข้อมูลตำแหน่งทั้งหมด
$sql = "SELECT * FROM club_positions ORDER BY level ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$positions = $stmt->fetchAll();

$page_title = 'จัดการตำแหน่งบอร์ด';
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

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title"><i class="mdi mdi-badge-account-outline me-1"></i><?= htmlspecialchars($page_title) ?></h4>
                    </div>
                </div>
            </div>

            <!-- Positions Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">รายการตำแหน่ง</h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                                    <i class="mdi mdi-plus me-1"></i>เพิ่มตำแหน่ง
                                </button>
                            </div>
                            
                            <table id="positions-table" class="table table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อตำแหน่ง</th>
                                        <th>คำอธิบาย</th>
                                        <th>สถานะ</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-positions">
                                    <?php foreach ($positions as $position): ?>
                                    <tr data-id="<?php echo $position['id']; ?>">
                                        <td>
                                            <i class="mdi mdi-drag-vertical" style="cursor: move;"></i>
                                            <?php echo $position['level']; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($position['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($position['description'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($position['is_active']): ?>
                                                <span class="badge bg-success">ใช้งาน</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">ไม่ใช้งาน</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editPosition(<?php echo $position['id']; ?>)">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deletePosition(<?php echo $position['id']; ?>, '<?php echo htmlspecialchars($position['name']); ?>')">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
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
        <!-- container -->

    </div>
    <!-- content -->

    <?php include_once('../includes/footer.php'); ?>

</div>
<!-- content-page -->

<!-- Add Position Modal -->
<div class="modal fade" id="addPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มตำแหน่ง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPositionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_level" class="form-label">ลำดับ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="add_level" name="level" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_is_active" name="is_active" checked>
                            <label class="form-check-label" for="add_is_active">ใช้งาน</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Position Modal -->
<div class="modal fade" id="editPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขตำแหน่ง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPositionForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_level" class="form-label">ลำดับ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_level" name="level" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">ใช้งาน</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jQuery UI for Sortable -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Page loaded, initializing sortable...');
    console.log('jQuery version:', $.fn.jquery);
    console.log('jQuery UI version:', $.ui ? $.ui.version : 'not loaded');
    
    // Make table rows sortable (DataTable conflicts with sortable, so we use simple table)
    $('#sortable-positions').sortable({
        handle: '.mdi-drag-vertical',
        axis: 'y',
        cursor: 'move',
        opacity: 0.6,
        helper: function(e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function(index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        start: function(event, ui) {
            console.log('Drag started');
        },
        stop: function(event, ui) {
            console.log('Drag stopped, waiting to update...');
        },
        update: function(event, ui) {
            console.log('Drag completed, updating order...');
            updateOrder();
        }
    });
    
    console.log('Sortable initialized');
});

// Add Position
$('#addPositionForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $('#add_name').val(),
        level: $('#add_level').val(),
        description: $('#add_description').val(),
        is_active: $('#add_is_active').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: '../api/club_position_create.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('สำเร็จ!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
        }
    });
});

// Edit Position
function editPosition(id) {
    $.ajax({
        url: '../api/club_position_get.php?id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Get position response:', response);
            if (response.success && response.data) {
                const data = response.data;
                $('#edit_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_level').val(data.level);
                $('#edit_description').val(data.description || '');
                $('#edit_is_active').prop('checked', data.is_active == 1);
                
                $('#editPositionModal').modal('show');
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Get position error:', error, xhr.responseText);
            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถโหลดข้อมูลได้', 'error');
        }
    });
}

$('#editPositionForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        id: $('#edit_id').val(),
        name: $('#edit_name').val(),
        level: $('#edit_level').val(),
        description: $('#edit_description').val(),
        is_active: $('#edit_is_active').is(':checked') ? 1 : 0
    };
    
    console.log('Updating position:', formData);
    
    $.ajax({
        url: '../api/club_position_update.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log('Update response:', response);
            if (response.success) {
                Swal.fire('สำเร็จ!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Update error:', error, xhr.responseText);
            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
        }
    });
});

// Delete Position
function deletePosition(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `คุณต้องการลบตำแหน่ง "${name}" หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../api/club_position_delete.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('ลบสำเร็จ!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('ข้อผิดพลาด!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }
    });
}

// Update Order after drag
function updateOrder() {
    const order = [];
    $('#sortable-positions tr').each(function(index) {
        order.push({
            id: $(this).data('id'),
            level: index + 1
        });
    });
    
    console.log('Updating order:', order);
    
    $.ajax({
        url: '../api/club_position_reorder.php',
        type: 'POST',
        data: { order: JSON.stringify(order) },
        dataType: 'json',
        success: function(response) {
            console.log('Reorder response:', response);
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'อัพเดทลำดับสำเร็จ',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message || 'ไม่สามารถอัพเดทลำดับได้', 'error').then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Reorder error:', error, xhr.responseText);
            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error').then(() => {
                location.reload();
            });
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
