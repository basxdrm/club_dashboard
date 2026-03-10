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

// Get user role for permission control
$userRole = $_SESSION['role'] ?? 'member';
$canManageTasks = in_array($userRole, ['admin', 'board', 'advisor']);
$canEditTaskDetails = in_array($userRole, ['admin', 'board', 'advisor']);
$canDeleteTasks = in_array($userRole, ['admin', 'board', 'advisor']);

$page_title = 'ตารางงาน';
$include_fullcalendar = true;
$include_select2 = true;

include_once '../includes/header.php';
?>

    <!-- ========== Left Sidebar Start ========== -->
    <?php include_once('../includes/sidebar.php'); ?>
    <!-- Left Sidebar End -->

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->


    <div class="content-page">
        <div class="content">

            <!-- Topbar Start -->
            <?php include_once('../includes/topbar.php'); ?>
            <!-- end Topbar -->

            <!-- Start Content-->
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h2 class="page-title">ตารางงาน</h2>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="row">

                                    <!-- Calendar side -->
                                    <div class="col-lg-9">
                                        <div class="mt-4 mt-lg-0">
                                            <div id="calendar" class="fc fc-media-screen fc-direction-ltr fc-theme-standard" style="min-height: 600px; height: auto;"></div>
                                        </div>
                                    </div> <!-- end col -->

                                    <!-- รายละเอียดงาน -->
                                    <div class="col-lg-3 d-flex flex-column">
                                        <?php if ($canManageTasks): ?>
                                        <div class="d-grid mb-3">
                                            <button class="btn btn-lg font-16 btn-success"
                                                    id="btn-new-event"
                                                    data-bs-toggle="modal" data-bs-target="#addTaskModal" disabled>
                                                <i class="mdi mdi-plus-circle-outline"></i> เพิ่มงาน
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-grid mb-3">
                                            <button class="btn btn-outline-success" id="refresh-btn" onclick="refreshCurrentView();">
                                                <i class="mdi mdi-refresh" id="refresh-icon"></i> <span id="refresh-text">รีเฟรชงาน</span>
                                            </button>
                                        </div>

                                        <div class="card h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">งานทั้งหมดของเดือน</h5>
                                                <span class="badge bg-primary" id="task-count">0</span>
                                            </div>
                                            <div class="card-body p-0 d-flex flex-column" style="height: calc(100vh - 300px);">
                                                <div id="monthly-tasks" class="small flex-grow-1" style="overflow-y: auto; padding: 1rem;">
                                                    <!-- จะถูกโหลดด้วย JavaScript -->
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- end col-->

                                </div> <!-- end row -->
                            </div> <!-- end card body-->
                        </div> <!-- end card -->

                        <?php if ($canManageTasks): ?>
                        <?php include_once('../includes/modals/task_add_modal.php'); ?>
                        <?php include_once('../includes/modals/task_edit_modal.php'); ?>
                        <?php endif; ?>

                        <!-- Task Detail Modal -->
                        <div class="modal fade" id="task-detail-modal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">รายละเอียดงาน</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="task-detail-content">
                                        <!-- Content will be loaded here -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-info" id="btn-view-detail">ดูรายละเอียดเต็ม</button>
                                        <?php if ($canManageTasks): ?>
                                        <button type="button" class="btn btn-primary" id="btn-edit-task">แก้ไขงาน</button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end col-12 -->
                </div> <!-- end row -->

            </div> <!-- container -->

        </div> <!-- content -->

<?php include_once '../includes/footer.php'; ?>

<!-- FullCalendar - โหลดหลัง footer -->
<script src="../assets/vendor/fullcalendar/index.global.min.js"></script>

<script>
function changeAcademicYear(yearId) {
    $.post('../api/set_academic_year.php', { year_id: yearId }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            Swal.fire('ข้อผิดพลาด', response.message, 'error');
        }
    }, 'json');
}
</script>

<script>
// User permission variables
const userRole = '<?php echo $userRole; ?>';
const canManageTasks = <?php echo $canManageTasks ? 'true' : 'false'; ?>;

// Initialize FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        locale: 'th',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: false,
        height: 'auto',
        contentHeight: 'auto',
        expandRows: true,
        events: function(info, successCallback, failureCallback) {
            // Load events from server
            fetch('../api/calendar_api.php?action=calendar_events', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Events loaded:', data);
                    if (data.error) {
                        console.error('API Error:', data.error);
                        successCallback([]);
                    } else {
                        successCallback(data);
                    }
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        }
    });
    
    calendar.render();
    
    // Make calendar accessible globally
    window.calendar = calendar;
});
</script>

<style>
/* Custom styles for expandable calendar */

/* ปรับความสูงของแต่ละวัน */
.fc-daygrid-day {
    min-height: 150px !important;
    height: auto !important;
}

