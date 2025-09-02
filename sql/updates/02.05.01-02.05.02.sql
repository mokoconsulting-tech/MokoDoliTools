START TRANSACTION;

UPDATE llx_const
   SET name = 'MAIN_MODULE_MOKODOLITOOLS'
 WHERE name = 'MAIN_MODULE_MOKOCRM';

UPDATE llx_const
   SET name = REPLACE(name, 'MOKOCRM_', 'MOKODOLITOOLS_')
 WHERE name LIKE 'MOKOCRM_%';

UPDATE llx_const
   SET value = REPLACE(value, 'mokocrm', 'mokodolitools')
 WHERE value LIKE '%mokocrm%';

UPDATE llx_const
   SET value = REPLACE(value, 'MokoCRM', 'MokoDoliTools')
 WHERE value LIKE '%MokoCRM%';

UPDATE llx_modules
   SET name       = 'MokoDoliTools',
       const_name = 'MAIN_MODULE_MOKODOLITOOLS',
       version    = '02.05.02'
 WHERE const_name = 'MAIN_MODULE_MOKOCRM';

-- Optional if menus store module string:
-- UPDATE llx_menu SET module = 'MokoDoliTools' WHERE module = 'MokoCRM';

COMMIT;

START TRANSACTION;

DELETE FROM llx_const
WHERE name IN (
    'MAIN_MODULE_MAILING',
    'MAIN_MODULE_WEBSITE',
    'MAIN_MODULE_MODULEBUILDER'
);

COMMIT;
