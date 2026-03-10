<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขรายการรับ-จ่าย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTransactionForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_trans_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                                <select class="form-select" name="type" id="edit_trans_type" required>
                                    <option value="">-- เลือกประเภท --</option>
                                    <option value="income">รายรับ</option>
                                    <option value="expense">รายจ่าย</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="edit_trans_category" required>
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    <?php
                                    $cat_stmt = $pdo->query("SELECT id, name, type FROM transaction_categories WHERE is_active = 1 ORDER BY type, name");
                                    while ($cat = $cat_stmt->fetch()): ?>
                                        <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" id="edit_amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">วันที่ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="transaction_date" id="edit_transaction_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">งานที่เกี่ยวข้อง</label>
                                <input type="text" class="form-control" id="edit_task_display" readonly disabled>
                                <input type="hidden" name="task_id" id="edit_task_id">
                                <small class="text-muted">ไม่สามารถแก้ไขงานที่เกี่ยวข้องได้</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ไฟล์แนบใหม่ (ใบเสร็จ/สลิป)</label>
                                <input type="file" class="form-control" name="receipt" accept="image/*,.pdf">
                                <div id="current_receipt_container" class="mt-2" style="display: none;">
                                    <small class="text-muted">ไฟล์ปัจจุบัน: </small>
                                    <a href="#" id="current_receipt_link" target="_blank" class="text-primary">
                                        <i class="mdi mdi-file-document-outline"></i>
                                        <span id="current_receipt_name"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="2"></textarea>
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

<script>
    function editTransaction(trans) {
        $('#edit_trans_id').val(trans.id);
        $('#edit_trans_type').val(trans.type);
        $('#edit_trans_category').val(trans.category_id);
        $('#edit_amount').val(trans.amount);
        $('#edit_transaction_date').val(trans.transaction_date);
        $('#edit_description').val(trans.description);
        $('#edit_task_id').val(trans.task_id || '');
        $('#edit_notes').val(trans.notes || '');

        // แสดงชื่องาน (disabled, ไม่สามารถแก้ไขได้)
        if (trans.task_title) {
            $('#edit_task_display').val(trans.task_title);
        } else {
            $('#edit_task_display').val('ไม่ระบุ');
        }

        if (trans.receipt_image) {
            $('#current_receipt_container').show();
            $('#current_receipt_name').text(trans.receipt_image);
            $('#current_receipt_link').attr('href', '../assets/images/receipts/' + trans.receipt_image);
        } else {
            $('#current_receipt_container').hide();
        }

        // Filter categories by type
        filterEditCategories();

        $('#editTransactionModal').modal('show');
    }

    // Filter categories by type in edit modal
    $('#edit_trans_type').on('change', filterEditCategories);

    function filterEditCategories() {
        const type = $('#edit_trans_type').val();
        $('#edit_trans_category option').each(function() {
            const optionType = $(this).data('type');
            if (!type || optionType === type || $(this).val() === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Submit edit form
    $('#editTransactionForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '../api/transaction_update.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดำเนินการได้', 'error');
            }
        });
    });
</script>