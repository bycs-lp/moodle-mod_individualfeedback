-- Alternative approach: Register the existing installation
-- This assumes the tables are correctly created and we just need to register the plugin

-- Insert the module registration if it doesn't exist
INSERT IGNORE INTO mdl_modules (name, cron, lastcron, search, visible) 
VALUES ('individualfeedback', 0, 0, 0, 1);

-- Insert the plugin version if it doesn't exist
INSERT INTO mdl_config_plugins (plugin, name, value) 
VALUES ('mod_individualfeedback', 'version', '2024100702')
ON DUPLICATE KEY UPDATE value = '2024100702';

COMMIT;
