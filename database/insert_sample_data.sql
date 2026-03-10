-- Insert Complete Sample Data
-- เพิ่มข้อมูลจำลองทั้งหมด

USE dashboard_db;

-- ลบข้อมูลเก่า
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE task_assignments;
TRUNCATE TABLE task_activity_logs;
TRUNCATE TABLE equipment_borrowing;
TRUNCATE TABLE project_members;
TRUNCATE TABLE tasks;
TRUNCATE TABLE projects;
TRUNCATE TABLE equipment;
TRUNCATE TABLE transactions;
TRUNCATE TABLE member_club_info;
TRUNCATE TABLE member_contacts;
TRUNCATE TABLE member_education;
TRUNCATE TABLE profiles;
TRUNCATE TABLE users;
TRUNCATE TABLE club_positions;
TRUNCATE TABLE club_departments;
SET FOREIGN_KEY_CHECKS = 1;

-- ฝ่ายต่างๆ
INSERT INTO club_departments (name, description) VALUES
('ฝ่ายสื่อสาร', 'รับผิดชอบการประชาสัมพันธ์และการสื่อสารภายในภายนอก'),
('ฝ่ายเทคนิค', 'รับผิดชอบด้านเทคนิคและอุปกรณ์'),
('ฝ่ายสร้างสรรค์', 'รับผิดชอบการออกแบบและสร้างสรรค์ผลงาน'),
('ฝ่ายอำนวยการ', 'รับผิดชอบงานธุรการและการประสานงาน');

-- ตำแหน่งต่างๆ
INSERT INTO club_positions (name, level) VALUES
('ประธาน', 5),
('รองประธาน', 4),
('หัวหน้าฝ่าย', 3),
('รองหัวหน้าฝ่าย', 2),
('สมาชิก', 1);

