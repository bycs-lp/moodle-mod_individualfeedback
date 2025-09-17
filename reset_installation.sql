-- SQL script to safely remove mod_individualfeedback tables and data
-- Run this script in your Moodle database to completely clean the installation
-- Then try installing the plugin again

-- Remove plugin configuration
DELETE FROM mdl_config_plugins WHERE plugin = 'mod_individualfeedback';

-- Remove course modules (this will also remove activities)
DELETE cm FROM mdl_course_modules cm 
JOIN mdl_modules m ON cm.module = m.id 
WHERE m.name = 'individualfeedback';

-- Remove module registration
DELETE FROM mdl_modules WHERE name = 'individualfeedback';

-- Drop all plugin tables
DROP TABLE IF EXISTS mdl_individualfeedback_sitecourse_map;
DROP TABLE IF EXISTS mdl_individualfeedback_valuetmp;
DROP TABLE IF EXISTS mdl_individualfeedback_value;
DROP TABLE IF EXISTS mdl_individualfeedback_completedtmp;
DROP TABLE IF EXISTS mdl_individualfeedback_completed;
DROP TABLE IF EXISTS mdl_individualfeedback_item;
DROP TABLE IF EXISTS mdl_individualfeedback_template;
DROP TABLE IF EXISTS mdl_individualfeedback;

-- Remove any capabilities
DELETE FROM mdl_capabilities WHERE name LIKE 'mod/individualfeedback:%';

-- Remove any events
DELETE FROM mdl_event WHERE modulename = 'individualfeedback';

-- Remove log display entries
DELETE FROM mdl_log_display WHERE module = 'individualfeedback';
DELETE FROM mdl_log_display WHERE component = 'mod_individualfeedback';

-- Clean up any file records
DELETE FROM mdl_files WHERE component = 'mod_individualfeedback';

COMMIT;
