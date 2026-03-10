USE dashboard_db;

-- เพิ่ม Users และ Profiles
INSERT INTO users (email, password, role, status, email_verified) VALUES
('mark.s@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('may.k@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('bank.p@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('ice.w@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1),
('top.n@student.msj.ac.th', '$argon2id$v=19$m=65536,t=4,p=1$RHVtbXlTYWx0MTIzNDU2Nzg$xKjF4Z8g9YvR3LpN2QwM1TcA5BvD6EuF7GxH8IyJ9Kz', 'member', 1, 1);

INSERT INTO profiles (user_id, student_id, prefix, first_name_th, last_name_th, nickname_th, first_name_en, last_name_en, birth_date, bio, is_active) VALUES
(11, '72011', 'นาย', 'มาร์ค', 'สุขใจ', 'มาร์ค', 'Mark', 'Sukchai', '2011-01-15', 'ชอบเขียนโปรแกรม', 1),
(12, '72012', 'นางสาว', 'เมย์', 'คิมหันต์', 'เมย์', 'May', 'Kimhan', '2011-03-20', 'ชอบออกแบบกราฟิก', 1),
(13, '72013', 'นาย', 'แบงค์', 'พิมพ์ชนก', 'แบงค์', 'Bank', 'Pimchanok', '2011-05-08', 'ชอบถ่ายรูป', 1),
(14, '72014', 'นางสาว', 'ไอซ์', 'วรรณา', 'ไอซ์', 'Ice', 'Wanna', '2011-07-12', 'ชอบตัดต่อวิดีโอ', 1),
(15, '72015', 'นาย', 'ท็อป', 'นภดล', 'ท็อป', 'Top', 'Napadon', '2011-09-25', 'ชอบทำเว็บไซต์', 1);

INSERT INTO member_education (user_id, class_academic, class_agama, education_level, academic_year, semester, is_current) VALUES
(11, 'ม.2/1', 'อ.2', 'ม.2', '2568', 1, 1),
(12, 'ม.2/2', 'อ.2', 'ม.2', '2568', 1, 1),
(13, 'ม.2/3', 'อ.2', 'ม.2', '2568', 1, 1),
(14, 'ม.2/1', 'อ.2', 'ม.2', '2568', 1, 1),
(15, 'ม.2/2', 'อ.2', 'ม.2', '2568', 1, 1);

INSERT INTO member_contacts (user_id, phone_number, line_id, facebook, instagram) VALUES
(11, '0901234567', 'mark_s', 'mark.sukchai', 'mark_s'),
(12, '0912345678', 'may_k', 'may.kimhan', 'may_k'),
(13, '0923456789', 'bank_p', 'bank.pimchanok', 'bank_p'),
(14, '0934567890', 'ice_w', 'ice.wanna', 'ice_w'),
(15, '0945678901', 'top_n', 'top.napadon', 'top_n');

INSERT INTO member_club_info (user_id, member_generation, joined_date, department_id, position_id) VALUES
(11, 29, '2024-09-01', 2, 5),
(12, 29, '2024-09-01', 3, 5),
(13, 29, '2024-09-01', 1, 5),
(14, 29, '2024-09-01', 3, 5),
(15, 29, '2024-09-01', 2, 5);

-- เพิ่ม Projects
INSERT INTO projects (project_code, name, description, category, status, priority, start_date, end_date, budget, actual_cost, project_manager_id, department_id, created_by) VALUES
('PRJ2024-004', 'Code Camp 2024', 'ค่ายเขียนโปรแกรมสำหรับน้องม.ปลาย', 'ค่าย', 'planning', 'medium', '2024-12-01', '2024-12-15', 20000.00, 0.00, 4, 2, 1),
('PRJ2024-005', 'Photo Contest', 'ประกวดภาพถ่ายธีม Technology & Life', 'กิจกรรม', 'in_progress', 'low', '2024-11-15', '2024-12-30', 8000.00, 3200.00, 5, 1, 1),
('PRJ2024-006', 'Website โรงเรียน', 'พัฒนาเว็บไซต์หลักของโรงเรียน', 'โครงการพัฒนา', 'planning', 'high', '2024-12-01', '2025-03-31', 30000.00, 0.00, 3, 2, 1);

-- เพิ่ม Project Members
INSERT INTO project_members (project_id, user_id, role, responsibility) VALUES
(4, 4, 'Project Manager', 'ดูแลโครงการโดยรวม'),
(4, 7, 'Instructor', 'วิทยากรสอน'),
(4, 11, 'Assistant', 'ผู้ช่วยงาน'),
(5, 5, 'Project Manager', 'ดูแลโครงการ'),
(5, 13, 'Photographer', 'ถ่ายภาพประกวด'),
(6, 3, 'Project Manager', 'ดูแลโครงการ'),
(6, 15, 'Developer', 'พัฒนาเว็บไซต์'),
(6, 12, 'Designer', 'ออกแบบ UI/UX');

-- เพิ่ม Equipment
INSERT INTO equipment (equipment_code, name, category_id, brand, model, description, quantity, available_quantity, status, purchase_date, purchase_price, location, created_by) VALUES
('EQ006', 'กล้อง Sony A7 III', 2, 'Sony', 'A7 III', 'กล้อง Mirrorless ฟูลเฟรม', 1, 1, 'available', '2024-03-10', 65000.00, 'ห้องชมรม ตู้ A1', 1),
('EQ007', 'โดรน DJI Mini 3', 2, 'DJI', 'Mini 3', 'โดรนสำหรับถ่ายภาพมุมสูง', 1, 1, 'available', '2024-04-15', 28000.00, 'ห้องชมรม ตู้ A3', 1),
('EQ008', 'MacBook Pro 14"', 1, 'Apple', 'MacBook Pro 14', 'แล็ปท็อปสำหรับงานออกแบบ', 1, 1, 'available', '2024-05-20', 78000.00, 'ห้องชมรม โต๊ะทำงาน', 1),
('EQ009', 'iPad Pro 12.9"', 1, 'Apple', 'iPad Pro', 'แท็บเล็ตสำหรับงานออกแบบ', 2, 2, 'available', '2024-06-10', 45000.00, 'ห้องชมรม ชั้นวาง', 1),
('EQ010', 'ชุดไฟสตูดิโอ', 2, 'Godox', 'SL-60W', 'ไฟสำหรับถ่ายภาพสตูดิโอ', 2, 2, 'available', '2024-07-05', 15000.00, 'ห้องชมรม ตู้ B1', 1);

-- เพิ่ม Equipment Borrowing
INSERT INTO equipment_borrowing (borrowing_code, equipment_id, borrower_id, quantity, purpose, project_id, borrow_date, due_date, status, approved_by, approved_at) VALUES
('BR003', 6, 13, 1, 'ถ่ายภาพกิจกรรม Photo Contest', 5, '2024-11-25 10:00:00', '2024-11-25 18:00:00', 'approved', 2, '2024-11-25 09:00:00'),
('BR004', 7, 13, 1, 'ถ่ายภาพมุมสูงงาน Open House', NULL, '2024-12-01 08:00:00', '2024-12-01 16:00:00', 'pending', NULL, NULL);

-- เพิ่ม Transactions
INSERT INTO transactions (type, amount, category_id, description, transaction_date, project_id, created_by) VALUES
('expense', 5000.00, 3, 'ซื้อวัสดุประกอบค่าย Code Camp', '2024-11-20', 4, 4),
('income', 10000.00, 4, 'เงินสนับสนุนจากผู้ปกครอง', '2024-11-22', 4, 2),
('expense', 3200.00, 1, 'ซื้อรางวัลการประกวดภาพถ่าย', '2024-11-18', 5, 5),
('expense', 2500.00, 2, 'ค่าอาหารว่างประชุมทีม', '2024-11-25', 6, 3),
('income', 50000.00, 4, 'งบจากโรงเรียนสำหรับเว็บไซต์', '2024-11-28', 6, 1);

-- เพิ่ม Activity Logs
INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES
(2, 'create_project', 'projects', 4, '127.0.0.1', 'Mozilla/5.0'),
(5, 'create_project', 'projects', 5, '127.0.0.1', 'Mozilla/5.0'),
(3, 'create_project', 'projects', 6, '127.0.0.1', 'Mozilla/5.0'),
(4, 'create_transaction', 'transactions', 6, '127.0.0.1', 'Mozilla/5.0'),
(13, 'create_borrowing', 'equipment_borrowing', 3, '127.0.0.1', 'Mozilla/5.0');

SELECT '✅ เพิ่มข้อมูลเรียบร้อยแล้ว' as status;