-- สร้าง Users (รหัสผ่านทั้งหมดคือ: password123)
INSERT INTO users (email, password, role, status, created_at) VALUES
('admin@msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'admin', 1, NOW()),
('somchai.p@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'board', 1, NOW()),
('suda.k@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'board', 1, NOW()),
('nattapong.w@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('pimchanok.s@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('kritsada.m@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('chanida.t@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('wuttichai.l@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('napasorn.c@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('thanawat.p@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW()),
('sarawut.n@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$UHYuTXhQcjhNOGhMN0V0Rg$mK7X61lcBtBoZGeJ5okprmhml9cas15k5vAIBS2UBM0', 'member', 1, NOW());

-- Get user IDs
SET @admin_id = (SELECT id FROM users WHERE email = 'admin@msj.ac.th');
SET @user2 = (SELECT id FROM users WHERE email = 'somchai.p@student.msj.ac.th');
SET @user3 = (SELECT id FROM users WHERE email = 'suda.k@student.msj.ac.th');
SET @user4 = (SELECT id FROM users WHERE email = 'nattapong.w@student.msj.ac.th');
SET @user5 = (SELECT id FROM users WHERE email = 'pimchanok.s@student.msj.ac.th');
SET @user6 = (SELECT id FROM users WHERE email = 'kritsada.m@student.msj.ac.th');
SET @user7 = (SELECT id FROM users WHERE email = 'chanida.t@student.msj.ac.th');
SET @user8 = (SELECT id FROM users WHERE email = 'wuttichai.l@student.msj.ac.th');
SET @user9 = (SELECT id FROM users WHERE email = 'napasorn.c@student.msj.ac.th');
SET @user10 = (SELECT id FROM users WHERE email = 'thanawat.p@student.msj.ac.th');
SET @user11 = (SELECT id FROM users WHERE email = 'sarawut.n@student.msj.ac.th');

-- Profiles
INSERT INTO profiles (user_id, student_id, prefix, first_name_th, last_name_th, nickname_th, birth_date) VALUES
(@admin_id, '66001', 'นาย', 'ผู้ดูแล', 'ระบบ', 'แอดมิน', '2007-01-01'),
(@user2, '66101', 'นาย', 'สมชาย', 'ประเสริฐ', 'ชาย', '2007-03-15'),
(@user3, '66102', 'นางสาว', 'สุดา', 'คงสมบูรณ์', 'ดา', '2007-05-20'),
(@user4, '66201', 'นาย', 'ณัฐพงษ์', 'วงศ์สุวรรณ', 'นัท', '2008-02-10'),
(@user5, '66202', 'นางสาว', 'พิมพ์ชนก', 'ศรีสุข', 'พิม', '2008-04-25'),
(@user6, '66203', 'นาย', 'กฤษดา', 'มณีรัตน์', 'กฤษ', '2008-06-30'),
(@user7, '66204', 'นางสาว', 'ชนิดา', 'ธนาวัฒน์', 'นิด', '2008-08-15'),
(@user8, '66301', 'นาย', 'วุฒิชัย', 'ลิ้มสกุล', 'วิน', '2009-01-20'),
(@user9, '66302', 'นางสาว', 'นภาสร', 'จันทร์เพ็ญ', 'นภา', '2009-03-10'),
(@user10, '66303', 'นาย', 'ธนวัฒน์', 'พูลสวัสดิ์', 'ธน', '2009-05-05'),
(@user11, '66304', 'นาย', 'สราวุฒิ', 'นาคสุวรรณ', 'วุฒิ', '2009-07-12');

-- Member Education
INSERT INTO member_education (user_id, education_level, class_academic, is_current) VALUES
(@admin_id, 'high_school', 'ม.6/1', 1),
(@user2, 'high_school', 'ม.6/1', 1),
(@user3, 'high_school', 'ม.6/2', 1),
(@user4, 'high_school', 'ม.5/1', 1),
(@user5, 'high_school', 'ม.5/2', 1),
(@user6, 'high_school', 'ม.5/3', 1),
(@user7, 'high_school', 'ม.5/4', 1),
(@user8, 'high_school', 'ม.4/1', 1),
(@user9, 'high_school', 'ม.4/2', 1),
(@user10, 'high_school', 'ม.4/3', 1),
(@user11, 'high_school', 'ม.4/4', 1);

-- Member Contacts
INSERT INTO member_contacts (user_id, phone_number, line_id, instagram_username) VALUES
(@admin_id, '0812345678', 'admin.msj', 'admin_msj'),
(@user2, '0823456789', 'somchai_p', 'somchai_p'),
(@user3, '0834567890', 'suda_k', 'suda.k'),
(@user4, '0845678901', 'nattapong_w', 'nattapong.w'),
(@user5, '0856789012', 'pim_s', 'pimchanok.s'),
(@user6, '0867890123', 'kritsada_m', 'kritsada.m'),
(@user7, '0878901234', 'chanida_t', 'chanida_t'),
(@user8, '0889012345', 'win_l', 'wuttichai.l'),
(@user9, '0890123456', 'napa_c', 'napasorn.c'),
(@user10, '0801234567', 'thanawat_p', 'thanawat.p'),
(@user11, '0812345679', 'sarawut_n', 'sarawut_n');

-- Member Club Info
INSERT INTO member_club_info (user_id, department_id, position_id, member_generation, joined_date) VALUES
(@admin_id, NULL, 1, 10, '2023-05-01'),
(@user2, 1, 1, 10, '2023-05-15'),
(@user3, 2, 2, 10, '2023-05-15'),
(@user4, 1, 3, 11, '2024-05-10'),
(@user5, 3, 3, 11, '2024-05-10'),
(@user6, 2, 4, 11, '2024-05-12'),
(@user7, 4, 5, 11, '2024-05-12'),
(@user8, 1, 5, 12, '2025-05-08'),
(@user9, 3, 5, 12, '2025-05-08'),
(@user10, 2, 5, 12, '2025-05-10'),
(@user11, 4, 5, 12, '2025-05-10');

-- Projects
INSERT INTO projects (name, description, project_code, start_date, end_date, status, budget, created_by) VALUES
('โครงการแข่งขันวีดีโอสั้น', 'จัดการแข่งขันสร้างสรรค์วีดีโอสั้นภายในโรงเรียน', 'PRJ-2025-001', '2025-11-01', '2025-12-15', 'in_progress', 15000.00, @user2),
('โครงการอบรมการถ่ายภาพ', 'อบรมเทคนิคการถ่ายภาพเบื้องต้นสำหรับนักเรียน', 'PRJ-2025-002', '2025-10-15', '2025-11-30', 'in_progress', 8000.00, @user3),
('งานคอนเสิร์ตปิดเทอม', 'จัดคอนเสิร์ตดนตรีเพื่อปิดภาคเรียน', 'PRJ-2025-003', '2025-12-01', '2025-12-20', 'planning', 25000.00, @user2),
('โครงการพัฒนาเว็บไซต์ชมรม', 'สร้างและพัฒนาเว็บไซต์ใหม่ของชมรม', 'PRJ-2025-004', '2025-09-01', '2025-11-30', 'completed', 12000.00, @user3);

-- Get project IDs
SET @proj1 = LAST_INSERT_ID();
SET @proj2 = @proj1 + 1;
SET @proj3 = @proj1 + 2;
SET @proj4 = @proj1 + 3;

-- Project Members
INSERT INTO project_members (project_id, user_id, role, joined_date) VALUES
(@proj1, @user2, 'leader', '2025-11-01'),
(@proj1, @user4, 'member', '2025-11-01'),
(@proj1, @user5, 'member', '2025-11-01'),
(@proj2, @user3, 'leader', '2025-10-15'),
(@proj2, @user6, 'member', '2025-10-15'),
(@proj2, @user8, 'member', '2025-10-15'),
(@proj3, @user2, 'leader', '2025-12-01'),
(@proj3, @user7, 'member', '2025-12-01'),
(@proj3, @user9, 'member', '2025-12-01'),
(@proj4, @user3, 'leader', '2025-09-01'),
(@proj4, @user10, 'member', '2025-09-01'),
(@proj4, @user11, 'member', '2025-09-01');

-- Tasks
INSERT INTO tasks (project_id, title, description, assignment_mode, due_date, status, priority, progress, max_assignees, created_by) VALUES
(@proj1, 'ออกแบบโปสเตอร์ประชาสัมพันธ์', 'ออกแบบโปสเตอร์เพื่อประชาสัมพันธ์การแข่งขัน', 'direct', '2025-11-10', 'เสร็จสิ้น', 'high', 100, 2, @user2),
(@proj1, 'จัดทำกติกาการแข่งขัน', 'เขียนกติกาและเงื่อนไขการแข่งขันให้ชัดเจน', 'direct', '2025-11-08', 'เสร็จสิ้น', 'high', 100, 1, @user2),
(@proj1, 'ประสานงานกรรมการตัดสิน', 'ติดต่อและประสานงานกับคุณครูกรรมการตัดสิน', 'registration', '2025-11-25', 'กำลังทำ', 'medium', 50, 3, @user2),
(@proj1, 'ตัดต่อวีดีโอตัวอย่าง', 'สร้างวีดีโอตัวอย่างเพื่อประชาสัมพันธ์', 'hybrid', '2025-11-15', 'กำลังทำ', 'high', 60, 2, @user2),
(@proj2, 'จัดเตรียมสถานที่อบรม', 'จองห้องและจัดเตรียมอุปกรณ์', 'direct', '2025-10-25', 'เสร็จสิ้น', 'high', 100, 2, @user3),
(@proj2, 'เตรียมเอกสารประกอบการอบรม', 'จัดทำเอกสารและสไลด์สำหรับอบรม', 'registration', '2025-11-15', 'กำลังทำ', 'medium', 70, 5, @user3),
(@proj3, 'ประสานงานวงดนตรี', 'ติดต่อและจองวงดนตรีสำหรับงาน', 'direct', '2025-12-10', 'รอดำเนินการ', 'high', 0, 2, @user2),
(@proj3, 'จัดทำแผนผังเวที', 'วาดแผนผังและวางแผนการจัดเวที', 'registration', '2025-12-08', 'รอดำเนินการ', 'medium', 0, 3, @user2),
(@proj4, 'ออกแบบ UI/UX', 'ออกแบบหน้าตาเว็บไซต์', 'direct', '2025-09-30', 'เสร็จสิ้น', 'high', 100, 2, @user3),
(@proj4, 'พัฒนา Frontend', 'เขียนโค้ด HTML, CSS, JavaScript', 'direct', '2025-10-31', 'เสร็จสิ้น', 'high', 100, 3, @user3),
(@proj4, 'พัฒนา Backend', 'เขียนโค้ด PHP และฐานข้อมูล', 'direct', '2025-11-15', 'เสร็จสิ้น', 'high', 100, 2, @user3),
(@proj4, 'ทดสอบระบบ', 'ทดสอบการทำงานของเว็บไซต์', 'registration', '2025-11-30', 'เสร็จสิ้น', 'medium', 100, 5, @user3);

-- Get task IDs
SET @task1 = LAST_INSERT_ID();

-- Task Assignments
INSERT INTO task_assignments (task_id, user_id, status) VALUES
(@task1, @user5, 'completed'),
(@task1 + 1, @user4, 'completed'),
(@task1 + 2, @user4, 'working'),
(@task1 + 3, @user5, 'working'),
(@task1 + 4, @user6, 'completed'),
(@task1 + 4, @user8, 'completed'),
(@task1 + 5, @user8, 'working'),
(@task1 + 8, @user10, 'completed'),
(@task1 + 9, @user10, 'completed'),
(@task1 + 9, @user11, 'completed'),
(@task1 + 10, @user11, 'completed'),
(@task1 + 11, @user8, 'completed'),
(@task1 + 11, @user9, 'completed');

-- Task Activity Logs
INSERT INTO task_activity_logs (task_id, user_id, action_type, status_from, status_to, description) VALUES
(@task1, @user2, 'assigned', NULL, 'มอบหมาย', 'มอบหมายงานให้ พิมพ์ชนก'),
(@task1, @user5, 'status_change', 'มอบหมาย', 'กำลังทำ', 'เริ่มทำงาน'),
(@task1, @user5, 'status_change', 'กำลังทำ', 'ตรวจสอบ', 'ส่งงานเพื่อตรวจสอบ'),
(@task1, @user2, 'status_change', 'ตรวจสอบ', 'เสร็จสิ้น', 'อนุมัติและเสร็จสิ้น'),
(@task1 + 1, @user2, 'assigned', NULL, 'มอบหมาย', 'มอบหมายงานให้ ณัฐพงษ์'),
(@task1 + 1, @user4, 'status_change', 'มอบหมาย', 'กำลังทำ', 'เริ่มเขียนกติกา'),
(@task1 + 1, @user4, 'status_change', 'กำลังทำ', 'เสร็จสิ้น', 'เสร็จสิ้น'),
(@task1 + 2, @user4, 'status_change', 'รอดำเนินการ', 'กำลังทำ', 'เริ่มติดต่อคุณครู'),
(@task1 + 3, @user5, 'status_change', 'รอดำเนินการ', 'กำลังทำ', 'เริ่มตัดต่อวีดีโอ'),
(@task1 + 4, @user3, 'assigned', NULL, 'มอบหมาย', 'มอบหมายงานจัดเตรียมสถานที่'),
(@task1 + 4, @user6, 'status_change', 'มอบหมาย', 'เสร็จสิ้น', 'จัดเตรียมเสร็จสิ้น');

-- Get existing category IDs
SET @cat_camera = 4; -- กล้อง
SET @cat_audio = 2; -- เสียง
SET @cat_light = 5; -- ไฟ
SET @cat_computer = 6; -- คอมพิวเตอร์
SET @cat_other = 7; -- อื่นๆ

-- Equipment
INSERT INTO equipment (equipment_code, name, category_id, brand, model, description, quantity, available_quantity, status, location, created_by) VALUES
('EQP-001', 'กล้อง DSLR Canon 850D', @cat_camera, 'Canon', '850D', 'กล้อง DSLR สำหรับถ่ายภาพและวีดีโอ พร้อมเลนส์ 18-55mm', 3, 2, 'available', 'ห้องอุปกรณ์', @admin_id),
('EQP-002', 'กล้อง Mirrorless Sony A6400', @cat_camera, 'Sony', 'A6400', 'กล้อง Mirrorless พร้อมเลนส์ 16-50mm', 2, 1, 'available', 'ห้องอุปกรณ์', @admin_id),
('EQP-003', 'ไมโครโฟน Rode VideoMic Pro', @cat_audio, 'Rode', 'VideoMic Pro', 'ไมโครโฟนติดกล้องแบบมีทิศทาง', 4, 3, 'available', 'ตู้เก็บของ A', @admin_id),
('EQP-004', 'ไมโครโฟนไร้สาย Saramonic', @cat_audio, 'Saramonic', 'Blink 500', 'ชุดไมโครโฟนไร้สายแบบหนีบปก', 2, 2, 'available', 'ตู้เก็บของ A', @admin_id),
('EQP-005', 'ขาตั้งกล้อง Manfrotto', @cat_camera, 'Manfrotto', 'MT055', 'ขาตั้งกล้องสูง 1.6 เมตร', 5, 4, 'available', 'ห้องอุปกรณ์', @admin_id),
('EQP-006', 'ไฟ LED Panel 50W', @cat_light, 'Godox', 'LED500', 'ไฟ LED สำหรับถ่ายภาพ-วีดีโอ', 6, 5, 'available', 'ตู้เก็บของ B', @admin_id),
('EQP-007', 'โดรน DJI Mini 3', @cat_camera, 'DJI', 'Mini 3', 'โดรนสำหรับถ่ายภาพมุมสูง', 1, 0, 'borrowed', 'ตู้นิรภัย', @admin_id),
('EQP-008', 'คอมพิวเตอร์ตัดต่อ iMac', @cat_computer, 'Apple', 'iMac 27"', 'iMac 27 นิ้ว สำหรับตัดต่อวีดีโอ', 2, 1, 'available', 'ห้องตัดต่อ', @admin_id),
('EQP-009', 'หูฟัง Sony WH-1000XM5', @cat_audio, 'Sony', 'WH-1000XM5', 'หูฟังตัดเสียงรบกวนสำหรับงานเสียง', 3, 3, 'available', 'ตู้เก็บของ A', @admin_id),
('EQP-010', 'กระเป๋ากล้อง Lowepro', @cat_other, 'Lowepro', 'ProTactic', 'กระเป๋าสำหรับใส่กล้องและอุปกรณ์', 5, 5, 'available', 'ห้องอุปกรณ์', @admin_id);

-- Get equipment IDs
SET @equip1 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-001');
SET @equip2 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-002');
SET @equip3 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-003');
SET @equip4 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-004');
SET @equip5 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-005');
SET @equip6 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-006');
SET @equip7 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-007');
SET @equip8 = (SELECT id FROM equipment WHERE equipment_code = 'EQP-008');

-- Equipment Borrowing
INSERT INTO equipment_borrowing (borrowing_code, equipment_id, borrower_id, quantity, purpose, project_id, borrow_date, due_date, return_date, status, approved_by, approved_at, returned_condition) VALUES
('BRW-2025-001', @equip1, @user5, 1, 'ถ่ายภาพประชาสัมพันธ์งานแข่งขันวีดีโอ', @proj1, '2025-11-01 09:00:00', '2025-11-10 17:00:00', '2025-11-10 15:00:00', 'returned', @user3, '2025-11-01 08:30:00', 'good'),
('BRW-2025-002', @equip3, @user5, 1, 'บันทึกเสียงสำหรับงานแข่งขัน', @proj1, '2025-11-01 09:00:00', '2025-11-10 17:00:00', '2025-11-10 15:00:00', 'returned', @user3, '2025-11-01 08:30:00', 'good'),
('BRW-2025-003', @equip2, @user9, 1, 'ถ่ายวีดีโอสำหรับโครงการอบรม', @proj2, '2025-11-15 08:00:00', '2025-11-25 17:00:00', NULL, 'borrowed', @user3, '2025-11-15 07:30:00', NULL),
('BRW-2025-004', @equip5, @user9, 1, 'ขาตั้งสำหรับกล้อง', @proj2, '2025-11-15 08:00:00', '2025-11-25 17:00:00', NULL, 'borrowed', @user3, '2025-11-15 07:30:00', NULL),
('BRW-2025-005', @equip8, @user11, 1, 'ตัดต่อวีดีโอโครงการเว็บไซต์', @proj4, '2025-11-20 09:00:00', '2025-12-05 17:00:00', NULL, 'borrowed', @user3, '2025-11-20 08:45:00', NULL),
('BRW-2025-006', @equip1, @user6, 1, 'ถ่ายภาพงานกิจกรรมของชมรม', NULL, '2025-11-25 09:00:00', '2025-11-30 17:00:00', NULL, 'approved', @user3, '2025-11-23 14:00:00', NULL),
('BRW-2025-007', @equip6, @user8, 2, 'ไฟสำหรับถ่ายสตูดิโอ', NULL, '2025-11-26 10:00:00', '2025-11-28 17:00:00', NULL, 'pending', NULL, NULL, NULL),
('BRW-2025-008', @equip7, @user4, 1, 'ถ่ายภาพทางอากาศสำหรับงานประชาสัมพันธ์', @proj1, '2025-11-22 08:00:00', '2025-11-29 17:00:00', NULL, 'borrowed', @user2, '2025-11-22 07:30:00', NULL);

-- Transactions
INSERT INTO transactions (transaction_code, type, category_id, amount, description, transaction_date, project_id, payment_method, reference_number, recorded_by, approved_by, status) VALUES
('TXN-2025-001', 'income', 2, 50000.00, 'งบประมาณประจำปีจากโรงเรียน', '2025-09-01', NULL, 'โอนเงิน', 'REF-001', @admin_id, @admin_id, 'approved'),
('TXN-2025-002', 'expense', 5, 12000.00, 'ซื้ออุปกรณ์ถ่ายภาพใหม่ - กล้อง Canon และอุปกรณ์เสริม', '2025-09-15', NULL, 'โอนเงิน', 'REF-002', @user3, @admin_id, 'approved'),
('TXN-2025-003', 'income', 3, 5000.00, 'เงินบริจาคจากศิษย์เก่า รุ่น 8', '2025-09-20', NULL, 'โอนเงิน', 'REF-003', @admin_id, @admin_id, 'approved'),
('TXN-2025-004', 'expense', 8, 3500.00, 'ค่าวัสดุและเอกสารประกอบการอบรมการถ่ายภาพ', '2025-10-25', @proj2, 'เงินสด', 'REF-004', @user3, @admin_id, 'approved'),
('TXN-2025-005', 'expense', 5, 1500.00, 'ซ่อมกล้อง DSLR ที่ชำรุด', '2025-10-30', NULL, 'เงินสด', 'REF-005', @user3, @admin_id, 'approved'),
('TXN-2025-006', 'income', 4, 8000.00, 'รายได้จากจำหน่ายภาพถ่ายงานกิจกรรม', '2025-11-05', NULL, 'เงินสด', 'REF-006', @user2, @admin_id, 'approved'),
('TXN-2025-007', 'expense', 8, 2000.00, 'ซื้อวัสดุสำนักงาน กระดาษ ปากกา แฟ้ม', '2025-11-10', NULL, 'เงินสด', 'REF-007', @admin_id, @admin_id, 'approved'),
('TXN-2025-008', 'expense', 6, 4500.00, 'ค่าอาหารว่างและเครื่องดื่มโครงการแข่งขันวีดีโอสั้น', '2025-11-15', @proj1, 'เงินสด', 'REF-008', @user2, @admin_id, 'approved'),
('TXN-2025-009', 'income', 3, 10000.00, 'เงินสนับสนุนจากบริษัท Media Tech Co.', '2025-11-18', NULL, 'โอนเงิน', 'REF-009', @admin_id, @admin_id, 'approved'),
('TXN-2025-010', 'expense', 6, 1200.00, 'ค่าขนมและเครื่องดื่มในการประชุมคณะกรรมการ', '2025-11-20', NULL, 'เงินสด', 'REF-010', @admin_id, @admin_id, 'approved'),
('TXN-2025-011', 'expense', 7, 3000.00, 'ค่าเดินทางไปดูงานที่มหาวิทยาลัย', '2025-11-22', NULL, 'โอนเงิน', 'REF-011', @user2, @admin_id, 'approved'),
('TXN-2025-012', 'income', 1, 15000.00, 'ค่าสมาชิกภาคเรียนที่ 2/2568', '2025-11-01', NULL, 'เงินสด', 'REF-012', @admin_id, @admin_id, 'approved'),
('TXN-2025-013', 'expense', 5, 8500.00, 'ซื้อไมโครโฟนไร้สายและอุปกรณ์เสียง', '2025-11-23', @proj3, 'โอนเงิน', 'REF-013', @user2, @admin_id, 'pending');

-- สรุป
SELECT 'สร้างข้อมูลเสร็จสมบูรณ์!' as status;
SELECT 
    (SELECT COUNT(*) FROM users) as users,
    (SELECT COUNT(*) FROM profiles) as profiles,
    (SELECT COUNT(*) FROM projects) as projects,
    (SELECT COUNT(*) FROM tasks) as tasks,
    (SELECT COUNT(*) FROM equipment) as equipment,
    (SELECT COUNT(*) FROM transactions) as transactions;
