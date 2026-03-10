<!-- Borrow Equipment Modal -->
<div class="modal fade" id="borrowEquipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="borrowEquipmentForm" method="POST" action="<?php echo $base_path; ?>api/equipment_borrow_submit.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-hand-extended me-2"></i>ขอยืมอุปกรณ์</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="equipmentSelect" class="form-label">อุปกรณ์ <span class="text-danger">*</span></label>
                                <select class="form-select" id="equipmentSelect" name="equipment_id" required>
                                    <option value="">-- เลือกอุปกรณ์ --</option>
                                </select>
                                <small class="text-muted">จะแสดงเฉพาะอุปกรณ์ที่พร้อมใช้งาน</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="borrowTask" class="form-label">งานที่เกี่ยวข้อง</label>
                                <select class="form-select" id="borrowTask" name="task_id">
                                    <option value="">-- ไม่ระบุ --</option>
                                    <?php
                                    if (isset($conn)) {
                                        $sql = "SELECT t.id, t.title, p.name as project_name 
                                                FROM tasks t 
                                                LEFT JOIN projects p ON t.project_id = p.id 
                                                WHERE t.status NOT IN ('completed', 'cancelled') 
                                                ORDER BY t.created_at DESC";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();
                                        while ($task = $stmt->fetch()) {
                                            $display = htmlspecialchars($task['title']);
                                            if ($task['project_name']) {
                                                $display .= ' (' . htmlspecialchars($task['project_name']) . ')';
                                            }
                                            echo '<option value="' . $task['id'] . '">' . $display . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="borrowPurpose" class="form-label">วัตถุประสงค์ <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="borrowPurpose" name="purpose" rows="3" required
                                  placeholder="ระบุวัตถุประสงค์ในการยืม"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="borrowDate" class="form-label">วันที่ยืม <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="borrowDate" name="borrow_date"
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dueDate" class="form-label">กำหนดคืน <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dueDate" name="due_date" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close"></i> ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-check"></i> ส่งคำขอ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadAvailableEquipment() {
    console.log('Loading equipment...');
    const apiUrl = '<?php echo $base_path; ?>api/get_available_equipment.php';
    console.log('API URL:', apiUrl);
    
    $.ajax({
        url: apiUrl,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Equipment data:', data);
            const select = $('#equipmentSelect');
            select.html('<option value="">-- เลือกอุปกรณ์ --</option>');
            
            if (data.error) {
                console.error('API Error:', data.error);
                select.append('<option value="" disabled>เกิดข้อผิดพลาด: ' + data.error + '</option>');
                return;
            }
            
            if (data && data.length > 0) {
                data.forEach(function(eq) {
                    select.append(`<option value="${eq.id}">${eq.name}${eq.category_name ? ' - ' + eq.category_name : ''}</option>`);
                });
            } else {
                select.append('<option value="" disabled>ไม่มีอุปกรณ์ว่าง</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading equipment:', error);
            console.error('Response:', xhr.responseText);
            const select = $('#equipmentSelect');
            select.html('<option value="">-- เลือกอุปกรณ์ --</option>');
            select.append('<option value="" disabled>เกิดข้อผิดพลาด: ' + error + '</option>');
        }
    });
}

// Load equipment when modal opens
$(document).ready(function() {
    $('#borrowEquipmentModal').on('show.bs.modal', function () {
        loadAvailableEquipment();
    });
});
</script>