.fc-daygrid-day-frame {
    min-height: 140px !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

.fc-daygrid-day-top {
    flex-shrink: 0 !important;
}

.fc-daygrid-day-events {
    flex-grow: 1 !important;
    margin-bottom: 5px !important;
}

.fc-daygrid-event-harness {
    margin-bottom: 2px !important;
}

.fc-event {
    font-size: 11px !important;
    padding: 2px 4px !important;
    margin-bottom: 2px !important;
    border-radius: 3px !important;
    cursor: pointer !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.fc-more-link {
    background-color: #007bff !important;
    color: white !important;
    font-size: 10px !important;
    padding: 2px 6px !important;
    border-radius: 3px !important;
    text-decoration: none !important;
}

.fc-more-link:hover {
    background-color: #0056b3 !important;
    color: white !important;
}

/* ให้ปฏิทินขยายตามเนื้อหา */
#calendar {
    min-height: 600px !important;
    height: auto !important;
}

.fc {
    height: auto !important;
}

.fc-view-harness {
    height: auto !important;
}

.fc-scroller {
    overflow: visible !important;
    height: auto !important;
}

.fc-scroller-liquid-absolute {
    position: relative !important;
}

.fc-daygrid-body {
    height: auto !important;
    width: 100% !important;
}

.fc-scrollgrid {
    border-collapse: collapse !important;
}

.fc-scrollgrid-sync-table {
    height: auto !important;
}

/* แก้เส้นตาราง */
.fc-theme-standard td,
.fc-theme-standard th {
    border: 1px solid #ddd !important;
}

.fc-daygrid-day-frame {
    position: relative !important;
}

/* ให้แถวขยายตามเนื้อหา */
.fc-daygrid-day-bg {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Refresh button animation */
.mdi-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Monthly tasks scrollbar styling */
#monthly-tasks {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

#monthly-tasks::-webkit-scrollbar {
    width: 8px;
}

#monthly-tasks::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 4px;
}

#monthly-tasks::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
    transition: background 0.2s ease;
}

#monthly-tasks::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Task card hover effects */
#monthly-tasks .border:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .fc-daygrid-day {
        min-height: 80px !important;
    }
    
    .fc-event {
        font-size: 10px !important;
        padding: 1px 2px !important;
    }
    
    .col-lg-3 .card-body {
        height: calc(100vh - 400px) !important;
    }
}
</style>

<script>
// Function to open edit task modal
function openEditTaskModal(taskId) {
    if (!taskId) {
        alert('ไม่พบรหัสงาน');
        return;
    }

    // Fetch task details from server
    fetch(`config/task_actions.php?action=get&id=${taskId}`)
        .then(response => response.json())
        .then(result => {
            // Handle both old and new response formats
            let task;
            if (result.success && result.data) {
                task = result.data; // New format
            } else {
                task = result; // Old format
            }
            
            if (task && task.id) {
                // Hide detail modal first
                const detailModal = bootstrap.Modal.getInstance(document.getElementById('task-detail-modal'));
                if (detailModal) {
                    detailModal.hide();
                }

                // Populate form with task data
                document.getElementById('editTaskId').value = task.id || '';
                document.getElementById('editTaskTitle').value = task.title || '';
                document.getElementById('editTaskDescription').value = task.description || '';
                document.getElementById('editTaskPriority').value = task.priority || 'medium';
                document.getElementById('editTaskStatus').value = task.status || 'pending';
                document.getElementById('editTaskStartDate').value = task.start_date || '';
                document.getElementById('editTaskEndDate').value = task.end_date || '';

                // Load projects with special handling for completed projects
                loadProjectsForEditModal(task.project_id);

                // Handle assignment type
                const assignedType = document.getElementById('editAssignedType');
                const individualAssignment = document.getElementById('editIndividualAssignment');
                const departmentAssignment = document.getElementById('editDepartmentAssignment');

                if (task.assigned_type) {
                    assignedType.value = task.assigned_type;
                    
                    if (task.assigned_type === 'department') {
                        individualAssignment.style.display = 'none';
                        departmentAssignment.style.display = 'block';
                        
                        // Set selected departments from task.assigned_departments
                        if (task.assigned_departments) {
                            const departmentIds = task.assigned_departments.split(',').filter(id => id.trim());
                            const deptSelect = document.getElementById('editAssignedDepartments');
                            if (deptSelect) {
                                // Initialize Select2 if needed
                                if ($(deptSelect).hasClass('select2-hidden-accessible')) {
                                    $(deptSelect).val(departmentIds).trigger('change');
                                } else {
                                    Array.from(deptSelect.options).forEach(option => {
                                        option.selected = departmentIds.includes(option.value);
                                    });
                                }
                            }
                        }
                    } else {
                        individualAssignment.style.display = 'block';
                        departmentAssignment.style.display = 'none';
                        
                        // Set selected individuals from task.assigned_individuals
                        if (task.assigned_individuals) {
                            const individualIds = task.assigned_individuals.split(',').filter(id => id.trim());
                            const individualSelect = document.getElementById('editAssignedIndividuals');
                            if (individualSelect) {
                                // Initialize Select2 if needed
                                if ($(individualSelect).hasClass('select2-hidden-accessible')) {
                                    $(individualSelect).val(individualIds).trigger('change');
                                } else {
                                    Array.from(individualSelect.options).forEach(option => {
                                        option.selected = individualIds.includes(option.value);
                                    });
                                }
                            }
                        }
                    }
                    
                    // Trigger change event
                    assignedType.dispatchEvent(new Event('change'));
                }

                // Re-initialize Select2 for edit modal after setting values
                setTimeout(() => {
                    if (typeof $ !== 'undefined' && $.fn.select2) {
                        const editIndividualsSelect = document.getElementById('editAssignedIndividuals');
                        const editDepartmentsSelect = document.getElementById('editAssignedDepartments');
                        
                        if (editIndividualsSelect) {
                            $(editIndividualsSelect).select2({
                                placeholder: "เลือกผู้รับผิดชอบ...",
                                allowClear: true
                            });
                        }
                        
                        if (editDepartmentsSelect) {
                            $(editDepartmentsSelect).select2({
                                placeholder: "เลือกฝ่าย...",
                                allowClear: true
                            });
                        }
                    }
                }, 100);

                // Show the edit modal (only if user can manage tasks)
                if (canManageTasks) {
                    const editModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                    editModal.show();
                } else {
                    alert('คุณไม่มีสิทธิ์แก้ไขงาน');
                }
            } else {
                alert('ไม่สามารถโหลดข้อมูลงานได้');
            }
        })
        .catch(error => {
            console.error('Error loading task:', error);
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูลงาน');
        });
}

