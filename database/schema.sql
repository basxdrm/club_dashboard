
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` varchar(10) NOT NULL COMMENT 'ปีการศึกษา เช่น 2567, 2568',
  `start_date` date NOT NULL COMMENT 'วันเริ่มต้นปีการศึกษา',
  `end_date` date NOT NULL COMMENT 'วันสิ้นสุดปีการศึกษา',
  `is_current` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'ปีการศึกษาปัจจุบัน',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'สถานะการใช้งาน',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `club_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'ชื่อฝ่าย',
  `description` text DEFAULT NULL COMMENT 'รายละเอียด',
  `icon` varchar(50) DEFAULT NULL COMMENT 'ไอคอน',
  `color` varchar(20) DEFAULT NULL COMMENT 'สี',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `club_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_positions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'ชื่อตำแหน่ง',
  `level` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'ระดับ 1=สูงสุด',
  `description` text DEFAULT NULL COMMENT 'รายละเอียด',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_level` (`level`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'ชื่อ',
  `category_id` int(10) unsigned NOT NULL COMMENT 'หมวดหมู่',
  `brand` varchar(100) DEFAULT NULL COMMENT 'ยี่ห้อ',
  `model` varchar(100) DEFAULT NULL COMMENT 'รุ่น',
  `serial_number` varchar(100) DEFAULT NULL COMMENT 'Serial Number',
  `description` text DEFAULT NULL COMMENT 'รายละเอียด',
  `status` enum('available','borrowed','maintenance','broken','retired') NOT NULL DEFAULT 'available',
  `purchase_date` date DEFAULT NULL COMMENT 'วันที่ซื้อ',
  `purchase_price` decimal(10,2) DEFAULT NULL COMMENT 'ราคาซื้อ',
  `location` varchar(255) DEFAULT NULL COMMENT 'ที่เก็บ',
  `image` varchar(255) DEFAULT NULL COMMENT 'รูปภาพ',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `created_by` (`created_by`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`),
  CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `equipment_borrowing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_borrowing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `borrowing_code` varchar(50) NOT NULL COMMENT 'รหัสการยืม',
  `equipment_id` int(10) unsigned NOT NULL,
  `borrower_id` int(10) unsigned NOT NULL COMMENT 'ผู้ยืม',
  `purpose` text DEFAULT NULL COMMENT 'วัตถุประสงค์',
  `task_id` int(10) unsigned DEFAULT NULL COMMENT 'งานที่เกี่ยวข้อง',
  `borrow_date` datetime NOT NULL COMMENT 'วันที่ยืม',
  `due_date` datetime NOT NULL COMMENT 'กำหนดคืน',
  `return_date` datetime DEFAULT NULL COMMENT 'วันที่คืนจริง',
  `status` enum('pending','borrowed','request_return','returned','overdue','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) unsigned DEFAULT NULL COMMENT 'ผู้อนุมัติ',
  `approved_at` datetime DEFAULT NULL,
  `returned_condition` enum('good','damaged','lost') DEFAULT NULL COMMENT 'สภาพตอนคืน',
  `notes` text DEFAULT NULL COMMENT 'หมายเหตุ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `borrowing_code` (`borrowing_code`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_equipment_id` (`equipment_id`),
  KEY `idx_borrower_id` (`borrower_id`),
  KEY `idx_status` (`status`),
  KEY `idx_borrow_date` (`borrow_date`),
  KEY `idx_due_date` (`due_date`),
  KEY `fk_equipment_borrowing_task` (`task_id`),
  CONSTRAINT `equipment_borrowing_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  CONSTRAINT `equipment_borrowing_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_borrowing_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_equipment_borrowing_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `equipment_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'ชื่อหมวดหมู่',
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL COMMENT 'ไอคอน',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_attempted_at` (`attempted_at`),
  KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_club_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_club_info` (
  `user_id` int(10) unsigned NOT NULL,
  `member_generation` tinyint(4) DEFAULT NULL,
  `joined_date` date NOT NULL COMMENT 'วันที่เข้าร่วมชมรม',
  `department_id` int(10) unsigned DEFAULT NULL COMMENT 'ฝ่ายในชมรม',
  `is_department_head` tinyint(1) DEFAULT 0,
  `position_id` int(10) unsigned DEFAULT NULL COMMENT 'ตำแหน่งในชมรม',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  KEY `idx_member_generation` (`member_generation`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_position_id` (`position_id`),
  CONSTRAINT `member_club_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_club_info_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `club_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `member_club_info_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `club_positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_contacts` (
  `user_id` int(10) unsigned NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `line_id` varchar(100) DEFAULT NULL COMMENT 'LINE ID',
  `facebook` varchar(255) DEFAULT NULL COMMENT 'Facebook URL หรือ Username',
  `instagram` varchar(100) DEFAULT NULL COMMENT 'Instagram Username',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `member_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_education` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1 COMMENT 'เป็นข้อมูลปัจจุบันหรือไม่',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `academic_grade` int(11) DEFAULT NULL COMMENT 'ชั้นปีสามัญ เช่น 1, 2, 3',
  `academic_room` varchar(10) DEFAULT NULL COMMENT 'ห้องสามัญ เช่น 1, 2, 5',
  `academic_status` enum('studying','graduated','not_enrolled') DEFAULT 'studying',
  `agama_grade` int(11) DEFAULT NULL COMMENT 'ชั้นปีศาสนา เช่น 1-10',
  `agama_room` varchar(10) DEFAULT NULL COMMENT 'ห้องศาสนา',
  `agama_status` enum('studying','graduated','not_enrolled') DEFAULT 'studying',
  `class_academic` varchar(50) GENERATED ALWAYS AS (case when `academic_grade` is not null and `academic_room` is not null then concat('ม.',`academic_grade`,'/',`academic_room`) else NULL end) STORED COMMENT 'ชั้นสามัญ (auto-generated)',
  `class_agama` varchar(50) GENERATED ALWAYS AS (case when `agama_grade` is not null and `agama_room` is not null then concat('ศ.',`agama_grade`,'/',`agama_room`) else NULL end) STORED COMMENT 'ชั้นศาสนา (auto-generated)',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_education_level` (`academic_year_id`),
  KEY `idx_is_current` (`is_current`),
  CONSTRAINT `member_education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profiles` (
  `user_id` int(10) unsigned NOT NULL,
  `student_id` varchar(20) DEFAULT NULL COMMENT 'รหัสนักเรียน',
  `prefix` enum('เด็กชาย','เด็กหญิง','นาย','นางสาว','นาง') NOT NULL COMMENT 'คำนำหน้าชื่อ',
  `first_name_th` varchar(100) NOT NULL COMMENT 'ชื่อภาษาไทย',
  `last_name_th` varchar(100) NOT NULL COMMENT 'นามสกุลภาษาไทย',
  `nickname_th` varchar(50) DEFAULT NULL COMMENT 'ชื่อเล่น',
  `first_name_en` varchar(100) DEFAULT NULL COMMENT 'ชื่อภาษาอังกฤษ',
  `last_name_en` varchar(100) DEFAULT NULL COMMENT 'นามสกุลภาษาอังกฤษ',
  `birth_date` date NOT NULL COMMENT 'วันเกิด',
  `profile_picture` varchar(255) DEFAULT NULL COMMENT 'รูปโปรไฟล์',
  `bio` text DEFAULT NULL COMMENT 'ประวัติย่อ/แนะนำตัว',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'สถานะใช้งาน',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_birth_date` (`birth_date`),
  CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) DEFAULT NULL,
  `project_code` varchar(20) NOT NULL COMMENT 'รหัสโปรเจค',
  `name` varchar(255) NOT NULL COMMENT 'ชื่อโปรเจค',
  `description` text DEFAULT NULL COMMENT 'รายละเอียด',
  `status` enum('planning','in_progress','completed','cancelled') NOT NULL DEFAULT 'planning',
  `start_date` date DEFAULT NULL COMMENT 'วันที่เริ่ม',
  `end_date` date DEFAULT NULL COMMENT 'วันที่สิ้นสุด',
  `location` varchar(255) DEFAULT NULL COMMENT 'สถานที่',
  `budget` decimal(10,2) DEFAULT 0.00 COMMENT 'งบประมาณ',
  `project_manager_id` int(10) unsigned DEFAULT NULL COMMENT 'ผู้จัดการโปรเจค',
  `department_id` int(10) unsigned DEFAULT NULL COMMENT 'ฝ่ายรับผิดชอบ',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_code` (`project_code`),
  KEY `department_id` (`department_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_project_manager_id` (`project_manager_id`),
  KEY `fk_projects_academic_year` (`academic_year_id`),
  CONSTRAINT `fk_projects_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`project_manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `club_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `projects_set_academic_year_insert` BEFORE INSERT ON `projects` FOR EACH ROW BEGIN
    
    IF NEW.academic_year_id IS NULL THEN
        
        IF NEW.start_date IS NOT NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE NEW.start_date BETWEEN start_date AND end_date 
                AND is_active = 1
                LIMIT 1
            );
        END IF;
        
        
        IF NEW.academic_year_id IS NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE is_current = 1 
                LIMIT 1
            );
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `projects_set_academic_year_update` BEFORE UPDATE ON `projects` FOR EACH ROW BEGIN
    
    IF NEW.start_date != OLD.start_date OR (NEW.start_date IS NOT NULL AND OLD.start_date IS NULL) THEN
        
        IF NEW.start_date IS NOT NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE NEW.start_date BETWEEN start_date AND end_date 
                AND is_active = 1
                LIMIT 1
            );
        END IF;
        
        
        IF NEW.academic_year_id IS NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE is_current = 1 
                LIMIT 1
            );
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remember_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_user_expires` (`user_id`,`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT 'ผู้ทำการเปลี่ยนแปลง',
  `action_type` varchar(50) NOT NULL COMMENT 'ประเภทการเปลี่ยนแปลง เช่น status_changed, assigned, progress_updated',
  `status_from` varchar(50) DEFAULT NULL COMMENT 'สถานะเดิม',
  `status_to` varchar(50) DEFAULT NULL COMMENT 'สถานะใหม่',
  `review_message` text DEFAULT NULL COMMENT 'ข้อความรีวิว/หมายเหตุ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `task_activity_logs_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_activity_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `assignment_type` enum('direct_assign','self_register','admin_add') NOT NULL DEFAULT 'direct_assign' COMMENT 'direct_assign=ถูกมอบหมายโดยตรง, self_register=ลงทะเบียนเองผ่านลิ้งค์, admin_add=admin เพิ่มให้',
  `assigned_by` int(10) unsigned DEFAULT NULL COMMENT 'ผู้มอบหมาย',
  `status` enum('pending','approved','rejected','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_task_assignment` (`task_id`,`user_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_assignment_type` (`assignment_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL COMMENT 'ขนาดไฟล์ (bytes)',
  `file_type` varchar(50) DEFAULT NULL COMMENT 'ประเภทไฟล์',
  `uploaded_by` int(10) unsigned NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_task_id` (`task_id`),
  CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL COMMENT 'ชื่อหัวข้องาน',
  `description` text DEFAULT NULL COMMENT 'รายละเอียดงาน',
  `status` enum('pending','in_progress','under_review','completed','cancelled') NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assignment_mode` enum('direct','registration','hybrid') NOT NULL DEFAULT 'direct' COMMENT 'direct=มอบหมายเลย, registration=ส่งลิ้งค์ให้ลงทะเบียน, hybrid=ทั้ง2แบบ',
  `assigned_to` int(10) unsigned DEFAULT NULL COMMENT 'มอบหมายให้ (สำหรับ direct mode)',
  `max_assignees` int(11) DEFAULT NULL COMMENT 'จำนวนคนรับงานสูงสุด (สำหรับ registration mode)',
  `current_assignees` int(11) DEFAULT 0 COMMENT 'จำนวนคนที่ลงทะเบียนแล้ว',
  `registration_link` varchar(500) DEFAULT NULL COMMENT 'ลิงค์ลงทะเบียนรับงาน (auto-generated)',
  `due_date` date DEFAULT NULL COMMENT 'กำหนดส่ง',
  `start_date` date DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_status` (`status`),
  KEY `idx_assignment_mode` (`assignment_mode`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_due_date` (`due_date`),
  KEY `fk_tasks_academic_year` (`academic_year_id`),
  CONSTRAINT `fk_tasks_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tasks_set_academic_year_insert` BEFORE INSERT ON `tasks` FOR EACH ROW BEGIN
    
    IF NEW.academic_year_id IS NULL THEN
        
        IF NEW.start_date IS NOT NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE NEW.start_date BETWEEN start_date AND end_date 
                AND is_active = 1
                LIMIT 1
            );
        END IF;
        
        
        IF NEW.academic_year_id IS NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE is_current = 1 
                LIMIT 1
            );
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tasks_set_academic_year_update` BEFORE UPDATE ON `tasks` FOR EACH ROW BEGIN
    
    IF NEW.start_date != OLD.start_date OR (NEW.start_date IS NOT NULL AND OLD.start_date IS NULL) THEN
        
        IF NEW.start_date IS NOT NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE NEW.start_date BETWEEN start_date AND end_date 
                AND is_active = 1
                LIMIT 1
            );
        END IF;
        
        
        IF NEW.academic_year_id IS NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE is_current = 1 
                LIMIT 1
            );
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `transaction_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('income','expense') NOT NULL COMMENT 'ประเภท รายรับ/รายจ่าย',
  `name` varchar(100) NOT NULL COMMENT 'ชื่อหมวดหมู่',
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(50) NOT NULL COMMENT 'รหัสรายการ',
  `type` enum('income','expense') NOT NULL COMMENT 'รายรับ/รายจ่าย',
  `category_id` int(10) unsigned NOT NULL COMMENT 'หมวดหมู่',
  `amount` decimal(10,2) NOT NULL COMMENT 'จำนวนเงิน',
  `transaction_date` date NOT NULL COMMENT 'วันที่ทำรายการ',
  `description` text NOT NULL COMMENT 'รายละเอียด',
  `task_id` int(11) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL COMMENT 'รูปใบเสร็จ',
  `recorded_by` int(10) unsigned NOT NULL COMMENT 'ผู้บันทึก',
  `approved_by` int(10) unsigned DEFAULT NULL COMMENT 'ผู้อนุมัติ',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_code` (`transaction_code`),
  KEY `category_id` (`category_id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_type` (`type`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_status` (`status`),
  KEY `idx_project_id` (`task_id`),
  CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('member','board','admin','advisor') NOT NULL DEFAULT 'member',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=ลาออก, 1=สมาชิก, 2=จบการศึกษา',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) unsigned DEFAULT NULL COMMENT 'Admin ที่สร้าง',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

