-- Add template permissions to roles
ALTER TABLE `roles` ADD COLUMN `template_permissions` JSON DEFAULT NULL;

-- Set default permissions for roles
UPDATE `roles` SET `template_permissions` = JSON_OBJECT(
    'allowed_templates', 
    JSON_ARRAY('dashboard', 'profile')
) WHERE `name` = 'subscriber';

UPDATE `roles` SET `template_permissions` = JSON_OBJECT(
    'allowed_templates', 
    JSON_ARRAY('dashboard', 'profile', 'articles', 'pages')
) WHERE `name` = 'author';

UPDATE `roles` SET `template_permissions` = JSON_OBJECT(
    'allowed_templates', 
    JSON_ARRAY('dashboard', 'profile', 'articles', 'pages', 'media', 'comments')
) WHERE `name` = 'editor';

UPDATE `roles` SET `template_permissions` = JSON_OBJECT(
    'allowed_templates', 
    JSON_ARRAY('dashboard', 'profile', 'users', 'articles', 'pages', 'media', 'comments', 'settings')
) WHERE `name` = 'admin';

UPDATE `roles` SET `template_permissions` = JSON_OBJECT(
    'allowed_templates', 
    JSON_ARRAY('*')
) WHERE `name` = 'super_admin';
