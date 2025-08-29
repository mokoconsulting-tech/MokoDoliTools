<?php
/*
Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>
This file is part of a Moko Consulting project.
SPDX-License-Identifier: GPL-3.0-or-later

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/ .
========================================================================
FILE INFORMATION
INGROUP: MokoCRM
FILE: mokocrm/admin/setup.php
VERSION: 02.05.000
BRIEF: Setup page for the MokoCRM module (help link settings only; URL hidden unless enabled).
PATH: htdocs/custom/mokocrm/admin/setup.php
NOTE: GeoIP settings are intentionally not loaded or shown here; managed elsewhere.
VARIABLES: MOKOCRM_HELPLINK (default yes), MOKOCRM_HELPLINK_URL
========================================================================
*/

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
    $res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
}
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once '../lib/mokocrm.lib.php';

/**
 * @var Conf        $conf
 * @var DoliDB      $db
 * @var HookManager $hookmanager
 * @var Translate   $langs
 * @var User        $user
 */

// ---- Helpers ----------------------------------------------------------

/**
 * Backward-compatible CSRF verification.
 * Uses dol_verifyToken(), then checkToken(), then falls back to session compare.
 *
 * @param string $token
 * @return bool
 */
function mokocrm_verify_csrf($token)
{
    if (function_exists('dol_verifyToken')) {
        return dol_verifyToken($token);
    }
    if (function_exists('checkToken')) {
        return checkToken($token);
    }
    if (empty($token)) return false;
    if (session_status() !== PHP_SESSION_ACTIVE) return false;

    // Common Dolibarr session keys set by newToken()
    $candidates = array('newtoken', 'token');
    foreach ($candidates as $key) {
        if (!empty($_SESSION[$key])) {
            if (function_exists('hash_equals')) {
                if (hash_equals((string) $_SESSION[$key], (string) $token)) return true;
            } else {
                if ((string) $_SESSION[$key] === (string) $token) return true;
            }
        }
    }
    return false;
}

// ----------------------------------------------------------------------

// Translations
$langs->loadLangs(array('admin', 'mokocrm@mokocrm'));

// Initialize hooks
$hookmanager->initHooks(array('mokocrmsetup', 'globalsetup'));

// Access control
if (empty($user->admin)) {
    accessforbidden();
}

// Parameters
$action     = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$token      = GETPOST('token', 'alpha');

// Constants saved from this page (entity-wide)
$entityForSave = 0; // apply to all entities

/*
 * Actions
 */

// Save settings (Help Link only)
if ($action === 'save' && !empty($user->admin)) {
    if (!mokocrm_verify_csrf($token)) {
        accessforbidden('Bad token');
    }

    // Coerce values to strings to avoid ErrorBadValueForParamNotAString
    $valHelpLink    = GETPOST('MOKOCRM_HELPLINK', 'int') ? '1' : '0'; // '1' or '0'
    $valHelpLinkUrl = (string) (GETPOST('MOKOCRM_HELPLINK_URL', 'restricthtml') ?? '');

    if ($valHelpLinkUrl === '') {
        $valHelpLinkUrl = 'https://mokoconsulting.tech/search?q=CRM%3A%20';
    }

    $error = 0;
    $db->begin();

    // Save toggle (entity=0)
    if (!dolibarr_set_const($db, 'MOKOCRM_HELPLINK', $valHelpLink, 'yesno', 0, '', $entityForSave)) $error++;

    // Only save/update the URL if the feature is enabled; otherwise preserve existing value
    if ($valHelpLink === '1') {
        if (!dolibarr_set_const($db, 'MOKOCRM_HELPLINK_URL', $valHelpLinkUrl, 'chaine', 0, '', $entityForSave)) $error++;
    }

    if (!$error) {
        $db->commit();
        setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
    } else {
        $db->rollback();
        setEventMessages($langs->trans('ErrorFailedToSave'), null, 'errors');
    }

    // Avoid resubmission
    header('Location: '.$_SERVER['PHP_SELF'].(empty($backtopage) ? '' : '?backtopage='.urlencode($backtopage)));
    exit;
}

