<?php
define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/auth.php';
requireLogin();

$equipment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$equipment_id) {
    header('Location: equipment.php');
    exit();
}

try {
    $pdo = getDatabaseConnection();

    // Get equipment details
    $stmt = $pdo->prepare("SELECT e.*, c.name as category_name
        FROM equipment e
        LEFT JOIN equipment_categories c ON e.category_id = c.id
        WHERE e.id = ?");
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch();

    if (!$equipment) {
        header('Location: equipment.php');
        exit();
    }

    // Get borrowing history
    $stmt = $pdo->prepare("SELECT eb.*,
        CONCAT(p.prefix, ' ', p.first_name_th, ' ', p.last_name_th) as borrower_name,
        p.student_id,
        CONCAT(ap.prefix, ' ', ap.first_name_th, ' ', ap.last_name_th) as approved_by_name
        FROM equipment_borrowing eb
        JOIN users u ON eb.borrower_id = u.id
        JOIN profiles p ON u.id = p.user_id
        LEFT JOIN users au ON eb.approved_by = au.id
        LEFT JOIN profiles ap ON au.id = ap.user_id
        WHERE eb.equipment_id = ?
        ORDER BY eb.borrow_date DESC, eb.created_at DESC");
    $stmt->execute([$equipment_id]);
    $borrowing_history = $stmt->fetchAll();

    $page_title = 'รายละเอียดอุปกรณ์: ' . htmlspecialchars($equipment['name']);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    header('Location: equipment.php');
    exit();
}
?>
<?php include '../includes/header.php'; ?>

<?php include '../includes/sidebar.php'; ?>
<div class="content-page">
    <div class="content">
        <?php include '../includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="equipment.php">อุปกรณ์</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($page_title) ?></li>
                            </ol>
                        </div>
                        <h4 class="page-title"><?php echo $page_title; ?></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">ข้อมูลอุปกรณ์</h5>

                            <?php if (!empty($equipment['image']) && file_exists('../assets/images/equipment/' . $equipment['image'])): ?>
                                <div class="text-center mb-3">
                                    <img src="../assets/images/equipment/<?= htmlspecialchars($equipment['image']) ?>" alt="<?= htmlspecialchars($equipment['name']) ?>" class="img-fluid rounded shadow" style="max-height:250px;object-fit:contain;">
                                </div>
                            <?php endif; ?>

                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th width="35%">ชื่ออุปกรณ์:</th>
                                        <td><?= htmlspecialchars($equipment['name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>หมวดหมู่:</th>
                                        <td><?= htmlspecialchars($equipment['category_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>ยี่ห้อ:</th>
                                        <td><?= htmlspecialchars($equipment['brand'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>รุ่น:</th>
                                        <td><?= htmlspecialchars($equipment['model'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Serial Number:</th>
                                        <td><?= htmlspecialchars($equipment['serial_number'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>สถานะ:</th>
                                        <td>
                                            <?php
                                            $statusClass = ['available' => 'success', 'borrowed' => 'warning', 'maintenance' => 'info', 'broken' => 'danger', 'retired' => 'secondary'];
                                            $statusText = ['available' => 'พร้อมใช้', 'borrowed' => 'ถูกยืม', 'maintenance' => 'ซ่อมบำรุง', 'broken' => 'ชำรุด', 'retired' => 'ปลดระ'];
                                            ?>
                                            <span class="badge bg-<?= $statusClass[$equipment['status']] ?>">
                                                <?= $statusText[$equipment['status']] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>วันที่จัดซื้อ:</th>
                                        <td><?= $equipment['purchase_date'] ? date('d/m/Y', strtotime($equipment['purchase_date'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th>ราคาซื้อ:</th>
                                        <td><?= $equipment['purchase_price'] ? number_format($equipment['purchase_price'], 2) . ' ฿' : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th>สถานที่เก็บ:</th>
                                        <td><?= htmlspecialchars($equipment['location'] ?? '-') ?></td>
                                    </tr>
                                    <?php if ($equipment['description']): ?>
                                        <tr>
                                            <th>รายละเอียด:</th>
                                            <td><?= nl2br(htmlspecialchars($equipment['description'])) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">ประวัติการยืม-คืน</h5>

                            <?php if (empty($borrowing_history)): ?>
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i> ยังไม่มีประวัติการยืม-คืนสำหรับอุปกรณ์นี้
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-centered table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ผู้ยืม</th>
                                                <th>วันที่ยืม</th>
                                                <th>วันที่ต้องคืน</th>
                                                <th>วันที่คืนจริง</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'borrowed' => 'primary',
                                                'returned' => 'success',
                                                'request_return' => 'dark',
                                                'overdue' => 'danger',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                            $statusText = [
                                                'pending' => 'รออนุมัติ',
                                                'approved' => 'อนุมัติแล้ว',
                                                'borrowed' => 'กำลังยืม',
                                                'returned' => 'คืนแล้ว',
                                                'request_return' => 'ขอคืน',
                                                'overdue' => 'เกินกำหนด',
                                                'rejected' => 'ปฏิเสธ',
                                                'cancelled' => 'ยกเลิก'
                                            ];
                                            foreach ($borrowing_history as $borrow):
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($borrow['borrower_name']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($borrow['student_id']) ?></small>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($borrow['borrow_date'])) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($borrow['due_date'])) ?></td>
                                                    <td>
                                                        <?php if ($borrow['return_date']): ?>
                                                            <?= date('d/m/Y H:i', strtotime($borrow['return_date'])) ?>
                                                            <?php if ($borrow['return_date'] > $borrow['due_date']): ?>
                                                                <br><span class="badge bg-danger">คืนช้า</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $statusClass[$borrow['status']] ?? 'secondary' ?>">
                                                            <?= $statusText[$borrow['status']] ?? $borrow['status'] ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="py-1 bg-light">
                                                        <small>
                                                            <strong>วัตถุประสงค์:</strong> <?= htmlspecialchars($borrow['purpose']) ?>
                                                            <?php if ($borrow['approved_by_name']): ?>
                                                                | <strong>ผู้อนุมัติ:</strong> <?= htmlspecialchars($borrow['approved_by_name']) ?>
                                                            <?php endif; ?>
                                                            <?php if ($borrow['notes']): ?>
                                                                | <strong>หมายเหตุ:</strong> <?= htmlspecialchars($borrow['notes']) ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>
</div>

</body>

</html>