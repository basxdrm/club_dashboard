<!-- Transaction Category Modal -->
<div class="modal fade" id="transactionCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionCategoryModalLabel">เพิ่มหมวดหมู่รายรับรายจ่าย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionCategoryForm">
                <div class="modal-body">
                    <input type="hidden" id="transaction_category_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="transaction_category_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                        <select class="form-select" id="transaction_category_type" name="type" required>
                            <option value="">เลือกประเภท</option>
                            <option value="income">รายรับ</option>
                            <option value="expense">รายจ่าย</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" id="transaction_category_description" name="description" rows="3"></textarea>
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