/*
 * View
 */

$form = new Form($db);

// Compute current values with defaults (default helplink = YES)
$helpEnabled = (getDolGlobalInt('MOKOCRM_HELPLINK', 1) ? 1 : 0);
$helpUrl     = (string) getDolGlobalString('MOKOCRM_HELPLINK_URL', 'https://mokoconsulting.tech/search?q=CRM%3A%20');

// Dynamic help URL only when enabled
$help_url = $helpEnabled ? ($helpUrl !== '' ? $helpUrl : 'https://mokoconsulting.tech/search?q=CRM%3A%20') : '';

$title = $langs->trans('MokoCRMSetup');
llxHeader('', $title, $help_url, '', 0, 0, '', '', '', 'mod-mokocrm page-admin');

// Subheader & breadcrumbs
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

// Tabs
$head = mokocrmAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $title, -1, 'mokocrm@mokocrm');

// Intro
print '<div class="opacitymedium">'.$langs->trans('MokoCRMSetupPage').'</div>';
print '<br>';

// Settings form
print '<form action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'" method="post">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="save">';
if (!empty($backtopage)) {
    print '<input type="hidden" name="backtopage" value="'.dol_escape_htmltag($backtopage).'">';
}

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

// Section header
print '<tr class="liste_titre">';
print '<th>'.$langs->trans('Parameter').'</th>';
print '<th>'.$langs->trans('Value').'</th>';
print '<th class="right">'.$langs->trans('Note').'</th>';
print '</tr>';

// Help link toggle (defaults to yes if not set)
print '<tr class="oddeven">';
print '<td>'.$langs->trans('HelpLinkEnabled').'</td>';
print '<td>'.$form->selectyesno('MOKOCRM_HELPLINK', $helpEnabled, 1).'</td>';
print '<td class="right opacitymedium">'.$langs->trans('IfEnabledAHelpLinkWillShow').'</td>';
print '</tr>';

// Help link URL row — VISIBLE ONLY when enabled
$initialDisplay = $helpEnabled ? '' : ' style="display:none"';
print '<tr class="oddeven" id="row_MOKOCRM_HELPLINK_URL"'.$initialDisplay.'>';
print '<td>'.$langs->trans('HelpLinkURL').'</td>';
print '<td><input type="text" class="minwidth500" name="MOKOCRM_HELPLINK_URL" id="MOKOCRM_HELPLINK_URL" value="'.dol_escape_htmltag($helpUrl).'"></td>';
print '<td class="right opacitymedium">'.$langs->trans('BaseURLForHelpSearch').'</td>';
print '</tr>';

print '</table>';
print '</div>';

// Hooks: allow other modules to inject fields/buttons
$parameters = array();
$object = new stdClass();
$reshook = $hookmanager->executeHooks('formMokoCRMSetup', $parameters, $object, $action);
if ($reshook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

// Buttons
print '<div class="center">';
print '<input type="submit" class="button button-save smallpaddingimp" value="'.$langs->trans('Save').'">';
print '</div>';

print '</form>';

// Client-side dependency: show/hide URL row based on toggle
print '<script>
(function(){
    function toggleHelpUrlRow(){
        var sel = document.querySelector(\'select[name="MOKOCRM_HELPLINK"]\');
        var row = document.getElementById("row_MOKOCRM_HELPLINK_URL");
        if (!sel || !row) return;
        row.style.display = String(sel.value) === "1" ? "" : "none";
    }
    document.addEventListener("DOMContentLoaded", function(){
        toggleHelpUrlRow();
        var sel = document.querySelector(\'select[name="MOKOCRM_HELPLINK"]\');
        if (sel) sel.addEventListener("change", toggleHelpUrlRow);
    });
})();
</script>';

// Close card
print dol_get_fiche_end();

// Footer
llxFooter();
$db->close();
