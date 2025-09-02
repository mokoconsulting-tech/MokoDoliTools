<?php
/*
 * Migration: MokoCRM → MokoDoliTools (02.05.02)
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
define('NOREQUIRESOC', 1);
define('NOREQUIRETRAN', 1);
define('NOREQUIREMENU', 1);
define('NOTOKENRENEWAL', 1);
define('NOCSRFCHECK', 1);
define('NOLOGIN', 0);

require_once dirname(__FILE__, 3) . '/main.inc.php';

if (empty(\) || empty(\->admin)) accessforbidden('Admin only');

global \;

\->begin();

\   = \->prefix() . 'const';
\ = \->prefix() . 'modules';

\ = array(
	"UPDATE {\} SET name = 'MAIN_MODULE_MOKODOLITOOLS' WHERE name = 'MAIN_MODULE_MOKOCRM'",
	"UPDATE {\} SET name = REPLACE(name, 'MOKOCRM_', 'MOKODOLITOOLS_') WHERE name LIKE 'MOKOCRM_%'",
	"UPDATE {\} SET value = REPLACE(value, 'mokocrm', 'mokodolitools') WHERE value LIKE '%mokocrm%'",
	"UPDATE {\} SET value = REPLACE(value, 'MokoCRM', 'MokoDoliTools') WHERE value LIKE '%MokoCRM%'",
	"UPDATE {\} SET name='MokoDoliTools', const_name='MAIN_MODULE_MOKODOLITOOLS', version='02.05.02' WHERE const_name='MAIN_MODULE_MOKOCRM'",
);

\ = true; \ = array();
foreach (\ as \) {
	if (!\->query(\)) { \ = false; \[] = \->lasterror(); break; }
}

if (\) {
	\->commit();
	print "Migration OK: MokoCRM → MokoDoliTools (02.05.02)\\n";
} else {
	\->rollback();
	if (!headers_sent()) header(\['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
	print "Migration FAILED:\\n".implode("\\n", \)."\\n";
}
