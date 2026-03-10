USE dashboard_db;

-- club_positions
ALTER TABLE club_positions
MODIFY COLUMN name VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อตำแหน่ง',
MODIFY COLUMN level TINYINT NOT NULL DEFAULT 1 COMMENT 'ระดับ 1=สมาชิก',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบาย';

-- equipment
ALTER TABLE equipment
MODIFY COLUMN equipment_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสอุปกรณ์',
MODIFY COLUMN name VARCHAR(255) NOT NULL COMMENT 'ชื่อ',
MODIFY COLUMN category_id INT UNSIGNED NOT NULL COMMENT 'หมวดหมู่',
MODIFY COLUMN brand VARCHAR(100) NULL COMMENT 'ยี่ห้อ',
MODIFY COLUMN model VARCHAR(100) NULL COMMENT 'รุ่น',
MODIFY COLUMN serial_number VARCHAR(100) NULL COMMENT 'หมายเลขซีเรียล',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบาย',
MODIFY COLUMN quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวน',
MODIFY COLUMN available_quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวนที่ว่าง',
MODIFY COLUMN status ENUM('available','borrowed','maintenance','broken','retired') NOT NULL DEFAULT 'available' COMMENT 'สถานะ',
MODIFY COLUMN purchase_date DATE NULL COMMENT 'วันที่ซื้อ',
MODIFY COLUMN purchase_price DECIMAL(10,2) NULL COMMENT 'ราคา',
MODIFY COLUMN location VARCHAR(255) NULL COMMENT 'สถานที่เก็บ',
MODIFY COLUMN image VARCHAR(255) NULL COMMENT 'รูปภาพ',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ',
MODIFY COLUMN created_by INT UNSIGNED NOT NULL COMMENT 'ผู้สร้าง';

-- equipment_categories
ALTER TABLE equipment_categories
MODIFY COLUMN name VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อหมวดหมู่',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบาย',
MODIFY COLUMN icon VARCHAR(50) NULL COMMENT 'ไอคอน';

