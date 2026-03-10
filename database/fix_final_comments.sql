USE dashboard_db;

ALTER TABLE equipment_borrowing
MODIFY COLUMN borrowing_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสการยืม',
MODIFY COLUMN equipment_id INT UNSIGNED NOT NULL COMMENT 'อุปกรณ์',
MODIFY COLUMN borrower_id INT UNSIGNED NOT NULL COMMENT 'ผู้ยืม',
MODIFY COLUMN quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวน',
MODIFY COLUMN purpose TEXT NULL COMMENT 'วัตถุประสงค์',
MODIFY COLUMN project_id INT UNSIGNED NULL COMMENT 'โครงการ',
MODIFY COLUMN borrow_date DATETIME NOT NULL COMMENT 'วันที่ยืม',
MODIFY COLUMN due_date DATETIME NOT NULL COMMENT 'วันครบกำหนด',
MODIFY COLUMN return_date DATETIME NULL COMMENT 'วันที่คืน',
MODIFY COLUMN status ENUM('pending','approved','borrowed','returned','overdue','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'สถานะ',
MODIFY COLUMN approved_by INT UNSIGNED NULL COMMENT 'ผู้อนุมัติ',
MODIFY COLUMN approved_at DATETIME NULL COMMENT 'วันที่อนุมัติ',
MODIFY COLUMN returned_condition ENUM('good','damaged','lost') NULL COMMENT 'สภาพตอนคืน',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ';

ALTER TABLE tasks
MODIFY COLUMN project_id INT UNSIGNED NOT NULL COMMENT 'โครงการ',
MODIFY COLUMN task_code VARCHAR(50) NULL COMMENT 'รหัสงาน',
MODIFY COLUMN title VARCHAR(255) NOT NULL COMMENT 'ชื่องาน',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium' COMMENT 'ความสำคัญ',
MODIFY COLUMN assignment_mode ENUM('direct','registration','hybrid') NOT NULL DEFAULT 'direct' COMMENT 'รูปแบบมอบหมาย',
MODIFY COLUMN assigned_to INT UNSIGNED NULL COMMENT 'มอบหมายให้',
MODIFY COLUMN max_assignees INT NULL COMMENT 'จำนวนผู้รับงานสูงสุด',
MODIFY COLUMN current_assignees INT NULL DEFAULT 0 COMMENT 'จำนวนผู้รับงานปัจจุบัน',
MODIFY COLUMN registration_link VARCHAR(500) NULL COMMENT 'ลิงก์ลงทะเบียน',
MODIFY COLUMN registration_deadline DATETIME NULL COMMENT 'กำหนดลงทะเบียน',
MODIFY COLUMN requires_approval TINYINT(1) NULL DEFAULT 0 COMMENT 'ต้องอนุมัติ',
MODIFY COLUMN due_date DATE NULL COMMENT 'กำหนดส่ง',
MODIFY COLUMN completed_date TIMESTAMP NULL COMMENT 'วันที่เสร็จ',
MODIFY COLUMN progress TINYINT NULL DEFAULT 0 COMMENT 'ความคืบหน้า',
MODIFY COLUMN estimated_hours DECIMAL(5,2) NULL COMMENT 'ชั่วโมงประมาณ',
MODIFY COLUMN actual_hours DECIMAL(5,2) NULL COMMENT 'ชั่วโมงจริง',
MODIFY COLUMN submit_message TEXT NULL COMMENT 'ข้อความส่งงาน',
MODIFY COLUMN submitted_at DATETIME NULL COMMENT 'ส่งงานเมื่อ',
MODIFY COLUMN review_message TEXT NULL COMMENT 'ข้อความรีวิว',
MODIFY COLUMN reviewed_by INT UNSIGNED NULL COMMENT 'ผู้รีวิว',
MODIFY COLUMN reviewed_at DATETIME NULL COMMENT 'รีวิวเมื่อ',
MODIFY COLUMN parent_task_id INT UNSIGNED NULL COMMENT 'งานหลัก',
MODIFY COLUMN created_by INT UNSIGNED NOT NULL COMMENT 'ผู้สร้าง';

ALTER TABLE project_members
MODIFY COLUMN project_id INT UNSIGNED NOT NULL COMMENT 'โครงการ',
MODIFY COLUMN user_id INT UNSIGNED NOT NULL COMMENT 'สมาชิก',
MODIFY COLUMN role VARCHAR(100) NULL COMMENT 'บทบาท',
MODIFY COLUMN responsibility TEXT NULL COMMENT 'ความรับผิดชอบ',
MODIFY COLUMN joined_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่เข้าร่วม';
