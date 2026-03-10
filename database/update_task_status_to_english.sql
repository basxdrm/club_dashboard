-- Update task status from Thai to English
-- Execute this file to convert existing task status values

USE dashboard_db;

-- Update task status
UPDATE tasks 
SET status = CASE 
    WHEN status = 'รอดำเนินการ' THEN 'pending'
    WHEN status = 'กำลังดำเนินการ' THEN 'in_progress' 
    WHEN status = 'เสร็จสิ้น' THEN 'completed'
    WHEN status = 'ยกเลิก' THEN 'cancelled'
    WHEN status = 'รออนุมัติ' THEN 'pending_approval'
    ELSE status -- Keep existing English values unchanged
END 
WHERE status IN ('รอดำเนินการ', 'กำลังดำเนินการ', 'เสร็จสิ้น', 'ยกเลิก', 'รออนุมัติ');

-- Update task_assignments status if needed
UPDATE task_assignments 
SET status = CASE 
    WHEN status = 'รอดำเนินการ' THEN 'pending'
    WHEN status = 'อนุมัติ' THEN 'approved'
    WHEN status = 'ปฏิเสธ' THEN 'rejected'
    WHEN status = 'ยกเลิก' THEN 'cancelled'
    ELSE status
END 
WHERE status IN ('รอดำเนินการ', 'อนุมัติ', 'ปฏิเสธ', 'ยกเลิก');

-- Show results
SELECT 'Tasks updated:' as result;
SELECT status, COUNT(*) as count FROM tasks GROUP BY status;

SELECT 'Task assignments updated:' as result;
SELECT status, COUNT(*) as count FROM task_assignments GROUP BY status;