// Function to delete task
function deleteTask(taskId) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', taskId);

    fetch('config/task_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('ลบงานเรียบร้อยแล้ว');
            const modal = bootstrap.Modal.getInstance(document.getElementById('event-modal'));
            if (modal) modal.hide();
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบงานได้'));
        }
    })
    .catch(error => {
        console.error('Error deleting task:', error);
        alert('เกิดข้อผิดพลาดในการลบงาน');
    });
}

// Debug script to test modal functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Debug: DOM loaded');
    
    // Check if bootstrap is loaded
    console.log('Debug: bootstrap object:', typeof bootstrap);
    
    // Check for edit parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'edit' && urlParams.get('id')) {
        const taskId = parseInt(urlParams.get('id'));
        if (taskId) {
            console.log('Edit task requested for ID:', taskId);
            // Wait for data to load then open edit modal
            setTimeout(() => {
                if (typeof openEditTaskModal === 'function') {
                    openEditTaskModal(taskId);
                } else {
                    console.error('openEditTaskModal function not found');
                }
            }, 1000);
        }
    }
    
    // Setup assignment type switcher for task detail modal (if exists)
    const assignedType = document.getElementById('event-assigned-type');
    if (assignedType) {
        assignedType.addEventListener('change', function() {
            const individualAssignment = document.getElementById('individual-assignment');
            const departmentAssignment = document.getElementById('department-assignment');
            
            if (this.value === 'department') {
                if (individualAssignment) individualAssignment.style.display = 'none';
                if (departmentAssignment) departmentAssignment.style.display = 'block';
            } else {
                if (individualAssignment) individualAssignment.style.display = 'block';
                if (departmentAssignment) departmentAssignment.style.display = 'none';
            }
        });
    }
    
    // Load dropdown options
    loadSimpleProjects();
    loadSimpleDepartments();
    loadSimpleMembers();
    loadMonthlyTasks();
    
    // Setup calendar month change detection
    setupCalendarMonthListener();
    
    // Setup form submission
    const form = document.getElementById('form-event');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveSimpleTask();
        });
    }

    // Setup edit task button
    const editTaskBtn = document.getElementById('btn-edit-task');
    if (editTaskBtn) {
        editTaskBtn.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            if (taskId) {
                openEditTaskModal(taskId);
            } else {
                alert('ไม่พบรหัสงาน กรุณาลองใหม่อีกครั้ง');
            }
        });
    }

    // Setup view detail button
    const viewDetailBtn = document.getElementById('btn-view-detail');
    if (viewDetailBtn) {
        viewDetailBtn.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            if (taskId) {
                window.location.href = `taskDetail.php?id=${taskId}`;
            } else {
                alert('ไม่พบรหัสงาน กรุณาลองใหม่อีกครั้ง');
            }
        });
    }
});

