USE dashboard_db;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE activity_logs;
TRUNCATE TABLE task_attachments;
TRUNCATE TABLE task_assignments;
TRUNCATE TABLE task_activity_logs;
TRUNCATE TABLE tasks;
TRUNCATE TABLE project_members;
TRUNCATE TABLE projects;
TRUNCATE TABLE equipment_borrowing;
TRUNCATE TABLE equipment;
TRUNCATE TABLE equipment_categories;
TRUNCATE TABLE transactions;
TRUNCATE TABLE transaction_categories;
TRUNCATE TABLE member_club_info;
TRUNCATE TABLE member_contacts;
TRUNCATE TABLE member_education;
TRUNCATE TABLE profiles;
TRUNCATE TABLE login_attempts;
TRUNCATE TABLE user_sessions;
TRUNCATE TABLE users;
TRUNCATE TABLE club_positions;
TRUNCATE TABLE club_departments;
SET FOREIGN_KEY_CHECKS = 1;

-- ฝ่ายต่างๆ
INSERT INTO club_departments (name, description, icon, color, is_active) VALUES
('ฝ่ายประชาสัมพันธ์', 'รับผิดชอบการประชาสัมพันธ์และการสื่อสารภายในภายนอก', 'megaphone', '#3B82F6', 1),
('ฝ่ายเทคนิค', 'รับผิดชอบด้านเทคนิคและอุปกรณ์', 'wrench', '#10B981', 1),
('ฝ่ายสร้างสรรค์', 'รับผิดชอบการออกแบบและสร้างสรรค์ผลงาน', 'palette', '#F59E0B', 1),
('ฝ่ายอำนวยการ', 'รับผิดชอบงานธุรการและการประสานงาน', 'clipboard', '#8B5CF6', 1);

-- ตำแหน่งต่างๆ
INSERT INTO club_positions (name, level, description, is_active) VALUES
('ประธานชมรม', 5, 'ผู้นำสูงสุดของชมรม', 1),
('รองประธาน', 4, 'ผู้ช่วยประธาน', 1),
('หัวหน้าฝ่าย', 3, 'ผู้นำฝ่าย', 1),
('รองหัวหน้าฝ่าย', 2, 'ผู้ช่วยหัวหน้าฝ่าย', 1),
('สมาชิก', 1, 'สมาชิกทั่วไป', 1);

