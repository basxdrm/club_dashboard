<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEquipmentModalLabel">แก้ไขอุปกรณ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEquipmentForm">
                <input type="hidden" id="edit_equipment_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_equipment_name" class="form-label">ชื่อ<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_equipment_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">หมวดหมู่<span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">เลือกหมวดหมู่</option>
                                <?php
                                try {
                                    $pdo_modal = getDatabaseConnection();
                                    $categories_modal = $pdo_modal->query("SELECT * FROM equipment_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
                                    foreach ($categories_modal as $cat) {
                                        echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                                    }
                                } catch (Exception $e) {
                                    echo '<option value="">ไม่สามารถโหลดข้อมูลได้</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_brand" class="form-label">ยี่ห้อ</label>
                            <input type="text" class="form-control" id="edit_brand" name="brand">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_model" class="form-label">รุ่น</label>
                            <input type="text" class="form-control" id="edit_model" name="model">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="edit_serial_number" name="serial_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_purchase_date" class="form-label">วันที่จัดซื้อ</label>
                            <input type="date" class="form-control" id="edit_purchase_date" name="purchase_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_purchase_price" class="form-label">ราคาจัดซื้อ</label>
                            <input type="number" class="form-control" id="edit_purchase_price" name="purchase_price" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_location" class="form-label">สถานที่จัดเก็บ</label>
                            <input type="text" class="form-control" id="edit_location" name="location">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">รูปภาพ</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif">
                        <small class="text-muted">รองรับไฟล์: JPG, PNG, GIF (ขนาดไม่เกิน 5MB) - เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยนรูป</small>
                        <div id="current_image_preview" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">สถานะ<span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="available">พร้อมใช้</option>
                            <option value="borrowed">ถูกยืม</option>
                            <option value="maintenance">ซ่อมบำรุง</option>
                            <option value="broken">ชำรุด</option>
                            <option value="retired">ปลดระ</option>
                        </select>
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
