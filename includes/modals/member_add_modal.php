<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">เพิ่มสมาชิกใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMemberForm">
                <div class="modal-body">
                    <div class="row">
                        <!-- Account Information -->
                        <div class="col-12">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle me-1"></i> ข้อมูลบัญชี</h5>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small class="text-muted">ใช้สำหรับเข้าสู่ระบบ</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="mdi mdi-eye-outline"></i>
                                </button>
                            </div>
                            <small class="text-muted">อย่างน้อย 8 ตัวอักษร</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">สิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="member">สมาชิก</option>
                                <option value="board">บอร์ดบริหาร</option>
                                <option value="advisor">อาจารย์ที่ปรึกษา</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                            </select>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-12 mt-3">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-details me-1"></i> ข้อมูลส่วนตัว</h5>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="prefix" class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                            <select class="form-select" id="prefix" name="prefix" required>
                                <option value="">เลือก</option>
                                <option value="นาย">นาย</option>
                                <option value="นางสาว">นางสาว</option>
                                <option value="นาง">นาง</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="first_name_th" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name_th" name="first_name_th" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="last_name_th" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name_th" name="last_name_th" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="nickname_th" class="form-label">ชื่อเล่น <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nickname_th" name="nickname_th" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="first_name_en" class="form-label">ชื่อภาษาอังกฤษ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name_en" name="first_name_en" placeholder="First Name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name_en" class="form-label">นามสกุลภาษาอังกฤษ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name_en" name="last_name_en" placeholder="Last Name" required>
                        </div>

                        <div class="col-md-4 mb-3" id="student-id-field">
                            <label for="student_id" class="form-label">รหัสนักเรียน <span class="text-danger" id="student-id-required">*</span></label>
                            <input type="text" class="form-control" id="student_id" name="student_id" required>
                        </div>

                        <div class="col-md-4 mb-3" id="birth-date-field">
                            <label for="birth_date" class="form-label">วันเกิด <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="phone_number" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                        </div>

                        <!-- Education Information -->
                        <div class="col-12 mt-3" id="section-education-header">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-school me-1"></i> ข้อมูลการศึกษา</h5>
                        </div>

                        <div id="section-education" class="row">
                        <div class="col-md-12 mb-3">
                            <label for="academic_year_id" class="form-label">ปีการศึกษา</label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                <option value="">เลือก</option>
                                <?php
                                try {
                                    $db = new Database();
                                    $conn = $db->getConnection();
                                    $years = $conn->query("SELECT id, year FROM academic_years ORDER BY year DESC")->fetchAll();
                                    foreach ($years as $year) {
                                        echo '<option value="' . $year['id'] . '">' . htmlspecialchars($year['year']) . '</option>';
                                    }
                                } catch (Exception $e) {}
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="academic_status" class="form-label">สถานะการศึกษาสามัญ</label>
                            <select class="form-select" id="academic_status" name="academic_status">
                                <option value="studying">กำลังเรียน</option>
                                <option value="graduated">จบแล้ว</option>
                                <option value="not_enrolled">ไม่ได้เรียน</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="academic_grade" class="form-label">ชั้นปีสามัญ</label>
                            <input type="number" class="form-control" id="academic_grade" name="academic_grade" min="1" max="6" placeholder="1-6">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="academic_room" class="form-label">ห้องสามัญ</label>
                            <input type="text" class="form-control" id="academic_room" name="academic_room" placeholder="1,2,3...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">ชั้นสามัญ (อัตโนมัติ)</label>
                            <input type="text" class="form-control" id="class_academic_preview" readonly placeholder="ม.X/X">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="agama_status" class="form-label">สถานะการศึกษาศาสนา</label>
                            <select class="form-select" id="agama_status" name="agama_status">
                                <option value="studying">กำลังเรียน</option>
                                <option value="graduated">จบแล้ว</option>
                                <option value="not_enrolled">ไม่ได้เรียน</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="agama_grade" class="form-label">ชั้นปีศาสนา</label>
                            <input type="number" class="form-control" id="agama_grade" name="agama_grade" min="1" max="10" placeholder="1-10">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="agama_room" class="form-label">ห้องศาสนา</label>
                            <input type="text" class="form-control" id="agama_room" name="agama_room" placeholder="1,2,3...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">ชั้นศาสนา (อัตโนมัติ)</label>
                            <input type="text" class="form-control" id="class_agama_preview" readonly placeholder="ศ.X/X">
                        </div>

                        <!-- Club Information -->
                        </div><!-- end #section-education -->

                        <div class="col-12 mt-3" id="section-club-header">
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-group me-1"></i> ข้อมูลชมรม</h5>
                        </div>

                        <div id="section-club" class="row">
                        <div class="col-md-4 mb-3">
                            <label for="department_id" class="form-label">ฝ่าย</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">ไม่ระบุ</option>
                                <?php
                                try {
                                    $depts = $conn->query("SELECT id, name FROM club_departments ORDER BY name")->fetchAll();
                                    foreach ($depts as $dept) {
                                        echo '<option value="' . $dept['id'] . '">' . htmlspecialchars($dept['name']) . '</option>';
                                    }
                                } catch (Exception $e) {}
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="position_id" class="form-label">ตำแหน่ง</label>
                            <select class="form-select" id="position_id" name="position_id">
                                <?php
                                try {
                                    $positions = $conn->query("SELECT id, name, level FROM club_positions ORDER BY level DESC")->fetchAll();
                                    // หา level ต่ำสุด (สมาชิก)
                                    $minLevel = min(array_column($positions, 'level'));
                                    foreach ($positions as $pos) {
                                        // เลือกตำแหน่งที่มี level ต่ำสุดเป็นค่าเริ่มต้น
                                        $selected = ($pos['level'] == $minLevel) ? 'selected' : '';
                                        echo '<option value="' . $pos['id'] . '" ' . $selected . '>' . htmlspecialchars($pos['name']) . '</option>';
                                    }
                                } catch (Exception $e) {}
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="member_generation" class="form-label">รุ่น</label>
                            <input type="number" class="form-control" id="member_generation" name="member_generation" min="1" placeholder="เช่น 12">
                        </div>
                    </div>
                        </div><!-- end #section-club -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('mdi-eye-outline', 'mdi-eye-off-outline');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('mdi-eye-off-outline', 'mdi-eye-outline');
    }
});

// Toggle education/club sections for advisor
function toggleAdvisorFields(isAdvisor) {
    const sections = ['#section-education-header', '#section-education', '#section-club-header', '#section-club'];
    sections.forEach(sel => {
        const el = document.querySelector(sel);
        if (el) el.style.display = isAdvisor ? 'none' : '';
    });
    const studentIdField = document.getElementById('student-id-field');
    const studentIdInput = document.getElementById('student_id');
    if (studentIdField) studentIdField.style.display = isAdvisor ? 'none' : '';
    if (studentIdInput) studentIdInput.required = !isAdvisor;

    const birthDateField = document.getElementById('birth-date-field');
    const birthDateInput = document.getElementById('birth_date');
    if (birthDateField) birthDateField.style.display = isAdvisor ? 'none' : '';
    if (birthDateInput) birthDateInput.required = !isAdvisor;
}

document.getElementById('role')?.addEventListener('change', function() {
    toggleAdvisorFields(this.value === 'advisor');
});

// Reset modal when opened
document.getElementById('addMemberModal')?.addEventListener('show.bs.modal', function() {
    document.getElementById('role').value = 'member';
    toggleAdvisorFields(false);
});
</script>
