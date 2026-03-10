<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">แก้ไขรายจ่าย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editExpenseForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_expense_id">
                <input type="hidden" name="type" value="expense">
                <input type="hidden" name="task_id" id="edit_expense_task_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="edit_expense_description" rows="2" required placeholder="ระบุรายละเอียดค่าใช้จ่าย"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมวดหมู่<span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" id="edit_expense_category" required>
                            <option value="">เลือกหมวดหมู่</option>
                            <?php foreach ($expense_categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จำนวนเงิน (บาท)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" id="edit_expense_amount" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="transaction_date" id="edit_expense_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ไฟล์แนบใหม่ (ใบเสร็จ/สลิป)</label>
                        <input type="file" class="form-control" name="receipt" accept="image/*,.pdf">
                        <small class="text-muted d-block">รองรับไฟล์: JPG, PNG, PDF (ไม่เกิน 5MB)</small>
                        <small class="text-muted" id="edit_current_receipt"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" name="notes" id="edit_expense_notes" rows="2" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>