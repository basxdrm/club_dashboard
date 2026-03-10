<!-- Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentModalLabel">เพิ่มฝ่าย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="departmentForm">
                <div class="modal-body">
                    <input type="hidden" id="department_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อฝ่าย <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="department_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" id="department_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ไอคอน (MDI)</label>
                        <input type="text" class="form-control" id="department_icon" name="icon" placeholder="mdi mdi-account-group">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สี</label>
                        <input type="color" class="form-control form-control-color" id="department_color" name="color" value="#3B7DDD">
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
