<?php
/**
 * Member View Page
 * หน้าดูข้อมูลสมาชิก
 */

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

$member_id = $_GET['id'] ?? 0;
$page_title = 'ข้อมูลสมาชิก';

try {
    $pdo = getDatabaseConnection();
    
    $sql = "SELECT
                u.id, u.email, u.role, u.status, u.created_at, u.last_login,
                p.*,
                e.class_academic, e.class_agama, e.academic_year_id, e.academic_status, e.agama_status,
                e.academic_grade, e.academic_room, e.agama_grade, e.agama_room,
                ay.year as academic_year,
                c.phone_number, c.line_id, c.facebook, c.instagram,
                ci.member_generation, ci.joined_date,
                d.name as department_name,
                pos.name as position_name
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            LEFT JOIN member_education e ON u.id = e.user_id AND e.is_current = 1
            LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
            LEFT JOIN member_contacts c ON u.id = c.user_id
            LEFT JOIN member_club_info ci ON u.id = ci.user_id
            LEFT JOIN club_departments d ON ci.department_id = d.id
            LEFT JOIN club_positions pos ON ci.position_id = pos.id
            WHERE u.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    
    if (!$member) {
        header('Location: members.php');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Error fetching member: " . $e->getMessage());
    header('Location: members.php');
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
                                <h4 class="page-title"><?php echo $page_title; ?></h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-4 col-lg-5">
                            <div class="card text-center">
                                <div class="card-body">
                                    <img src="<?php echo $member['profile_picture'] ? '../assets/images/users/' . $member['profile_picture'] : '../assets/images/users/avatar-1.jpg'; ?>" 
                                         class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">

                                    <h4 class="mb-0 mt-2"><?php echo htmlspecialchars($member['prefix'] . ' ' . $member['first_name_th'] . ' ' . $member['last_name_th']); ?></h4>
                                    <p class="text-muted font-14"><?php echo htmlspecialchars($member['nickname_th'] ?? ''); ?></p>

                                    <?php
                                    $statusClass = $member['status'] == 1 ? 'success' : ($member['status'] == 0 ? 'danger' : 'secondary');
                                    $statusText = $member['status'] == 1 ? 'สมาชิก' : ($member['status'] == 0 ? 'ลาออก' : 'จบการศึกษา');
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?> mb-2"><?php echo $statusText; ?></span>

                                    <div class="text-start mt-3">
                                        <h4 class="font-13 text-uppercase">ข้อมูลติดต่อ</h4>
                                        <p class="text-muted mb-2 font-13">
                                            <strong>อีเมล:</strong> 
                                            <span class="ms-2"><?php echo htmlspecialchars($member['email']); ?></span>
                                        </p>
                                        <p class="text-muted mb-2 font-13">
                                            <strong>โทรศัพท์:</strong> 
                                            <span class="ms-2"><?php echo htmlspecialchars($member['phone_number'] ?? '-'); ?></span>
                                        </p>
                                        <p class="text-muted mb-2 font-13">
                                            <strong>LINE ID:</strong> 
                                            <span class="ms-2"><?php echo htmlspecialchars($member['line_id'] ?? '-'); ?></span>
                                        </p>
                                        <p class="text-muted mb-1 font-13">
                                            <strong>Facebook:</strong> 
                                            <span class="ms-2"><?php echo htmlspecialchars($member['facebook'] ?? '-'); ?></span>
                                        </p>
                                        <p class="text-muted mb-1 font-13">
                                            <strong>Instagram:</strong> 
                                            <span class="ms-2"><?php echo htmlspecialchars($member['instagram'] ?? '-'); ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-7">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-uppercase mb-3"><i class="mdi mdi-account-circle me-1"></i> ข้อมูลส่วนตัว</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">รหัสนักเรียน</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($member['student_id'] ?? '-'); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">วันเกิด</label>
                                                <p class="form-control-static"><?php echo $member['birth_date'] ? date('d/m/Y', strtotime($member['birth_date'])) : '-'; ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">ชื่อ-นามสกุล (ภาษาอังกฤษ)</label>
                                                <p class="form-control-static">
                                                    <?php echo htmlspecialchars(($member['first_name_en'] ?? '') . ' ' . ($member['last_name_en'] ?? '') ?: '-'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">ประวัติย่อ</label>
                                                <p class="form-control-static"><?php echo nl2br(htmlspecialchars($member['bio'] ?? '-')); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="text-uppercase mb-3"><i class="mdi mdi-school me-1"></i> ข้อมูลการศึกษา</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ระดับชั้น</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($member['academic_year'] ?? '-'); ?></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">สถานะสามัญ:</label>
                                                    <?php
                                                    $academicStatusText = [
                                                        'studying' => '<span class="badge bg-primary">กำลังเรียน</span>',
                                                        'graduated' => '<span class="badge bg-success">จบแล้ว</span>',
                                                        'not_enrolled' => '<span class="badge bg-secondary">ไม่ได้เรียน</span>'
                                                    ];
                                                    echo $academicStatusText[$member['academic_status'] ?? 'studying'] ?? '-';
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">สถานะศาสนา:</label>
                                                    <?php
                                                    $agamaStatusText = [
                                                        'studying' => '<span class="badge bg-primary">กำลังเรียน</span>',
                                                        'graduated' => '<span class="badge bg-success">จบแล้ว</span>',
                                                        'not_enrolled' => '<span class="badge bg-secondary">ไม่ได้เรียน</span>'
                                                    ];
                                                    echo $agamaStatusText[$member['agama_status'] ?? 'studying'] ?? '-';
                                                    ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">ชั้นสามัญ:</label>
                                                    <p class="form-control-static"><?php echo htmlspecialchars($member['class_academic'] ?? '-'); ?></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">ชั้นศาสนา:</label>
                                                    <p class="form-control-static"><?php echo htmlspecialchars($member['class_agama'] ?? '-'); ?></p>
                                                </div>
                                            </div>
                                        </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ชั้น (อาคาม่า)</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($member['class_agama'] ?? '-'); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ปีการศึกษา</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($member['academic_year'] ?? '-'); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="text-uppercase mb-3"><i class="mdi mdi-account-group me-1"></i> ข้อมูลชมรม</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">รุ่นสมาชิก</label>
                                                <p class="form-control-static">รุ่น <?php echo htmlspecialchars($member['member_generation'] ?? '-'); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">วันที่เข้าร่วม</label>
                                                <p class="form-control-static">
                                                    <?php echo $member['joined_date'] ? date('d/m/Y', strtotime($member['joined_date'])) : '-'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ฝ่าย</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($member['department_name'] ?? '-'); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ตำแหน่ง</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($member['position_name'] ?? '-'); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">บทบาทในระบบ</label>
                                                <p class="form-control-static">
                                                    <?php
                                                    $roleText = match($member['role']) {
                                                        'admin'   => 'Admin',
                                                        'board'   => 'Board',
                                                        'advisor' => 'Advisor',
                                                        default   => 'Member'
                                                    };
                                                    $roleClass = match($member['role']) {
                                                        'admin'   => 'danger',
                                                        'board'   => 'warning',
                                                        'advisor' => 'success',
                                                        default   => 'primary'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">วันที่สร้างบัญชี</label>
                                                <p class="form-control-static">
                                                    <?php echo date('d/m/Y H:i', strtotime($member['created_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
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
