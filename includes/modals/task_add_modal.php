<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addTaskModalLabel"><i class="mdi mdi-plus-circle me-2"></i>สร้างงานใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addTaskForm" novalidate>
                <div class="modal-body">
                    <!-- ข้อมูลพื้นฐาน -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="mdi mdi-information me-1"></i>ข้อมูลพื้นฐาน</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="task_title" class="form-label">ชื่องาน <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="task_title" name="title" placeholder="กรอกชื่องาน" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="task_project_id" class="form-label">โปรเจค</label>
                                    <select class="form-select" id="task_project_id" name="project_id">
                                        <option value="">ไม่ระบุโปรเจค</option>
                                        <?php
                                        try {
                                            $db = new Database();
                                            $conn = $db->getConnection();
                                            $projects = $conn->query("SELECT id, name FROM projects WHERE status != 'cancelled' ORDER BY name")->fetchAll();
                                            foreach ($projects as $proj) {
                                                echo '<option value="' . $proj['id'] . '">' . htmlspecialchars($proj['name']) . '</option>';
                                            }
                                        } catch (Exception $e) {
                                            // Silently ignore
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="task_priority" class="form-label">ความสำคัญ <span class="text-danger">*</span></label>
                                    <select class="form-select" id="task_priority" name="priority" required>
                                        <option value="low">ต่ำ</option>
                                        <option value="medium" selected>ปานกลาง</option>
                                        <option value="high">สูง</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="task_description" class="form-label">รายละเอียด</label>
                                    <textarea class="form-control" id="task_description" name="description" rows="3" placeholder="อธิบายรายละเอียดงาน..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- กำหนดเวลา -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="mdi mdi-calendar-clock me-1"></i>กำหนดเวลา</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="task_start_date" class="form-label">วันเริ่มต้น <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="task_start_date" name="start_date" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="task_due_date" class="form-label">วันสิ้นสุด <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="task_due_date" name="due_date" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- การมอบหมายงาน -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="mdi mdi-account-multiple me-1"></i>การมอบหมายงาน</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">วิธีการมอบหมายงาน <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="assignment_mode" id="mode_direct" value="direct" checked>
                                            <label class="form-check-label" for="mode_direct">
                                                <i class="mdi mdi-account-check me-1 text-primary"></i>เลือกผู้รับผิดชอบเอง
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="assignment_mode" id="mode_registration" value="registration">
                                            <label class="form-check-label" for="mode_registration">
                                                <i class="mdi mdi-clipboard-text me-1 text-success"></i>ให้สมาชิกลงทะเบียนเอง
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- ส่วนเลือกผู้รับผิดชอบเอง (Direct Assignment) -->
                                <div id="directAssignmentSection">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">ประเภทผู้รับผิดชอบ <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="assignee_type" id="type_department" value="department">
                                                <label class="form-check-label" for="type_department">
                                                    <i class="mdi mdi-account-group me-1"></i>ฝ่าย
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="assignee_type" id="type_individual" value="individual" checked>
                                                <label class="form-check-label" for="type_individual">
                                                    <i class="mdi mdi-account me-1"></i>บุคคล
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- เลือกฝ่าย -->
                                    <div class="col-md-12 mb-3" id="departmentSelectWrapper" style="display: none;">
                                        <label for="task_departments" class="form-label">เลือกฝ่าย <span class="text-danger">*</span></label>
                                        <select class="select2-multiple form-control" id="task_departments" name="departments[]" multiple="multiple" data-placeholder="เลือกฝ่ายที่รับผิดชอบ...">
                                            <?php
                                            try {
                                                $departments = $conn->query("SELECT id, name FROM club_departments WHERE is_active = 1 ORDER BY name")->fetchAll();
                                                foreach ($departments as $dept) {
                                                    echo '<option value="' . $dept['id'] . '">' . htmlspecialchars($dept['name']) . '</option>';
                                                }
                                            } catch (Exception $e) {
                                                // Silently ignore
                                            }
                                            ?>
                                        </select>
                                        <small class="text-muted">สามารถเลือกได้หลายฝ่าย</small>
                                    </div>

                                    <!-- เลือกบุคคล -->
                                    <div class="col-md-12 mb-3" id="individualSelectWrapper">
                                        <label for="task_assignees" class="form-label">เลือกผู้รับผิดชอบ <span class="text-danger">*</span></label>
                                        <select class="select2-multiple form-control" id="task_assignees" name="assignees[]" multiple="multiple" data-placeholder="เลือกผู้รับผิดชอบ...">
                                            <?php
                                            try {
                                                // จัดกลุ่มตามฝ่าย
                                                $members = $conn->query("
                                                    SELECT u.id, CONCAT(p.prefix, p.first_name_th, ' ', p.last_name_th) as full_name,
                                                           COALESCE(d.name, 'ไม่มีฝ่าย') as department_name
                                                    FROM users u
                                                    JOIN profiles p ON u.id = p.user_id
                                                    LEFT JOIN member_club_info mci ON u.id = mci.user_id
                                                    LEFT JOIN club_departments d ON mci.department_id = d.id
                                                    WHERE u.status = 1
                                                    ORDER BY d.name, p.first_name_th
                                                ")->fetchAll();
                                                
                                                $grouped = [];
                                                foreach ($members as $member) {
                                                    $grouped[$member['department_name']][] = $member;
                                                }
                                                
                                                foreach ($grouped as $deptName => $deptMembers) {
                                                    echo '<optgroup label="' . htmlspecialchars($deptName) . '">';
                                                    foreach ($deptMembers as $m) {
                                                        echo '<option value="' . $m['id'] . '">' . htmlspecialchars($m['full_name']) . '</option>';
                                                    }
                                                    echo '</optgroup>';
                                                }
                                            } catch (Exception $e) {
                                                // Silently ignore
                                            }
                                            ?>
                                        </select>
                                        <small class="text-muted">สามารถเลือกได้หลายคน</small>
                                    </div>
                                </div>

                                <!-- ส่วนลงทะเบียนเอง (Registration Mode) -->
                                <div id="registrationSection" style="display: none;">
                                    <div class="col-md-12 mb-3">
                                        <label for="task_max_assignees" class="form-label">จำนวนผู้ลงทะเบียนสูงสุด <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="task_max_assignees" name="max_assignees" min="1" value="1" placeholder="กรอกจำนวนคน">
                                        <small class="text-muted">กำหนดจำนวนคนที่สามารถลงทะเบียนรับงานนี้ได้</small>
                                    </div>

                                    <div class="col-12">
                                        <div class="alert alert-info mb-0">
                                            <i class="mdi mdi-information me-1"></i>
                                            <strong>หมายเหตุ:</strong> ระบบจะสร้างลิงก์ลงทะเบียนให้อัตโนมัติ สามารถแชร์ให้สมาชิกลงทะเบียนรับงานได้
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>สร้างงาน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for multiple select
    function initSelect2() {
        $('#task_departments').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addTaskModal'),
            placeholder: 'เลือกฝ่ายที่รับผิดชอบ...',
            allowClear: true,
            width: '100%'
        });
        
        $('#task_assignees').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addTaskModal'),
            placeholder: 'เลือกผู้รับผิดชอบ...',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Re-initialize Select2 when modal is shown
    $('#addTaskModal').on('shown.bs.modal', function() {
        initSelect2();
    });
    
    // Destroy Select2 when modal is hidden
    $('#addTaskModal').on('hidden.bs.modal', function() {
        if ($('#task_departments').hasClass('select2-hidden-accessible')) {
            $('#task_departments').select2('destroy');
        }
        if ($('#task_assignees').hasClass('select2-hidden-accessible')) {
            $('#task_assignees').select2('destroy');
        }
    });
    
    // Toggle assignment sections based on mode
    $('input[name="assignment_mode"]').on('change', function() {
        const mode = $(this).val();
        
        if (mode === 'direct') {
            $('#directAssignmentSection').show();
            $('#registrationSection').hide();
            
            // Make assignee fields required
            toggleAssigneeRequirement(true);
        } else {
            $('#directAssignmentSection').hide();
            $('#registrationSection').show();
            
            // Remove assignee fields requirement
            toggleAssigneeRequirement(false);
        }
    });
    
    // Toggle between department and individual selection
    $('input[name="assignee_type"]').on('change', function() {
        const type = $(this).val();
        
        if (type === 'department') {
            $('#departmentSelectWrapper').show();
            $('#individualSelectWrapper').hide();
            
            // Clear individual selection
            $('#task_assignees').val(null).trigger('change');
        } else {
            $('#departmentSelectWrapper').hide();
            $('#individualSelectWrapper').show();
            
            // Clear department selection
            $('#task_departments').val(null).trigger('change');
        }
    });
    
    function toggleAssigneeRequirement(required) {
        // Remove HTML5 validation attributes since we use custom validation
        // Just clear selections when switching modes
        if (!required) {
            $('#task_assignees').val(null).trigger('change');
            $('#task_departments').val(null).trigger('change');
        }
    }
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    $('#task_start_date').val(today);
    
    // Validate end date >= start date
    $('#task_start_date, #task_due_date').on('change', function() {
        const startDate = $('#task_start_date').val();
        const endDate = $('#task_due_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'วันสิ้นสุดต้องไม่น้อยกว่าวันเริ่มต้น'
            });
            $('#task_due_date').val('');
        }
    });
});
</script>

<style>
/* Select2 styles for modal */
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
}

.select2-container--bootstrap-5 .select2-dropdown {
    z-index: 1060;
}

#addTaskModal .card {
    box-shadow: none;
}

#addTaskModal .card-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

#addTaskModal .form-check-label {
    cursor: pointer;
}
</style>
