-- Update task status from Thai to English
-- Copy and paste this into phpMyAdmin SQL tab

USE dashboard_db;

-- Update task status
UPDATE tasks 
SET status = CASE 
    WHEN status = 'รอดำเนินการ' THEN 'pending'
    WHEN status = 'กำลังดำเนินการ' THEN 'in_progress' 
    WHEN status = 'เสร็จสิ้น' THEN 'completed'
    WHEN status = 'ยกเลิก' THEN 'cancelled'
    WHEN status = 'รอตรวจสอบ' THEN 'under_review'
    ELSE status
END 
WHERE status IN ('รอดำเนินการ', 'กำลังดำเนินการ', 'เสร็จสิ้น', 'ยกเลิก', 'รอตรวจสอบ');

-- Show results
SELECT 'Tasks updated successfully' as message;
SELECT status, COUNT(*) as count FROM tasks GROUP BY status;