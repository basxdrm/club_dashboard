<!-- View Transaction Modal -->
<div class="modal fade" id="viewTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-file-document-outline me-2"></i>
                    รายละเอียดรายการ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- ข้อมูลหลัก -->
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 40%;">รหัสรายการ:</td>
                                <td><strong id="view_trans_code"></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">ประเภท:</td>
                                <td><span id="view_trans_type"></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">หมวดหมู่:</td>
                                <td id="view_trans_category"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">จำนวนเงิน:</td>
                                <td><strong id="view_trans_amount" class="fs-5"></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">วันที่ทำรายการ:</td>
                                <td id="view_trans_date"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">สถานะ:</td>
                                <td><span id="view_trans_status"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 40%;">งานที่เกี่ยวข้อง:</td>
                                <td id="view_trans_task"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">โปรเจค:</td>
                                <td id="view_trans_project"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">ผู้บันทึก:</td>
                                <td id="view_trans_recorded_by"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">วันที่บันทึก:</td>
                                <td id="view_trans_created_at"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">อัปเดตล่าสุด:</td>
                                <td id="view_trans_updated_at"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- รายละเอียด -->
                <div class="mt-3">
                    <label class="text-muted">รายละเอียด:</label>
                    <div class="p-2 bg-light rounded" id="view_trans_description"></div>
                </div>

                <!-- หมายเหตุ -->
                <div class="mt-3" id="view_notes_container">
                    <label class="text-muted">หมายเหตุ:</label>
                    <div class="p-2 bg-light rounded" id="view_trans_notes"></div>
                </div>

                <!-- ใบเสร็จ -->
                <div class="mt-3" id="view_receipt_container">
                    <label class="text-muted">ใบเสร็จ/สลิป:</label>
                    <div class="p-2">
                        <a href="#" id="view_receipt_link" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-file-document-outline me-1"></i>
                            <span id="view_receipt_name">ดูไฟล์</span>
                        </a>
                        <div id="view_receipt_preview" class="mt-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script>
    function viewTransaction(trans) {
        // รหัสรายการ
        $('#view_trans_code').text(trans.transaction_code || '-');

        // ประเภท
        if (trans.type === 'income') {
            $('#view_trans_type').html('<span class="badge bg-success">รายรับ</span>');
        } else {
            $('#view_trans_type').html('<span class="badge bg-danger">รายจ่าย</span>');
        }

        // หมวดหมู่
        $('#view_trans_category').text(trans.category_name || '-');

        // จำนวนเงิน
        const amountFormatted = parseFloat(trans.amount).toLocaleString('th-TH', {
            minimumFractionDigits: 2
        });
        const amountClass = trans.type === 'income' ? 'text-success' : 'text-danger';
        const amountSign = trans.type === 'income' ? '+' : '-';
        $('#view_trans_amount').html(`<span class="${amountClass}">${amountSign}${amountFormatted} ฿</span>`);

        // วันที่ทำรายการ
        if (trans.transaction_date) {
            const date = new Date(trans.transaction_date);
            $('#view_trans_date').text(date.toLocaleDateString('th-TH', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }));
        } else {
            $('#view_trans_date').text('-');
        }

        // สถานะ
        const statusBadge = {
            'pending': '<span class="badge bg-warning">รออนุมัติ</span>',
            'approved': '<span class="badge bg-success">อนุมัติแล้ว</span>',
            'rejected': '<span class="badge bg-danger">ปฏิเสธ</span>'
        };
        $('#view_trans_status').html(statusBadge[trans.status] || trans.status);

        // งานที่เกี่ยวข้อง
        if (trans.task_title) {
            $('#view_trans_task').html(`<a href="task_view.php?id=${trans.task_id}">${trans.task_title}</a>`);
        } else {
            $('#view_trans_task').text('ไม่ระบุ');
        }

        // โปรเจค
        $('#view_trans_project').text(trans.project_name || 'ไม่ระบุ');

        // ผู้บันทึก
        $('#view_trans_recorded_by').text(trans.recorded_by_name || '-');

        // วันที่บันทึก
        if (trans.created_at) {
            const createdDate = new Date(trans.created_at);
            $('#view_trans_created_at').text(createdDate.toLocaleString('th-TH'));
        } else {
            $('#view_trans_created_at').text('-');
        }

        // อัปเดตล่าสุด
        if (trans.updated_at) {
            const updatedDate = new Date(trans.updated_at);
            $('#view_trans_updated_at').text(updatedDate.toLocaleString('th-TH'));
        } else {
            $('#view_trans_updated_at').text('-');
        }

        // รายละเอียด
        $('#view_trans_description').text(trans.description || '-');

        // หมายเหตุ
        if (trans.notes) {
            $('#view_notes_container').show();
            $('#view_trans_notes').text(trans.notes);
        } else {
            $('#view_notes_container').hide();
        }

        // ใบเสร็จ
        if (trans.receipt_image) {
            $('#view_receipt_container').show();
            const receiptUrl = '../assets/images/receipts/' + trans.receipt_image;
            $('#view_receipt_link').attr('href', receiptUrl);
            $('#view_receipt_name').text(trans.receipt_image);

            // Preview รูปภาพ (ถ้าเป็นรูป)
            const ext = trans.receipt_image.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                $('#view_receipt_preview').html(`<img src="${receiptUrl}" class="img-fluid rounded" style="max-height: 200px;">`);
            } else {
                $('#view_receipt_preview').html('');
            }
        } else {
            $('#view_receipt_container').hide();
        }

        $('#viewTransactionModal').modal('show');
    }
</script>