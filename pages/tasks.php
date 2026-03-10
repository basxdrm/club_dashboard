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

$page_title = 'งาน';
$include_datatables = true;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $project_filter = $_GET['project'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    $sql = "SELECT t.*, p.name as project_name,
                CONCAT(prof_creator.prefix, prof_creator.first_name_th, ' ', prof_creator.last_name_th) as creator_name,
                GROUP_CONCAT(DISTINCT CONCAT(prof_assignee.prefix, prof_assignee.first_name_th, ' ', prof_assignee.last_name_th) SEPARATOR ', ') as assigned_name
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN users u_creator ON t.created_by = u_creator.id
            LEFT JOIN profiles prof_creator ON u_creator.id = prof_creator.user_id
            LEFT JOIN task_assignments ta ON t.id = ta.task_id AND ta.status IN ('approved', 'working', 'completed')
            LEFT JOIN users u_assignee ON ta.user_id = u_assignee.id
            LEFT JOIN profiles prof_assignee ON u_assignee.id = prof_assignee.user_id
            WHERE 1=1";
    
    $params = [];
    $year_filter = $_SESSION['selected_academic_year'] ?? null;
    if ($year_filter && $year_filter != 0) {
        $sql .= " AND t.academic_year_id = ?";
        $params[] = $year_filter;
    }
    if ($project_filter) {
        $sql .= " AND t.project_id = ?";
        $params[] = $project_filter;
    }
    if ($status_filter) {
        $sql .= " AND t.status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " GROUP BY t.id ORDER BY t.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    
    // Get projects for filter (filtered by academic year)
    if ($year_filter && $year_filter != 0) {
        $projects_stmt = $pdo->prepare("SELECT id, name FROM projects WHERE academic_year_id = ? ORDER BY name");
        $projects_stmt->execute([$year_filter]);
        $projects = $projects_stmt->fetchAll();
    } else {
        $projects = $pdo->query("SELECT id, name FROM projects ORDER BY name")->fetchAll();
    }
    
    // Get task statistics (filtered by academic year)
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN t.status IN ('in_progress', 'under_review') THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN t.due_date < CURDATE() AND t.status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE 1=1";
    
    $stats_params = [];
    if ($year_filter && $year_filter != 0) {
        $stats_sql .= " AND t.academic_year_id = ?";
        $stats_params[] = $year_filter;
    }
    if ($project_filter) {
        $stats_sql .= " AND t.project_id = ?";
        $stats_params[] = $project_filter;
    }
    if ($status_filter) {
        $stats_sql .= " AND t.status = ?";
        $stats_params[] = $status_filter;
    }
    
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($stats_params);
    $stats = $stats_stmt->fetch();
    
    // Status and Priority mapping
    $statusClass = [
        'pending' => 'secondary',
        'กำลังทำ' => 'primary',
        'กำลังดำเนินการ' => 'primary',
        'รอตรวจสอบ' => 'warning',
        'completed' => 'success'
    ];
    
    $priorityClass = [
        'low' => 'info',
        'medium' => 'warning',
        'high' => 'danger'
    ];
    
    $priorityText = [
        'low' => 'ต่ำ',
        'medium' => 'ปานกลาง',
        'high' => 'สูง'
    ];
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $tasks = [];
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
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> เพิ่มงาน
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <h4 class="page-title"><?php echo $page_title; ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-clipboard-text-multiple widget-icon bg-primary-lighten text-primary"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0" title="งานทั้งหมด">งานทั้งหมด</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['total']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">จำนวนงานทั้งหมดในระบบ</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-progress-clock widget-icon bg-warning-lighten text-warning"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0" title="รอดำเนินการ">รอดำเนินการ</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['pending']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">งานที่ยังไม่ได้เริ่มทำ</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-timer-sand widget-icon bg-info-lighten text-info"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0" title="กำลังดำเนินการ">กำลังดำเนินการ</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['in_progress']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">งานที่กำลังดำเนินการ</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-check-circle widget-icon bg-success-lighten text-success"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0" title="เสร็จสิ้น">เสร็จสิ้น</h5>
                                    <h3 class="mt-3 mb-3"><?php echo number_format($stats['completed']); ?></h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">งานที่เสร็จสมบูรณ์แล้ว</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($stats['overdue'] > 0): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                <strong>แจ้งเตือน!</strong> มีงานเกินกำหนด <strong><?php echo number_format($stats['overdue']); ?></strong> งาน ที่ยังไม่เสร็จสิ้น
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" class="row g-2 mb-3">
                                        <div class="col-md-4">
                                            <select class="form-select" name="project">
                                                <option value="">ทุกโปรเจค</option>
                                                <?php foreach ($projects as $p): ?>
                                                <option value="<?php echo $p['id']; ?>" <?php echo $project_filter == $p['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($p['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="status">
                                                <option value="">ทุกสถานะ</option>
                                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                                <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                                <option value="under_review" <?php echo $status_filter == 'under_review' ? 'selected' : ''; ?>>รอตรวจสอบ</option>
                                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                                <option value="กำลังดำเนินการ" <?php echo $status_filter == 'กำลังดำเนินการ' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                                <option value="รอตรวจสอบ" <?php echo $status_filter == 'รอตรวจสอบ' ? 'selected' : ''; ?>>รอตรวจสอบ</option>

                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-magnify"></i> ค้นหา</button>
                                        </div>
                                    </form>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-striped dt-responsive nowrap w-100" id="tasks-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>ชื่องาน</th>
                                                    <th>โปรเจค</th>
                                                    <th>ผู้รับผิดชอบ</th>
                                                    <th>สถานะ</th>
                                                    <th>ความสำคัญ</th>
                                                    <th>กำหนดส่ง</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tasks as $task):
                                                    $statusClass = ['pending' => 'info', 'in_progress' => 'warning', 'under_review' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                                    $statusText = ['pending' => 'รอดำเนินการ', 'in_progress' => 'กำลังดำเนินการ', 'under_review' => 'รอตรวจสอบ', 'completed' => 'เสร็จสิ้น', 'cancelled' => 'ยกเลิก'];
                                                    $priorityClass = ['low' => 'secondary', 'medium' => 'info', 'high' => 'danger'];
                                                    $priorityText = ['low' => 'ต่ำ', 'medium' => 'ปานกลาง', 'high' => 'สูง'];
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($task['id']); ?></td>
                                                    <td>
                                                        <a href="task_view.php?id=<?php echo $task['id']; ?>" class="text-body fw-bold">
                                                            <?php echo htmlspecialchars($task['title']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($task['project_name'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($task['assigned_name'] ?? 'ยังไม่มอบหมาย'); ?></td>
                                                    <td><span class="badge bg-<?php echo $statusClass[$task['status']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($statusText[$task['status']] ?? $task['status']); ?></span></td>
                                                    <td><span class="badge bg-<?php echo $priorityClass[$task['priority']] ?? 'info'; ?>"><?php echo htmlspecialchars($priorityText[$task['priority']] ?? $task['priority']); ?></span></td>
                                                    <td><?php echo $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-'; ?></td>
                                                    <td>
                                                        <a href="task_view.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-info" title="ดู"><i class="mdi mdi-eye"></i></a>
                                                        <?php if (hasRole(['admin', 'board', 'advisor'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning edit-task" data-id="<?php echo $task['id']; ?>" title="แก้ไข"><i class="mdi mdi-pencil"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-task" data-id="<?php echo $task['id']; ?>" data-title="<?php echo htmlspecialchars($task['title']); ?>" title="ลบ">
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

    <?php include '../includes/modals/task_add_modal.php'; ?>
    <?php include '../includes/modals/task_edit_modal.php'; ?>
    
    <script>
        $(document).ready(function() {
            $('#tasks-table').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json' },
                order: [[0, 'desc']]
            });
            
            // Task add form submission
            $('#addTaskForm').on('submit', function(e) {
                e.preventDefault();
                
                // Custom validation for select2 fields
                let isValid = true;
                let errorMessage = '';
                
                // Check assignment mode
                const assignmentMode = $('input[name="assignment_mode"]:checked').val();
                
                if (assignmentMode === 'direct') {
                    const assigneeType = $('input[name="assignee_type"]:checked').val();
                    
                    if (assigneeType === 'department') {
                        const departments = $('#task_departments').val();
                        if (!departments || departments.length === 0) {
                            isValid = false;
                            errorMessage = 'กรุณาเลือกฝ่ายที่รับผิดชอบอย่างน้อย 1 ฝ่าย';
                        }
                    } else {
                        const assignees = $('#task_assignees').val();
                        if (!assignees || assignees.length === 0) {
                            isValid = false;
                            errorMessage = 'กรุณาเลือกผู้รับผิดชอบอย่างน้อย 1 คน';
                        }
                    }
                }
                
                // Show validation error if any
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อมูลไม่ครบถ้วน!',
                        text: errorMessage
                    });
                    return;
                }
                
                $.ajax({
                    url: '../api/task_create.php',
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
            
            // Delete task
            $('.delete-task').on('click', function() {
                const taskId = $(this).data('id');
                const taskTitle = $(this).data('title');
                
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: `คุณต้องการลบงาน "${taskTitle}" ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../api/task_delete.php',
                            type: 'POST',
                            data: { id: taskId },
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
            
            // Edit task - load data and show modal
            $('.edit-task').on('click', function() {
                const taskId = $(this).data('id');
                
                $.ajax({
                    url: '../api/task_get.php',
                    type: 'GET',
                    data: { id: taskId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            $('#edit_task_id').val(data.id);
                            $('#edit_task_title').val(data.title);
                            $('#edit_task_project_id').val(data.project_id || '');
                            $('#edit_task_priority').val(data.priority);
                            $('#edit_task_description').val(data.description);
                            $('#edit_task_start_date').val(data.start_date);
                            $('#edit_task_due_date').val(data.due_date);
                            $('#edit_task_assignment_mode').val(data.assignment_mode);
                            $('#edit_task_max_assignees').val(data.max_assignees);
                            
                            // Set current assignees
                            if (data.assigned_user_ids && data.assigned_user_ids.length > 0) {
                                console.log('Setting assignees:', data.assigned_user_ids);
                                
                                // Set to individual assignment type
                                $('input[name="edit_assignee_type"][value="individual"]').prop('checked', true);
                                
                                // Show individual selection and hide department selection
                                $('#editIndividualSelectWrapper').show();
                                $('#editDepartmentSelectWrapper').hide();
                                
                                // Set the selected assignees
                                $('#edit_task_assignees').val(data.assigned_user_ids);
                                
                                // If using Select2, trigger change to update display
                                if (typeof $('#edit_task_assignees').select2 === 'function') {
                                    $('#edit_task_assignees').trigger('change');
                                }
                            } else {
                                console.log('No assignees found');
                                $('#edit_task_assignees').val([]).trigger('change');
                            }
                            
                            // Show assignment information in console
                            if (data.assignments && data.assignments.length > 0) {
                                console.log('Current assignments:', data.assignments.map(a => a.user_name));
                            }
                            
                            // Show the modal
                            $('#editTaskModal').modal('show');
                            
                            // Debug: Log what was actually set
                            setTimeout(() => {
                                console.log('Form values after setting:');
                                console.log('Title:', $('#edit_task_title').val());
                                console.log('Project:', $('#edit_task_project_id').val());
                                console.log('Priority:', $('#edit_task_priority').val());
                                console.log('Assignees:', $('#edit_task_assignees').val());
                            }, 100);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    }
                });
            });
            
            // Edit task form submission
            $('#editTaskForm').on('submit', function(e) {
                e.preventDefault();
                console.log('Submitting edit form...');
                
                const formData = $(this).serialize();
                console.log('Form data:', formData);
                
                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="spinner-border spinner-border-sm me-1"></i>บันทึก...').prop('disabled', true);
                
                $.ajax({
                    url: '../api/task_update.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Update response:', response);
                        
                        // Reset button
                        submitBtn.html(originalText).prop('disabled', false);
                        
                        if (response.success) {
                            console.log('Update successful, hiding modal and reloading...');
                            $('#editTaskModal').modal('hide');
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                console.log('Reloading page...');
                                window.location.reload();
                            });
                        } else {
                            console.error('Update failed:', response.message);
                            Swal.fire('ข้อผิดพลาด!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        
                        // Reset button
                        submitBtn.html(originalText).prop('disabled', false);
                        
                        let errorMessage = 'เกิดข้อผิดพลาดในการบันทึก';
                        if (xhr.responseText) {
                            try {
                                const errorData = JSON.parse(xhr.responseText);
                                errorMessage = errorData.message || errorMessage;
                            } catch (e) {
                                errorMessage += ': ' + xhr.responseText.substring(0, 100);
                            }
                        }
                        
                        Swal.fire('ข้อผิดพลาด!', errorMessage, 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
