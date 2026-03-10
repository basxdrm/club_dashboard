<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProjectModalLabel">แก้ไขโปรเจค</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProjectForm">
                <input type="hidden" id="edit_project_id" name="project_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_project_name" class="form-label">ชื่อโปรเจค <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_project_name" name="name" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="edit_project_description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="edit_project_description" name="description" rows="3"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_project_start_date" class="form-label">วันที่เริ่มต้น <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_project_start_date" name="start_date" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_project_end_date" class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_project_end_date" name="end_date" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_project_budget" class="form-label">งบประมาณ (บาท)</label>
                            <input type="number" class="form-control" id="edit_project_budget" name="budget" min="0" step="0.01" value="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_project_location" class="form-label">สถานที่</label>
                            <input type="text" class="form-control" id="edit_project_location" name="location" placeholder="เช่น ห้องประชุม, อาคาร 1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_project_status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_project_status" name="status" required>
                                <option value="planning">วางแผน</option>
                                <option value="in_progress">กำลังดำเนินการ</option>
                                <option value="on_hold">พักชั่วคราว</option>
                                <option value="completed">เสร็จสิ้น</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                    </div>
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
