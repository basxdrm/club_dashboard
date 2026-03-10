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

$page_title = 'โปรเจค';
$include_datatables = true;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $status_filter = $_GET['status'] ?? '';
    
    $sql = "SELECT p.*, 
                COUNT(DISTINCT t.id) as task_count,
                prof.first_name_th, prof.last_name_th
            FROM projects p
            LEFT JOIN tasks t ON p.id = t.project_id
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN profiles prof ON u.id = prof.user_id
            WHERE 1=1";
    
    $params = [];
    $year_filter = $_SESSION['selected_academic_year'] ?? null;
    if ($year_filter && $year_filter != 0) {
        $sql .= " AND p.academic_year_id = ?";
        $params[] = $year_filter;
    }
    if ($status_filter) {
        $sql .= " AND p.status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $projects = [];
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
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> สร้างโปรเจค
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <h4 class="page-title"><?php echo $page_title; ?></h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <form method="GET">
                                                <select class="form-select" name="status" onchange="this.form.submit()">
                                                    <option value="">ทุกสถานะ</option>
                                                    <option value="planning" <?php echo $status_filter == 'planning' ? 'selected' : ''; ?>>วางแผน</option>
                                                    <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                                                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-striped dt-responsive nowrap w-100" id="projects-table">
                                            <thead>
                                                <tr>
                                                    <th>ชื่อโปรเจค</th>
                                                    <th>สถานะ</th>
                                                    <th>งบประมาณ</th>
                                                    <th>งาน</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projects as $project): 
                                                    $statusClass = ['planning' => 'secondary', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                                    $statusText = ['planning' => 'วางแผน', 'in_progress' => 'กำลังดำเนินการ', 'completed' => 'เสร็จสิ้น', 'cancelled' => 'ยกเลิก'];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a href="project_view.php?id=<?php echo $project['id']; ?>" class="text-body fw-bold">
                                                            <?php echo htmlspecialchars($project['name']); ?>
                                                        </a>
                                                    </td>
                                                    <td><span class="badge bg-<?php echo $statusClass[$project['status']]; ?>"><?php echo $statusText[$project['status']]; ?></span></td>
                                                    <td><?php echo number_format($project['budget'], 2); ?> ฿</td>
                                                    <td><?php echo $project['task_count']; ?> งาน</td>
                                                    <td>
                                                        <a href="project_view.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-info" title="ดู"><i class="mdi mdi-eye"></i></a>
                                                        <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning edit-project" data-id="<?php echo $project['id']; ?>" title="แก้ไข"><i class="mdi mdi-pencil"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-project" data-id="<?php echo $project['id']; ?>" data-name="<?php echo htmlspecialchars($project['name']); ?>" title="ลบ">
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

    <?php include '../includes/modals/project_add_modal.php'; ?>
    <?php include '../includes/modals/project_edit_modal.php'; ?>
    
    <script>
        $(document).ready(function() {
            console.log('Projects page loaded');
            console.log('jQuery version:', $.fn.jquery);
            console.log('SweetAlert available:', typeof Swal !== 'undefined');
            
            $('#projects-table').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json' },
                order: [] // ปิดการเรียงลำดับของ DataTables ให้ใช้ลำดับจาก SQL
            });
            
            // Project add form submission
            $('#addProjectForm').on('submit', function(e) {
                e.preventDefault();
                console.log('Project form submitted');
                
                $.ajax({
                    url: '../api/project_create.php',
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
            
            // Delete project
            $('.delete-project').on('click', function() {
                const projectId = $(this).data('id');
                const projectName = $(this).data('name');
                
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: `คุณต้องการลบโปรเจค "${projectName}" ใช่หรือไม่? (งานทั้งหมดในโปรเจคจะถูกลบด้วย)`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../api/project_delete.php',
                            type: 'POST',
                            data: { id: projectId },
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
            
            // Edit project - load data and show modal
            $(document).on('click', '.edit-project', function() {
                const projectId = $(this).data('id');
                console.log('Edit button clicked, project ID:', projectId);
                
                $.ajax({
                    url: '../api/project_get.php',
                    type: 'GET',
                    data: { id: projectId },
                    dataType: 'json',
                    success: function(response) {
                        console.log('API Response:', response);
                        if (response.success) {
                            const data = response.data;
                            $('#edit_project_id').val(data.id);
                            $('#edit_project_name').val(data.name);
                            $('#edit_project_description').val(data.description);
                            $('#edit_project_start_date').val(data.start_date);
                            $('#edit_project_end_date').val(data.end_date);
                            $('#edit_project_budget').val(data.budget);
                            $('#edit_project_location').val(data.location || '');
                            $('#edit_project_status').val(data.status);
                            $('#editProjectModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลได้: ' + error, 'error');
                    }
                });
            });
            
            // Edit project form submission
            $('#editProjectForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '../api/project_update.php',
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
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