// Simple load functions
function loadSimpleProjects() {
    fetch('../api/project_get.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('event-project');
            if (select) {
                select.innerHTML = '<option value="">เลือกโปรเจค</option>';
                data.forEach(project => {
                    select.innerHTML += `<option value="${project.id}">${project.title || project.name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading projects:', error));
}

function loadSimpleDepartments() {
    fetch('../api/departments.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('event-department');
            if (select) {
                select.innerHTML = '';
                data.forEach(dept => {
                    select.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading departments:', error));
}

function loadSimpleMembers() {
    fetch('../api/members.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('event-individuals');
            if (select) {
                select.innerHTML = '';
                data.forEach(member => {
                    select.innerHTML += `<option value="${member.student_id}">${member.name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading members:', error));
}

// Function to load monthly tasks
function loadMonthlyTasks(year = null, month = null) {
    const currentDate = new Date();
    
    // Use provided year/month or current date
    if (!year) year = currentDate.getFullYear();
    if (!month) month = String(currentDate.getMonth() + 1).padStart(2, '0');
    
    console.log(`🗓️ Loading monthly tasks for ${year}-${month}`);
    console.log('Function called with params:', { providedYear: arguments[0], providedMonth: arguments[1], finalYear: year, finalMonth: month });
    
    fetch(`../api/calendar_api.php?action=monthly&year=${year}&month=${month}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Parsed data:', data);
                
                const container = document.getElementById('monthly-tasks');
                if (container) {
                    if (data && data.length > 0) {
                        let html = '';
                        
                        // Add scroll hint if there are many tasks
                        if (data.length > 5) {
                            html += `
                                <div class="alert alert-info py-2 mb-3" style="font-size: 11px;">
                                    <i class="mdi mdi-information-outline"></i> มีงาน ${data.length} รายการ เลื่อนลงเพื่อดูทั้งหมด
                                </div>
                            `;
                        }
                        
                        data.forEach(task => {
                            const priorityClass = getPriorityClass(task.priority);
                            const statusClass = getStatusClass(task.status);
                            const dueDate = new Date(task.end_date);
                            const currentDateObj = new Date();
                            const isOverdue = dueDate < currentDateObj && task.status !== 'completed';
                            
                            html += `
                                <div class="mb-2 p-2 border rounded ${isOverdue ? 'border-danger' : 'border-light'}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-truncate" title="${task.title}">${task.title}</h6>
                                            <div class="d-flex gap-1 mb-1">
                                                <span class="badge ${priorityClass}">${getPriorityText(task.priority)}</span>
                                                <span class="badge ${statusClass}">${getStatusText(task.status)}</span>
                                            </div>
                                            <small class="text-muted">
                                                ${task.start_date} - ${task.end_date}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                        console.log(`✅ Tasks rendered successfully for ${year}-${month}`);
                        
                        // Update task count badge
                        const taskCount = document.getElementById('task-count');
                        if (taskCount) {
                            taskCount.textContent = data.length;
                            taskCount.className = data.length > 10 ? 'badge bg-warning' : 'badge bg-primary';
                        }
                        
                        // Update card header with current month/year
                        const cardHeader = document.querySelector('#monthly-tasks').closest('.card').querySelector('.card-title');
                        if (cardHeader) {
                            const monthNames = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                              'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                            cardHeader.textContent = `งานทั้งหมดของเดือน${monthNames[parseInt(month)]} ${year}`;
                            console.log(`📝 Updated header to: ${cardHeader.textContent}`);
                        }
                    } else {
                        container.innerHTML = '<div class="text-center py-4"><i class="mdi mdi-calendar-check text-muted" style="font-size: 2rem;"></i><p class="text-muted mb-0 mt-2">ไม่มีงานในเดือนนี้</p></div>';
                        console.log(`ℹ️ No tasks found for ${year}-${month}`);
                        
                        // Update task count badge
                        const taskCount = document.getElementById('task-count');
                        if (taskCount) {
                            taskCount.textContent = '0';
                            taskCount.className = 'badge bg-secondary';
                        }
                        
                        // Update card header even when no tasks
                        const cardHeader = document.querySelector('#monthly-tasks').closest('.card').querySelector('.card-title');
                        if (cardHeader) {
                            const monthNames = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                              'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                            cardHeader.textContent = `งานทั้งหมดของเดือน${monthNames[parseInt(month)]} ${year}`;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error loading monthly tasks:', error);
                const container = document.getElementById('monthly-tasks');
                if (container) {
                    container.innerHTML = '<p class="text-danger mb-0">เกิดข้อผิดพลาดในการโหลดงาน</p>';
                }
            });
}

// Helper functions for task display
function getPriorityClass(priority) {
    switch(priority) {
        case 'urgent': return 'bg-danger';
        case 'high': return 'bg-warning';
        case 'medium': return 'bg-info';
        case 'low': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function getStatusClass(status) {
    switch(status) {
        case 'completed': return 'bg-success';
        case 'in_progress': return 'bg-primary';
        case 'pending': return 'bg-warning';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function getPriorityText(priority) {
    switch(priority) {
        case 'urgent': return 'เร่งด่วน';
        case 'high': return 'สูง';
        case 'medium': return 'ปานกลาง';
        case 'low': return 'ต่ำ';
        default: return 'ไม่ระบุ';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'completed': return 'เสร็จสิ้น';
        case 'in_progress': return 'กำลังดำเนินการ';
        case 'pending': return 'รอดำเนินการ';
        case 'cancelled': return 'ยกเลิก';
        default: return 'ไม่ระบุ';
    }
}

// Refresh current view
function refreshCurrentView() {
    console.log('Refreshing current view...');
    
    // Show loading state
    const refreshBtn = document.getElementById('refresh-btn');
    const refreshIcon = document.getElementById('refresh-icon');
    const refreshText = document.getElementById('refresh-text');
    
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.classList.remove('btn-outline-success');
        refreshBtn.classList.add('btn-outline-secondary');
    }
    if (refreshIcon) {
        refreshIcon.classList.add('mdi-spin');
    }
    if (refreshText) {
        refreshText.textContent = 'กำลังรีเฟรช...';
    }
    
    // Refresh calendar
    if (typeof loadTasks === 'function') {
        loadTasks();
    }
    
    // Refresh monthly tasks based on current calendar view
    updateMonthlyTasksFromCalendar();
    
    // Reset button state after delay
    setTimeout(() => {
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.classList.remove('btn-outline-secondary');
            refreshBtn.classList.add('btn-outline-success');
        }
        if (refreshIcon) {
            refreshIcon.classList.remove('mdi-spin');
        }
        if (refreshText) {
            refreshText.textContent = 'รีเฟรชงาน';
        }
    }, 1500);
}

// Setup calendar month change listener
function setupCalendarMonthListener() {
    console.log('Setting up calendar month listener...');
    
    // Try to get calendar from taskCalendar.js
    function checkCalendarAndSetup() {
        if (typeof calendar !== 'undefined' && calendar) {
            console.log('Calendar found, setting up month change listener');
            
            // Override the calendar initialization to add our listener
            const originalRender = calendar.render;
            calendar.render = function() {
                originalRender.call(this);
                console.log('Calendar rendered, adding datesSet listener');
                
                // Add datesSet callback after render
                this.setOption('datesSet', function(dateInfo) {
                    console.log('Calendar datesSet event triggered:', dateInfo);
                    
                    // Get the view's current date - use the view's currentStart for better accuracy
                    const currentDate = dateInfo.view.currentStart || dateInfo.start;
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    
                    console.log(`Calendar view changed to: ${year}-${month}`);
                    console.log('View type:', dateInfo.view.type);
                    
                    // Load tasks for the current month being viewed
                    setTimeout(() => {
                        loadMonthlyTasks(year, month);
                    }, 100); // Small delay to ensure calendar is fully updated
                });
            };
            
            // If calendar is already rendered, set up the listener immediately
            if (calendar.el && calendar.el.querySelector('.fc-daygrid-body')) {
                console.log('Calendar already rendered, setting up listener now');
                calendar.setOption('datesSet', function(dateInfo) {
                    console.log('Calendar datesSet event triggered:', dateInfo);
                    
                    const currentDate = dateInfo.view.currentStart || dateInfo.start;
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    
                    console.log(`Calendar view changed to: ${year}-${month}`);
                    
                    setTimeout(() => {
                        loadMonthlyTasks(year, month);
                    }, 100);
                });
            }
            
        } else {
            console.log('Calendar not ready yet, retrying in 1 second...');
            setTimeout(checkCalendarAndSetup, 1000);
        }
    }
    
    // Start checking for calendar
    setTimeout(checkCalendarAndSetup, 2000); // Give more time for calendar to initialize
    
    // Backup method: Listen for clicks on prev/next buttons
    setTimeout(() => {
        setupButtonListeners();
    }, 3000);
}

// Setup backup button listeners for prev/next navigation
function setupButtonListeners() {
    console.log('Setting up backup button listeners...');
    
    // Find and attach listeners to calendar navigation buttons
    const prevButton = document.querySelector('.fc-prev-button');
    const nextButton = document.querySelector('.fc-next-button');
    const todayButton = document.querySelector('.fc-today-button');
    
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            console.log('Previous button clicked');
            setTimeout(() => {
                updateMonthlyTasksFromCalendar();
            }, 300); // Wait for calendar to update
        });
        console.log('Previous button listener attached');
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            console.log('Next button clicked');
            setTimeout(() => {
                updateMonthlyTasksFromCalendar();
            }, 300); // Wait for calendar to update
        });
        console.log('Next button listener attached');
    }
    
    if (todayButton) {
        todayButton.addEventListener('click', function() {
            console.log('Today button clicked');
            setTimeout(() => {
                updateMonthlyTasksFromCalendar();
            }, 300); // Wait for calendar to update
        });
        console.log('Today button listener attached');
    }
}

// Update monthly tasks based on current calendar view
function updateMonthlyTasksFromCalendar() {
    console.log('Updating monthly tasks from calendar...');
    
    if (typeof calendar !== 'undefined' && calendar) {
        try {
            const currentDate = calendar.getDate();
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            
            console.log(`Updating tasks for ${year}-${month} (from calendar.getDate())`);
            loadMonthlyTasks(year, month);
        } catch (error) {
            console.error('Error getting calendar date:', error);
            
            // Fallback: try to extract from calendar title
            const titleEl = document.querySelector('.fc-toolbar-title');
            if (titleEl) {
                const titleText = titleEl.textContent;
                console.log('Calendar title:', titleText);
                
                // Try to extract year and month from title
                const currentDate = new Date();
                loadMonthlyTasks(currentDate.getFullYear(), String(currentDate.getMonth() + 1).padStart(2, '0'));
            }
        }
    } else {
        console.log('Calendar not available, using current date');
        const currentDate = new Date();
        loadMonthlyTasks(currentDate.getFullYear(), String(currentDate.getMonth() + 1).padStart(2, '0'));
    }
}

// Simple save task function
function saveSimpleTask() {
    const formData = new FormData();
    const taskId = document.getElementById('event-id').value;
    
    formData.append('action', taskId ? 'edit' : 'add');
    if (taskId) formData.append('id', taskId);
    
    formData.append('title', document.getElementById('event-title').value);
    formData.append('description', document.getElementById('event-description').value);
    formData.append('project_id', document.getElementById('event-project').value);
    formData.append('start_date', document.getElementById('event-start-date').value);
    formData.append('end_date', document.getElementById('event-end-date').value);
    formData.append('priority', document.getElementById('event-priority').value);
    formData.append('status', document.getElementById('event-status').value);
    
    const assignedType = document.getElementById('event-assigned-type').value;
    formData.append('assigned_type', assignedType);
    
    if (assignedType === 'department') {
        const selectedDepartments = Array.from(document.getElementById('event-department').selectedOptions).map(option => option.value);
        selectedDepartments.forEach(deptId => {
            formData.append('assigned_departments[]', deptId);
        });
    } else {
        const selectedIndividuals = Array.from(document.getElementById('event-individuals').selectedOptions).map(option => option.value);
        selectedIndividuals.forEach(studentId => {
            formData.append('assigned_individuals[]', studentId);
        });
    }
    
    fetch('config/task_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('บันทึกงานเรียบร้อยแล้ว');
            const modal = bootstrap.Modal.getInstance(document.getElementById('event-modal'));
            if (modal) modal.hide();
            // Refresh page or calendar if needed
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกงานได้'));
        }
    })
    .catch(error => {
        console.error('Error saving task:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
    });
}

