<?php
/**
 * Profile Page - View Only
 * หน้าโปรไฟล์ของฉัน (ดูข้อมูลอย่างเดียว)
 */

define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/security.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.id, u.email, u.role, u.status, u.updated_at,
           p.*, 
           me.academic_year_id, me.academic_grade, me.academic_room, me.academic_status,
           me.agama_grade, me.agama_room, me.agama_status,
           me.class_academic, me.class_agama,
           mc.phone_number, mc.line_id, mc.facebook, mc.instagram,
           mci.member_generation, mci.joined_date,
           d.name as department_name, cp.name as position_name
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    LEFT JOIN member_education me ON u.id = me.user_id AND me.is_current = 1
    LEFT JOIN member_contacts mc ON u.id = mc.user_id
    LEFT JOIN member_club_info mci ON u.id = mci.user_id
    LEFT JOIN club_departments d ON mci.department_id = d.id
    LEFT JOIN club_positions cp ON mci.position_id = cp.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ERROR: No user data found for user_id = $user_id");
}

// Helper function to display value or dash
function displayValue($array, $key) {
    return (isset($array[$key]) && $array[$key] !== null && $array[$key] !== '') 
        ? htmlspecialchars($array[$key]) 
        : '-';
}

$page_title = 'โปรไฟล์ของฉัน';
require_once '../includes/header.php';
?>

<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 10px;
}

.info-row {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    min-width: 150px;
}

.info-value {
    color: #2c3e50;
}
</style>

<!-- ========== Left Sidebar Start ========== -->
<?php include_once('../includes/sidebar.php'); ?>
<!-- Left Sidebar End -->

<div class="content-page">
    <div class="content">

        <!-- Topbar Start -->
        <?php include_once('../includes/topbar.php'); ?>
        <!-- end Topbar -->

        <!-- Start Content-->
        <div class="container-fluid">

            <!-- Profile Header -->
            <div class="profile-header text-center">
                <div class="avatar-container mb-3" style="width: 120px; height: 120px; margin: 0 auto;">
                    <?php
                    $avatar = !empty($user['profile_picture'])
                        ? '../assets/images/users/' . $user['profile_picture']
                        : '../assets/images/users/avatar-1.jpg';
                    ?>
                    <img src="<?php echo htmlspecialchars($avatar); ?>"
                         class="rounded-circle"
                         alt="profile"
                         style="width: 100%; height: 100%; object-fit: cover; border: 5px solid rgba(255,255,255,0.3);">
                </div>
                <h3 class="text-white mb-1"><?php echo htmlspecialchars($user['prefix'] . $user['first_name_th'] . ' ' . $user['last_name_th']); ?></h3>
                <p class="text-white-50 mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">ข้อมูลส่วนตัว</h4>
                                <a href="profile_edit.php" class="btn btn-primary">
                                    <i class="mdi mdi-pencil me-1"></i>แก้ไขข้อมูล
                                </a>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row d-flex">
                                        <div class="info-label">รหัสนักเรียน:</div>
                                        <div class="info-value"><?php echo $user['student_id'] ?? '-'; ?></div>
                                    </div>
                                    
                                    <div class="info-row d-flex">
                                        <div class="info-label">คำนำหน้า:</div>
                                        <div class="info-value"><?php echo $user['prefix'] ?? '-'; ?></div>
                                    </div>
                                    
                                    <div class="info-row d-flex">
                                        <div class="info-label">ชื่อ (ไทย):</div>
                                        <div class="info-value"><?php echo $user['first_name_th'] ?? '-'; ?></div>
                                    </div>
                                    
                                    <div class="info-row d-flex">
                                        <div class="info-label">นามสกุล (ไทย):</div>
                                        <div class="info-value"><?php echo $user['last_name_th'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">ชื่อ (อังกฤษ):</div>
                                        <div class="info-value"><?php echo $user['first_name_en'] ?? '-'; ?></div>
                                    </div>
                                    
                                    <div class="info-row d-flex">
                                        <div class="info-label">นามสกุล (อังกฤษ):</div>
                                        <div class="info-value"><?php echo $user['last_name_en'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">วันเกิด:</div>
                                        <div class="info-value">
                                            <?php 
                                            if (isset($user['birth_date']) && $user['birth_date']) {
                                                $date = new DateTime($user['birth_date']);
                                                echo $date->format('d/m/Y');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">รุ่น:</div>
                                        <div class="info-value"><?php echo $user['member_generation'] ?? '-'; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row d-flex">
                                        <div class="info-label">เบอร์โทร:</div>
                                        <div class="info-value"><?php echo $user['phone_number'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">ชั้นสามัญ:</div>
                                        <div class="info-value"><?php echo $user['class_academic'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">ชั้นศาสนา:</div>
                                        <div class="info-value"><?php echo $user['class_agama'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">แผนก:</div>
                                        <div class="info-value"><?php echo $user['department_name'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">ตำแหน่ง:</div>
                                        <div class="info-value"><?php echo $user['position_name'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">Line ID:</div>
                                        <div class="info-value"><?php echo $user['line_id'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">Facebook:</div>
                                        <div class="info-value"><?php echo $user['facebook'] ?? '-'; ?></div>
                                    </div>

                                    <div class="info-row d-flex">
                                        <div class="info-label">Instagram:</div>
                                        <div class="info-value"><?php echo $user['instagram'] ?? '-'; ?></div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($user['bio']): ?>
                            <div class="mt-4">
                                <h5 class="text-muted mb-2">เกี่ยวกับฉัน</h5>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="mt-4 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="mdi mdi-clock-outline me-1"></i>
                                    อัพเดทล่าสุด: <?php 
                                    if (!empty($user['updated_at'])) {
                                        $updated = new DateTime($user['updated_at']);
                                        echo $updated->format('d/m/Y H:i');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- container -->

    </div>
    <!-- content -->

    <?php include_once('../includes/footer.php'); ?>

</div>
<!-- content-page -->

<?php require_once '../includes/footer.php'; ?>
