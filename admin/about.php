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
INGROUP:    MokoDoliTools
FILE:       admin/about.php
VERSION:    02.05.05
BRIEF:      About & Changelog page for the MokoDoliTools module.
PATH:       htdocs/custom/mokodolitools/admin/about.php
NOTE:       Finds ChangeLog.md case-insensitively in the module root and renders it as Markdown.
VARIABLES:
========================================================================
*/

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php';
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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . '/main.inc.php')) {
	$res = @include substr($tmp, 0, ($i + 1)) . '/main.inc.php';
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php')) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php';
}
// Try main.inc.php using relative path
if (!$res && file_exists('../../main.inc.php')) {
	$res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
	$res = @include '../../../main.inc.php';
}
if (!$res) {
	die('Include of main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once '../lib/mokodolitools.lib.php';

/**
 * @var Conf        $conf
 * @var DoliDB      $db
 * @var HookManager $hookmanager
 * @var Translate   $langs
 * @var User        $user
 */

// Translations
$langs->loadLangs(array('errors', 'admin', 'mokodolitools@mokodolitools'));

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
$titlekey = 'MOKODOLITOOLS_About'; // Use lang key (add in langs/en_US/mokodolitools.lang if missing)

llxHeader('', $langs->trans($titlekey), $help_url, '', 0, 0, '', '', '', 'mod-mokodolitools page-admin_about');

dol_htmloutput_events(); // render queued messages if any

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($langs->trans($titlekey), $linkback, 'title_setup');

// Configuration header
$head = mokodolitoolsAdminPrepareHead();
print dol_get_fiche_head($head, 'about', $langs->trans($titlekey), 0, 'mokodolitools@mokodolitools');

// Module long description
if (!class_exists('modMokoDoliTools', false)) {
	dol_include_once('/mokodolitools/core/modules/modMokoDoliTools.class.php');
}
if (class_exists('modMokoDoliTools', false)) {
	$tmpmodule = new modMokoDoliTools($db);
	print $tmpmodule->getDescLong();
}

// Locate ChangeLog.md case-insensitively within the module root
$dir = DOL_DOCUMENT_ROOT . '/custom/mokodolitools';
$target = 'changelog.md';
$filepath = null;

if (is_dir($dir)) {
	foreach (new DirectoryIterator($dir) as $fi) {
		if ($fi->isFile() && strcasecmp($fi->getFilename(), $target) === 0) {
			$filepath = $fi->getPathname();
			break;
		}
	}
}

if (!$filepath) {
	setEventMessages($langs->trans('MOKODOLITOOLS_ChangelogNotFound', $dir), null, 'errors');
} else {
	// Read markdown file
	$markdown = @file_get_contents($filepath);
	if ($markdown === false) {
		setEventMessages($langs->trans('MOKODOLITOOLS_CannotReadFile', $filepath), null, 'errors');
	} else {
		// Parse Markdown into HTML (prefer Dolibarr helper if available; fallback to Parsedown in safe mode)
		if (!function_exists('dol_md')) {
			$parsedownPath = DOL_DOCUMENT_ROOT . '/includes/parsedown/Parsedown.php';
			if (!file_exists($parsedownPath)) {
				setEventMessages($langs->trans('MOKODOLITOOLS_MarkdownParserMissing', $parsedownPath), null, 'errors');
			} else {
				require_once $parsedownPath;
				$parser = new Parsedown();
				if (method_exists($parser, 'setSafeMode')) {
					$parser->setSafeMode(true); // prevent raw HTML injection
				}
				$html = $parser->text($markdown);
			}
		} else {
			$html = dol_md($markdown);
		}

		// Output HTML when available
		if (!empty($html)) {
			print '<div id="mokodolitools-changelog" class="changelog markdown-body">' . $html . '</div>';

			// Make external links open in a new tab (UX + safety)
			?>
			<script>
			(function () {
				var c = document.getElementById('mokodolitools-changelog');
				if (!c) return;
				c.querySelectorAll('a[href]').forEach(function (a) {
					var href = a.getAttribute('href') || '';
					if (/^https?:\/\//i.test(href)) {
						a.setAttribute('target', '_blank');
						a.setAttribute('rel', 'noopener noreferrer');
					}
				});
			})();
			</script>
			<?php
		} else {
			setEventMessages($langs->trans('MOKODOLITOOLS_ChangelogEmpty'), null, 'warnings');
		}
	}
}

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
