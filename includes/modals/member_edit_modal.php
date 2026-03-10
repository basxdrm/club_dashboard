<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMemberModalLabel">แก้ไขข้อมูลสมาชิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMemberForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Account Information -->
                        <div class="col-12">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle me-1"></i> ข้อมูลบัญชี</h5>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_password" class="form-label">รหัสผ่านใหม่</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="edit_password" name="password" minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword">
                                    <i class="mdi mdi-eye-outline"></i>
                                </button>
                            </div>
                            <small class="text-muted">เว้นว่างไว้หากไม่ต้องการเปลี่ยน</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">สิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="member">สมาชิก</option>
                                <option value="board">บอร์ดบริหาร</option>
                                <option value="advisor">อาจารย์ที่ปรึกษา</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="1">สมาชิก</option>
                                <option value="0">ลาออก</option>
                                <option value="2">จบการศึกษา</option>
                            </select>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-12 mt-3">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-details me-1"></i> ข้อมูลส่วนตัว</h5>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="edit_prefix" class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prefix" name="prefix" required>
                                <option value="">เลือก</option>
                                <option value="นาย">นาย</option>
                                <option value="นางสาว">นางสาว</option>
                                <option value="นาง">นาง</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="edit_first_name_th" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_first_name_th" name="first_name_th" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="edit_last_name_th" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_last_name_th" name="last_name_th" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="edit_nickname_th" class="form-label">ชื่อเล่น <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nickname_th" name="nickname_th" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_first_name_en" class="form-label">ชื่อภาษาอังกฤษ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_first_name_en" name="first_name_en" placeholder="First Name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_last_name_en" class="form-label">นามสกุลภาษาอังกฤษ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_last_name_en" name="last_name_en" placeholder="Last Name" required>
                        </div>

                        <div class="col-md-4 mb-3" id="edit-student-id-field">
                            <label for="edit_student_id" class="form-label">รหัสนักเรียน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_student_id" name="student_id" required>
                        </div>

                        <div class="col-md-4 mb-3" id="edit-birth-date-field">
                            <label for="edit_birth_date" class="form-label">วันเกิด <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_birth_date" name="birth_date" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="edit_phone_number" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="edit_phone_number" name="phone_number" required>
                        </div>

                        <!-- Education Information -->
                        <div class="col-12 mt-3" id="edit-section-education-header">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-school me-1"></i> ข้อมูลการศึกษา</h5>
                        </div>
                        <div id="edit-section-education" class="row">

                        <div class="col-md-12 mb-3">
                            <label for="edit_academic_year_id" class="form-label">ปีการศึกษา</label>
                            <select class="form-select" id="edit_academic_year_id" name="academic_year_id">
                                <option value="">เลือก</option>
                                <?php
                                try {
                                    $years = $conn->query("SELECT id, year FROM academic_years ORDER BY year DESC")->fetchAll();
                                    foreach ($years as $year) {
                                        echo '<option value="' . $year['id'] . '">' . htmlspecialchars($year['year']) . '</option>';
                                    }
                                } catch (Exception $e) {}
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="edit_academic_status" class="form-label">สถานะการศึกษาสามัญ</label>
                            <select class="form-select" id="edit_academic_status" name="academic_status">
                                <option value="studying">กำลังเรียน</option>
                                <option value="graduated">จบแล้ว</option>
                                <option value="not_enrolled">ไม่ได้เรียน</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="edit_academic_grade" class="form-label">ชั้นปีสามัญ</label>
                            <input type="number" class="form-control" id="edit_academic_grade" name="academic_grade" min="1" max="6" placeholder="1-6">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="edit_academic_room" class="form-label">ห้องสามัญ</label>
                            <input type="text" class="form-control" id="edit_academic_room" name="academic_room" placeholder="1,2,3...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">ชั้นสามัญ (อัตโนมัติ)</label>
                            <input type="text" class="form-control" id="edit_class_academic_preview" readonly placeholder="ม.X/X">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="edit_agama_status" class="form-label">สถานะการศึกษาศาสนา</label>
                            <select class="form-select" id="edit_agama_status" name="agama_status">
                                <option value="studying">กำลังเรียน</option>
                                <option value="graduated">จบแล้ว</option>
                                <option value="not_enrolled">ไม่ได้เรียน</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="edit_agama_grade" class="form-label">ชั้นปีศาสนา</label>
                            <input type="number" class="form-control" id="edit_agama_grade" name="agama_grade" min="1" max="10" placeholder="1-10">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="edit_agama_room" class="form-label">ห้องศาสนา</label>
                            <input type="text" class="form-control" id="edit_agama_room" name="agama_room" placeholder="1,2,3...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">ชั้นศาสนา (อัตโนมัติ)</label>
                            <input type="text" class="form-control" id="edit_class_agama_preview" readonly placeholder="ศ.X/X">
                        </div>

                        <!-- Club Information -->
                        </div><!-- end #edit-section-education -->

                        <div class="col-12 mt-3" id="edit-section-club-header">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-group me-1"></i> ข้อมูลชมรม</h5>
                        </div>
                        <div id="edit-section-club" class="row">

                        <div class="col-md-4 mb-3">
                            <label for="edit_department_id" class="form-label">ฝ่าย</label>
                            <select class="form-select" id="edit_department_id" name="department_id">
                                <option value="">ไม่ระบุ</option>
                                <?php
                                try {
                                    $db = new Database();
                                    $conn = $db->getConnection();
                                    $depts = $conn->query("SELECT id, name FROM club_departments ORDER BY name")->fetchAll();
                                    foreach ($depts as $dept) {
                                        echo '<option value="' . $dept['id'] . '">' . htmlspecialchars($dept['name']) . '</option>';
                                    }
                                } catch (Exception $e) {
                                    error_log("Error loading departments: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="edit_position_id" class="form-label">ตำแหน่ง</label>
                            <select class="form-select" id="edit_position_id" name="position_id">
                                <?php
                                try {
                                    $positions = $conn->query("SELECT id, name, level FROM club_positions ORDER BY level DESC")->fetchAll();
                                    foreach ($positions as $pos) {
                                        echo '<option value="' . $pos['id'] . '">' . htmlspecialchars($pos['name']) . '</option>';
                                    }
                                } catch (Exception $e) {
                                    error_log("Error loading positions: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="edit_member_generation" class="form-label">รุ่น</label>
                            <input type="number" class="form-control" id="edit_member_generation" name="member_generation" min="1" placeholder="เช่น 12">
                        </div>
                    </div>
                        </div><!-- end #edit-section-club -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle edit password visibility
document.getElementById('toggleEditPassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('edit_password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('mdi-eye-outline', 'mdi-eye-off-outline');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('mdi-eye-off-outline', 'mdi-eye-outline');
    }
});

// Toggle education/club sections for advisor (edit modal)
function toggleEditAdvisorFields(isAdvisor) {
    const sections = ['#edit-section-education-header', '#edit-section-education', '#edit-section-club-header', '#edit-section-club'];
    sections.forEach(sel => {
        const el = document.querySelector(sel);
        if (el) el.style.display = isAdvisor ? 'none' : '';
    });
    const studentIdField = document.getElementById('edit-student-id-field');
    const studentIdInput = document.getElementById('edit_student_id');
    if (studentIdField) studentIdField.style.display = isAdvisor ? 'none' : '';
    if (studentIdInput) studentIdInput.required = !isAdvisor;

    const birthDateField = document.getElementById('edit-birth-date-field');
    const birthDateInput = document.getElementById('edit_birth_date');
    if (birthDateField) birthDateField.style.display = isAdvisor ? 'none' : '';
    if (birthDateInput) birthDateInput.required = !isAdvisor;
}

document.getElementById('edit_role')?.addEventListener('change', function() {
    toggleEditAdvisorFields(this.value === 'advisor');
});
</script>