-- equipment_borrowing
ALTER TABLE equipment_borrowing
MODIFY COLUMN equipment_id INT UNSIGNED NOT NULL COMMENT 'อุปกรณ์',
MODIFY COLUMN borrower_id INT UNSIGNED NOT NULL COMMENT 'ผู้ยืม',
MODIFY COLUMN quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวน',
MODIFY COLUMN borrow_date DATETIME NOT NULL COMMENT 'วันที่ยืม',
MODIFY COLUMN expected_return_date DATETIME NULL COMMENT 'วันที่คาดว่าจะคืน',
MODIFY COLUMN actual_return_date DATETIME NULL COMMENT 'วันที่คืนจริง',
MODIFY COLUMN purpose TEXT NULL COMMENT 'วัตถุประสงค์',
MODIFY COLUMN status ENUM('pending','approved','borrowed','returned','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'สถานะ',
MODIFY COLUMN approved_by INT UNSIGNED NULL COMMENT 'ผู้อนุมัติ',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ';

-- member_club_info
ALTER TABLE member_club_info
MODIFY COLUMN member_generation TINYINT NOT NULL COMMENT 'รุ่นสมาชิก',
MODIFY COLUMN joined_date DATE NOT NULL COMMENT 'วันที่เข้าชมรม',
MODIFY COLUMN department_id INT UNSIGNED NULL COMMENT 'ฝ่าย',
MODIFY COLUMN position_id INT UNSIGNED NULL COMMENT 'ตำแหน่ง';

-- projects
ALTER TABLE projects
MODIFY COLUMN project_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'รหัสโครงการ',
MODIFY COLUMN name VARCHAR(255) NOT NULL COMMENT 'ชื่อโครงการ',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN category VARCHAR(100) NULL COMMENT 'หมวดหมู่',
MODIFY COLUMN status ENUM('planning','in_progress','completed','cancelled') NOT NULL DEFAULT 'planning' COMMENT 'สถานะ',
MODIFY COLUMN priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium' COMMENT 'ความสำคัญ',
MODIFY COLUMN start_date DATE NULL COMMENT 'วันเริ่ม',
MODIFY COLUMN end_date DATE NULL COMMENT 'วันสิ้นสุด',
MODIFY COLUMN location VARCHAR(255) NULL COMMENT 'สถานที่',
MODIFY COLUMN budget DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'งบประมาณ',
MODIFY COLUMN actual_cost DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'ค่าใช้จ่ายจริง',
MODIFY COLUMN project_manager_id INT UNSIGNED NULL COMMENT 'ผู้จัดการโครงการ',
MODIFY COLUMN department_id INT UNSIGNED NULL COMMENT 'ฝ่ายที่รับผิดชอบ',
MODIFY COLUMN cover_image VARCHAR(255) NULL COMMENT 'รูปปก',
MODIFY COLUMN created_by INT UNSIGNED NOT NULL COMMENT 'ผู้สร้าง';

-- project_members
ALTER TABLE project_members
MODIFY COLUMN project_id INT UNSIGNED NOT NULL COMMENT 'โครงการ',
MODIFY COLUMN user_id INT UNSIGNED NOT NULL COMMENT 'สมาชิก',
MODIFY COLUMN role VARCHAR(100) NULL COMMENT 'บทบาท',
MODIFY COLUMN joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่เข้าร่วม';

-- tasks
ALTER TABLE tasks
MODIFY COLUMN title VARCHAR(255) NOT NULL COMMENT 'ชื่องาน',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN project_id INT UNSIGNED NULL COMMENT 'โครงการ',
MODIFY COLUMN assigned_to INT UNSIGNED NULL COMMENT 'มอบหมายให้',
MODIFY COLUMN priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium' COMMENT 'ความสำคัญ',
MODIFY COLUMN status ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'สถานะ',
MODIFY COLUMN due_date DATETIME NULL COMMENT 'กำหนดส่ง',
MODIFY COLUMN completed_at DATETIME NULL COMMENT 'เสร็จเมื่อ',
MODIFY COLUMN created_by INT UNSIGNED NOT NULL COMMENT 'ผู้สร้าง';

-- task_assignments
ALTER TABLE task_assignments
MODIFY COLUMN task_id INT UNSIGNED NOT NULL COMMENT 'งาน',
MODIFY COLUMN user_id INT UNSIGNED NOT NULL COMMENT 'สมาชิก',
MODIFY COLUMN role VARCHAR(100) NULL COMMENT 'บทบาท',
MODIFY COLUMN assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'มอบหมายเมื่อ';

-- task_attachments
ALTER TABLE task_attachments
MODIFY COLUMN task_id INT UNSIGNED NOT NULL COMMENT 'งาน',
MODIFY COLUMN file_name VARCHAR(255) NOT NULL COMMENT 'ชื่อไฟล์',
MODIFY COLUMN file_path VARCHAR(500) NOT NULL COMMENT 'ที่อยู่ไฟล์',
MODIFY COLUMN file_size INT NULL COMMENT 'ขนาดไฟล์',
MODIFY COLUMN file_type VARCHAR(100) NULL COMMENT 'ประเภท',
MODIFY COLUMN uploaded_by INT UNSIGNED NOT NULL COMMENT 'ผู้อัปโหลด';

-- task_activity_logs
ALTER TABLE task_activity_logs
MODIFY COLUMN task_id INT UNSIGNED NOT NULL COMMENT 'งาน',
MODIFY COLUMN user_id INT UNSIGNED NOT NULL COMMENT 'สมาชิก',
MODIFY COLUMN action VARCHAR(100) NOT NULL COMMENT 'การกระทำ',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN old_value TEXT NULL COMMENT 'ค่าเก่า',
MODIFY COLUMN new_value TEXT NULL COMMENT 'ค่าใหม่';

-- transaction_categories
ALTER TABLE transaction_categories
MODIFY COLUMN name VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อหมวดหมู่',
MODIFY COLUMN type ENUM('income','expense') NOT NULL COMMENT 'ประเภท',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบาย',
MODIFY COLUMN icon VARCHAR(50) NULL COMMENT 'ไอคอน',
MODIFY COLUMN color VARCHAR(20) NULL COMMENT 'สี';

-- transactions
ALTER TABLE transactions
MODIFY COLUMN type ENUM('income','expense') NOT NULL COMMENT 'ประเภท',
MODIFY COLUMN amount DECIMAL(10,2) NOT NULL COMMENT 'จำนวนเงิน',
MODIFY COLUMN category_id INT UNSIGNED NULL COMMENT 'หมวดหมู่',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN transaction_date DATE NOT NULL COMMENT 'วันที่ทำรายการ',
MODIFY COLUMN project_id INT UNSIGNED NULL COMMENT 'โครงการ',
MODIFY COLUMN receipt_image VARCHAR(500) NULL COMMENT 'รูปใบเสร็จ',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ',
MODIFY COLUMN created_by INT UNSIGNED NOT NULL COMMENT 'ผู้บันทึก';
