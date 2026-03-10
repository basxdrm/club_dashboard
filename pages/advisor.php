<?php
/**
 * Advisor Page
 * หน้าแสดงรายชื่ออาจารย์ที่ปรึกษา
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

// ดึงข้อมูลอาจารย์ที่ปรึกษา
$sql = "SELECT u.id, u.email, u.role,
        p.prefix, p.first_name_th, p.last_name_th,
        p.profile_picture, p.bio
        FROM users u
        JOIN profiles p ON u.id = p.user_id
        WHERE u.role = 'advisor'
        AND u.status IN (1, 2)
        ORDER BY p.first_name_th ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$advisors = $stmt->fetchAll();

$page_title = 'รายชื่ออาจารย์ที่ปรึกษา';
require_once '../includes/header.php';
?>

<style>
    .advisor-container {
        min-height: 100vh;
        background: white;
        padding: 2rem 0;
    }

    .advisor-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }

    .advisor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    }

    .avatar-container {
        width: 150px;
        height: 150px;
        margin: 0 auto 1.5rem;
        position: relative;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .member-name {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .member-sub {
        font-size: 0.9rem;
        color: #888;
        margin-bottom: 1rem;
        min-height: 2.5rem;
    }

    .member-badge {
        display: inline-block;
        padding: 0.4rem 1.2rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 500;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .page-header {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 3rem;
    }

    .page-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .page-header .subtitle {
        font-size: 1.1rem;
        color: #6c757d;
    }

    .advisor-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .advisor-grid > div {
        flex: 0 0 280px;
    }

    @media (max-width: 768px) {
        .advisor-grid > div {
            flex: 0 0 100%;
            max-width: 360px;
        }

        .page-header h2 {
            font-size: 1.8rem;
        }
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
        <div class="advisor-container">
            <div class="container-fluid">

                <div class="page-header">
                    <h2>อาจารย์ที่ปรึกษา</h2>
                    <p class="subtitle">รายชื่ออาจารย์ที่ปรึกษาของชมรม</p>
                </div>

                <?php if (count($advisors) > 0): ?>
                <div class="advisor-grid">
                    <?php foreach ($advisors as $advisor): ?>
                    <div>
                        <a href="member_view.php?id=<?php echo $advisor['id']; ?>" class="text-decoration-none" style="color: inherit;">
                            <div class="advisor-card" style="cursor: pointer;">
                                <div class="avatar-container">
                                    <?php
                                    $avatar = !empty($advisor['profile_picture'])
                                        ? '../assets/images/users/' . $advisor['profile_picture']
                                        : '../assets/images/users/avatar-1.jpg';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($avatar); ?>"
                                         alt="<?php echo htmlspecialchars($advisor['first_name_th'] . ' ' . $advisor['last_name_th']); ?>"
                                         class="avatar-img">
                                </div>

                                <div class="member-name">
                                    <?php echo htmlspecialchars(($advisor['prefix'] ?? '') . $advisor['first_name_th'] . ' ' . $advisor['last_name_th']); ?>
                                </div>

                                <div class="member-sub">
                                    <?php echo !empty($advisor['bio']) ? htmlspecialchars($advisor['bio']) : '&nbsp;'; ?>
                                </div>

                                <div class="text-center">
                                    <span class="member-badge">อาจารย์ที่ปรึกษา</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php else: ?>
                <div class="text-center" style="color: #6c757d; padding: 3rem;">
                    <i class="mdi mdi-account-tie-outline" style="font-size: 4rem; opacity: 0.4;"></i>
                    <p style="font-size: 1.2rem; margin-top: 1rem;">ยังไม่มีอาจารย์ที่ปรึกษาในระบบ</p>
                </div>
                <?php endif; ?>

            </div>
            <!-- container -->

        </div>
        <!-- content -->

        <?php include_once('../includes/footer.php'); ?>

    </div>
    <!-- content-page -->

</div>
<!-- end wrapper -->