-- Users (รหัสผ่าน: password123)
INSERT INTO users (email, password, role, status, email_verified) VALUES
('admin@msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'admin', 1, 1),
('president@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'board', 1, 1),
('vicepresident@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'board', 1, 1),
('somchai.p@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('suda.k@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('anan.w@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('manee.s@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('praew.n@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('nattawut.j@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('ploy.t@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1);

-- Profiles
INSERT INTO profiles (user_id, student_id, prefix, first_name_th, last_name_th, nickname_th, first_name_en, last_name_en, birth_date, bio, is_active) VALUES
(1, '68001', 'นาย', 'ผู้ดูแล', 'ระบบ', 'แอดมิน', 'Admin', 'System', '2007-01-01', 'ผู้ดูแลระบบ', 1),
(2, '68002', 'นาย', 'สมชาย', 'ใจดี', 'ชาย', 'Somchai', 'Jaidee', '2007-05-15', 'ประธานชมรมคอมพิวเตอร์', 1),
(3, '68003', 'นางสาว', 'สุดา', 'ขยัน', 'ดา', 'Suda', 'Kayan', '2007-08-20', 'รองประธานชมรม', 1),
(4, '69004', 'นาย', 'อนันต์', 'วิทยา', 'นนท์', 'Anan', 'Wittaya', '2008-03-10', 'หัวหน้าฝ่ายเทคนิค', 1),
(5, '69005', 'นางสาว', 'มณี', 'สวยงาม', 'นี', 'Manee', 'Suayngam', '2008-06-25', 'หัวหน้าฝ่ายประชาสัมพันธ์', 1),
(6, '70006', 'นางสาว', 'แพรว', 'นภา', 'แพรว', 'Praew', 'Napa', '2009-11-12', 'สมาชิกฝ่ายสร้างสรรค์', 1),
(7, '70007', 'นาย', 'ณัฐวุฒิ', 'จริงใจ', 'วุฒิ', 'Nattawut', 'Chingchai', '2009-02-28', 'สมาชิกฝ่ายเทคนิค', 1),
(8, '71008', 'นางสาว', 'พลอย', 'ทอง', 'พลอย', 'Ploy', 'Thong', '2010-07-05', 'สมาชิกใหม่', 1),
(9, '71009', 'นาย', 'ธนกฤต', 'เก่ง', 'ธน', 'Thanakrit', 'Keng', '2010-09-18', 'สมาชิกใหม่', 1),
(10, '71010', 'นางสาว', 'รัตนา', 'มีสุข', 'ตา', 'Rattana', 'Meesuk', '2010-04-22', 'สมาชิกใหม่', 1);

-- Member Education
INSERT INTO member_education (user_id, class_academic, class_agama, education_level, academic_year, semester, is_current) VALUES
(1, 'ม.6/1', 'อ.6', 'ม.6', '2568', 1, 1),
(2, 'ม.6/2', 'อ.6', 'ม.6', '2568', 1, 1),
(3, 'ม.6/3', 'อ.6', 'ม.6', '2568', 1, 1),
(4, 'ม.5/1', 'อ.5', 'ม.5', '2568', 1, 1),
(5, 'ม.5/2', 'อ.5', 'ม.5', '2568', 1, 1),
(6, 'ม.4/1', 'อ.4', 'ม.4', '2568', 1, 1),
(7, 'ม.4/2', 'อ.4', 'ม.4', '2568', 1, 1),
(8, 'ม.3/1', 'อ.3', 'ม.3', '2568', 1, 1),
(9, 'ม.3/2', 'อ.3', 'ม.3', '2568', 1, 1),
(10, 'ม.3/3', 'อ.3', 'ม.3', '2568', 1, 1);

-- Member Contacts
INSERT INTO member_contacts (user_id, phone_number, line_id, facebook, instagram) VALUES
(1, '0801234567', 'admin.msj', 'admin.msj', 'admin.msj'),
(2, '0812345678', 'somchai_p', 'somchai.jaidee', 'somchai_p'),
(3, '0823456789', 'suda_k', 'suda.kayan', 'suda_k'),
(4, '0834567890', 'anan_w', 'anan.wittaya', 'anan_w'),
(5, '0845678901', 'manee_s', 'manee.suayngam', 'manee_s'),
(6, '0856789012', 'praew_n', 'praew.napa', 'praew_n'),
(7, '0867890123', 'nattawut_j', 'nattawut.chingchai', 'nattawut_j'),
(8, '0878901234', 'ploy_t', 'ploy.thong', 'ploy_t'),
(9, '0889012345', 'thanakrit', 'thanakrit.keng', 'thanakrit'),
(10, '0890123456', 'rattana_m', 'rattana.meesuk', 'rattana_m');

-- Member Club Info
INSERT INTO member_club_info (user_id, member_generation, joined_date, department_id, position_id) VALUES
(1, 25, '2024-06-01', 4, 5),
(2, 25, '2024-06-01', 1, 1),
(3, 25, '2024-06-01', 2, 2),
(4, 26, '2024-06-15', 2, 3),
(5, 26, '2024-06-15', 1, 3),
(6, 27, '2024-07-01', 3, 5),
(7, 27, '2024-07-01', 2, 5),
(8, 28, '2024-08-01', 1, 5),
(9, 28, '2024-08-01', 3, 5),
(10, 28, '2024-08-01', 4, 5);

-- Equipment Categories
INSERT INTO equipment_categories (name, description, icon) VALUES
('คอมพิวเตอร์', 'เครื่องคอมพิวเตอร์และแล็ปท็อป', 'laptop'),
('กล้อง', 'กล้องถ่ายรูปและอุปกรณ์ถ่ายภาพ', 'camera'),
('เสียง', 'ไมโครโฟนและอุปกรณ์เสียง', 'microphone'),
('อุปกรณ์เครือข่าย', 'Router, Switch และอุปกรณ์เครือข่าย', 'wifi'),
('อื่นๆ', 'อุปกรณ์อื่นๆ', 'box');

-- Equipment
INSERT INTO equipment (equipment_code, name, category_id, brand, model, description, quantity, available_quantity, status, purchase_date, purchase_price, location, created_by) VALUES
('EQ001', 'กล้อง Canon EOS 90D', 2, 'Canon', 'EOS 90D', 'กล้อง DSLR สำหรับถ่ายภาพกิจกรรม', 1, 1, 'available', '2024-01-15', 45000.00, 'ห้องชมรม ตู้ A1', 1),
('EQ002', 'ไมโครโฟนไร้สาย Shure', 3, 'Shure', 'SM58', 'ไมโครโฟนสำหรับงานพูด', 2, 2, 'available', '2024-02-20', 8500.00, 'ห้องชมรม ตู้ B2', 1),
('EQ003', 'แล็ปท็อป Dell XPS 15', 1, 'Dell', 'XPS 15', 'แล็ปท็อปสำหรับตัดต่อวิดีโอ', 1, 0, 'borrowed', '2023-11-10', 55000.00, 'ห้องชมรม โต๊ะทำงาน', 1),
('EQ004', 'ขาตั้งกล้อง Manfrotto', 2, 'Manfrotto', 'MT055XPRO3', 'ขาตั้งกล้องระดับมืออาชีพ', 2, 2, 'available', '2024-01-15', 12000.00, 'ห้องชมรม ตู้ A2', 1),
('EQ005', 'Router TP-Link Archer', 4, 'TP-Link', 'Archer AX73', 'Router WiFi 6 สำหรับงานอีเวนต์', 1, 1, 'available', '2024-03-01', 4500.00, 'ห้องชมรม ชั้นวางอุปกรณ์', 1);

-- Equipment Borrowing
INSERT INTO equipment_borrowing (borrowing_code, equipment_id, borrower_id, quantity, purpose, borrow_date, due_date, status, approved_by, approved_at) VALUES
('BR001', 3, 4, 1, 'ตัดต่อวิดีโอโครงการ Tech Day', '2024-11-20 09:00:00', '2024-12-05 17:00:00', 'borrowed', 2, '2024-11-20 08:30:00'),
('BR002', 1, 5, 1, 'ถ่ายภาพกิจกรรม Open House', '2024-11-15 08:00:00', '2024-11-15 18:00:00', 'returned', 2, '2024-11-15 07:45:00');

UPDATE equipment_borrowing SET return_date = '2024-11-15 17:30:00', returned_condition = 'good' WHERE borrowing_code = 'BR002';

-- Transaction Categories
INSERT INTO transaction_categories (name, type, description, icon, color) VALUES
('ค่าอุปกรณ์', 'expense', 'ค่าใช้จ่ายในการซื้ออุปกรณ์', 'shopping-cart', '#EF4444'),
('ค่าอาหาร', 'expense', 'ค่าอาหารและเครื่องดื่มในงาน', 'utensils', '#F97316'),
('ค่าวัสดุ', 'expense', 'ค่าวัสดุสิ้นเปลือง', 'package', '#F59E0B'),
('เงินสนับสนุน', 'income', 'เงินสนับสนุนจากโรงเรียน', 'dollar-sign', '#10B981'),
('ค่าลงทะเบียน', 'income', 'ค่าลงทะเบียนกิจกรรม', 'ticket', '#3B82F6');

-- Projects
INSERT INTO projects (project_code, name, description, category, status, priority, start_date, end_date, budget, actual_cost, project_manager_id, department_id, created_by) VALUES
('PRJ2024-001', 'Tech Day 2024', 'งานแสดงผลงานและนำเสนอเทคโนโลยีประจำปี', 'งานใหญ่', 'in_progress', 'high', '2024-11-01', '2024-12-15', 50000.00, 28500.00, 2, 1, 1),
('PRJ2024-002', 'Workshop Python', 'อบรม Python Programming สำหรับน้องม.ต้น', 'อบรม', 'completed', 'medium', '2024-10-01', '2024-10-31', 5000.00, 4200.00, 4, 2, 1),
('PRJ2024-003', 'ระบบจัดการชมรม', 'พัฒนาระบบจัดการสมาชิกและกิจกรรม', 'โครงการพัฒนา', 'in_progress', 'urgent', '2024-09-01', '2024-12-31', 15000.00, 8900.00, 3, 2, 1);

-- Project Members
INSERT INTO project_members (project_id, user_id, role, responsibility) VALUES
(1, 2, 'Project Manager', 'ดูแลโครงการโดยรวม'),
(1, 4, 'Technical Lead', 'ดูแลด้านเทคนิค'),
(1, 5, 'PR Lead', 'ประชาสัมพันธ์'),
(1, 6, 'Designer', 'ออกแบบกราฟิก'),
(2, 4, 'Instructor', 'วิทยากรสอน'),
(2, 7, 'Assistant', 'ผู้ช่วยวิทยากร'),
(3, 3, 'Project Manager', 'ดูแลโครงการ'),
(3, 4, 'Developer', 'พัฒนาระบบ'),
(3, 7, 'Tester', 'ทดสอบระบบ');

-- Tasks
INSERT INTO tasks (project_id, task_code, title, description, status, priority, assignment_mode, assigned_to, due_date, progress, estimated_hours, created_by) VALUES
(1, 'T001', 'ออกแบบโปสเตอร์', 'ออกแบบโปสเตอร์ประชาสัมพันธ์งาน Tech Day', 'กำลังดำเนินการ', 'high', 'direct', 6, '2024-11-30', 60, 8.00, 2),
(1, 'T002', 'จัดทำ Rundown', 'วางแผนและจัดทำ Rundown งาน', 'เสร็จแล้ว', 'high', 'direct', 5, '2024-11-25', 100, 4.00, 2),
(1, 'T003', 'เตรียมอุปกรณ์', 'ตรวจสอบและเตรียมอุปกรณ์ที่ใช้ในงาน', 'รอดำเนินการ', 'medium', 'direct', 4, '2024-12-10', 0, 6.00, 2),
(2, 'T004', 'เตรียมเอกสารประกอบ', 'จัดทำเอกสาร Handout สำหรับผู้เข้าร่วม', 'เสร็จแล้ว', 'medium', 'direct', 7, '2024-09-25', 100, 5.00, 4),
(3, 'T005', 'พัฒนาระบบ Login', 'พัฒนาระบบ Authentication', 'เสร็จแล้ว', 'urgent', 'direct', 4, '2024-10-15', 100, 20.00, 3),
(3, 'T006', 'ออกแบบ UI/UX', 'ออกแบบหน้าตาระบบ', 'กำลังดำเนินการ', 'high', 'direct', 6, '2024-11-20', 75, 15.00, 3);

UPDATE tasks SET completed_date = NOW() WHERE status = 'เสร็จแล้ว';

-- Task Assignments
INSERT INTO task_assignments (task_id, user_id, role, assigned_at) VALUES
(1, 6, 'Designer', '2024-11-15 10:00:00'),
(2, 5, 'Coordinator', '2024-11-10 14:00:00'),
(3, 4, 'Technical', '2024-11-20 09:00:00'),
(4, 7, 'Content Creator', '2024-09-20 11:00:00'),
(5, 4, 'Developer', '2024-09-15 08:00:00'),
(6, 6, 'Designer', '2024-10-25 13:00:00');

-- Transactions
INSERT INTO transactions (type, amount, category_id, description, transaction_date, project_id, created_by) VALUES
('expense', 12000.00, 1, 'ซื้อไมโครโฟนไร้สาย 2 ตัว', '2024-02-20', NULL, 1),
('expense', 8500.00, 2, 'ค่าอาหารว่างสำหรับ Workshop Python', '2024-10-15', 2, 4),
('income', 30000.00, 4, 'เงินสนับสนุนจากโรงเรียนสำหรับ Tech Day', '2024-11-01', 1, 2),
('expense', 15000.00, 1, 'ซื้อวัสดุตกแต่งบูธ', '2024-11-10', 1, 2),
('expense', 3500.00, 3, 'ค่าวัสดุสิ้นเปลืองในการทำงาน', '2024-11-15', 1, 5);

-- Activity Logs
INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES
(1, 'login', 'users', 1, '127.0.0.1', 'Mozilla/5.0'),
(2, 'create_project', 'projects', 1, '127.0.0.1', 'Mozilla/5.0'),
(2, 'create_task', 'tasks', 1, '127.0.0.1', 'Mozilla/5.0'),
(4, 'update_task', 'tasks', 5, '127.0.0.1', 'Mozilla/5.0'),
(5, 'create_transaction', 'transactions', 3, '127.0.0.1', 'Mozilla/5.0');

SELECT '✅ สำเร็จ! ใส่ข้อมูลตัวอย่างทุกตารางเรียบร้อยแล้ว' as status;
SELECT 'Users' as table_name, COUNT(*) as count FROM users
UNION ALL SELECT 'Profiles', COUNT(*) FROM profiles
UNION ALL SELECT 'Projects', COUNT(*) FROM projects
UNION ALL SELECT 'Tasks', COUNT(*) FROM tasks
UNION ALL SELECT 'Equipment', COUNT(*) FROM equipment
UNION ALL SELECT 'Transactions', COUNT(*) FROM transactions;
