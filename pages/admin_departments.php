<?php
/**
 * Admin Departments Management
 * หน้าจัดการฝ่าย (Admin Only)
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

// ดึงข้อมูลฝ่ายทั้งหมด
$dept_stmt = $conn->prepare("SELECT d.*, 
    (SELECT COUNT(*) FROM member_club_info WHERE department_id = d.id) as member_count
    FROM club_departments d 
    ORDER BY d.name ASC");
$dept_stmt->execute();
$departments = $dept_stmt->fetchAll();

// ดึงข้อมูลสมาชิกที่ยังไม่มีฝ่าย
$no_dept_stmt = $conn->prepare("SELECT u.id, u.email, 
    CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as full_name,
    p.student_id, p.profile_picture
    FROM users u
    JOIN profiles p ON u.id = p.user_id
    LEFT JOIN member_club_info ci ON u.id = ci.user_id
    WHERE u.status IN (1, 2) AND (ci.department_id IS NULL OR ci.department_id = 0)
    ORDER BY p.student_id ASC");
$no_dept_stmt->execute();
$no_department_members = $no_dept_stmt->fetchAll();

$page_title = 'จัดการฝ่าย';
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
                        <div class="page-title-right">
                            <button class="btn btn-primary" onclick="addDepartment()">
                                <i class="mdi mdi-plus-circle-outline me-1"></i> เพิ่มฝ่ายใหม่
                            </button>
                        </div>
                        <h4 class="page-title"><?= htmlspecialchars($page_title) ?></h4>
                    </div>
                </div>
            </div>

            <!-- Departments List -->
            <div class="row">
                <?php foreach ($departments as $dept): 
                    // ดึงสมาชิกในฝ่าย
                    $members_stmt = $conn->prepare("SELECT u.id, u.email,
                        CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as full_name,
                        p.student_id, p.profile_picture,
                        ci.is_department_head
                        FROM member_club_info ci
                        JOIN users u ON ci.user_id = u.id
                        JOIN profiles p ON u.id = p.user_id
                        WHERE ci.department_id = ? AND u.status IN (1, 2)
                        ORDER BY ci.is_department_head DESC, p.student_id ASC");
                    $members_stmt->execute([$dept['id']]);
                    $dept_members = $members_stmt->fetchAll();
                ?>
                    <div class="col-md-6">
                        <div class="card" style="border-left: 4px solid <?= htmlspecialchars($dept['color'] ?? '#3B7DDD') ?>;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <?php if ($dept['icon']): ?>
                                            <div class="avatar-sm rounded-circle me-3" style="background-color: <?= htmlspecialchars($dept['color'] ?? '#3B7DDD') ?>20;">
                                                <span class="avatar-title rounded-circle" style="background-color: transparent;">
                                                    <i class="<?= htmlspecialchars($dept['icon']) ?> font-size-24" style="color: <?= htmlspecialchars($dept['color'] ?? '#3B7DDD') ?>;"></i>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <?= htmlspecialchars($dept['name']) ?>
                                            </h5>
                                            <p class="text-muted mb-0 small"><?= htmlspecialchars($dept['description'] ?? '') ?></p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge" style="background-color: <?= htmlspecialchars($dept['color'] ?? '#3B7DDD') ?>;"><?= count($dept_members) ?> คน</span>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <button class="btn btn-sm btn-primary" onclick="addMemberToDepartment(<?= $dept['id'] ?>, '<?= htmlspecialchars($dept['name']) ?>')">
                                        <i class="mdi mdi-plus"></i> เพิ่มสมาชิก
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick='editDepartment(<?= json_encode($dept, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="mdi mdi-pencil"></i> แก้ไข
                                    </button>
                                    <?php if (count($dept_members) == 0): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteDepartment(<?= $dept['id'] ?>, '<?= htmlspecialchars($dept['name']) ?>')">
                                            <i class="mdi mdi-delete"></i> ลบ
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php if (empty($dept_members)): ?>
                                    <p class="text-muted text-center py-3">ยังไม่มีสมาชิกในฝ่าย</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>รหัส</th>
                                                    <th>ชื่อ</th>
                                                    <th>หัวหน้าฝ่าย</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dept_members as $member): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($member['student_id']) ?></td>
                                                        <td><?= htmlspecialchars($member['full_name']) ?></td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    <?= $member['is_department_head'] ? 'checked' : '' ?>
                                                                    onchange="toggleDepartmentHead(<?= $member['id'] ?>, <?= $dept['id'] ?>, this.checked)">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="removeMemberFromDepartment(<?= $member['id'] ?>, <?= $dept['id'] ?>, '<?= htmlspecialchars($member['full_name']) ?>')">
                                                                <i class="mdi mdi-close"></i>
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
                <?php endforeach; ?>
            </div>

            <!-- Members without Department -->
            <?php if (!empty($no_department_members)): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">
                                    <i class="mdi mdi-alert"></i> สมาชิกที่ยังไม่มีฝ่าย (<?= count($no_department_members) ?> คน)
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>รหัสนักเรียน</th>
                                                <th>ชื่อ-นามสกุล</th>
                                                <th>อีเมล์</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($no_department_members as $member): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($member['student_id']) ?></td>
                                                    <td><?= htmlspecialchars($member['full_name']) ?></td>
                                                    <td><?= htmlspecialchars($member['email']) ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" onclick="quickAssignDepartment(<?= $member['id'] ?>, '<?= htmlspecialchars($member['full_name']) ?>')">
                                                            <i class="mdi mdi-arrow-right"></i> เพิ่มเข้าฝ่าย
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../includes/modals/department_modal.php';
include '../includes/modals/department_add_member_modal.php';
include '../includes/footer.php';
?>

<script>
// Department Functions
function addDepartment() {
    $('#departmentModalLabel').text('เพิ่มฝ่ายใหม่');
    $('#departmentForm')[0].reset();
    $('#department_id').val('');
    $('#departmentModal').modal('show');
}

function editDepartment(dept) {
    $('#departmentModalLabel').text('แก้ไขฝ่าย');
    $('#department_id').val(dept.id);
    $('#department_name').val(dept.name);
    $('#department_description').val(dept.description);
    $('#department_icon').val(dept.icon);
    $('#department_color').val(dept.color || '#3B7DDD');
    $('#departmentModal').modal('show');
}

function deleteDepartment(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: `คุณต้องการลบฝ่าย "${name}" ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../api/department_action.php', {
                action: 'delete',
                id: id
            }, function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดำเนินการได้', 'error');
            });
        }
    });
}

$('#departmentForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const action = $('#department_id').val() ? 'update' : 'create';
    
    $.post('../api/department_action.php', formData + '&action=' + action, function(response) {
        if (response.success) {
            Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดำเนินการได้', 'error');
    });
});

// Member Management Functions
function addMemberToDepartment(deptId, deptName) {
    $('#addMemberModalLabel').text(`เพิ่มสมาชิกเข้าฝ่าย: ${deptName}`);
    $('#target_department_id').val(deptId);
    loadAvailableMembers(deptId);
    $('#addMemberModal').modal('show');
}

function loadAvailableMembers(deptId) {
    $.get('../api/department_action.php', {
        action: 'get_available_members',
        department_id: deptId
    }, function(response) {
        if (response.success) {
            displayAvailableMembers(response.members);
        }
    }, 'json');
}

function displayAvailableMembers(members) {
    const html = members.map(m => `
        <div class="d-flex justify-content-between align-items-center p-2 border-bottom member-item">
            <div>
                <strong>${m.student_id}</strong> - ${m.full_name}
                <br><small class="text-muted">${m.email}</small>
            </div>
            <button class="btn btn-sm btn-primary" onclick="assignMemberToDepartment(${m.id})">
                <i class="mdi mdi-plus"></i> เพิ่ม
            </button>
        </div>
    `).join('');
    $('#availableMembersList').html(html || '<p class="text-center text-muted">ไม่มีสมาชิกที่สามารถเพิ่มได้</p>');
}

$('#memberSearch').on('keyup', function() {
    const search = $(this).val().toLowerCase();
    $('.member-item').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.includes(search));
    });
});

function assignMemberToDepartment(userId) {
    const deptId = $('#target_department_id').val();
    
    Swal.fire({
        title: 'ยืนยันการเพิ่มสมาชิก',
        text: 'คุณต้องการเพิ่มสมาชิกคนนี้เข้าฝ่ายใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, เพิ่มเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../api/department_action.php',
                type: 'POST',
                data: {
                    action: 'assign_member',
                    user_id: userId,
                    department_id: deptId
                },
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
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดำเนินการได้: ' + error, 'error');
                }
            });
        }
    });
}

function quickAssignDepartment(userId, userName) {
    Swal.fire({
        title: 'เลือกฝ่าย',
        text: `เพิ่ม ${userName} เข้าฝ่าย:`,
        input: 'select',
        inputOptions: <?= json_encode(array_column($departments, 'name', 'id')) ?>,
        showCancelButton: true,
        confirmButtonText: 'เพิ่ม',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.post('../api/department_action.php', {
                action: 'assign_member',
                user_id: userId,
                department_id: result.value
            }, function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                }
            }, 'json');
        }
    });
}

function removeMemberFromDepartment(userId, deptId, userName) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: `คุณต้องการลบ ${userName} ออกจากฝ่ายใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'ใช่, ลบออก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../api/department_action.php', {
                action: 'remove_member',
                user_id: userId
            }, function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                }
            }, 'json');
        }
    });
}

function toggleDepartmentHead(userId, deptId, isHead) {
    $.post('../api/department_action.php', {
        action: 'toggle_department_head',
        user_id: userId,
        department_id: deptId,
        is_head: isHead ? 1 : 0
    }, function(response) {
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: response.message,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
            location.reload();
        }
    }, 'json');
}
</script>
