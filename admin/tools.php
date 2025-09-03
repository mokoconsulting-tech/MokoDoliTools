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
========================================================================
FILE INFORMATION
INGROUP: MokoDoliTools
FILE: tools.php
VERSION: 02.05.05
BRIEF: Admin tools for MokoDoliTools with modal links, quick filter, accordion UI, and a Security Advisor.
PATH: htdocs/custom/mokodolitools/admin/tools.php
NOTE: Disallowed tools are hidden when install.lock is present (unless upgrade.unlock exists). Uses MOKODOLITOOLS_* language keys throughout.
VARIABLES:
========================================================================
*/

// -----------------------------------------------------------------------------
// Bootstrap Dolibarr
// -----------------------------------------------------------------------------
$res = 0;
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php';
}
$tmp  = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, $i + 1) . '/main.inc.php')) { $res = @include substr($tmp, 0, $i + 1) . '/main.inc.php'; }
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, $i + 1)) . '/main.inc.php')) { $res = @include dirname(substr($tmp, 0, $i + 1)) . '/main.inc.php'; }
if (!$res && file_exists('../../main.inc.php'))    { $res = @include '../../main.inc.php'; }
if (!$res && file_exists('../../../main.inc.php')) { $res = @include '../../../main.inc.php'; }
if (!$res) { die('Include of main fails'); }

require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once '../lib/mokodolitools.lib.php';

/** @var Translate $langs */
/** @var User $user */
/** @var DoliDB $db */
/** @var HookManager $hookmanager */
/** @var Conf $conf */

$langs->loadLangs(['admin', 'install', 'mokodolitools@mokodolitools']);
$hookmanager->initHooks(['mokodolitoolssetup', 'globalsetup']);

if (!$user->admin) { accessforbidden(); }

$form  = new Form($db);
$title = 'MOKODOLITOOLS_ToolsTitle';
llxHeader('', $langs->trans($title), '', '', 0, 0, '', '', '', 'mod-mokodolitools page-admin');

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

$head = mokodolitoolsAdminPrepareHead();
print dol_get_fiche_head($head, 'tools', $langs->trans($title), -1);
// Security check - Protection if external user
if (!isModEnabled('mokodolitools') || empty($user->rights->mokodolitools->tools->access)) {
	accessforbidden('', 0, 0);
}
// -----------------------------------------------------------------------------
// Security helpers
// -----------------------------------------------------------------------------
function mokodolitools_find_conf_file() {
	$candidates = array(
		dirname(DOL_DOCUMENT_ROOT) . '/conf/conf.php',
		DOL_DOCUMENT_ROOT . '/conf/conf.php',
		(isset($_SERVER['CONTEXT_DOCUMENT_ROOT']) ? rtrim($_SERVER['CONTEXT_DOCUMENT_ROOT'], '/').'/conf/conf.php' : null)
	);
	foreach ($candidates as $p) {
		if ($p && @is_file($p)) return $p;
	}
	return '';
}
function mokodolitools_recommended_conf_perms() { return 0440; }

// -----------------------------------------------------------------------------
// Data
// -----------------------------------------------------------------------------
$repairs = [
	'standard'                         => 'MOKODOLITOOLS_Repair_Standard',
	'clean_linked_elements'            => 'MOKODOLITOOLS_Repair_CleanLinkedElements',
	'restore_thirdparties_logos'       => 'MOKODOLITOOLS_Repair_RestoreThirdPartyLogos',
	'clean_menus'                      => 'MOKODOLITOOLS_Repair_CleanMenus',
	'clean_orphelin_dir'               => 'MOKODOLITOOLS_Repair_CleanOrphanDirectories',
	'clean_product_stock_batch'        => 'MOKODOLITOOLS_Repair_CleanProductStockBatch',
	'set_empty_time_spent_amount'      => 'MOKODOLITOOLS_Repair_SetEmptyTimeSpentAmount',
	'rebuild_product_thumbs'           => 'MOKODOLITOOLS_Repair_RebuildProductThumbs',
	'force_disable_of_modules_not_found' => 'MOKODOLITOOLS_Repair_ForceDisableModulesNotFound',
	'clean_perm_table'                 => 'MOKODOLITOOLS_Repair_CleanPermsTable',
	'force_utf8_on_tables'             => 'MOKODOLITOOLS_Repair_ForceUTF8OnTables',
];

$helpfulTools = [
	[ 'label' => 'MOKODOLITOOLS_Tool_PHPInfo',      'href' => DOL_URL_ROOT . '/admin/system/phpinfo.php' ],
	[ 'label' => 'MOKODOLITOOLS_Tool_Environment',  'href' => DOL_URL_ROOT . '/admin/system/dolibarr.php' ],
	[ 'label' => 'MOKODOLITOOLS_Tool_AboutDolibarr','href' => DOL_URL_ROOT . '/admin/system/about.php' ],
];

