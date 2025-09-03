<?php
/*
 Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>
 This file is part of a Moko Consulting project.

 SPDX-License-Identifier: GPL-3.0-or-later

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program. If not, see https://www.gnu.org/licenses/ .

 FILE INFORMATION
 DEFGROUP: Dolibarr
 INGROUP:  MokoDoliTools
 FILE:     migrations/02.05.02_mokocrm_to_mokodolitools.php
 VERSION:  LOCK02.05.02
 BRIEF:    Migration script to rename MokoCRM → MokoDoliTools and update constants
 PATH:     mokodolitools/migrations/02.05.02_mokocrm_to_mokodolitools.php
 NOTE:     Run once as admin (CLI or web) to update database constants and module registry
*/

define('NOREQUIRESOC', 1);
define('NOREQUIRETRAN', 1);
define('NOREQUIREMENU', 1);
define('NOTOKENRENEWAL', 1);
define('NOCSRFCHECK', 1);
define('NOLOGIN', 0);

// From custom/mokodolitools/migrations/* to main.inc.php is typically 3 levels up
require_once dirname(__FILE__, 3) . '/main.inc.php';

if (empty($user) || empty($user->admin)) accessforbidden('Admin only');

global $db;

$db->begin();

$prefix_const   = $db->prefix() . 'const';
$prefix_modules = $db->prefix() . 'modules';

$queries = array(
    "UPDATE {$prefix_const} SET name = 'MAIN_MODULE_MOKODOLITOOLS' WHERE name = 'MAIN_MODULE_MOKOCRM'",
    "UPDATE {$prefix_const} SET name = REPLACE(name, 'MOKOCRM_', 'MOKODOLITOOLS_') WHERE name LIKE 'MOKOCRM_%'",
    "UPDATE {$prefix_const} SET value = REPLACE(value, 'mokocrm', 'mokodolitools') WHERE value LIKE '%mokocrm%'",
    "UPDATE {$prefix_const} SET value = REPLACE(value, 'MokoCRM', 'MokoDoliTools') WHERE value LIKE '%MokoCRM%'",
    "UPDATE {$prefix_modules} SET name='MokoDoliTools', const_name='MAIN_MODULE_MOKODOLITOOLS', version='02.05.02' WHERE const_name='MAIN_MODULE_MOKOCRM'",
);

$ok = true; $errors = array();
foreach ($queries as $sql) {
    if (!$db->query($sql)) {
        $ok = false;
        $errors[] = $db->lasterror();
        break;
    }
}

if ($ok) {
    $db->commit();
    print "Migration OK: MokoCRM → MokoDoliTools (02.05.02)\n";
} else {
    $db->rollback();
    if (!headers_sent()) header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
    print "Migration FAILED:\n".implode("\n", $errors)."\n";
}
