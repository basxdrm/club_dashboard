USE dashboard_db;

-- users table
ALTER TABLE users 
MODIFY COLUMN status TINYINT NOT NULL DEFAULT 1 COMMENT '0=ลาออก, 1=สมาชิก, 2=จบการศึกษา',
MODIFY COLUMN created_by INT UNSIGNED NULL COMMENT 'Admin ที่สร้าง';

-- profiles table
ALTER TABLE profiles
MODIFY COLUMN student_id VARCHAR(20) NOT NULL UNIQUE COMMENT 'รหัสนักเรียน',
MODIFY COLUMN prefix ENUM('เด็กชาย', 'เด็กหญิง', 'นาย', 'นางสาว', 'นาง') NOT NULL COMMENT 'คำนำหน้าชื่อ',
MODIFY COLUMN first_name_th VARCHAR(100) NOT NULL COMMENT 'ชื่อภาษาไทย',
MODIFY COLUMN last_name_th VARCHAR(100) NOT NULL COMMENT 'นามสกุลภาษาไทย',
MODIFY COLUMN nickname_th VARCHAR(50) NULL COMMENT 'ชื่อเล่น',
MODIFY COLUMN first_name_en VARCHAR(100) NULL COMMENT 'ชื่อภาษาอังกฤษ',
MODIFY COLUMN last_name_en VARCHAR(100) NULL COMMENT 'นามสกุลภาษาอังกฤษ',
MODIFY COLUMN birth_date DATE NOT NULL COMMENT 'วันเกิด',
MODIFY COLUMN profile_picture VARCHAR(255) NULL COMMENT 'รูปโปรไฟล์',
MODIFY COLUMN bio TEXT NULL COMMENT 'ประวัติย่อ/แนะนำตัว',
MODIFY COLUMN is_active BOOLEAN DEFAULT TRUE COMMENT 'สถานะใช้งาน';

-- member_education table
ALTER TABLE member_education
MODIFY COLUMN class_academic VARCHAR(20) NOT NULL COMMENT 'ชั้น (วิชาการ) เช่น ม.1/1',
MODIFY COLUMN class_agama VARCHAR(20) NULL COMMENT 'ชั้น (อาคาม่า) เช่น อ.1',
MODIFY COLUMN education_level ENUM('ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6') NOT NULL COMMENT 'ระดับชั้น',
MODIFY COLUMN academic_year VARCHAR(10) NOT NULL COMMENT 'ปีการศึกษา เช่น 2568',
MODIFY COLUMN semester TINYINT NULL COMMENT 'ภาคเรียน 1 หรือ 2',
MODIFY COLUMN is_current BOOLEAN DEFAULT TRUE COMMENT 'เป็นข้อมูลปัจจุบันหรือไม่';

-- member_contacts table
ALTER TABLE member_contacts
MODIFY COLUMN phone VARCHAR(20) NULL COMMENT 'เบอร์โทรศัพท์',
MODIFY COLUMN line_id VARCHAR(50) NULL COMMENT 'LINE ID',
MODIFY COLUMN address TEXT NULL COMMENT 'ที่อยู่',
MODIFY COLUMN emergency_contact VARCHAR(100) NULL COMMENT 'ผู้ติดต่อฉุกเฉิน',
MODIFY COLUMN emergency_phone VARCHAR(20) NULL COMMENT 'เบอร์ฉุกเฉิน',
MODIFY COLUMN emergency_relation VARCHAR(50) NULL COMMENT 'ความสัมพันธ์กับผู้ติดต่อฉุกเฉิน';

-- club_positions table
ALTER TABLE club_positions
MODIFY COLUMN name VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อตำแหน่ง',
MODIFY COLUMN level TINYINT NOT NULL DEFAULT 1 COMMENT 'ระดับ (1-5)',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบายตำแหน่ง',
MODIFY COLUMN display_order INT NULL COMMENT 'ลำดับการแสดงผล';

