<?php
// Get user info from session (already loaded in auth.php)
$user = $_SESSION['user'] ?? null;

// Get academic years for dropdown (with error handling)
$academic_years_list = [];
try {
    $db = new Database();
    $pdo = $db->getConnection();
    $academic_years_query = $pdo->query("SELECT * FROM academic_years ORDER BY year DESC");
    $academic_years_list = $academic_years_query->fetchAll();

    // Get current academic year from session or database
    if (!isset($_SESSION['selected_academic_year'])) {
        $current_year = $pdo->query("SELECT id FROM academic_years WHERE is_current = 1 LIMIT 1")->fetch();
        $_SESSION['selected_academic_year'] = $current_year ? $current_year['id'] : null;
    }
} catch (Exception $e) {
    // Table might not exist yet, ignore error
    $academic_years_list = [];
}

// Display name
$display_name = 'Guest';
if ($user) {
    $first_name = $user['first_name_th'] ?? '';
    $last_name = $user['last_name_th'] ?? '';
    $nickname = $user['nickname_th'] ?? '';
    
    if ($first_name && $last_name) {
        $display_name = $first_name . ' ' . $last_name;
    } elseif ($first_name) {
        $display_name = $first_name;
    } elseif ($nickname) {
        $display_name = $nickname;
    } else {
        $display_name = $user['email'];
    }
}

// Display role
$role_display = [
    'admin' => 'ผู้ดูแลระบบ',
    'board' => 'บอร์ดบริหาร',
    'member' => 'สมาชิก'
];
$user_role = $role_display[$user['role'] ?? 'member'] ?? 'สมาชิก';

// Profile picture path with dynamic base path
$base_path_assets = '';
if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    $base_path_assets = '../';
}

$profile_pic = $user['profile_picture'] ?? '';
if (!empty($profile_pic) && !str_contains($profile_pic, 'avatar-')) {
    $profile_pic_url = $base_path_assets . 'assets/images/users/' . $profile_pic;
} else {
    $profile_pic_url = $base_path_assets . 'assets/images/users/avatar-1.jpg';
}

// Logout URL
$logout_url = $base_path_assets . 'logout.php';
?>
<!-- Topbar Start -->
<div class="navbar-custom">
    <ul class="list-unstyled topbar-menu float-end mb-0">
        <!-- Academic Year Dropdown -->
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="mdi mdi-calendar-range font-18"></i>
                <span class="ms-1 d-none d-md-inline-block">
                    ปีการศึกษา: <?php
                        if ($_SESSION['selected_academic_year'] == 0) {
                            echo 'ทุกปีการศึกษา';
                        } else {
                            $selected_year = null;
                            foreach ($academic_years_list as $ay) {
                                if ($ay['id'] == $_SESSION['selected_academic_year']) {
                                    $selected_year = $ay['year'];
                                    break;
                                }
                            }
                            echo $selected_year ?? 'เลือกปี';
                        }
                    ?>
                </span>
                <i class="mdi mdi-chevron-down d-none d-md-inline-block"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a href="#" class="dropdown-item <?php echo $_SESSION['selected_academic_year'] == 0 ? 'active' : ''; ?>" 
                   onclick="changeAcademicYear(0); return false;">
                    <i class="mdi mdi-calendar-multiple-check me-1"></i>
                    ทุกปีการศึกษา
                </a>
                <div class="dropdown-divider"></div>
                <?php foreach ($academic_years_list as $ay): ?>
                    <a href="#" class="dropdown-item <?php echo $ay['id'] == $_SESSION['selected_academic_year'] ? 'active' : ''; ?>" 
                       onclick="changeAcademicYear(<?php echo $ay['id']; ?>); return false;">
                        <i class="mdi mdi-calendar-check me-1"></i>
                        <?php echo htmlspecialchars($ay['year']); ?>
                        <?php if ($ay['is_current']): ?>
                            <span class="badge bg-success ms-1">ปัจจุบัน</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </li>

        <li class="dropdown notification-list d-lg-none">
            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="mdi mdi-magnify font-20"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                <form class="p-3">
                    <input type="text" class="form-control" placeholder="ค้นหา..." aria-label="Search">
                </form>
            </div>
        </li>

        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <span class="account-user-avatar">
                    <img src="<?php echo htmlspecialchars($profile_pic_url); ?>" alt="user-image" class="rounded-circle">
                </span>
                <span>
                    <span class="account-user-name"><?php echo htmlspecialchars($display_name); ?></span>
                    <span class="account-position"><?php echo htmlspecialchars($user_role); ?></span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                <a href="<?php echo $base_path; ?>pages/profile.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-circle me-1"></i>
                    <span>ดูโปรไฟล์</span>
                </a>
                <a href="<?php echo $base_path; ?>pages/my_account.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-edit me-1"></i>
                    <span>แก้ไขข้อมูลส่วนตัว</span>
                </a>
                <a href="<?php echo $base_path; ?>pages/change_password.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-lock-reset me-1"></i>
                    <span>เปลี่ยนรหัสผ่าน</span>
                </a>
                <a href="<?php echo htmlspecialchars($logout_url); ?>" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout me-1"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </div>
        </li>

    </ul>
    
    <button class="button-menu-mobile open-left">
        <i class="mdi mdi-menu"></i>
    </button>
    
    <div class="app-search dropdown d-none d-lg-block">
        <form>
            <div class="input-group">
                <input type="text" class="form-control dropdown-toggle" placeholder="Search..." id="top-search">
                <span class="mdi mdi-magnify search-icon"></span>
                <button class="input-group-text btn-primary" type="submit">Search</button>
            </div>
        </form>

        <div class="dropdown-menu dropdown-menu-animated dropdown-lg" id="search-dropdown">
            <div class="dropdown-header noti-title">
                <h5 class="text-overflow mb-2">Found <span class="text-danger">17</span> results</h5>
            </div>
            <a href="javascript:void(0);" class="dropdown-item notify-item">
                <i class="uil-notes font-16 me-1"></i>
                <span>Analytics Report</span>
            </a>
        </div>
    </div>
</div>
<!-- end Topbar -->

<script>
function changeAcademicYear(yearId) {
    $.post('<?php echo $base_path_assets; ?>api/set_academic_year.php', { year_id: yearId }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            Swal.fire('ข้อผิดพลาด', response.message, 'error');
        }
    }, 'json');
}
</script>
