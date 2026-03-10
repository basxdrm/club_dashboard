<?php
/**
 * Board Members Page
 * หน้าแสดงรายชื่อบอร์ดบริหาร
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

// Only allow all members to view, but board management should be restricted
// For now, all logged-in users can view board members

$db = new Database();
$conn = $db->getConnection();

// ดึงข้อมูลตำแหน่งทั้งหมด
$positions_sql = "SELECT * FROM club_positions WHERE is_active = 1 ORDER BY level ASC";
$positions_stmt = $conn->prepare($positions_sql);
$positions_stmt->execute();
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลบอร์ดบริหาร (เฉพาะตำแหน่งพิเศษ)
$sql = "SELECT u.id, u.email, u.role,
        p.prefix, p.first_name_th, p.last_name_th, p.student_id,
        p.profile_picture, p.bio,
        cp.name as position_name, cp.level as position_level
        FROM users u
        JOIN profiles p ON u.id = p.user_id
        JOIN member_club_info mci ON u.id = mci.user_id
        JOIN club_positions cp ON mci.position_id = cp.id
        WHERE u.role IN ('board', 'admin')
        AND u.status IN (1, 2)
        AND cp.is_active = 1
        AND cp.id IN (1, 2, 3, 4, 6)
        ORDER BY cp.level ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$board_members = $stmt->fetchAll();

// แยกประธานกับตำแหน่งอื่นๆ
$president = null;
$members = [];

if (count($board_members) > 0) {
    // หาประธาน (level 1)
    foreach ($board_members as $member) {
        if ($member['position_level'] == 1) {
            $president = $member;
        } else {
            $members[] = $member;
        }
    }
}

$page_title = 'รายชื่อบอร์ดบริหาร';
require_once '../includes/header.php';
?>

<style>
    .board-container {
        min-height: 100vh;
        background: white;
        padding: 2rem 0;
    }
    
    .board-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .board-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    }
    
    .president-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 3rem 2rem;
        max-width: 400px;
        margin: 0 auto 3rem;
    }
    
    .avatar-container {
        width: 150px;
        height: 150px;
        margin: 0 auto 1.5rem;
        position: relative;
    }
    
    .president-card .avatar-container {
        width: 180px;
        height: 180px;
    }
    
    .avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    
    .president-card .avatar-img {
        border-width: 8px;
    }
    
    .member-name {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-align: center;
    }
    
    .president-card .member-name {
        font-size: 1.8rem;
    }
    
    .member-id {
        font-size: 0.95rem;
        opacity: 0.8;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .member-badge {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 500;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
    }
    
    .president-card .member-badge {
        background: rgba(255,255,255,0.3);
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
    
    .member-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
        margin-top: 2rem;
        max-width: 1400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .member-grid > div {
        flex: 0 0 280px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .president-card {
            max-width: 100%;
            margin: 0 0 2rem;
        }
        
        .member-grid > div {
            flex: 0 0 100%;
            max-width: 400px;
        }
        
        .board-card {
            min-height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .president-card {
            min-height: 400px;
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
        <div class="board-container">
            <div class="container-fluid">
                
                <div class="page-header">
                    <h2>รายชื่อบอร์ดบริหาร</h2>
                </div>

                <!-- President Card -->
                <?php if ($president): ?>
                <a href="member_view.php?id=<?php echo $president['id']; ?>" class="text-decoration-none" style="color: inherit;">
                    <div class="president-card board-card" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.2)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                        <div class="avatar-container">
                        <?php
                        $avatar = !empty($president['profile_picture'])
                            ? '../assets/images/users/' . $president['profile_picture']
                            : '../assets/images/users/avatar-1.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($avatar); ?>"
                             alt="<?php echo htmlspecialchars($president['first_name_th'] . ' ' . $president['last_name_th']); ?>"
                             class="avatar-img">
                    </div>
                    
                    <div class="member-name">
                        <?php echo htmlspecialchars($president['prefix'] . $president['first_name_th'] . ' ' . $president['last_name_th']); ?>
                    </div>
                    
                    <div class="member-id">
                        รหัสนักเรียน <?php echo htmlspecialchars($president['student_id']); ?>
                    </div>
                    
                    <div class="text-center">
                        <span class="member-badge">ประธาน</span>
                    </div>
                    </div>
                </a>
                <?php endif; ?>

                <!-- Board Members Grid -->
                <?php if (count($members) > 0): ?>
                <div class="member-grid">
                    <?php foreach ($members as $index => $member): ?>
                    <a href="member_view.php?id=<?php echo $member['id']; ?>" class="text-decoration-none" style="color: inherit;">
                        <div class="board-card" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.2)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                            <div class="avatar-container">
                            <?php 
                            $avatar = !empty($member['profile_picture'])
                                ? '../assets/images/users/' . $member['profile_picture']
                                : '../assets/images/users/avatar-1.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($avatar); ?>"
                                 alt="<?php echo htmlspecialchars($member['first_name_th'] . ' ' . $member['last_name_th']); ?>"
                                 class="avatar-img">
                        </div>
                        
                        <div class="member-name" style="color: #333;">
                            <?php echo htmlspecialchars($member['prefix'] . $member['first_name_th'] . ' ' . $member['last_name_th']); ?>
                        </div>
                        
                        <div class="member-id" style="color: #666;">
                            รหัสนักเรียน <?php echo htmlspecialchars($member['student_id']); ?>
                        </div>
                        
                        <div class="text-center">
                            <span class="member-badge" style="background: #667eea; color: white;">
                                <?php echo htmlspecialchars($member['position_name']); ?>
                            </span>
                        </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (empty($board_members)): ?>
                <div class="text-center" style="color: #6c757d; padding: 3rem;">
                    <i class="mdi mdi-account-multiple-outline" style="font-size: 4rem; opacity: 0.5;"></i>
                    <p style="font-size: 1.2rem; margin-top: 1rem;">ยังไม่มีข้อมูลบอร์ดบริหาร</p>
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

