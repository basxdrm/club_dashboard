-- Create academic_years table
CREATE TABLE IF NOT EXISTS `academic_years` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add academic_year_id to projects table
ALTER TABLE `projects` 
ADD COLUMN `academic_year_id` int(11) NULL AFTER `id`,
ADD KEY `fk_projects_academic_year` (`academic_year_id`),
ADD CONSTRAINT `fk_projects_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL;

-- Insert current academic year (2567)
INSERT INTO `academic_years` (`year`, `start_date`, `end_date`, `is_current`, `is_active`) 
VALUES ('2567', '2024-05-01', '2025-04-30', 1, 1);

-- Update existing projects to current academic year
UPDATE `projects` 
SET `academic_year_id` = (SELECT id FROM `academic_years` WHERE `is_current` = 1 LIMIT 1)
WHERE `academic_year_id` IS NULL;
