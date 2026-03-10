<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">เพิ่มรายจ่าย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm" enctype="multipart/form-data">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <input type="hidden" name="type" value="expense">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="2" required placeholder="ระบุรายละเอียดค่าใช้จ่าย"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมวดหมู่<span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" required>
                            <option value="">เลือกหมวดหมู่</option>
                            <?php foreach ($expense_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จำนวนเงิน (บาท)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ไฟล์แนบ (ใบเสร็จ/สลิป)</label>
                        <input type="file" class="form-control" name="receipt" accept="image/*,.pdf">
                        <small class="text-muted">รองรับไฟล์: JPG, PNG, PDF (ไม่เกิน 5MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