// Load data for the new add task modal
document.addEventListener('DOMContentLoaded', function() {
    // Load projects for the new modal
    loadProjectsForModal();
    loadMembersForModal();
    loadDepartmentsForModal();
    setupAssignmentTypeToggle();
    
    // Load data for edit modal too
    loadProjectsForEditModal();
    loadMembersForEditModal();
    loadDepartmentsForEditModal();
    setupEditAssignmentTypeToggle();
});

function loadProjectsForModal() {
    console.log('Loading projects for modal...');
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../api/project_get.php',
            type: 'GET',
            dataType: 'json',
            success: function(projects) {
                console.log('Parsed projects data:', projects);
                console.log('Projects count:', projects.length);
                    let options = '<option value="">ไม่มีโครงการ</option>';
                    projects.forEach(function(project) {
                        console.log('Processing project:', project);
                        // Filter out completed projects
                        if (project.status !== 'completed') {
                            const projectName = project.title || project.name || 'ไม่ระบุชื่อ';
                            options += `<option value="${project.id}">${projectName}</option>`;
                        }
                    });
                    
                    console.log('Generated project options HTML:', options);
                    $('#taskProject').html(options);
                    resolve(projects);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading projects:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    reject(error);
                }
        });
    });
}


function loadProjectsForEditModal(currentProjectId = null) {
    console.log('Loading projects for edit modal with current project ID:', currentProjectId);
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../api/project_get.php',
            type: 'GET',
            dataType: 'json',
            success: function(projects) {
                console.log('Parsed edit projects data:', projects);
                console.log('Edit projects count:', projects.length);
                    
                    let options = '<option value="">ไม่มีโครงการ</option>';
                    projects.forEach(function(project) {
                        console.log('Processing edit project:', project);
                        // If no currentProjectId (initial load), only show active projects
                        if (!currentProjectId) {
                            if (project.status !== 'completed') {
                                const projectName = project.title || project.name || 'ไม่ระบุชื่อ';
                                options += `<option value="${project.id}">${projectName}</option>`;
                            }
                        } else {
                            // Include active projects or the current project (even if completed)
                            if (project.status !== 'completed' || project.id == currentProjectId) {
                                const projectName = project.title || project.name || 'ไม่ระบุชื่อ';
                                let optionText = projectName;
                                if (project.status === 'completed' && project.id == currentProjectId) {
                                    optionText += ' (เสร็จสิ้นแล้ว)';
                                }
                                options += `<option value="${project.id}">${optionText}</option>`;
                            }
                        }
                    });
                    
                    console.log('Generated edit project options HTML:', options);
                    $('#editTaskProject').html(options);
                    
                    // Set the current project value after options are loaded
                    if (currentProjectId) {
                        setTimeout(() => {
                            $('#editTaskProject').val(currentProjectId || '');
                        }, 100);
                    }
                    resolve(projects);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading edit projects:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    reject(error);
                }
        });
    });
}

