<?php
/* Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025				Jonathan Miller || Moko Consulting		<dev@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    mokocrm/admin/setup.php
 * \ingroup mokocrm
 * \brief   MokoCRM setup page.
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
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/mokocrm.lib.php';
//require_once "../class/myclass.class.php";

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Translations
$langs->loadLangs(["admin", "mokocrm@mokocrm"]);

// Initialize a technical object to manage hooks of page. Note that conf->hooks_modules contains an array of hook context
/** @var HookManager $hookmanager */
$hookmanager->initHooks(['mokocrmsetup', 'globalsetup']);

$error = 0;
$setupnotempty = 0;

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formsetup.class.php';
}
$formSetup = new FormSetup($db);

$setupnotempty += count($formSetup->items);

$moduledir = 'mokocrm';

/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if (versioncompare(explode('.', DOL_VERSION), [15]) < 0 && $action == 'update' && !empty($user->admin)) {
    $formSetup->saveConfFromPost();
}

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = "MokoCRMSetup";

llxHeader('', $langs->trans($title), $help_url, '', 0, 0, '', '', '', 'mod-mokocrm page-admin');

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// Configuration header
$head = mokocrmAdminPrepareHead();
print dol_get_fiche_head($head, 'run', $langs->trans($title), -1, "mokocrm@mokocrm");
// Security check - Protection if external user
$permissionToRead = $user->rights->mokocrm->repair->access && $user->admin;
if (isModEnabled('mokocrm') < 1 || !$permissionToRead) {
    accessforbidden('', 0, 0);
}
// Setup page goes here
print '<div class="info">'.$langs->trans("RepairArea-Warning").'</div>';
print load_fiche_titre($langs->trans("RepairArea-Test"), '', 'mokocrm.png@mokocrm');
print '<ul><li>';
print '<a target="_blank" href="../../../install/repair.php?standard=test">';
print $langs->trans("Standard");
print '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?clean_linked_elements=test">';
print $langs->trans("Clean Linked Elements");
print '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?restore_thirdparties_logos=test">';
print $langs->trans("unestore Third Party Logos");
print '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?clean_menus=test">';
print $langs->trans("Clean Menus") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?clean_orphelin_dir=test">';
print $langs->trans("Clean Orphan Directories") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?clean_product_stock_batch=test">';
print $langs->trans("Clean Prodyct Stock Batch") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?set_empty_time_spent_amount=test">';
print $langs->trans("Set Empty Time Spent Amount") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?rebuild_product_thumbs=test">';
print $langs->trans("Rebuild Product Thumbs") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?force_disable_of_modules_not_found=test">';
print $langs->trans("Force Disable Moduled Not Found") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?clean_perm_table=test">';
print $langs->trans("Clean Perms Table") . '</a></li>';
print '<li><a target="_blank" href="../../../install/repair.php?force_utf8_on_tables=test">';
print $langs->trans("Force UTYF8 on tables") . '</a></li></ul>';

dol_include_once('/mokocrm/core/modules/modmokocrm.class.php');
include_once 'inc.php';
if (file_exists($conffile)) {
    include_once $conffile;
}
// Check install.lock (for both install and upgrade)

$lockfile = DOL_DATA_ROOT . '/install.lock'; // To lock all /install pages
$lockfile2 = DOL_DOCUMENT_ROOT . '/install.lock'; // To lock all /install pages (recommended)
$upgradeunlockfile = DOL_DATA_ROOT . '/upgrade.unlock'; // To unlock upgrade process
$upgradeunlockfile2 = DOL_DOCUMENT_ROOT . '/upgrade.unlock'; // To unlock upgrade process
if (constant('DOL_DATA_ROOT') === null) {
    // We don't have a configuration file yet
    // Try to detect any lockfile in the default documents path
    $lockfile = '../../documents/install.lock';
    $upgradeunlockfile = '../../documents/upgrade.unlock';
}
$islocked = false;
if (@file_exists($lockfile) || @file_exists($lockfile2)) {
    if (!defined('ALLOWED_IF_UPGRADE_UNLOCK_FOUND') || (!@file_exists($upgradeunlockfile) && !@file_exists($upgradeunlockfile2))) {
        // If this is a dangerous install page (ALLOWED_IF_UPGRADE_UNLOCK_FOUND not defined) or
        // if there is no upgrade unlock files, we lock the pages.
        $islocked = true;
    }
}
if ($islocked) {
    // Pages are locked
    if (!isset($langs) || !is_object($langs)) {
        $langs = new Translate('..', $conf);
        $langs->setDefaultLang('auto');
    }
    $langs->load("install");

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN"); // Frames allowed only if on same domain (stop some XSS attacks)

    if (GETPOST('action') != 'upgrade') {
        print $langs->trans("YouTryInstallDisabledByFileLock") . '<br>';
    }
} else {
print '
<script>
$(document).ready(function(){
    $("<div title=\'Warning\'>'.$langs->trans("RepairArea-RunWarning").'</div>").dialog({
        modal: true,
        buttons: {
            "'.$langs->trans("I Understand").'": function() {
                $(this).dialog("close");
            }
        }
    });
});
</script>';

print '<div class="warning">'.$langs->trans("RepairArea-RunWarning").'</div>';
    print load_fiche_titre($langs->trans("RepairArea-Run"), '', 'mokocrm.png@mokocrm');

    print '<ul><li>';
    print '<a target="_blank" href="../../../install/repair.php?standard=confirmed">';
    print $langs->trans("Standard");
    print '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?clean_linked_elements=confirmed">';
    print $langs->trans("Clean Linked Elements");
    print '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?restore_thirdparties_logos=confirmed">';
    print $langs->trans("Restore Third Party Logos");
    print '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?clean_menus=confirmed">';
    print $langs->trans("Clean Menus") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?clean_orphelin_dir=confirmed">';
    print $langs->trans("Clean Orphan Directories") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?clean_product_stock_batch=confirmed">';
    print $langs->trans("Clean Prodyct Stock Batch") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?set_empty_time_spent_amount=confirmed">';
    print $langs->trans("Set Empty Time Spent Amount") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?rebuild_product_thumbs=confirmed">';
    print $langs->trans("Rebuild Product Thumbs") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?force_disable_of_modules_not_found=confirmed">';
    print $langs->trans("Force Disable Moduled Not Found") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?clean_perm_table=confirmed">';
    print $langs->trans("Clean Perms Table") . '</a></li>';
    print '<li><a target="_blank" href="../../../install/repair.php?force_utf8_on_tables=confirmed">';
    print $langs->trans("Force UTYF8 on tables") . '</a></li></ul>';
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