// -----------------------------------------------------------------------------
// Install/upgrade lock detection
// -----------------------------------------------------------------------------
$lockfile          = DOL_DATA_ROOT . '/install.lock';
$lockfile2         = DOL_DOCUMENT_ROOT . '/install.lock';
$upgradeunlockfile = DOL_DATA_ROOT . '/upgrade.unlock';
$upgradeunlockfile2= DOL_DOCUMENT_ROOT . '/upgrade.unlock';
if (constant('DOL_DATA_ROOT') === null) { // fallback
	$lockfile = '../../documents/install.lock';
	$upgradeunlockfile = '../../documents/upgrade.unlock';
}
$islocked = (@file_exists($lockfile) || @file_exists($lockfile2))
	&& (!defined('ALLOWED_IF_UPGRADE_UNLOCK_FOUND') || (!@file_exists($upgradeunlockfile) && !@file_exists($upgradeunlockfile2)));

// -----------------------------------------------------------------------------
// Actions (Security Advisor fixes)
// -----------------------------------------------------------------------------
$action = GETPOST('action','aZ09');
if (!empty($action) && GETPOST('token','aZ09') == $_SESSION['newtoken']) {
	$confFile = mokodolitools_find_conf_file();
	$messages = array();
	$errors = array();

	if ($action == 'fix_conf_perms') {
		if ($confFile && @is_file($confFile)) {
			$ok = @chmod($confFile, mokodolitools_recommended_conf_perms());
			if ($ok) $messages[] = $langs->trans('MOKODOLITOOLS_Sec_FixedConfPerms');
			else $errors[] = $langs->trans('MOKODOLITOOLS_Sec_FixConfPermsFailed', $confFile);
		} else {
			$errors[] = $langs->trans('MOKODOLITOOLS_Sec_ConfNotFound');
		}
	}
	elseif ($action == 'create_lock') {
		$targets = array($lockfile, $lockfile2);
		$ok = false;
		foreach ($targets as $t) { if ($t && @touch($t)) $ok = true; }
		if ($ok) $messages[] = $langs->trans('MOKODOLITOOLS_Sec_LockCreated');
		else $errors[] = $langs->trans('MOKODOLITOOLS_Sec_LockCreateFailed');
	}
	elseif ($action == 'remove_lock') {
		$targets = array($lockfile, $lockfile2);
		$ok = false;
		foreach ($targets as $t) { if ($t && @file_exists($t) && @unlink($t)) $ok = true; }
		if ($ok) $messages[] = $langs->trans('MOKODOLITOOLS_Sec_LockRemoved');
		else $errors[] = $langs->trans('MOKODOLITOOLS_Sec_LockRemoveFailed');
	}
	elseif ($action == 'set_env_prod' || $action == 'set_env_dev') {
		$val = ($action == 'set_env_dev') ? '2' : '0';
		if (dolibarr_set_const($db, 'MAIN_FEATURES_LEVEL', $val, 'chaine', 0, '', $conf->entity) > 0) {
			$messages[] = $langs->trans('MOKODOLITOOLS_Sec_EnvUpdated');
		} else {
			$errors[] = $langs->trans('MOKODOLITOOLS_Sec_EnvUpdateFailed');
		}
	}

	if ($messages) setEventMessages(implode('<br>', $messages), null, 'mesgs');
	if ($errors) setEventMessages('', $errors, 'errors');

	// Recompute lock status after action
	$islocked = (@file_exists($lockfile) || @file_exists($lockfile2))
		&& (!defined('ALLOWED_IF_UPGRADE_UNLOCK_FOUND') || (!@file_exists($upgradeunlockfile) && !@file_exists($upgradeunlockfile2)));
}

// -----------------------------------------------------------------------------
// UI: Notice + Filter + Accordion
// -----------------------------------------------------------------------------
print '<div class="opacitymedium">' . $langs->trans('MOKODOLITOOLS_LinksOpenInModal') . '</div>';
print '<div class="divsearchfield" style="margin:8px 0 16px 0;">';
print '<input type="text" id="mokodolitools-filter" class="flat inputsearch" placeholder="' . dol_escape_htmltag($langs->trans('MOKODOLITOOLS_FilterPlaceholder')) . '" autocomplete="off">';
print '</div>';

print '<div id="mokodolitools-accordion">';

