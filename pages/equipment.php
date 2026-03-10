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

$page_title = 'อุปกรณ์';
$include_datatables = true;

// Base path for modal
$base_path = '../';

try {
    $pdo = getDatabaseConnection();
    $conn = $pdo; // For equipment_borrow_modal.php compatibility
    $category_filter = $_GET['category'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    $sql = "SELECT e.*, c.name as category_name
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.id
            WHERE 1=1";
    
    $params = [];
    if ($category_filter) {
        $sql .= " AND e.category_id = ?";
        $params[] = $category_filter;
    }
    if ($status_filter) {
        $sql .= " AND e.status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY e.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipment_list = $stmt->fetchAll();
    
    $categories = $pdo->query("SELECT * FROM equipment_categories WHERE is_active = 1")->fetchAll();
    
    // Get equipment counts by status
    $status_counts = [
        'available' => 0,
        'borrowed' => 0,
        'maintenance' => 0,
        'broken' => 0
    ];
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM equipment GROUP BY status");
    while ($row = $stmt->fetch()) {
        if (isset($status_counts[$row['status']])) {
            $status_counts[$row['status']] = $row['count'];
        }
    }
    $total_equipment = array_sum($status_counts);
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $equipment_list = [];
    $categories = [];
    $status_counts = ['available' => 0, 'borrowed' => 0, 'maintenance' => 0, 'broken' => 0];
    $total_equipment = 0;
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
                                    <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                                        <i class="mdi mdi-plus-circle"></i> เพิ่มอุปกรณ์
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#borrowEquipmentModal">
                                        <i class="mdi mdi-hand-extended"></i> ขอยืมอุปกรณ์
                                    </button>
                                    <a href="equipment_borrowing.php" class="btn btn-outline-info"><i class="mdi mdi-format-list-bulleted"></i> รายการยืม-คืน</a>
                                </div>
                                <h4 class="page-title"><?php echo $page_title; ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-md-6 col-xl-3">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-toolbox widget-icon bg-primary-lighten text-primary"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="อุปกรณ์ทั้งหมด">อุปกรณ์ทั้งหมด</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($total_equipment); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">รวมทุกสถานะ</span>  
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
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="พร้อมใช้งาน">พร้อมใช้งาน</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($status_counts['available']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-success">
                                            <i class="mdi mdi-arrow-up"></i> <?php echo $total_equipment > 0 ? round(($status_counts['available']/$total_equipment)*100, 1) : 0; ?>%
                                        </span>
                                        <span class="text-nowrap">ของทั้งหมด</span>  
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-hand-extended widget-icon bg-warning-lighten text-warning"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="ถูกยืม">ถูกยืม</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($status_counts['borrowed']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-warning">
                                            <i class="mdi mdi-arrow-right"></i> <?php echo $total_equipment > 0 ? round(($status_counts['borrowed']/$total_equipment)*100, 1) : 0; ?>%
                                        </span>
                                        <span class="text-nowrap">กำลังใช้งาน</span>  
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-tools widget-icon bg-danger-lighten text-danger"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0 text-truncate" title="ซ่อมบำรุง/ชำรุด">ซ่อมบำรุง/ชำรุด</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($status_counts['maintenance'] + $status_counts['broken']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-danger">
                                            <i class="mdi mdi-alert"></i> <?php echo $total_equipment > 0 ? round((($status_counts['maintenance'] + $status_counts['broken'])/$total_equipment)*100, 1) : 0; ?>%
                                        </span>
                                        <span class="text-nowrap">ต้องดูแล</span>  
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
                                        <div class="col-md-4">
                                            <select class="form-select" name="category">
                                                <option value="">ทุกหมวดหมู่</option>
                                                <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="status">
                                                <option value="">ทุกสถานะ</option>
                                                <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?>>พร้อมใช้</option>
                                                <option value="borrowed" <?php echo $status_filter == 'borrowed' ? 'selected' : ''; ?>>ถูกยืม</option>
                                                <option value="maintenance" <?php echo $status_filter == 'maintenance' ? 'selected' : ''; ?>>ซ่อมบำรุง</option>
                                                <option value="broken" <?php echo $status_filter == 'broken' ? 'selected' : ''; ?>>ชำรุด</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-magnify"></i> ค้นหา</button>
                                        </div>
                                    </form>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-striped dt-responsive nowrap w-100" id="equipment-table">
                                            <thead>
                                                <tr>
                                                    <th>ชื่ออุปกรณ์</th>
                                                    <th>หมวดหมู่</th>
                                                    <th>ยี่ห้อ/รุ่น</th>
                                                    <th>Serial Number</th>
                                                    <th>สถานะ</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($equipment_list as $equip):
                                                    $statusClass = ['available' => 'success', 'borrowed' => 'warning', 'maintenance' => 'info', 'broken' => 'danger', 'retired' => 'secondary'];
                                                    $statusText = ['available' => 'พร้อมใช้', 'borrowed' => 'ถูกยืม', 'maintenance' => 'ซ่อมบำรุง', 'broken' => 'ชำรุด', 'retired' => 'ปลดระ'];
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($equip['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($equip['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars(($equip['brand'] ?? '') . ' ' . ($equip['model'] ?? '')); ?></td>
                                                    <td><?php echo htmlspecialchars($equip['serial_number'] ?? '-'); ?></td>
                                                    <td><span class="badge bg-<?php echo $statusClass[$equip['status']]; ?>"><?php echo $statusText[$equip['status']]; ?></span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-info"
                                                                onclick="window.location.href='equipment_view.php?id=<?php echo $equip['id']; ?>'"
                                                                title="ดูรายละเอียด">
                                                            <i class="mdi mdi-eye"></i>
                                                        </button>
                                                        <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                                        <button class="btn btn-sm btn-outline-warning edit-equipment"
                                                                data-id="<?php echo $equip['id']; ?>"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editEquipmentModal"
                                                                title="แก้ไข">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger delete-equipment"
                                                                data-id="<?php echo $equip['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($equip['name']); ?>"
                                                                title="ลบ">
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

    <?php include '../includes/modals/equipment_add_modal.php'; ?>
    <?php include '../includes/modals/equipment_edit_modal.php'; ?>
    <?php include '../includes/modals/equipment_borrow_modal.php'; ?>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#equipment-table').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json' }
            });
            
            // View equipment - use event delegation on table body
            $('#equipment-table tbody').on('click', '.view-equipment', function(e) {
                e.preventDefault();
                const equipmentId = $(this).data('id');
                window.location.href = 'equipment_view.php?id=' + equipmentId;
            });
            
            // Add equipment form submission
            $('#addEquipmentForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: '../api/equipment_create.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                    }
                });
            });
            
            // Edit equipment button
            $('.edit-equipment').on('click', function() {
                const equipmentId = $(this).data('id');
                
                $.ajax({
                    url: '../api/equipment_get.php',
                    type: 'GET',
                    data: { id: equipmentId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            const equip = data.equipment;
                            $('#edit_equipment_id').val(equip.id);
                            $('#edit_equipment_name').val(equip.name);
                            $('#edit_category_id').val(equip.category_id);
                            $('#edit_brand').val(equip.brand);
                            $('#edit_model').val(equip.model);
                            $('#edit_serial_number').val(equip.serial_number);
                            $('#edit_quantity').val(equip.quantity);
                            $('#edit_purchase_date').val(equip.purchase_date);
                            $('#edit_purchase_price').val(equip.purchase_price);
                            $('#edit_location').val(equip.location);
                            $('#edit_description').val(equip.description);
                            $('#edit_status').val(equip.status);
                            
                            // Show current image
                            if (equip.image) {
                                $('#current_image_preview').html(
                                    '<p class="text-muted mb-1">รูบภาพปัจจุบัน:</p>' +
                                    '<img src="../assets/images/equipment/' + equip.image + '" class="img-thumbnail" style="max-height:120px;object-fit:contain;">'
                                );
                            } else {
                                $('#current_image_preview').html('<p class="text-muted"><small>ไม่มีรูปภาพ</small></p>');
                            }
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    }
                });
            });
            
            // Edit equipment form submission
            $('#editEquipmentForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: '../api/equipment_update.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                    }
                });
            });
            
            // Delete equipment
            $('.delete-equipment').on('click', function() {
                const equipmentId = $(this).data('id');
                const equipmentName = $(this).data('name');
                
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: `คุณต้องการลบอุปกรณ์ "${equipmentName}" ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../api/equipment_delete.php',
                            type: 'POST',
                            data: { id: equipmentId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'ลบแล้ว!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'ผิดพลาด!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ผิดพลาด!',
                                    text: 'เกิดข้อผิดพลาดในการลบ'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