function loadMembersForModal() {
    console.log('Loading members for modal...');
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../api/members.php',
            type: 'GET',
            dataType: 'json',
            success: function(members) {
                console.log('Parsed members data:', members);
                console.log('Members count:', members.length);
                    
                    let options = '';
                    members.forEach(function(member) {
                        console.log('Processing member:', member);
                        options += `<option value="${member.student_id}">${member.full_name}</option>`;
                    });
                    
                    console.log('Generated options HTML:', options);
                    $('#assignedIndividuals').html(options);
                    resolve(members);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading members:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    reject(error);
                }
        });
    });
}

function loadMembersForEditModal() {
    console.log('Loading members for edit modal...');
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../api/members.php',
            type: 'GET',
            dataType: 'json',
            success: function(members) {
                console.log('Parsed edit members data:', members);
                console.log('Edit members count:', members.length);
                
                let options = '';
                members.forEach(function(member) {
                    console.log('Processing edit member:', member);
                    options += `<option value="${member.student_id}">${member.full_name}</option>`;
                });
                
                console.log('Generated edit options HTML:', options);
                $('#editAssignedIndividuals').html(options);
                resolve(members);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading edit members:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                reject(error);
            }
        });
    });
}

function loadDepartmentsForModal() {
    console.log('Loading departments for add modal...');
    fetch('../api/departments.php')
        .then(response => response.json())
        .then(data => {
            console.log('Departments data received:', data);
            const departmentSelect = document.getElementById('assignedDepartments');
            if (departmentSelect) {
                departmentSelect.innerHTML = '';
                
                // Support both formats: array directly or {success: true, departments: []}
                const departments = Array.isArray(data) ? data : (data.departments || []);
                console.log('Departments to load:', departments.length);
                
                departments.forEach(department => {
                    const option = document.createElement('option');
                    option.value = department.id;
                    option.textContent = department.name;
                    departmentSelect.appendChild(option);
                });
                
                // Initialize Select2 if available
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(departmentSelect).select2({
                        placeholder: "เลือกฝ่าย...",
                        allowClear: true
                    });
                }
            } else {
                console.error('assignedDepartments element not found');
            }
        })
        .catch(error => console.error('Error loading departments:', error));
}

