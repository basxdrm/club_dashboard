-- Sample Data for Club Management System
-- ข้อมูลจำลองสำหรับระบบจัดการชมรม

USE dashboard_db;

-- ลบข้อมูลเก่า
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE task_registrations;
TRUNCATE TABLE task_assignments;
TRUNCATE TABLE task_activity_logs;
TRUNCATE TABLE tasks;
TRUNCATE TABLE project_members;
TRUNCATE TABLE projects;
TRUNCATE TABLE equipment_borrowing;
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
-- Admin
INSERT INTO users (email, password, role, status, created_at) VALUES
('admin@msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'admin', 1, NOW());

SET @admin_id = LAST_INSERT_ID();

-- Board Members
INSERT INTO users (email, password, role, status, created_at) VALUES
('somchai.p@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'board', 1, NOW()),
('suda.k@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'board', 1, NOW());

-- Regular Members
INSERT INTO users (email, password, role, status, created_at) VALUES
('nattapong.w@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('pimchanok.s@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('kritsada.m@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('chanida.t@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('wuttichai.l@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('napasorn.c@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('thanawat.p@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW()),
('sarawut.n@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, NOW());

-- Profiles
INSERT INTO profiles (user_id, student_id, prefix, first_name_th, last_name_th, nickname_th, gender, date_of_birth) VALUES
(@admin_id, '66001', 'นาย', 'ผู้ดูแล', 'ระบบ', 'แอดมิน', 'male', '2007-01-01'),
(@admin_id + 1, '66101', 'นาย', 'สมชาย', 'ประเสริฐ', 'ชาย', 'male', '2007-03-15'),
(@admin_id + 2, '66102', 'นางสาว', 'สุดา', 'คงสมบูรณ์', 'ดา', 'female', '2007-05-20'),
(@admin_id + 3, '66201', 'นาย', 'ณัฐพงษ์', 'วงศ์สุวรรณ', 'นัท', 'male', '2008-02-10'),
(@admin_id + 4, '66202', 'นางสาว', 'พิมพ์ชนก', 'ศรีสุข', 'พิม', 'female', '2008-04-25'),
(@admin_id + 5, '66203', 'นาย', 'กฤษดา', 'มณีรัตน์', 'กฤษ', 'male', '2008-06-30'),
(@admin_id + 6, '66204', 'นางสาว', 'ชนิดา', 'ธนาวัฒน์', 'นิด', 'female', '2008-08-15'),
(@admin_id + 7, '66301', 'นาย', 'วุฒิชัย', 'ลิ้มสกุล', 'วิน', 'male', '2009-01-20'),
(@admin_id + 8, '66302', 'นางสาว', 'นภาสร', 'จันทร์เพ็ญ', 'นภา', 'female', '2009-03-10'),
(@admin_id + 9, '66303', 'นาย', 'ธนวัฒน์', 'พูลสวัสดิ์', 'ธน', 'male', '2009-05-05'),
(@admin_id + 10, '66304', 'นาย', 'สราวุฒิ', 'นาคสุวรรณ', 'วุฒิ', 'male', '2009-07-12');

-- Member Education
INSERT INTO member_education (user_id, education_level, class_academic, is_current) VALUES
(@admin_id, 'high_school', 'ม.6/1', 1),
(@admin_id + 1, 'high_school', 'ม.6/1', 1),
(@admin_id + 2, 'high_school', 'ม.6/2', 1),
(@admin_id + 3, 'high_school', 'ม.5/1', 1),
(@admin_id + 4, 'high_school', 'ม.5/2', 1),
(@admin_id + 5, 'high_school', 'ม.5/3', 1),
(@admin_id + 6, 'high_school', 'ม.5/4', 1),
(@admin_id + 7, 'high_school', 'ม.4/1', 1),
(@admin_id + 8, 'high_school', 'ม.4/2', 1),
(@admin_id + 9, 'high_school', 'ม.4/3', 1),
(@admin_id + 10, 'high_school', 'ม.4/4', 1);

-- Member Contacts
INSERT INTO member_contacts (user_id, phone_number, line_id, facebook_url, instagram_username) VALUES
(@admin_id, '0812345678', 'admin.msj', NULL, 'admin_msj'),
(@admin_id + 1, '0823456789', 'somchai_p', 'somchai.prasert', 'somchai_p'),
(@admin_id + 2, '0834567890', 'suda_k', 'suda.kong', 'suda.k'),
(@admin_id + 3, '0845678901', 'nattapong_w', NULL, 'nattapong.w'),
(@admin_id + 4, '0856789012', 'pim_s', 'pimchanok.s', 'pimchanok.s'),
(@admin_id + 5, '0867890123', 'kritsada_m', NULL, 'kritsada.m'),
(@admin_id + 6, '0878901234', 'chanida_t', 'chanida.t', 'chanida_t'),
(@admin_id + 7, '0889012345', 'win_l', NULL, 'wuttichai.l'),
(@admin_id + 8, '0890123456', 'napa_c', 'napasorn.c', 'napasorn.c'),
(@admin_id + 9, '0801234567', 'thanawat_p', NULL, 'thanawat.p'),
(@admin_id + 10, '0812345679', 'sarawut_n', 'sarawut.n', 'sarawut_n');

-- Member Club Info
INSERT INTO member_club_info (user_id, department_id, position_id, member_generation, joined_date) VALUES
(@admin_id, NULL, 1, 10, '2023-05-01'),
(@admin_id + 1, 1, 1, 10, '2023-05-15'),
(@admin_id + 2, 2, 2, 10, '2023-05-15'),
(@admin_id + 3, 1, 3, 11, '2024-05-10'),
(@admin_id + 4, 3, 3, 11, '2024-05-10'),
(@admin_id + 5, 2, 4, 11, '2024-05-12'),
(@admin_id + 6, 4, 5, 11, '2024-05-12'),
(@admin_id + 7, 1, 5, 12, '2025-05-08'),
(@admin_id + 8, 3, 5, 12, '2025-05-08'),
(@admin_id + 9, 2, 5, 12, '2025-05-10'),
(@admin_id + 10, 4, 5, 12, '2025-05-10');

-- Projects
INSERT INTO projects (name, description, start_date, end_date, status, budget, created_by) VALUES
('โครงการแข่งขันวีดีโอสั้น', 'จัดการแข่งขันสร้างสรรค์วีดีโอสั้นภายในโรงเรียน', '2025-11-01', '2025-12-15', 'in_progress', 15000.00, @admin_id + 1),
('โครงการอบรมการถ่ายภาพ', 'อบรมเทคนิคการถ่ายภาพเบื้องต้นสำหรับนักเรียน', '2025-10-15', '2025-11-30', 'in_progress', 8000.00, @admin_id + 2),
('งานคอนเสิร์ตปิดเทอม', 'จัดคอนเสิร์ตดนตรีเพื่อปิดภาคเรียน', '2025-12-01', '2025-12-20', 'planning', 25000.00, @admin_id + 1),
('โครงการพัฒนาเว็บไซต์ชมรม', 'สร้างและพัฒนาเว็บไซต์ใหม่ของชมรม', '2025-09-01', '2025-11-30', 'completed', 12000.00, @admin_id + 2);

-- Project Members
INSERT INTO project_members (project_id, user_id, role, joined_date) VALUES
(1, @admin_id + 1, 'leader', '2025-11-01'),
(1, @admin_id + 3, 'member', '2025-11-01'),
(1, @admin_id + 4, 'member', '2025-11-01'),
(2, @admin_id + 2, 'leader', '2025-10-15'),
(2, @admin_id + 5, 'member', '2025-10-15'),
(2, @admin_id + 7, 'member', '2025-10-15'),
(3, @admin_id + 1, 'leader', '2025-12-01'),
(3, @admin_id + 6, 'member', '2025-12-01'),
(3, @admin_id + 8, 'member', '2025-12-01'),
(4, @admin_id + 2, 'leader', '2025-09-01'),
(4, @admin_id + 9, 'member', '2025-09-01'),
(4, @admin_id + 10, 'member', '2025-09-01');

-- Tasks
INSERT INTO tasks (project_id, title, description, assignment_mode, start_date, due_date, status, priority, progress, max_assignees, created_by) VALUES
(1, 'ออกแบบโปสเตอร์ประชาสัมพันธ์', 'ออกแบบโปสเตอร์เพื่อประชาสัมพันธ์การแข่งขัน', 'direct', '2025-11-01', '2025-11-10', 'completed', 'high', 100, 2, @admin_id + 1),
(1, 'จัดทำกติกาการแข่งขัน', 'เขียนกติกาและเงื่อนไขการแข่งขันให้ชัดเจน', 'direct', '2025-11-02', '2025-11-08', 'completed', 'high', 100, 1, @admin_id + 1),
(1, 'ประสานงานกรรมการตัดสิน', 'ติดต่อและประสานงานกับคุณครูกรรมการตัดสิน', 'registration', '2025-11-05', '2025-11-25', 'in_progress', 'medium', 50, 3, @admin_id + 1),
(1, 'ตัดต่อวีดีโอตัวอย่าง', 'สร้างวีดีโอตัวอย่างเพื่อประชาสัมพันธ์', 'hybrid', '2025-11-08', '2025-11-15', 'in_progress', 'high', 60, 2, @admin_id + 1),
(2, 'จัดเตรียมสถานที่อบรม', 'จองห้องและจัดเตรียมอุปกรณ์', 'direct', '2025-10-15', '2025-10-25', 'completed', 'high', 100, 2, @admin_id + 2),
(2, 'เตรียมเอกสารประกอบการอบรม', 'จัดทำเอกสารและสไลด์สำหรับอบรม', 'registration', '2025-10-20', '2025-11-15', 'in_progress', 'medium', 70, 5, @admin_id + 2),
(3, 'ประสานงานวงดนตรี', 'ติดต่อและจองวงดนตรีสำหรับงาน', 'direct', '2025-12-01', '2025-12-10', 'pending', 'high', 0, 2, @admin_id + 1),
(3, 'จัดทำแผนผังเวที', 'วาดแผนผังและวางแผนการจัดเวที', 'registration', '2025-12-01', '2025-12-08', 'pending', 'medium', 0, 3, @admin_id + 1),
(4, 'ออกแบบ UI/UX', 'ออกแบบหน้าตาเว็บไซต์', 'direct', '2025-09-01', '2025-09-30', 'completed', 'high', 100, 2, @admin_id + 2),
(4, 'พัฒนา Frontend', 'เขียนโค้ด HTML, CSS, JavaScript', 'direct', '2025-10-01', '2025-10-31', 'completed', 'high', 100, 3, @admin_id + 2),
(4, 'พัฒนา Backend', 'เขียนโค้ด PHP และฐานข้อมูล', 'direct', '2025-10-15', '2025-11-15', 'completed', 'high', 100, 2, @admin_id + 2),
(4, 'ทดสอบระบบ', 'ทดสอบการทำงานของเว็บไซต์', 'registration', '2025-11-16', '2025-11-30', 'completed', 'medium', 100, 5, @admin_id + 2);

-- Task Assignments
INSERT INTO task_assignments (task_id, user_id, assigned_by, assigned_date, status) VALUES
(1, @admin_id + 4, @admin_id + 1, '2025-11-01', 'completed'),
(2, @admin_id + 3, @admin_id + 1, '2025-11-02', 'completed'),
(3, @admin_id + 3, @admin_id + 1, '2025-11-05', 'working'),
(4, @admin_id + 4, @admin_id + 1, '2025-11-08', 'working'),
(5, @admin_id + 5, @admin_id + 2, '2025-10-15', 'completed'),
(5, @admin_id + 7, @admin_id + 2, '2025-10-15', 'completed'),
(6, @admin_id + 7, @admin_id + 2, '2025-10-20', 'working'),
(9, @admin_id + 9, @admin_id + 2, '2025-09-01', 'completed'),
(10, @admin_id + 9, @admin_id + 2, '2025-10-01', 'completed'),
(10, @admin_id + 10, @admin_id + 2, '2025-10-01', 'completed'),
(11, @admin_id + 10, @admin_id + 2, '2025-10-15', 'completed'),
(12, @admin_id + 7, @admin_id + 2, '2025-11-16', 'completed'),
(12, @admin_id + 8, @admin_id + 2, '2025-11-16', 'completed');

-- Task Registrations
INSERT INTO task_registrations (task_id, user_id, registration_date, status, approved_by, approved_date) VALUES
(3, @admin_id + 6, '2025-11-06 10:30:00', 'approved', @admin_id + 1, '2025-11-06 14:00:00'),
(4, @admin_id + 8, '2025-11-09 09:15:00', 'approved', @admin_id + 1, '2025-11-09 15:30:00'),
(6, @admin_id + 8, '2025-10-21 11:00:00', 'approved', @admin_id + 2, '2025-10-21 16:00:00'),
(6, @admin_id + 9, '2025-10-22 14:30:00', 'pending', NULL, NULL),
(8, @admin_id + 6, '2025-12-02 10:00:00', 'pending', NULL, NULL);

-- Task Activity Logs
INSERT INTO task_activity_logs (task_id, user_id, action_type, status_from, status_to, comment, created_at) VALUES
(1, @admin_id + 1, 'assigned', NULL, 'assigned', 'มอบหมายงานให้ พิมพ์ชนก', '2025-11-01 09:00:00'),
(1, @admin_id + 4, 'status_change', 'assigned', 'working', 'เริ่มทำงาน', '2025-11-01 14:00:00'),
(1, @admin_id + 4, 'status_change', 'working', 'review', 'ส่งงานเพื่อตรวจสอบ', '2025-11-08 16:30:00'),
(1, @admin_id + 1, 'status_change', 'review', 'completed', 'อนุมัติและเสร็จสิ้น', '2025-11-09 10:00:00'),
(2, @admin_id + 1, 'assigned', NULL, 'assigned', 'มอบหมายงานให้ ณัฐพงษ์', '2025-11-02 10:00:00'),
(2, @admin_id + 3, 'status_change', 'assigned', 'working', 'เริ่มเขียนกติกา', '2025-11-02 15:00:00'),
(2, @admin_id + 3, 'status_change', 'working', 'completed', 'เสร็จสิ้น', '2025-11-07 11:00:00'),
(3, @admin_id + 3, 'status_change', 'pending', 'working', 'เริ่มติดต่อคุณครู', '2025-11-05 13:00:00'),
(4, @admin_id + 4, 'status_change', 'pending', 'working', 'เริ่มตัดต่อวีดีโอ', '2025-11-08 10:00:00'),
(5, @admin_id + 2, 'assigned', NULL, 'assigned', 'มอบหมายงานจัดเตรียมสถานที่', '2025-10-15 09:00:00'),
(5, @admin_id + 5, 'status_change', 'assigned', 'completed', 'จัดเตรียมเสร็จสิ้น', '2025-10-24 16:00:00');

-- Equipment
INSERT INTO equipment (name, category, description, quantity, available_quantity, condition_status) VALUES
('กล้อง DSLR Canon 850D', 'camera', 'กล้อง DSLR สำหรับถ่ายภาพและวีดีโอ พร้อมเลนส์ 18-55mm', 3, 2, 'good'),
('กล้อง Mirrorless Sony A6400', 'camera', 'กล้อง Mirrorless พร้อมเลนส์ 16-50mm', 2, 1, 'good'),
('ไมโครโฟน Rode VideoMic Pro', 'audio', 'ไมโครโฟนติดกล้องแบบมีทิศทาง', 4, 3, 'good'),
('ไมโครโฟนไร้สาย Saramonic', 'audio', 'ชุดไมโครโฟนไร้สายแบบหนีบปก', 2, 2, 'good'),
('ขาตั้งกล้อง Manfrotto', 'support', 'ขาตั้งกล้องสูง 1.6 เมตร', 5, 4, 'good'),
('ไฟ LED Panel 50W', 'lighting', 'ไฟ LED สำหรับถ่ายภาพ-วีดีโอ', 6, 5, 'good'),
('โดรน DJI Mini 3', 'camera', 'โดรนสำหรับถ่ายภาพมุมสูง', 1, 1, 'good'),
('คอมพิวเตอร์ตัดต่อ iMac', 'computer', 'iMac 27 นิ้ว สำหรับตัดต่อวีดีโอ', 2, 1, 'good'),
('หูฟัง Sony WH-1000XM5', 'audio', 'หูฟังตัดเสียงรบกวนสำหรับงานเสียง', 3, 3, 'good'),
('กระเป๋ากล้อง Lowepro', 'accessory', 'กระเป๋าสำหรับใส่กล้องและอุปกรณ์', 5, 5, 'good');

-- Equipment Borrowing
INSERT INTO equipment_borrowing (equipment_id, user_id, borrow_date, return_date, purpose, status, approved_by, approved_date, actual_return_date) VALUES
(1, @admin_id + 4, '2025-11-01', '2025-11-10', 'ถ่ายภาพประชาสัมพันธ์งานแข่งขันวีดีโอ', 'returned', @admin_id + 2, '2025-11-01 09:00:00', '2025-11-10 15:00:00'),
(3, @admin_id + 4, '2025-11-01', '2025-11-10', 'ใช้งานร่วมกับกล้อง', 'returned', @admin_id + 2, '2025-11-01 09:00:00', '2025-11-10 15:00:00'),
(2, @admin_id + 8, '2025-11-15', '2025-11-25', 'ถ่ายวีดีโอสำหรับโครงการอบรม', 'borrowed', @admin_id + 2, '2025-11-15 08:30:00', NULL),
(5, @admin_id + 8, '2025-11-15', '2025-11-25', 'ใช้งานร่วมกับกล้อง', 'borrowed', @admin_id + 2, '2025-11-15 08:30:00', NULL),
(8, @admin_id + 10, '2025-11-20', '2025-12-05', 'ตัดต่อวีดีโอโครงการเว็บไซต์', 'borrowed', @admin_id + 2, '2025-11-20 10:00:00', NULL),
(1, @admin_id + 5, '2025-11-23', '2025-11-30', 'ถ่ายภาพงานกิจกรรม', 'approved', @admin_id + 2, '2025-11-23 14:00:00', NULL),
(6, @admin_id + 7, '2025-11-24', '2025-11-28', 'ใช้ไฟสำหรับถ่ายสตูดิโอ', 'pending', NULL, NULL, NULL);

-- Transactions
INSERT INTO transactions (type, category, amount, description, transaction_date, reference_number, receipt_file, recorded_by) VALUES
('income', 'budget', 50000.00, 'งบประมาณประจำปีจากโรงเรียน', '2025-09-01', 'INC-2025-001', NULL, @admin_id),
('expense', 'equipment', 12000.00, 'ซื้ออุปกรณ์ถ่ายภาพใหม่', '2025-09-15', 'EXP-2025-001', NULL, @admin_id + 2),
('income', 'donation', 5000.00, 'เงินบริจาคจากศิษย์เก่า', '2025-09-20', 'INC-2025-002', NULL, @admin_id),
('expense', 'activity', 3500.00, 'ค่าใช้จ่ายงานอบรมการถ่ายภาพ', '2025-10-25', 'EXP-2025-002', NULL, @admin_id + 2),
('expense', 'maintenance', 1500.00, 'ซ่อมกล้องที่ชำรุด', '2025-10-30', 'EXP-2025-003', NULL, @admin_id + 2),
('income', 'activity', 8000.00, 'รายได้จากจำหน่ายภาพถ่าย', '2025-11-05', 'INC-2025-003', NULL, @admin_id + 1),
('expense', 'office', 2000.00, 'ซื้อวัสดุสำนักงาน', '2025-11-10', 'EXP-2025-004', NULL, @admin_id),
('expense', 'activity', 4500.00, 'ค่าใช้จ่ายโครงการแข่งขันวีดีโอสั้น', '2025-11-15', 'EXP-2025-005', NULL, @admin_id + 1),
('income', 'sponsorship', 10000.00, 'เงินสนับสนุนจากบริษัทเอกชน', '2025-11-18', 'INC-2025-004', NULL, @admin_id),
('expense', 'other', 1200.00, 'ค่าขนมและเครื่องดื่มในการประชุม', '2025-11-20', 'EXP-2025-006', NULL, @admin_id);

-- สรุปข้อมูล
SELECT '=== สรุปข้อมูลที่สร้าง ===' as summary;
SELECT 'Users' as table_name, COUNT(*) as total FROM users
UNION ALL
SELECT 'Projects', COUNT(*) FROM projects
UNION ALL
SELECT 'Tasks', COUNT(*) FROM tasks
UNION ALL
SELECT 'Equipment', COUNT(*) FROM equipment
UNION ALL
SELECT 'Transactions', COUNT(*) FROM transactions;

SELECT CONCAT('✓ สร้างข้อมูลจำลองเสร็จสมบูรณ์!') as status;
SELECT CONCAT('✓ ใช้ admin@msj.ac.th / password123 เพื่อ Login') as login_info;
