-- Migration: แก้ไข profile_picture ให้เก็บแค่ชื่อไฟล์ (ไม่เก็บ path)
-- Created: 2026-01-10

-- อัพเดตรูปที่มี path เต็มให้เหลือแค่ชื่อไฟล์
UPDATE profiles 
SET profile_picture = SUBSTRING_INDEX(profile_picture, '/', -1)
WHERE profile_picture IS NOT NULL 
  AND profile_picture LIKE '%/%';

-- แสดงผลลัพธ์
SELECT 
    user_id,
    profile_picture,
    CASE 
        WHEN profile_picture IS NULL THEN 'ไม่มีรูป'
        WHEN profile_picture LIKE '%/%' THEN 'ยังมี path เหลืออยู่'
        ELSE 'เป็นชื่อไฟล์เท่านั้น ✓'
    END as status
FROM profiles
WHERE profile_picture IS NOT NULL
LIMIT 20;
