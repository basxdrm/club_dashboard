<?php
/**
 * Members List Page
 * หน้าแสดงรายชื่อสมาชิกทั้งหมด
 */

define('APP_ACCESS', true);

// ตั้งค่า Session ก่อน session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';

// ต้อง Login และเป็น Admin เท่านั้น
requireLogin();
requireRole('admin');

$page_title = 'จัดการผู้ใช้งาน';
$include_datatables = true;

// ดึงข้อมูลสมาชิก
try {
    $pdo = getDatabaseConnection();
    
    // Filter
    $status_filter = $_GET['status'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT
                u.id, u.email, u.role, u.status, u.created_at,
                p.student_id, p.prefix, p.first_name_th, p.last_name_th, p.nickname_th,
                p.profile_picture,
                e.academic_year_id, e.class_academic,
                c.phone_number,
                ci.member_generation, ci.joined_date,
                d.name as department_name,
                pos.name as position_name
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            LEFT JOIN member_education e ON u.id = e.user_id AND e.is_current = 1
            LEFT JOIN member_contacts c ON u.id = c.user_id
            LEFT JOIN member_club_info ci ON u.id = ci.user_id
            LEFT JOIN club_departments d ON ci.department_id = d.id
            LEFT JOIN club_positions pos ON ci.position_id = pos.id
            WHERE 1=1";
    
    $params = [];
    
    if ($status_filter !== '') {
        $sql .= " AND u.status = ?";
        $params[] = $status_filter;
    }
    
    if ($role_filter !== '') {
        $sql .= " AND u.role = ?";
        $params[] = $role_filter;
    }
    
    if ($search !== '') {
        $sql .= " AND (p.first_name_th LIKE ? OR p.last_name_th LIKE ? OR p.nickname_th LIKE ? OR p.student_id LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    $sql .= " ORDER BY p.student_id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error fetching members: " . $e->getMessage());
    $members = [];
}

// ฟังก์ชันแสดงสถานะ
function getStatusBadge($status) {
    switch($status) {
        case 0: return '<span class="badge bg-danger">ลาออก</span>';
        case 1: return '<span class="badge bg-success">สมาชิก</span>';
        case 2: return '<span class="badge bg-secondary">จบการศึกษา</span>';
        default: return '<span class="badge bg-secondary">-</span>';
    }
}

function getRoleBadge($role) {
    switch($role) {
        case 'admin':   return '<span class="badge bg-danger">Admin</span>';
        case 'board':   return '<span class="badge bg-warning">Board</span>';
        case 'advisor': return '<span class="badge bg-success">Advisor</span>';
        case 'member':  return '<span class="badge bg-primary">Member</span>';
        default:        return '<span class="badge bg-secondary">-</span>';
    }
}
?>
<?php include '../includes/header.php'; ?>

        <?php include '../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <?php include '../includes/topbar.php'; ?>

                <div class="container-fluid">
                    
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> เพิ่มสมาชิก
                                    </button>
                                </div>
                                <h4 class="page-title"><?php echo $page_title; ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Row -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label">ค้นหา</label>
                                            <input type="text" class="form-control" name="search"
                                                   placeholder="ชื่อ, รหัสนักเรียน, อีเมล"
                                                   value="<?php echo htmlspecialchars($search); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">สถานะ</label>
                                            <select class="form-select" name="status">
                                                <option value="">ทั้งหมด</option>
                                                <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>สมาชิก</option>
                                                <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>ลาออก</option>
                                                <option value="2" <?php echo $status_filter === '2' ? 'selected' : ''; ?>>จบการศึกษา</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">บทบาท</label>
                                            <select class="form-select" name="role">
                                                <option value="">ทั้งหมด</option>
                                                <option value="member" <?php echo $role_filter === 'member' ? 'selected' : ''; ?>>Member</option>
                                                <option value="board" <?php echo $role_filter === 'board' ? 'selected' : ''; ?>>Board</option>
                                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="mdi mdi-magnify me-1"></i> ค้นหา
                                            </button>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <a href="members.php" class="btn btn-secondary w-100">
                                                <i class="mdi mdi-refresh"></i>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Members Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="members-table" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th>รหัสนักเรียน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>อีเมล์</th>
                                                    <th>รุ่นที่</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>สิทธิ์</th>
                                                    <th>สถานะ</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $row_number = 1;
                                                foreach ($members as $member): 
                                                    $fullname = ($member['prefix'] ?? '') . ' ' . ($member['first_name_th'] ?? '') . ' ' . ($member['last_name_th'] ?? '');
                                                ?>
                                                <tr>
                                                    <td><?php echo $row_number++; ?></td>
                                                    <td><?php echo htmlspecialchars($member['student_id'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars(trim($fullname) ?: '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($member['member_generation'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($member['position_name'] ?? '-'); ?></td>
                                                    <td><?php echo getRoleBadge($member['role']); ?></td>
                                                    <td><?php echo getStatusBadge($member['status']); ?></td>
                                                    <td>
                                                        <a href="member_view.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-info" title="ดูข้อมูล">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-warning edit-member" data-id="<?php echo $member['id']; ?>" title="แก้ไข">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-member" data-id="<?php echo $member['id']; ?>" data-name="<?php echo htmlspecialchars(trim($fullname)); ?>" title="ลบ">
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

                </div>
            </div>

            <?php include '../includes/footer.php'; ?>



    <script>
        // Define functions outside $(document).ready() so they can be called from AJAX callbacks
        function updateClassPreviews() {
            const academicGrade = $('#academic_grade').val();
            const academicRoom = $('#academic_room').val();
            const agamaGrade = $('#agama_grade').val();
            const agamaRoom = $('#agama_room').val();
            
            if (academicGrade && academicRoom) {
                $('#class_academic_preview').val(`ม.${academicGrade}/${academicRoom}`);
            } else {
                $('#class_academic_preview').val('');
            }
            
            if (agamaGrade && agamaRoom) {
                $('#class_agama_preview').val(`ศ.${agamaGrade}/${agamaRoom}`);
            } else {
                $('#class_agama_preview').val('');
            }
        }
        
        function updateEditClassPreviews() {
            const academicGrade = $('#edit_academic_grade').val();
            const academicRoom = $('#edit_academic_room').val();
            const agamaGrade = $('#edit_agama_grade').val();
            const agamaRoom = $('#edit_agama_room').val();
            
            if (academicGrade && academicRoom) {
                $('#edit_class_academic_preview').val(`ม.${academicGrade}/${academicRoom}`);
            } else {
                $('#edit_class_academic_preview').val('');
            }
            
            if (agamaGrade && agamaRoom) {
                $('#edit_class_agama_preview').val(`ศ.${agamaGrade}/${agamaRoom}`);
            } else {
                $('#edit_class_agama_preview').val('');
            }
        }

        $(document).ready(function() {
            $('#members-table').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                },
                order: [[0, 'asc']],
                pageLength: 25
            });
            
            // Bind events
            $('#academic_grade, #academic_room').on('input', updateClassPreviews);
            $('#agama_grade, #agama_room').on('input', updateClassPreviews);
            $('#edit_academic_grade, #edit_academic_room').on('input', updateEditClassPreviews);
            $('#edit_agama_grade, #edit_agama_room').on('input', updateEditClassPreviews);
            
            // Member add form submission
            $('#addMemberForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '../api/member_create.php',
                    type: 'POST',
                    data: $(this).serialize(),
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
                            Swal.fire({
                                icon: 'error',
                                title: 'ผิดพลาด!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด!',
                            text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error
                        });
                    }
                });
            });
            
            // Delete member
            $('.delete-member').on('click', function() {
                const memberId = $(this).data('id');
                const memberName = $(this).data('name');
                
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: `คุณต้องการลบ ${memberName} ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../api/member_delete.php',
                            type: 'POST',
                            data: { id: memberId },
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

<?php include '../includes/modals/member_add_modal.php'; ?>
<?php include '../includes/modals/member_edit_modal.php'; ?>

<script>
// Edit member - load data and show modal
$('.edit-member').on('click', function() {
    const memberId = $(this).data('id');
    
    $.ajax({
        url: '../api/member_get.php',
        type: 'GET',
        data: { id: memberId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#edit_user_id').val(data.id);
                $('#edit_email').val(data.email);
                $('#edit_role').val(data.role);
                $('#edit_status').val(data.status);
                $('#edit_prefix').val(data.prefix);
                $('#edit_first_name_th').val(data.first_name_th);
                $('#edit_last_name_th').val(data.last_name_th);
                $('#edit_nickname_th').val(data.nickname_th);
                $('#edit_first_name_en').val(data.first_name_en);
                $('#edit_last_name_en').val(data.last_name_en);
                $('#edit_student_id').val(data.student_id);
                $('#edit_birth_date').val(data.birth_date);
                $('#edit_phone_number').val(data.phone_number);
                $('#edit_academic_year_id').val(data.academic_year_id);
                $('#edit_academic_status').val(data.academic_status || 'studying');
                $('#edit_academic_grade').val(data.academic_grade);
                $('#edit_academic_room').val(data.academic_room);
                $('#edit_agama_status').val(data.agama_status || 'studying');
                $('#edit_agama_grade').val(data.agama_grade);
                $('#edit_agama_room').val(data.agama_room);
                $('#edit_department_id').val(data.department_id);
                $('#edit_position_id').val(data.position_id);
                $('#edit_member_generation').val(data.member_generation);
                
                // Update previews
                updateEditClassPreviews();

                // Toggle advisor fields based on loaded role
                if (typeof toggleEditAdvisorFields === 'function') {
                    toggleEditAdvisorFields(data.role === 'advisor');
                }

                $('#editMemberModal').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลได้', 'error');
        }
    });
});

// Edit member form submission
$('#editMemberForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    
    $.ajax({
        url: '../api/member_update.php',
        type: 'POST',
        data: formData,
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
            console.error('Response:', xhr.responseText);
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
        }
    });
});
</script>
