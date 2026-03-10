-- Migration: Add triggers to auto-assign academic_year_id for projects based on start_date

-- Step 1: Update existing projects to assign academic_year_id based on start_date
UPDATE `projects` p
LEFT JOIN `academic_years` ay ON p.start_date BETWEEN ay.start_date AND ay.end_date
SET p.academic_year_id = ay.id
WHERE p.start_date IS NOT NULL;

-- Step 2: For projects without start_date or not matching any year, assign current academic year
UPDATE `projects` p
SET p.academic_year_id = (SELECT id FROM `academic_years` WHERE `is_current` = 1 LIMIT 1)
WHERE p.academic_year_id IS NULL;

-- Step 3: Create trigger to auto-assign academic_year_id on INSERT based on start_date
DELIMITER $$

CREATE TRIGGER `projects_set_academic_year_insert`
BEFORE INSERT ON `projects`
FOR EACH ROW
BEGIN
    -- Only set if not manually specified
    IF NEW.academic_year_id IS NULL THEN
        -- Try to find academic year based on start_date
        IF NEW.start_date IS NOT NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE NEW.start_date BETWEEN start_date AND end_date 
                AND is_active = 1
                LIMIT 1
            );
        END IF;
        
        -- If still null, use current academic year
        IF NEW.academic_year_id IS NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE is_current = 1 
                LIMIT 1
            );
        END IF;
    END IF;
END$$

DELIMITER ;

-- Step 4: Create trigger to auto-update academic_year_id on UPDATE when start_date changes
DELIMITER $$

CREATE TRIGGER `projects_set_academic_year_update`
BEFORE UPDATE ON `projects`
FOR EACH ROW
BEGIN
    -- Only update if start_date changed
    IF NEW.start_date != OLD.start_date OR (NEW.start_date IS NOT NULL AND OLD.start_date IS NULL) THEN
        -- Try to find academic year based on new start_date
        IF NEW.start_date IS NOT NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE NEW.start_date BETWEEN start_date AND end_date 
                AND is_active = 1
                LIMIT 1
            );
        END IF;
        
        -- If still null, use current academic year
        IF NEW.academic_year_id IS NULL THEN
            SET NEW.academic_year_id = (
                SELECT id 
                FROM academic_years 
                WHERE is_current = 1 
                LIMIT 1
            );
        END IF;
    END IF;
END$$

DELIMITER ;
