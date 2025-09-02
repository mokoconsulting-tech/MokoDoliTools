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
 FILE:     admin/dbadmin.php
 VERSION:  02.05.02
 BRIEF:    Admin interface for database administration within MokoDoliTools
 PATH:     mokodolitools/admin/dbadmin.php
 NOTE:     This file is part of the MokoDoliTools module for Dolibarr
*/


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, $i + 1) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, $i + 1) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, $i + 1)) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, $i + 1)) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once '../lib/mokodolitools.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Translations
$langs->loadLangs(["errors", "admin", "mokodolitools@mokodolitools"]);

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

/*
 * Actions
 */

// None

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = "MokoDoliToolsSetup";

llxHeader('', $langs->trans($title), $help_url, '', 0, 0, '', '', '', 'mod-mokodolitools page-admin_dbadmin');

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// Configuration header
$head = mokodolitoolsAdminPrepareHead();
print dol_get_fiche_head($head, 'dbadmin', $langs->trans($title), 0, 'mokodolitools@mokodolitools');

// Security check - Protection if external user
$permissionToRead = $user->rights->mokodolitools->dbadmin->access && $user->admin;
if (isModEnabled('mokodolitools') < 1 || !$permissionToRead) {
	accessforbidden('', 0, 0);
}
print '
<script>
$(document).ready(function(){
	$("<div title=\'!! ' .	$langs->trans("Warning") .' !!\'>' .
	$langs->trans("MOKODOLITOOLS_DBADMIN_Warning") .
	'</div>").dialog({
		modal: true,
		buttons: {
			"' .
	$langs->trans("IUnderstand") .
	'": function() {
				$(this).dialog("close");
			}
		}
	});
});
</script>';
dol_include_once('/mokodolitools/core/modules/modMokoDoliTools.class.php');

print '<div><iframe src="iframe.php" width="100%" height="100%" frameborder="0" style="display: block; height: 90vh; margin: 0; padding: 0; border: 0 none; box-sizing: border-box;"></iframe>';

print '</div>';

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
