<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEquipmentModalLabel">เพิ่มอุปกรณ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEquipmentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_equipment_name" class="form-label">ชื่อ<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_equipment_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_category_id" class="form-label">หมวดหมู่<span class="text-danger">*</span></label>
                            <select class="form-select" id="add_category_id" name="category_id" required>
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
                            <label for="add_brand" class="form-label">ยี่ห้อ</label>
                            <input type="text" class="form-control" id="add_brand" name="brand">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_model" class="form-label">รุ่น</label>
                            <input type="text" class="form-control" id="add_model" name="model">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="add_serial_number" name="serial_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_purchase_date" class="form-label">วันที่จัดซื้อ</label>
                            <input type="date" class="form-control" id="add_purchase_date" name="purchase_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_purchase_price" class="form-label">ราคาจัดซื้อ</label>
                            <input type="number" class="form-control" id="add_purchase_price" name="purchase_price" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_location" class="form-label">สถานที่จัดเก็บ</label>
                            <input type="text" class="form-control" id="add_location" name="location">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add_image" class="form-label">รูปภาพ</label>
                        <input type="file" class="form-control" id="add_image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif">
                        <small class="text-muted">รองรับไฟล์: JPG, PNG, GIF (ขนาดไม่เกิน 5MB)</small>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">สถานะ<span class="text-danger">*</span></label>
                        <select class="form-select" id="add_status" name="status" required>
                            <option value="available" selected>พร้อมใช้</option>
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
