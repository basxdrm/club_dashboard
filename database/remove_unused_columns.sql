-- Remove Unused Columns from Database
-- Created: 2026-01-16

USE dashboard_db;

-- Equipment table
ALTER TABLE equipment DROP COLUMN IF EXISTS notes;

-- Member Education table
ALTER TABLE member_education DROP COLUMN IF EXISTS academic_year;
ALTER TABLE member_education DROP COLUMN IF EXISTS semester;

-- Projects table
ALTER TABLE projects DROP COLUMN IF EXISTS category;
ALTER TABLE projects DROP COLUMN IF EXISTS cover_image;
ALTER TABLE projects DROP COLUMN IF EXISTS priority;
ALTER TABLE projects DROP COLUMN IF EXISTS actual_cost;

-- Tasks table - Drop foreign key constraints first
ALTER TABLE tasks DROP FOREIGN KEY IF EXISTS tasks_ibfk_3;
ALTER TABLE tasks DROP FOREIGN KEY IF EXISTS tasks_ibfk_4;
ALTER TABLE tasks DROP INDEX IF EXISTS idx_reviewed_by;
ALTER TABLE tasks DROP INDEX IF EXISTS parent_task_id;

-- Drop columns
ALTER TABLE tasks DROP COLUMN IF EXISTS completed_date;
ALTER TABLE tasks DROP COLUMN IF EXISTS progress;
ALTER TABLE tasks DROP COLUMN IF EXISTS estimated_hours;
ALTER TABLE tasks DROP COLUMN IF EXISTS actual_hours;
ALTER TABLE tasks DROP COLUMN IF EXISTS submit_message;
ALTER TABLE tasks DROP COLUMN IF EXISTS submitted_at;
ALTER TABLE tasks DROP COLUMN IF EXISTS review_message;
ALTER TABLE tasks DROP COLUMN IF EXISTS reviewed_by;
ALTER TABLE tasks DROP COLUMN IF EXISTS reviewed_at;
ALTER TABLE tasks DROP COLUMN IF EXISTS parent_task_id;
ALTER TABLE tasks DROP COLUMN IF EXISTS registration_deadline;
ALTER TABLE tasks DROP COLUMN IF EXISTS requires_approval;

-- Task Assignments table - Drop foreign key first
ALTER TABLE task_assignments DROP FOREIGN KEY IF EXISTS task_assignments_ibfk_4;
ALTER TABLE task_assignments DROP INDEX IF EXISTS approved_by;

-- Drop columns
ALTER TABLE task_assignments DROP COLUMN IF EXISTS work_note;
ALTER TABLE task_assignments DROP COLUMN IF EXISTS completion_note;
ALTER TABLE task_assignments DROP COLUMN IF EXISTS work_hours;
ALTER TABLE task_assignments DROP COLUMN IF EXISTS completed_at;
ALTER TABLE task_assignments DROP COLUMN IF EXISTS notes;
ALTER TABLE task_assignments DROP COLUMN IF EXISTS approved_by;
ALTER TABLE task_assignments DROP COLUMN IF EXISTS approved_at;

-- Users table
ALTER TABLE users DROP COLUMN IF EXISTS email_verified;

-- Drop Project Members table (not used)
DROP TABLE IF EXISTS project_members;

SELECT 'Unused columns removed successfully!' as result;