-- member_club_info table
ALTER TABLE member_club_info
MODIFY COLUMN join_date DATE NOT NULL COMMENT 'วันที่เข้าชมรม',
MODIFY COLUMN leave_date DATE NULL COMMENT 'วันที่ออกจากชมรม',
MODIFY COLUMN skills TEXT NULL COMMENT 'ความสามารถพิเศษ',
MODIFY COLUMN interests TEXT NULL COMMENT 'ความสนใจ',
MODIFY COLUMN goals TEXT NULL COMMENT 'เป้าหมายในชมรม';

-- projects table
ALTER TABLE projects
MODIFY COLUMN project_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสโครงการ',
MODIFY COLUMN name VARCHAR(200) NOT NULL COMMENT 'ชื่อโครงการ',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN start_date DATE NULL COMMENT 'วันที่เริ่มต้น',
MODIFY COLUMN end_date DATE NULL COMMENT 'วันที่สิ้นสุด',
MODIFY COLUMN budget DECIMAL(10,2) NULL COMMENT 'งบประมาณ',
MODIFY COLUMN actual_cost DECIMAL(10,2) NULL COMMENT 'ค่าใช้จ่ายจริง',
MODIFY COLUMN status ENUM('planning', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'planning' COMMENT 'สถานะ';

-- project_members table
ALTER TABLE project_members
MODIFY COLUMN role VARCHAR(100) NULL COMMENT 'บทบาทในโครงการ';

-- tasks table
ALTER TABLE tasks
MODIFY COLUMN title VARCHAR(200) NOT NULL COMMENT 'ชื่องาน',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium' COMMENT 'ความสำคัญ',
MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'สถานะ',
MODIFY COLUMN due_date DATETIME NULL COMMENT 'กำหนดส่ง',
MODIFY COLUMN completed_at DATETIME NULL COMMENT 'วันที่เสร็จ',
MODIFY COLUMN estimated_hours DECIMAL(5,2) NULL COMMENT 'ชั่วโมงโดยประมาณ',
MODIFY COLUMN actual_hours DECIMAL(5,2) NULL COMMENT 'ชั่วโมงจริง';

-- task_assignments table  
ALTER TABLE task_assignments
MODIFY COLUMN role VARCHAR(100) NULL COMMENT 'บทบาทในงาน',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ';

-- task_attachments table
ALTER TABLE task_attachments
MODIFY COLUMN file_name VARCHAR(255) NOT NULL COMMENT 'ชื่อไฟล์',
MODIFY COLUMN file_path VARCHAR(500) NOT NULL COMMENT 'ที่อยู่ไฟล์',
MODIFY COLUMN file_size INT NULL COMMENT 'ขนาดไฟล์ (bytes)',
MODIFY COLUMN file_type VARCHAR(50) NULL COMMENT 'ประเภทไฟล์';

-- equipment_categories table
ALTER TABLE equipment_categories
MODIFY COLUMN name VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อหมวดหมู่',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบาย';

-- equipment table
ALTER TABLE equipment
MODIFY COLUMN code VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสอุปกรณ์',
MODIFY COLUMN name VARCHAR(200) NOT NULL COMMENT 'ชื่ออุปกรณ์',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวน',
MODIFY COLUMN available_quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวนที่ว่าง',
MODIFY COLUMN location VARCHAR(200) NULL COMMENT 'ที่เก็บ',
MODIFY COLUMN purchase_date DATE NULL COMMENT 'วันที่ซื้อ',
MODIFY COLUMN purchase_price DECIMAL(10,2) NULL COMMENT 'ราคาซื้อ',
MODIFY COLUMN condition_status ENUM('excellent', 'good', 'fair', 'poor', 'broken') NOT NULL DEFAULT 'good' COMMENT 'สภาพ',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ';

-- equipment_borrowing table
ALTER TABLE equipment_borrowing
MODIFY COLUMN borrow_date DATETIME NOT NULL COMMENT 'วันที่ยืม',
MODIFY COLUMN expected_return_date DATETIME NULL COMMENT 'วันที่คาดว่าจะคืน',
MODIFY COLUMN actual_return_date DATETIME NULL COMMENT 'วันที่คืนจริง',
MODIFY COLUMN quantity INT NOT NULL DEFAULT 1 COMMENT 'จำนวนที่ยืม',
MODIFY COLUMN purpose TEXT NULL COMMENT 'วัตถุประสงค์',
MODIFY COLUMN status ENUM('pending', 'approved', 'borrowed', 'returned', 'rejected') NOT NULL DEFAULT 'pending' COMMENT 'สถานะ',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ';

-- transaction_categories table
ALTER TABLE transaction_categories
MODIFY COLUMN name VARCHAR(100) NOT NULL UNIQUE COMMENT 'ชื่อหมวดหมู่',
MODIFY COLUMN type ENUM('income', 'expense') NOT NULL COMMENT 'ประเภท: รายรับ/รายจ่าย',
MODIFY COLUMN description TEXT NULL COMMENT 'คำอธิบาย';

-- transactions table
ALTER TABLE transactions
MODIFY COLUMN type ENUM('income', 'expense') NOT NULL COMMENT 'ประเภท: รายรับ/รายจ่าย',
MODIFY COLUMN amount DECIMAL(10,2) NOT NULL COMMENT 'จำนวนเงิน',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN transaction_date DATE NOT NULL COMMENT 'วันที่ทำรายการ',
MODIFY COLUMN receipt_image VARCHAR(500) NULL COMMENT 'รูปใบเสร็จ',
MODIFY COLUMN notes TEXT NULL COMMENT 'หมายเหตุ';

-- activity_logs table
ALTER TABLE activity_logs
MODIFY COLUMN action VARCHAR(100) NOT NULL COMMENT 'การกระทำ',
MODIFY COLUMN table_name VARCHAR(100) NULL COMMENT 'ชื่อตาราง',
MODIFY COLUMN record_id INT UNSIGNED NULL COMMENT 'ID ของข้อมูล',
MODIFY COLUMN old_data JSON NULL COMMENT 'ข้อมูลเก่า',
MODIFY COLUMN new_data JSON NULL COMMENT 'ข้อมูลใหม่',
MODIFY COLUMN ip_address VARCHAR(45) NULL COMMENT 'IP Address',
MODIFY COLUMN user_agent TEXT NULL COMMENT 'User Agent';

-- login_attempts table
ALTER TABLE login_attempts
MODIFY COLUMN email VARCHAR(255) NOT NULL COMMENT 'อีเมลที่พยายาม Login',
MODIFY COLUMN ip_address VARCHAR(45) NOT NULL COMMENT 'IP Address',
MODIFY COLUMN user_agent TEXT NULL COMMENT 'User Agent',
MODIFY COLUMN success BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'สำเร็จหรือไม่',
MODIFY COLUMN attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาที่พยายาม';

-- user_sessions table
ALTER TABLE user_sessions
MODIFY COLUMN session_token VARCHAR(64) NOT NULL UNIQUE COMMENT 'Session Token',
MODIFY COLUMN ip_address VARCHAR(45) NOT NULL COMMENT 'IP Address',
MODIFY COLUMN user_agent TEXT NULL COMMENT 'User Agent',
MODIFY COLUMN last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'กิจกรรมล่าสุด',
MODIFY COLUMN expires_at TIMESTAMP NOT NULL COMMENT 'วันหมดอายุ';

-- task_activity_logs table
ALTER TABLE task_activity_logs
MODIFY COLUMN action VARCHAR(100) NOT NULL COMMENT 'การกระทำ',
MODIFY COLUMN description TEXT NULL COMMENT 'รายละเอียด',
MODIFY COLUMN old_value TEXT NULL COMMENT 'ค่าเก่า',
MODIFY COLUMN new_value TEXT NULL COMMENT 'ค่าใหม่';