// Section: Security Advisor (always)
$confFile = mokodolitools_find_conf_file();
$perm = $confFile ? (@fileperms($confFile) & 0777) : 0;
$permOk = $confFile && !is_writable($confFile) && ($perm <= 0640);
$envDev = ((int) $conf->global->MAIN_FEATURES_LEVEL >= 2);
$envText = $envDev ? $langs->trans('MOKODOLITOOLS_Env_Development') : $langs->trans('MOKODOLITOOLS_Env_Production');
$lockPresent = $islocked;
$lockIssue = (!$envDev && !$lockPresent); // In production, lock should exist

print '<h3>' . $langs->trans('MOKODOLITOOLS_Sec_Title') . '</h3>';
print '<div>';
print '<ul class="listwithicon">';

// Config perms
print '<li>' . $langs->trans('MOKODOLITOOLS_Sec_ConfigPerms') . ': ' . ($permOk ? $langs->trans('MOKODOLITOOLS_Sec_OK') : $langs->trans('MOKODOLITOOLS_Sec_Bad', decoct($perm)));
if (!$permOk) {
	print ' <form method="post" action="" style="display:inline">'
		. '<input type="hidden" name="token" value="' . newToken() . '">'
		. '<input type="hidden" name="action" value="fix_conf_perms">'
		. '<button class="button small" type="submit">' . $langs->trans('MOKODOLITOOLS_Sec_FixPerms') . '</button>'
		. '</form>';
}
print '</li>';

// Lock status
print '<li>' . $langs->trans('MOKODOLITOOLS_Sec_LockStatus') . ': ' . ($lockPresent ? $langs->trans('MOKODOLITOOLS_Sec_LockPresent') : $langs->trans('MOKODOLITOOLS_Sec_LockMissing'));
if ($lockIssue) {
	print ' <form method="post" action="" style="display:inline">'
		. '<input type="hidden" name="token" value="' . newToken() . '">'
		. '<input type="hidden" name="action" value="create_lock">'
		. '<button class="button small" type="submit">' . $langs->trans('MOKODOLITOOLS_Sec_CreateLock') . '</button>'
		. '</form>';
} elseif ($envDev && $lockPresent) {
	print ' <form method="post" action="" style="display:inline">'
		. '<input type="hidden" name="token" value="' . newToken() . '">'
		. '<input type="hidden" name="action" value="remove_lock">'
		. '<button class="button small" type="submit">' . $langs->trans('MOKODOLITOOLS_Sec_RemoveLock') . '</button>'
		. '</form>';
}
print '</li>';

// Environment
print '<li>' . $langs->trans('MOKODOLITOOLS_Sec_Environment') . ': ' . $envText;
if ($envDev) {
	print ' <form method="post" action="" style="display:inline">'
		. '<input type="hidden" name="token" value="' . newToken() . '">'
		. '<input type="hidden" name="action" value="set_env_prod">'
		. '<button class="button small" type="submit">' . $langs->trans('MOKODOLITOOLS_Sec_SetProd') . '</button>'
		. '</form>';
} else {
	print ' <form method="post" action="" style="display:inline">'
		. '<input type="hidden" name="token" value="' . newToken() . '">'
		. '<input type="hidden" name="action" value="set_env_dev">'
		. '<button class="button small" type="submit">' . $langs->trans('MOKODOLITOOLS_Sec_SetDev') . '</button>'
		. '</form>';
}
print '</li>';

print '</ul>';
print '</div>';

// Section: Repair Test (only if not locked)
if (!$islocked) {
	$testLinks = [];
	foreach ($repairs as $param => $labelkey) {
		$testLinks[] = '<li><a href="' . DOL_URL_ROOT . '/install/repair.php?' . $param . '=test">' . $langs->trans($labelkey) . '</a></li>';
	}
	if (!empty($testLinks)) {
		print '<h3>' . $langs->trans('MOKODOLITOOLS_Repair_TestTitle') . '</h3>';
		print '<div><ul id="mokodolitools-repairs-test" class="listwithicon">' . implode("\n", $testLinks) . '</ul></div>';
	}
}

// Section: Repair Run (only if not locked)
if (!$islocked) {
	$runLinks = [];
	foreach ($repairs as $param => $labelkey) {
		$runLinks[] = '<li><a href="' . DOL_URL_ROOT . '/install/repair.php?' . $param . '=confirmed">' . $langs->trans($labelkey) . '</a></li>';
	}
	if (!empty($runLinks)) {
		print '<h3>' . $langs->trans('MOKODOLITOOLS_Repair_RunTitle') . '</h3>';
		print '<div><ul id="mokodolitools-repairs-run" class="listwithicon">' . implode("\n", $runLinks) . '</ul></div>';
	}
}

