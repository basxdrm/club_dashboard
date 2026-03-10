USE dashboard_db;
INSERT INTO tasks (project_id, task_code, title, description, status, priority, assignment_mode, assigned_to, due_date, progress, estimated_hours, created_by) VALUES 
(1, 'T001', 'ออกแบบโปสเตอร์', 'ออกแบบโปสเตอร์ประชาสัมพันธ์งาน', 'กำลังดำเนินการ', 'high', 'direct', 6, '2024-11-30', 60, 8.00, 2),
(1, 'T002', 'จัดทำ Rundown', 'วางแผนและจัดทำ Rundown', 'เสร็จแล้ว', 'high', 'direct', 5, '2024-11-25', 100, 4.00, 2),
(1, 'T003', 'เตรียมอุปกรณ์', 'ตรวจสอบและเตรียมอุปกรณ์', 'รอดำเนินการ', 'medium', 'direct', 4, '2024-12-10', 0, 6.00, 2),
(3, 'T005', 'พัฒนาระบบ Login', 'พัฒนาระบบ Authentication', 'เสร็จแล้ว', 'urgent', 'direct', 4, '2024-10-15', 100, 20.00, 3),
(3, 'T006', 'ออกแบบ UI/UX', 'ออกแบบหน้าตาระบบ', 'กำลังดำเนินการ', 'high', 'direct', 6, '2024-11-20', 75, 15.00, 3);
INSERT INTO transactions (type, amount, category_id, description, transaction_date, project_id, created_by) VALUES 
('expense', 12000.00, 1, 'ซื้อไมโครโฟนไร้สาย', '2024-02-20', NULL, 1),
('expense', 8500.00, 2, 'ค่าอาหารว่าง Workshop', '2024-10-15', 2, 4),
('income', 30000.00, 4, 'เงินสนับสนุนจากโรงเรียน', '2024-11-01', 1, 2),
('expense', 15000.00, 1, 'ซื้อวัสดุตกแต่งบูธ', '2024-11-10', 1, 2),
('expense', 3500.00, 3, 'ค่าวัสดุสิ้นเปลือง', '2024-11-15', 1, 5);