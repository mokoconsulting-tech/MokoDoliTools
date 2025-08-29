<?php
/*
Copyright (C) 2024–2025 Moko Consulting <hello@mokoconsulting.tech>
This file is part of a Moko Consulting project.

SPDX-License-Identifier: GPL-3.0-or-later
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version. This program is distributed in the
hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

FILE INFORMATION
DEFGROUP: Dolibarr
INGROUP:  MokoCRM
FILE:     admin/dbadmin.php
VERSION:  02.05.00
BRIEF:    Dolibarr admin Setup page that embeds Adminer in an iframe (parameters hidden).
PATH:     htdocs/custom/mokocrm/admin/dbadmin.php
NOTE:     The iframe loads /custom/mokocrm/admin/adminer_iframe.php which handles POST autologin.
*/

require dirname(__DIR__, 3) . '/main.inc.php';
if (empty($user) || !$user->admin) accessforbidden();

$langs->loadLangs(array('admin', 'errors', 'mokocrm@mokocrm'));

// -----------------------------------------------------------------------------
// Load module admin head from library: /custom/mokocrm/lib/mokocrm.lib.php
// -----------------------------------------------------------------------------
$libPath = DOL_DOCUMENT_ROOT . '/custom/mokocrm/lib/mokocrm.lib.php';
if (is_file($libPath)) {
    require_once $libPath;
}

// Build $head via library function if present, else hard-code a safe fallback
if (function_exists('mokocrmAdminPrepareHead')) {
    $head = mokocrmAdminPrepareHead();
} else {
    $h = 0; $head = array();
    $head[$h][0] = dol_buildpath('/custom/mokocrm/admin/setup.php', 1);
    $head[$h][1] = $langs->trans('MOKOCRM_AdminSetup'); $head[$h][2] = 'setup'; $h++;
    $head[$h][0] = dol_buildpath('/custom/mokocrm/admin/tools.php', 1);
    $head[$h][1] = $langs->trans('MOKOCRM_Tools'); $head[$h][2] = 'tools'; $h++;
    $head[$h][0] = dol_buildpath('/custom/mokocrm/admin/dbadmin.php', 1);
    $head[$h][1] = $langs->trans('MOKOCRM_DBAdmin'); $head[$h][2] = 'dbadmin'; $h++;
    $head[$h][0] = dol_buildpath('/custom/mokocrm/admin/about.php', 1);
    $head[$h][1] = $langs->trans('MOKOCRM_About'); $head[$h][2] = 'about'; $h++;
}

// -----------------------------------------------------------------------------
// Page content
// -----------------------------------------------------------------------------
$title = $langs->trans('MOKOCRM_DBAdmin');
llxHeader('', $title);

// Setup-style title
print load_fiche_titre($title, '', 'title_setup');

// Tabs head (active = dbadmin)
print dol_get_fiche_head($head, 'dbadmin', $langs->trans('MOKOCRM_ModuleName', 'MokoCRM'), -1);

// Warn if Adminer core is missing (so users know why iframe is blank)
$adminerFile = DOL_DOCUMENT_ROOT . '/custom/mokocrm/adminer/adminer.php';
if (!is_file($adminerFile)) {
    print '<div class="warning" style="margin-bottom:12px;">'
        . $langs->trans('Error') . ': Adminer not found at <code>'
        . dol_escape_htmltag($adminerFile) . '</code>.'
        . '</div>';
}

// Help/intro text
print '<div class="opacitymedium" style="margin-bottom:10px;">'
    . $langs->trans('MOKOCRM_DBAdminHelp', 'Adminer')
    . '</div>';

// Fallback button (opens Adminer in a new tab if iframe is blocked by headers)
$iframeUrl = DOL_URL_ROOT . '/custom/mokocrm/admin/adminer_iframe.php';
print '<div style="margin:8px 0 16px">';
print '<a class="butAction" href="' . dol_escape_htmltag($iframeUrl) . '" target="_blank" rel="noopener">'
    . $langs->trans('OpenInNewTab', 'Adminer') . '</a>';
print '</div>';

// Iframe container
print '<div class="fichecenter">';
print '  <div class="underbanner clearboth"></div>';
print '  <div class="fichehalfleft" style="width:100%;">';
print '    <div class="div-table-responsive-no-min">';
print '      <iframe id="adminerframe" src="' . dol_escape_htmltag($iframeUrl) . '" '
        . 'style="width:100%; height: calc(100vh - 260px); border:1px solid #ddd; border-radius:8px;" '
        . 'loading="lazy" referrerpolicy="no-referrer"></iframe>';
print '    </div>';
print '  </div>';
print '</div>';

// Tabs foot + footer
print dol_get_fiche_end();
llxFooter();
