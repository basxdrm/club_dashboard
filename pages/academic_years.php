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

$db = new Database();
$pdo = $db->getConnection();

// Get all academic years
$sql = "SELECT * FROM academic_years ORDER BY year DESC";
$stmt = $pdo->query($sql);
$academic_years = $stmt->fetchAll();

$page_title = 'จัดการปีการศึกษา';
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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal">
                                <i class="mdi mdi-plus-circle"></i> เพิ่มปีการศึกษา
                            </button>
                        </div>
                        <h4 class="page-title"><?= htmlspecialchars($page_title) ?></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0">
                                    <thead>
                                        <tr>
                                            <th>ปีการศึกษา</th>
                                            <th>วันที่เริ่มต้น</th>
                                            <th>วันที่สิ้นสุด</th>
                                            <th>สถานะ</th>
                                            <th class="text-center">การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($academic_years)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">ยังไม่มีข้อมูลปีการศึกษา</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($academic_years as $year): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($year['year']); ?></strong>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($year['start_date'])); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($year['end_date'])); ?></td>
                                                    <td>
                                                        <?php if ($year['is_current']): ?>
                                                            <span class="badge bg-success">ปีปัจจุบัน</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">ไม่ใช้งาน</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <?php if (!$year['is_current']): ?>
                                                                <button class="btn btn-sm btn-success" 
                                                                    onclick="setCurrentYear(<?php echo $year['id']; ?>)"
                                                                    title="ตั้งเป็นปีปัจจุบัน">
                                                                    <i class="mdi mdi-check-circle"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-warning" 
                                                                onclick="editYear(<?php echo htmlspecialchars(json_encode($year)); ?>)"
                                                                title="แก้ไข">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger" 
                                                                onclick="deleteYear(<?php echo $year['id']; ?>)"
                                                                title="ลบ">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </div>
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
    <?php include '../includes/footer.php'; ?>
</div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addAcademicYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มปีการศึกษา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addYearForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ปีการศึกษา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="year" placeholder="เช่น 2567" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่เริ่มต้น <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                        <label class="form-check-label" for="is_current">
                            ตั้งเป็นปีการศึกษาปัจจุบัน
                        </label>
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

<!-- Edit Modal -->
<div class="modal fade" id="editAcademicYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขปีการศึกษา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editYearForm">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ปีการศึกษา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="year" id="edit_year" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่เริ่มต้น <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" id="edit_end_date" required>
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

<script>
$(document).ready(function() {
    // Add year
    $('#addYearForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '../api/academic_year_create.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('ข้อผิดพลาด!', response.message, 'error');
                }
            }
        });
    });

    // Edit year
    $('#editYearForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '../api/academic_year_update.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('ข้อผิดพลาด!', response.message, 'error');
                }
            }
        });
    });
});

function editYear(year) {
    $('#edit_id').val(year.id);
    $('#edit_year').val(year.year);
    $('#edit_start_date').val(year.start_date);
    $('#edit_end_date').val(year.end_date);
    $('#editAcademicYearModal').modal('show');
}

function setCurrentYear(id) {
    Swal.fire({
        title: 'ยืนยันการเปลี่ยนปีการศึกษา?',
        text: 'ต้องการตั้งเป็นปีการศึกษาปัจจุบันใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../api/academic_year_set_current.php', { id: id }, function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('ข้อผิดพลาด!', response.message, 'error');
                }
            }, 'json');
        }
    });
}

function deleteYear(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'คุณต้องการลบปีการศึกษานี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../api/academic_year_delete.php', { id: id }, function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('ข้อผิดพลาด!', response.message, 'error');
                }
            }, 'json');
        }
    });
}
</script>
</body>
</html>