function loadDepartmentsForEditModal() {
    console.log('Loading departments for edit modal...');
    fetch('../api/departments.php')
        .then(response => response.json())
        .then(data => {
            console.log('Departments data for edit received:', data);
            const departmentSelect = document.getElementById('editAssignedDepartments');
            if (departmentSelect) {
                departmentSelect.innerHTML = '';
                
                // Support both formats: array directly or {success: true, departments: []}
                const departments = Array.isArray(data) ? data : (data.departments || []);
                console.log('Departments for edit to load:', departments.length);
                
                departments.forEach(department => {
                    const option = document.createElement('option');
                    option.value = department.id;
                    option.textContent = department.name;
                    departmentSelect.appendChild(option);
                });
                
                // Initialize Select2 if available
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(departmentSelect).select2({
                        placeholder: "เลือกฝ่าย...",
                        allowClear: true
                    });
                }
            } else {
                console.error('editAssignedDepartments element not found');
            }
        })
        .catch(error => console.error('Error loading departments for edit:', error));
}

function setupAssignmentTypeToggle() {
    const assignedType = document.getElementById('assignedType');
    const individualAssignment = document.getElementById('individualAssignment');
    const departmentAssignment = document.getElementById('departmentAssignment');
    
    if (assignedType && individualAssignment && departmentAssignment) {
        assignedType.addEventListener('change', function() {
            if (this.value === 'individual') {
                individualAssignment.style.display = 'block';
                departmentAssignment.style.display = 'none';
            } else {
                individualAssignment.style.display = 'none';
                departmentAssignment.style.display = 'block';
            }
        });
        
        // Set initial state
        assignedType.dispatchEvent(new Event('change'));
    }
}

