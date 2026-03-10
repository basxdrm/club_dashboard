<!-- Add Member to Department Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">เพิ่มสมาชิกเข้าฝ่าย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="target_department_id">
                <div class="mb-3">
                    <input type="text" class="form-control" id="memberSearch" placeholder="ค้นหาสมาชิก...">
                </div>
                <div id="availableMembersList" style="max-height: 400px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>
