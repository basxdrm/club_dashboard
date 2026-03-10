<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editTaskModalLabel"><i class="mdi mdi-pencil me-2"></i>แก้ไขงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTaskForm">
                <input type="hidden" id="edit_task_id" name="task_id">
                <div class="modal-body">
                    <!-- ข้อมูลพื้นฐาน -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="mdi mdi-information me-1"></i>ข้อมูลพื้นฐาน</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="edit_task_title" class="form-label">ชื่องาน <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_task_title" name="title" placeholder="กรอกชื่องาน" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_task_project_id" class="form-label">โปรเจค</label>
                                    <select class="form-select" id="edit_task_project_id" name="project_id">
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
                                    <label for="edit_task_priority" class="form-label">ความสำคัญ <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_task_priority" name="priority" required>
                                        <option value="low">ต่ำ</option>
                                        <option value="medium">ปานกลาง</option>
                                        <option value="high">สูง</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="edit_task_description" class="form-label">รายละเอียด</label>
                                    <textarea class="form-control" id="edit_task_description" name="description" rows="3" placeholder="อธิบายรายละเอียดงาน..."></textarea>
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
                            <label for="edit_task_start_date" class="form-label">วันเริ่มต้น <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_task_start_date" name="start_date" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_task_due_date" class="form-label">วันสิ้นสุด <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_task_due_date" name="due_date" required>
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
                                    <input class="form-check-input" type="radio" name="assignment_mode" id="edit_mode_direct" value="direct" checked>
                                    <label class="form-check-label" for="edit_mode_direct">
                                        <i class="mdi mdi-account-check me-1 text-primary"></i>เลือกผู้รับผิดชอบเอง
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="assignment_mode" id="edit_mode_registration" value="registration">
                                    <label class="form-check-label" for="edit_mode_registration">
                                        <i class="mdi mdi-clipboard-text me-1 text-success"></i>ให้สมาชิกลงทะเบียนเอง
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- ส่วนเลือกผู้รับผิดชอบเอง (Direct Assignment) -->
                        <div id="editDirectAssignmentSection">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">ประเภทผู้รับผิดชอบ <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="edit_assignee_type" id="edit_type_department" value="department">
                                        <label class="form-check-label" for="edit_type_department">
                                            <i class="mdi mdi-account-group me-1"></i>ฝ่าย
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="edit_assignee_type" id="edit_type_individual" value="individual" checked>
                                        <label class="form-check-label" for="edit_type_individual">
                                            <i class="mdi mdi-account me-1"></i>บุคคล
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- เลือกฝ่าย -->
                            <div class="col-md-12 mb-3" id="editDepartmentSelectWrapper" style="display: none;">
                                <label for="edit_task_departments" class="form-label">เลือกฝ่าย <span class="text-danger">*</span></label>
                                <select class="select2-multiple form-control" id="edit_task_departments" name="departments[]" multiple="multiple" data-placeholder="เลือกฝ่ายที่รับผิดชอบ...">
                                    <?php
                                    try {
                                        $db = new Database();
                                        $conn = $db->getConnection();
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
                            <div class="col-md-12 mb-3" id="editIndividualSelectWrapper">
                                <label for="edit_task_assignees" class="form-label">เลือกผู้รับผิดชอบ <span class="text-danger">*</span></label>
                                <select class="select2-multiple form-control" id="edit_task_assignees" name="assignees[]" multiple="multiple" data-placeholder="เลือกผู้รับผิดชอบ...">
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
                        <div id="editRegistrationSection" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label for="edit_task_max_assignees" class="form-label">จำนวนผู้ลงทะเบียนสูงสุด <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_task_max_assignees" name="max_assignees" min="1" value="1" placeholder="กรอกจำนวนคน">
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
                    <button type="submit" class="btn btn-warning">
                        <i class="mdi mdi-content-save me-1"></i>บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for edit modal
    function initEditSelect2() {
        $('#edit_task_departments').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#editTaskModal'),
            placeholder: 'เลือกฝ่ายที่รับผิดชอบ...',
            allowClear: true,
            width: '100%'
        });
        
        $('#edit_task_assignees').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#editTaskModal'),
            placeholder: 'เลือกผู้รับผิดชอบ...',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Re-initialize Select2 when modal is shown
    $('#editTaskModal').on('shown.bs.modal', function() {
        initEditSelect2();
    });
    
    // Destroy Select2 when modal is hidden
    $('#editTaskModal').on('hidden.bs.modal', function() {
        if ($('#edit_task_departments').hasClass('select2-hidden-accessible')) {
            $('#edit_task_departments').select2('destroy');
        }
        if ($('#edit_task_assignees').hasClass('select2-hidden-accessible')) {
            $('#edit_task_assignees').select2('destroy');
        }
    });
    
    // Toggle assignment sections based on mode in edit modal
    $('input[name="assignment_mode"]').on('change', function() {
        const mode = $(this).val();
        
        if (mode === 'direct') {
            $('#editDirectAssignmentSection').show();
            $('#editRegistrationSection').hide();
        } else {
            $('#editDirectAssignmentSection').hide();
            $('#editRegistrationSection').show();
        }
    });
    
    // Toggle between department and individual selection in edit modal
    $('input[name="edit_assignee_type"]').on('change', function() {
        const type = $(this).val();
        
        if (type === 'department') {
            $('#editDepartmentSelectWrapper').show();
            $('#editIndividualSelectWrapper').hide();
            
            // Clear individual selection
            $('#edit_task_assignees').val(null).trigger('change');
        } else {
            $('#editDepartmentSelectWrapper').hide();
            $('#editIndividualSelectWrapper').show();
            
            // Clear department selection
            $('#edit_task_departments').val(null).trigger('change');
        }
    });
    
    // Edit Task Form Submission
    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        // Custom validation for select2 fields in edit modal
        let isValid = true;
        let errorMessage = '';
        
        // Check assignment mode
        const assignmentMode = $('input[name="assignment_mode"]:checked').val();
        
        if (assignmentMode === 'direct') {
            const assigneeType = $('input[name="assignee_type"]:checked').val();
            
            if (assigneeType === 'department') {
                const departments = $('#edit_task_departments').val();
                if (!departments || departments.length === 0) {
                    isValid = false;
                    errorMessage = 'กรุณาเลือกฝ่ายที่รับผิดชอบอย่างน้อย 1 ฝ่าย';
                }
            } else {
                const assignees = $('#edit_task_assignees').val();
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
            url: '../api/task_update.php',
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
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error
                });
            }
        });
    });
    
    // Validate end date >= start date in edit modal
    $('#edit_task_start_date, #edit_task_due_date').on('change', function() {
        const startDate = $('#edit_task_start_date').val();
        const endDate = $('#edit_task_due_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'วันสิ้นสุดต้องไม่น้อยกว่าวันเริ่มต้น'
            });
            $('#edit_task_due_date').val('');
        }
    });
});
</script>

<style>
#editTaskModal .card {
    box-shadow: none;
}

#editTaskModal .card-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

#editTaskModal .form-check-label {
    cursor: pointer;
}
</style>