function setupEditAssignmentTypeToggle() {
    const editAssignedType = document.getElementById('editAssignedType');
    const editIndividualAssignment = document.getElementById('editIndividualAssignment');
    const editDepartmentAssignment = document.getElementById('editDepartmentAssignment');
    
    if (editAssignedType && editIndividualAssignment && editDepartmentAssignment) {
        editAssignedType.addEventListener('change', function() {
            if (this.value === 'individual') {
                editIndividualAssignment.style.display = 'block';
                editDepartmentAssignment.style.display = 'none';
            } else {
                editIndividualAssignment.style.display = 'none';
                editDepartmentAssignment.style.display = 'block';
            }
        });
        
        // Set initial state
        editAssignedType.dispatchEvent(new Event('change'));
    }
}

// Handle form submission for the new modal
document.addEventListener('DOMContentLoaded', function() {
    const addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create');
            
            fetch('config/task_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
                    if (modal) modal.hide();
                    
                    // Reset form
                    this.reset();
                    
                    // Refresh calendar
                    if (typeof calendar !== 'undefined') {
                        calendar.refetchEvents();
                    }
                    
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'เพิ่มงานใหม่เรียบร้อยแล้ว',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('เพิ่มงานใหม่เรียบร้อยแล้ว');
                    }
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถเพิ่มงานได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            });
        });
    }
});

// Handle edit task form submission
document.addEventListener('DOMContentLoaded', function() {
    const editTaskForm = document.getElementById('editTaskForm');
    if (editTaskForm) {
        editTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'edit');
            
            fetch('config/task_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTaskModal'));
                    if (modal) modal.hide();
                    
                    // Refresh calendar
                    if (typeof calendar !== 'undefined') {
                        calendar.refetchEvents();
                    }
                    
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'อัปเดตงานเรียบร้อยแล้ว',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('อัปเดตงานเรียบร้อยแล้ว');
                    }
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถอัปเดตงานได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            });
        });
    }
});

// Setup edit modal data loading
document.addEventListener('DOMContentLoaded', function() {
    // Setup assignment type toggle for edit modal
    const editAssignedType = document.getElementById('editAssignedType');
    const editIndividualAssignment = document.getElementById('editIndividualAssignment');
    const editDepartmentAssignment = document.getElementById('editDepartmentAssignment');
    
    if (editAssignedType && editIndividualAssignment && editDepartmentAssignment) {
        editAssignedType.addEventListener('change', function() {
            if (this.value === 'individual') {
                editIndividualAssignment.style.display = 'block';
                editDepartmentAssignment.style.display = 'none';
            } else {
                editIndividualAssignment.style.display = 'none';
                editDepartmentAssignment.style.display = 'block';
            }
        });
    }
    
    // Re-initialize Select2 when modals are shown
    const addTaskModal = document.getElementById('addTaskModal');
    const editTaskModal = document.getElementById('editTaskModal');
    
    if (addTaskModal) {
        addTaskModal.addEventListener('shown.bs.modal', function() {
            // Re-initialize Select2 for add modal
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#assignedIndividuals').select2({
                    placeholder: "เลือกผู้รับผิดชอบ...",
                    allowClear: true,
                    dropdownParent: $('#addTaskModal')
                });
                
                $('#assignedDepartments').select2({
                    placeholder: "เลือกฝ่าย...",
                    allowClear: true,
                    dropdownParent: $('#addTaskModal')
                });
            }
        });
    }
    
    if (editTaskModal) {
        editTaskModal.addEventListener('shown.bs.modal', function() {
            // Re-initialize Select2 for edit modal
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#editAssignedIndividuals').select2({
                    placeholder: "เลือกผู้รับผิดชอบ...",
                    allowClear: true,
                    dropdownParent: $('#editTaskModal')
                });
                
                $('#editAssignedDepartments').select2({
                    placeholder: "เลือกฝ่าย...",
                    allowClear: true,
                    dropdownParent: $('#editTaskModal')
                });
            }
        });
    }
});
</script>
