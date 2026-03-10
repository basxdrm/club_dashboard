<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$id = intval($_GET['id']);
$format = $_GET['format'] ?? 'html'; // html or json

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT eb.*, 
        e.name as equipment_name,
        e.serial_number,
        e.brand,
        e.model,
        ec.name as category_name,
        CONCAT(prof_borrower.first_name_th, ' ', prof_borrower.last_name_th) as borrower_name,
        prof_borrower.profile_picture as borrower_picture,
        u_borrower.email as borrower_email,
        mc.phone_number as borrower_phone,
        t.title as task_name,
        CONCAT(prof_approved.first_name_th, ' ', prof_approved.last_name_th) as approved_by_name
        FROM equipment_borrowing eb
        JOIN equipment e ON eb.equipment_id = e.id
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        JOIN users u_borrower ON eb.borrower_id = u_borrower.id
        JOIN profiles prof_borrower ON u_borrower.id = prof_borrower.user_id
        LEFT JOIN member_contacts mc ON u_borrower.id = mc.user_id
        LEFT JOIN tasks t ON eb.task_id = t.id
        LEFT JOIN users u_approved ON eb.approved_by = u_approved.id
        LEFT JOIN profiles prof_approved ON u_approved.id = prof_approved.user_id
        WHERE eb.id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$borrow = $stmt->fetch();

if (!$borrow) {
    if ($format === 'json') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    } else {
        echo '<div class="alert alert-danger">ไม่พบข้อมูล</div>';
    }
    exit;
}

// ถ้าต้องการ JSON สำหรับการแก้ไข
if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'borrowing' => $borrow
    ]);
    exit;
}

$statusBadges = [
    'pending' => ['bg-warning', 'รอดำเนินการ'],
    'borrowed' => ['bg-primary', 'กำลังยืม'],
    'request_return' => ['bg-info', 'ขอคืน'],
    'returned' => ['bg-success', 'คืนแล้ว'],
    'overdue' => ['bg-danger', 'เกินกำหนด'],
    'cancelled' => ['bg-secondary', 'ยกเลิก']
];
$badge = $statusBadges[$borrow['status']] ?? ['bg-secondary', 'ไม่ระบุ'];
?>

<div class="row">
    <div class="col-md-6">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">ข้อมูลผู้ยืม</h5>
        <div class="d-flex align-items-center mb-3">
            <img src="../assets/images/users/<?php echo $borrow['borrower_picture'] ?: 'avatar-1.jpg'; ?>"
                 class="rounded-circle avatar-lg me-3" alt="">
            <div>
                <h4 class="mb-1"><?php echo htmlspecialchars($borrow['borrower_name']); ?></h4>
                <p class="text-muted mb-0">
                    <i class="mdi mdi-email-outline"></i> <?php echo htmlspecialchars($borrow['borrower_email']); ?>
                </p>
                <?php if ($borrow['borrower_phone']): ?>
                <p class="text-muted mb-0">
                    <i class="mdi mdi-phone"></i> <?php echo htmlspecialchars($borrow['borrower_phone']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">ข้อมูลอุปกรณ์</h5>
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <th style="width: 120px;">ชื่ออุปกรณ์:</th>
                <td><strong><?php echo htmlspecialchars($borrow['equipment_name']); ?></strong></td>
            </tr>
            <?php if ($borrow['serial_number']): ?>
            <tr>
                <th>S/N:</th>
                <td><?php echo htmlspecialchars($borrow['serial_number']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>หมวดหมู่:</th>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($borrow['category_name']); ?></span></td>
            </tr>
            <?php if ($borrow['brand']): ?>
            <tr>
                <th>ยี่ห้อ/รุ่น:</th>
                <td>
                    <?php echo htmlspecialchars($borrow['brand']); ?>
                    <?php if ($borrow['model']): ?>
                    / <?php echo htmlspecialchars($borrow['model']); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">รายละเอียดการยืม</h5>
        
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th style="width: 150px;">สถานะ:</th>
                        <td><span class="badge <?php echo $badge[0]; ?> fs-6"><?php echo $badge[1]; ?></span></td>
                    </tr>
                    <tr>
                        <th>วันที่ขอยืม:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($borrow['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>วันที่กำหนดยืม:</th>
                        <td><?php echo date('d/m/Y', strtotime($borrow['borrow_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>วันที่กำหนดคืน:</th>
                        <td>
                            <?php
                            $due_date = strtotime($borrow['due_date']);
                            $today = strtotime(date('Y-m-d'));
                            $is_overdue = ($due_date < $today && $borrow['status'] === 'borrowed');
                            ?>
                            <span class="<?php echo $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                <?php echo date('d/m/Y', $due_date); ?>
                            </span>
                            <?php if ($is_overdue): ?>
                            <span class="badge bg-danger ms-2">เกินกำหนด</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (isset($borrow['actual_borrow_date']) && $borrow['actual_borrow_date']): ?>
                    <tr>
                        <th>วันที่ยืมจริง:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($borrow['actual_borrow_date'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($borrow['return_date']): ?>
                    <tr>
                        <th>วันที่คืน:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($borrow['return_date'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th style="width: 150px;">วัตถุประสงค์:</th>
                        <td><?php echo nl2br(htmlspecialchars($borrow['purpose'])); ?></td>
                    </tr>
                    <?php if ($borrow['task_name']): ?>
                    <tr>
                        <th>งานที่เกี่ยวข้อง:</th>
                        <td>
                            <i class="mdi mdi-checkbox-marked-outline me-1"></i>
                            <?php echo htmlspecialchars($borrow['task_name']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($borrow['approved_by_name']): ?>
                    <tr>
                        <th>ผู้อนุมัติ:</th>
                        <td><?php echo htmlspecialchars($borrow['approved_by_name']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($borrow['approved_at']): ?>
                    <tr>
                        <th>วันที่อนุมัติ:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($borrow['approved_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isset($borrow['notes']) && $borrow['notes']): ?>
                    <tr>
                        <th>หมายเหตุ:</th>
                        <td><?php echo nl2br(htmlspecialchars($borrow['notes'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