// Section: Helpful Tools (always render if not empty)
$helpLinks = [];
foreach ($helpfulTools as $t) {
	$helpLinks[] = '<li><a href="' . dol_escape_htmltag($t['href']) . '" rel="noopener">' . dol_escape_htmltag($langs->trans($t['label'])) . '</a></li>';
}
if (!empty($helpLinks)) {
	print '<h3>' . $langs->trans('MOKODOLITOOLS_HelpfulToolsTitle') . '</h3>';
	print '<div><ul id="mokodolitools-tools" class="listwithicon">' . implode("\n", $helpLinks) . '</ul></div>';
}

// Section: Security Tools (hide install-only entries when locked)
$secLinks = [];
$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/admin/security.php" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_SecurityConfig') . '</a></li>';
$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/admin/tools/listevents.php" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_AuditSecurity') . '</a></li>';
if (!$islocked) {
	$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/install/check.php" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_InstallChecker') . '</a></li>';
}
$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/admin/tools/purge.php?choice=tempfiles" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_PurgeCache') . '</a></li>';
$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/admin/tools/purge.php?choice=logfile" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_PurgeLogs') . '</a></li>';
$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/user/index.php" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_ManageUsers') . '</a></li>';
$secLinks[] = '<li><a href="' . DOL_URL_ROOT . '/user/group/index.php" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_ManageGroups') . '</a></li>';
if (!empty($secLinks)) {
	print '<h3>' . $langs->trans('MOKODOLITOOLS_SecurityToolsTitle') . '</h3>';
	print '<div><ul id="mokodolitools-secure" class="listwithicon">' . implode("\n", $secLinks) . '</ul></div>';
}

// Section: Improvements (hidden when locked because actions require installer)
$improveLinks = [];
if (!$islocked) {
	$improveLinks[] = '<li><a href="' . DOL_URL_ROOT . '/install/repair.php?rebuild_product_thumbs=confirmed" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_RebuildThumbs') . '</a></li>';
	$improveLinks[] = '<li><a href="' . DOL_URL_ROOT . '/install/repair.php?force_utf8_on_tables=confirmed" rel="noopener">' . $langs->trans('MOKODOLITOOLS_Tool_ForceUTF8') . '</a></li>';
}
if (!empty($improveLinks)) {
	print '<h3>' . $langs->trans('MOKODOLITOOLS_ImprovementsTitle') . '</h3>';
	print '<div><ul id="mokodolitools-improve" class="listwithicon">' . implode("\n", $improveLinks) . '</ul></div>';
}

print '</div>'; // #mokodolitools-accordion

// Modal container + JS (init accordion + filter + modal behavior)
print '<div id="mokodolitools-modal" style="display:none;"></div>';
print '<script>
jQuery(function($){
  // Accordion
  $("#mokodolitools-accordion").accordion({
	collapsible: true,
	heightStyle: "content",
	active: false
  });

  // Modal opener (for internal links)
  function openInModal(e){
	var href = $(this).attr("href");
	if (!href) return;
	var isExternal = /^https?:\/\//i.test(href) && href.indexOf(' . json_encode(DOL_URL_ROOT) . ') !== 0;
	if (isExternal) return; // let browser handle
	e.preventDefault();
	var title = $.trim($(this).text()) || "Tool";
	var $dlg = $("#mokodolitools-modal");
	if (!$dlg.length) { $dlg = $("<div id=\"mokodolitools-modal\"></div>").appendTo("body"); }
	$dlg.empty();
	var $frame = $("<iframe>").attr({src: href, width: "100%", height: "100%", frameborder: 0, allow: "clipboard-read; clipboard-write"});
	$dlg.append($frame).dialog({
	  modal: true,
	  width: Math.min($(window).width()*0.9, 1200),
	  height: Math.min($(window).height()*0.9, 900),
	  title: title,
	  close: function(){ $(this).dialog("destroy").hide().empty(); }
	});
  }
  $(document).on("click", "#mokodolitools-tools a, #mokodolitools-secure a, #mokodolitools-repairs-test a, #mokodolitools-repairs-run a, #mokodolitools-improve a", openInModal);

  // Quick filter (filter list items across sections)
  $(document).on("input", "#mokodolitools-filter", function(){
	var q = $(this).val().toLowerCase();
	$("#mokodolitools-accordion ul.listwithicon li").each(function(){
	  var t = $(this).text().toLowerCase();
	  $(this).toggle(t.indexOf(q) !== -1);
	});
  });
});
</script>';

print dol_get_fiche_end();
llxFooter();
$db->close();
