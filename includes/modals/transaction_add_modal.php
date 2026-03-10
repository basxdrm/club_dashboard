<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มรายการรับ-จ่าย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTransactionForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                                <select class="form-select" name="type" id="trans_type" required>
                                    <option value="">-- เลือกประเภท --</option>
                                    <option value="income">รายรับ</option>
                                    <option value="expense">รายจ่าย</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="trans_category" required>
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
                                <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">วันที่ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">งานที่เกี่ยวข้อง</label>
                                <input type="text" class="form-control" value="ไม่ระบุ" readonly disabled>
                                <small class="text-muted">หากต้องการเพิ่มรายการให้งาน กรุณาไปที่หน้ารายละเอียดงาน</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ไฟล์แนบ (ใบเสร็จ/สลิป)</label>
                                <input type="file" class="form-control" name="receipt" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
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
// Filter categories by type
$('#trans_type').on('change', function() {
    const type = $(this).val();
    $('#trans_category option').each(function() {
        const optionType = $(this).data('type');
        if (!type || optionType === type || $(this).val() === '') {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    $('#trans_category').val('');
});

// Submit form
$('#addTransactionForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: '../api/transaction_add.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('Success response:', response);
            if (response.success) {
                Swal.fire('สำเร็จ', response.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            let errorMsg = 'ไม่สามารถดำเนินการได้';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMsg = response.message;
                }
            } catch(e) {
                console.error('Parse error:', e);
            }
            
            Swal.fire('เกิดข้อผิดพลาด', errorMsg, 'error');
        }
    });
});
</script>
