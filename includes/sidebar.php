<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <?php
    // กำหนด base path สำหรับลิงก์ใน sidebar
    $base_path = '';
    if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
        // ถ้าอยู่ใน folder pages/ ให้ใช้ ../
        $base_path = '../';
    }
    
    // Get current page for active menu
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Function to check if menu is active
    function isActive($page) {
        global $current_page;
        return ($current_page === $page) ? 'active' : '';
    }
    ?>

    <!-- LOGO -->
    <a href="<?php echo $base_path; ?>index.php" class="logo text-center logo-light">
        <span class="logo-lg">
            <img src="<?php echo $base_path; ?>assets/images/logos/MSJ logo new 512.png" alt="" height="40">
        </span>
        <span class="logo-sm">
            <img src="<?php echo $base_path; ?>assets/images/logos/MSJ logo new 512.png" alt="" height="25">
        </span>
    </a>

    <!-- LOGO -->
    <a href="<?php echo $base_path; ?>index.php" class="logo text-center logo-dark">
        <span class="logo-lg">
            <img src="<?php echo $base_path; ?>assets/images/logos/MSJ logo new 512.png" alt="" height="40">
        </span>
        <span class="logo-sm">
            <img src="<?php echo $base_path; ?>assets/images/logos/MSJ logo new 512.png" alt="" height="25">
        </span>
    </a>

    <div class="h-100" id="leftside-menu-container" data-simplebar>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title side-nav-item">เมนูหลัก</li>

            <li class="side-nav-item <?php echo isActive('index.php'); ?>">
                <a href="<?php echo $base_path; ?>index.php" class="side-nav-link">
                    <i class="uil-home-alt"></i>
                    <span> หน้าแรก </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('calendar.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/calendar.php" class="side-nav-link">
                    <i class="uil-calendar-alt"></i>
                    <span> ปฏิทิน </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('tasks.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/tasks.php" class="side-nav-link">
                    <i class="mdi mdi-bookmark-multiple-outline"></i>
                    <span> งาน </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('projects.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/projects.php" class="side-nav-link">
                    <i class="uil-notebooks"></i>
                    <span> โปรเจค </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo in_array($current_page, ['equipment.php', 'equipment_view.php']) ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>pages/equipment.php" class="side-nav-link">
                    <i class="uil-desktop"></i>
                    <span> อุปกรณ์ </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('equipment_borrowing.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/equipment_borrowing.php" class="side-nav-link">
                    <i class="uil-exchange"></i>
                    <span> รายการยืม-คืน </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('transactions.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/transactions.php" class="side-nav-link">
                    <i class="uil-bill"></i>
                    <span> รายรับ-รายจ่าย </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('members.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/members.php" class="side-nav-link">
                    <i class="uil-users-alt"></i>
                    <span> สมาชิก </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('board.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/board.php" class="side-nav-link">
                    <i class="uil-award"></i>
                    <span> บอร์ดบริหาร </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('advisor.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/advisor.php" class="side-nav-link">
                    <i class="uil-user-check"></i>
                    <span> อาจารย์ที่ปรึกษา </span>
                </a>
            </li>

            <?php if (in_array($_SESSION['role'], ['admin', 'board', 'advisor'])): ?>
            <li class="side-nav-title side-nav-item">การจัดการ</li>

            <li class="side-nav-item <?php echo isActive('equipment_approval.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/equipment_approval.php" class="side-nav-link">
                    <i class="uil-check-circle"></i>
                    <span> อนุมัติการยืม-คืน </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('transaction_approval.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/transaction_approval.php" class="side-nav-link">
                    <i class="uil-clipboard-alt"></i>
                    <span> อนุมัติรายรับรายจ่าย </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('registration_link.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/registration_link.php" class="side-nav-link">
                    <i class="uil-link"></i>
                    <span> งานรับลงทะเบียน </span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="side-nav-title side-nav-item">ผู้ดูแลระบบ</li>

            <li class="side-nav-item <?php echo isActive('manageUsers.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/manageUsers.php" class="side-nav-link">
                    <i class="uil-user-check"></i>
                    <span> จัดการผู้ใช้งาน </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('login_bg_settings.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/login_bg_settings.php" class="side-nav-link">
                    <i class="uil-image"></i>
                    <span> พื้นหลัง Login </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('activity_logs.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/activity_logs.php" class="side-nav-link">
                    <i class="uil-history"></i>
                    <span> Activity Logs </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('login_attempts.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/login_attempts.php" class="side-nav-link">
                    <i class="uil-shield-check"></i>
                    <span> Login Attempts </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('session_management.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/session_management.php" class="side-nav-link">
                    <i class="uil-monitor"></i>
                    <span> จัดการ Session </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('admin_club_positions.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/admin_club_positions.php" class="side-nav-link">
                    <i class="uil-sitemap"></i>
                    <span> จัดการตำแหน่ง </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('admin_departments.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/admin_departments.php" class="side-nav-link">
                    <i class="uil-building"></i>
                    <span> จัดการฝ่าย </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('admin_categories.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/admin_categories.php" class="side-nav-link">
                    <i class="uil-tag-alt"></i>
                    <span> จัดการหมวดหมู่ </span>
                </a>
            </li>

            <li class="side-nav-item <?php echo isActive('academic_years.php'); ?>">
                <a href="<?php echo $base_path; ?>pages/academic_years.php" class="side-nav-link">
                    <i class="mdi mdi-calendar-range"></i>
                    <span> จัดการปีการศึกษา </span>
                </a>
            </li>
            <?php endif; ?>

        </ul>

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->