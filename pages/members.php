<?php

/**
 * Members Page
 * หน้าแสดงรายชื่อสมาชิกทั้งหมด (เฉพาะสมาชิกที่มีสถานะ active)
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

$page_title = 'สมาชิก';

// ดึงข้อมูลสมาชิก (เฉพาะที่มี status = 1 หรือ 2 และ role = member)
try {
    $db = new Database();
    $pdo = $db->getConnection();

    // ดึงสมาชิกชาย (ทุกตำแหน่ง)
    $sql = "SELECT 
                u.id, u.email, u.role, u.created_at,
                p.student_id, p.prefix, p.first_name_th, p.last_name_th, p.nickname_th,
                p.profile_picture, p.birth_date,
                e.academic_year_id, e.class_academic, e.class_agama,
                c.phone_number, c.line_id,
                ci.member_generation, ci.joined_date,
                d.name as department_name,
                pos.name as position_name
            FROM users u
            JOIN profiles p ON u.id = p.user_id
            LEFT JOIN member_education e ON u.id = e.user_id AND e.is_current = 1
            LEFT JOIN member_contacts c ON u.id = c.user_id
            LEFT JOIN member_club_info ci ON u.id = ci.user_id
            LEFT JOIN club_departments d ON ci.department_id = d.id
            LEFT JOIN club_positions pos ON ci.position_id = pos.id
            WHERE u.status = 1
            AND u.role != 'advisor'
            AND p.prefix IN ('นาย', 'เด็กชาย')
            ORDER BY p.student_id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $male_members = $stmt->fetchAll();

    // ดึงสมาชิกหญิง (ทุกตำแหน่ง)
    $sql = "SELECT 
                u.id, u.email, u.role, u.created_at,
                p.student_id, p.prefix, p.first_name_th, p.last_name_th, p.nickname_th,
                p.profile_picture, p.birth_date,
                e.academic_year_id, e.class_academic, e.class_agama,
                c.phone_number, c.line_id,
                ci.member_generation, ci.joined_date,
                d.name as department_name,
                pos.name as position_name
            FROM users u
            JOIN profiles p ON u.id = p.user_id
            LEFT JOIN member_education e ON u.id = e.user_id AND e.is_current = 1
            LEFT JOIN member_contacts c ON u.id = c.user_id
            LEFT JOIN member_club_info ci ON u.id = ci.user_id
            LEFT JOIN club_departments d ON ci.department_id = d.id
            LEFT JOIN club_positions pos ON ci.position_id = pos.id
            WHERE u.status = 1
            AND u.role != 'advisor'
            AND p.prefix IN ('นางสาว', 'นาง', 'เด็กหญิง')
            ORDER BY p.student_id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $female_members = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching members: " . $e->getMessage());
    $male_members = [];
    $female_members = [];
}

$base_path = '../';
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

            <!-- Male Members -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="mdi mdi-account-tie me-1"></i> สมาชิกชาย
                                <span class="badge bg-primary ms-2"><?php echo count($male_members); ?> คน</span>
                            </h4>
                            <div class="row">
                                <?php foreach ($male_members as $member): ?>
                                    <div class="col-md-6 col-xl-3 mb-3">
                                        <a href="member_view.php?id=<?php echo $member['id']; ?>" class="text-decoration-none">
                                            <div class="card border" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.boxShadow=''">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <img src="<?php echo $base_path . 'assets/images/users/' . ($member['profile_picture'] ?: 'avatar-1.jpg'); ?>"
                                                            class="me-3 rounded-circle"
                                                            alt="profile"
                                                            style="width: 64px; height: 64px; object-fit: cover;">
                                                        <div class="flex-grow-1">
                                                            <h5 class="mt-0 mb-1 text-dark">
                                                                <?php echo htmlspecialchars($member['first_name_th'] . ' ' . $member['last_name_th']); ?>
                                                            </h5>
                                                            <p class="text-muted mb-1">
                                                                <small>
                                                                    <i class="mdi mdi-card-account-details me-1"></i>
                                                                    <?php echo htmlspecialchars($member['student_id']); ?>
                                                                </small>
                                                            </p>
                                                            <?php if ($member['class_academic']): ?>
                                                                <p class="text-muted mb-1">
                                                                    <small>
                                                                        <i class="mdi mdi-school me-1"></i>
                                                                        <?php echo htmlspecialchars($member['class_academic']); ?>
                                                                    </small>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($member['department_name']): ?>
                                                                <p class="text-muted mb-1">
                                                                    <small>
                                                                        <i class="mdi mdi-briefcase me-1"></i>
                                                                        <?php echo htmlspecialchars($member['department_name']); ?>
                                                                        <?php if ($member['position_name']): ?>
                                                                            - <?php echo htmlspecialchars($member['position_name']); ?>
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($member['member_generation']): ?>
                                                                <span class="badge bg-info">รุ่น <?php echo $member['member_generation']; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($male_members)): ?>
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">ไม่มีข้อมูลสมาชิกชาย</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Female Members -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="mdi mdi-human-female me-1"></i> สมาชิกหญิง
                                <span class="badge bg-danger ms-2"><?php echo count($female_members); ?> คน</span>
                            </h4>
                            <div class="row">
                                <?php foreach ($female_members as $member): ?>
                                    <div class="col-md-6 col-xl-3 mb-3">
                                        <a href="member_view.php?id=<?php echo $member['id']; ?>" class="text-decoration-none">
                                            <div class="card border" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.boxShadow=''">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <img src="<?php echo $base_path . 'assets/images/users/' . ($member['profile_picture'] ?: 'avatar-1.jpg'); ?>"
                                                            class="me-3 rounded-circle"
                                                            alt="profile"
                                                            style="width: 64px; height: 64px; object-fit: cover;">
                                                        <div class="flex-grow-1">
                                                            <h5 class="mt-0 mb-1 text-dark">
                                                                <?php echo htmlspecialchars($member['first_name_th'] . ' ' . $member['last_name_th']); ?>
                                                            </h5>
                                                            <p class="text-muted mb-1">
                                                                <small>
                                                                    <i class="mdi mdi-card-account-details me-1"></i>
                                                                    <?php echo htmlspecialchars($member['student_id']); ?>
                                                                </small>
                                                            </p>
                                                            <?php if ($member['class_academic']): ?>
                                                                <p class="text-muted mb-1">
                                                                    <small>
                                                                        <i class="mdi mdi-school me-1"></i>
                                                                        <?php echo htmlspecialchars($member['class_academic']); ?>
                                                                    </small>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($member['department_name']): ?>
                                                                <p class="text-muted mb-1">
                                                                    <small>
                                                                        <i class="mdi mdi-briefcase me-1"></i>
                                                                        <?php echo htmlspecialchars($member['department_name']); ?>
                                                                        <?php if ($member['position_name']): ?>
                                                                            - <?php echo htmlspecialchars($member['position_name']); ?>
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($member['member_generation']): ?>
                                                                <span class="badge bg-info">รุ่น <?php echo $member['member_generation']; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($female_members)): ?>
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">ไม่มีข้อมูลสมาชิกหญิง</div>
                                    </div>
                                <?php endif; ?>
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