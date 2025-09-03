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
FILE: mokodolitools.css.php
VERSION: 02.05.05
BRIEF: Dynamic CSS for MokoDoliTools UI tokens, utilities, icons, and admin tooling views
PATH: htdocs/custom/mokodolitools/mokodolitools.css.php
NOTE: Serves CSS with cache headers; colors can be overridden via Dolibarr conf constants
VARIABLES: MOKODOLITOOLS_PRIMARY_COLOR, MOKODOLITOOLS_ACCENT_COLOR, MOKODOLITOOLS_NEUTRAL_COLOR, MOKODOLITOOLS_RADIUS, MOKODOLITOOLS_HELPLINK
========================================================================
*/

// ---- Dolibarr boot flags (CSS, no menus) --------------------------------------------------------
if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML')) define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', 1);
if (!defined('NOREQUIRESOC'))  define('NOREQUIRESOC', 1);
if (!defined('NOREQUIRETRAN')) define('NOREQUIRETRAN', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN'))        define('NOLOGIN', 1); // may be used on login page
if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', 1);

// Keep DB load enabled (we want $conf)
// if (!defined('NOREQUIREDB')) define('NOREQUIREDB', 1);

// Be permissive with caching at PHP level (web server headers may append too)
session_cache_limiter('public');

// ---- Load Dolibarr environment ------------------------------------------------------------------
$res = 0;
// Try the usual relative paths from /htdocs/custom/mokodolitools/
if (!$res && file_exists(__DIR__ . '/../../main.inc.php')) $res = (include __DIR__ . '/../../main.inc.php');
if (!$res && file_exists(__DIR__ . '/../main.inc.php'))   $res = (include __DIR__ . '/../main.inc.php');
// Try using web root hints
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT']) && file_exists($_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php')) {
    $res = (include $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php');
}
if (!$res && !empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/main.inc.php')) {
    $res = (include $_SERVER['DOCUMENT_ROOT'] . '/main.inc.php');
}
// Fallback: climb up to find main.inc.php
if (!$res) {
    $d = __DIR__;
    for ($i = 0; $i < 6; $i++) {
        $try = $d . '/main.inc.php';
        if (file_exists($try)) { $res = (include $try); break; }
        $d = dirname($d);
    }
}
if (!$res) { die('Include of main fails'); }

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

// ---- HTTP headers -------------------------------------------------------------------------------
header('Content-Type: text/css; charset=UTF-8');

// Helper to safely extract Dolibarr global conf values
function mc_conf($key, $default = '') {
    global $conf;
    if (isset($conf->global->$key) && $conf->global->$key !== '') return (string) $conf->global->$key;
    if (function_exists('getDolGlobalString')) return (string) getDolGlobalString($key, $default);
    return (string) $default;
}

// Colors and tokens (override via conf constants if present)
$primary = mc_conf('MOKODOLITOOLS_PRIMARY_COLOR', '#0d6efd'); // Bootstrap-like blue
$accent  = mc_conf('MOKODOLITOOLS_ACCENT_COLOR',  '#dc3545'); // Bootstrap-like red
$neutral = mc_conf('MOKODOLITOOLS_NEUTRAL_COLOR', '#6c757d'); // Bootstrap-like gray
$radius  = mc_conf('MOKODOLITOOLS_RADIUS', '12px');

$helplink_enabled = (int) mc_conf('MOKODOLITOOLS_HELPLINK', '1'); // 1=show, 0=hide

$logoUrl = (defined('DOL_URL_ROOT') ? DOL_URL_ROOT : '') . '/custom/mokodolitools/img/logo.png';

// Build CSS content --------------------------------------------------------------------------------
$css = <<<CSS
/* MokoDoliTools dynamic CSS (v02.05.05) */
:root {--mokodolitools-primary: {$primary};--mokodolitools-accent: {$accent};--mokodolitools-neutral: {$neutral};--mokodolitools-radius: {$radius};}
@media (prefers-color-scheme: dark){:root{--mokodolitools-neutral:#adb5bd;}}

/* Basic utilities */
.mokodolitools-hidden{display:none!important}
.mokodolitools-visually-hidden{position:absolute!important;height:1px;width:1px;overflow:hidden;clip:rect(1px,1px,1px,1px);white-space:nowrap}
.mokodolitools-rounded{border-radius:var(--mokodolitools-radius)}

/* Brand elements */
.mokodolitools-badge{display:inline-flex;align-items:center;gap:.35rem;font-weight:600;padding:.25rem .5rem;border-radius:999px;background:var(--mokodolitools-primary);color:#fff}
.mokodolitools-badge::before{content:"";width:1rem;height:1rem;background:url('{$logoUrl}') no-repeat center/contain;display:inline-block;filter:drop-shadow(0 0 0 rgba(0,0,0,.1))}

/* Buttons */
.mokodolitools-btn{appearance:none;border:none;cursor:pointer;padding:.5rem .875rem;border-radius:var(--mokodolitools-radius);background:var(--mokodolitools-primary);color:#fff;font-weight:600}
.mokodolitools-btn:hover{filter:brightness(1.05)}
.mokodolitools-btn:active{filter:brightness(.95)}
.mokodolitools-btn--accent{background:var(--mokodolitools-accent)}

/* Cards / panels */
.mokodolitools-card{border:1px solid rgba(0,0,0,.08);border-radius:var(--mokodolitools-radius);padding:1rem;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)}
html.theme-dark .mokodolitools-card,body.dark .mokodolitools-card{background:#1d1f23;border-color:rgba(255,255,255,.08)}

/* Admin Tools grid (for admin/tools.php) */
.mokodolitools-tools-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;align-items:stretch}
.mokodolitools-tools-grid .tool{padding:1rem;border-radius:var(--mokodolitools-radius);border:1px dashed rgba(0,0,0,.15);background:rgba(13,110,253,.03)}
.mokodolitools-tools-grid .tool h3{margin:.25rem 0 .5rem;font-size:1.05rem}
.mokodolitools-tools-grid .tool p{margin:0;color:var(--mokodolitools-neutral)}

/* About header */
.mokodolitools-about-header{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem}
.mokodolitools-about-logo{width:42px;height:42px;background:url('{$logoUrl}') no-repeat center/contain;border-radius:10px}
.mokodolitools-about-title{font-weight:700}

/* Modal-friendly links */
.mokodolitools-modal-link{text-decoration:underline;cursor:pointer}
CSS;

// Inline help-link visibility (controlled by MOKODOLITOOLS_HELPLINK)
$css .= ".mokodolitools-helplink{display:" . ($helplink_enabled ? 'inline-flex' : 'none') . ";align-items:center;gap:.35rem}\n";
$css .= ".mokodolitools-helplink a{color:var(--mokodolitools-primary);text-decoration:underline dotted}\n";

// --- User-supplied icon tweaks and print modal ----------------------------------------------------
$css .= <<<CSS

/* Ensure inline Font Awesome SVGs don't inherit unwanted colors */
.svg-inline--fa{color:unset!important}

/* Links opening in new tab/window (shows square-arrow icon) */
a[target="_blank"]:after{font-family:"Font Awesome 6 Free";font-weight:900;content:"\f35d";margin-left:.3em;font-size:.8em;vertical-align:middle}

/* Dolibarr feature icon mappings */
.fa.fa-dol-project:before{content:"\f542"}
.fa.fa-weather-level1:before{font-family:"Font Awesome 5 Free"!important;content:"\f6c4";color:#bc9526}
.fa.fa-dol-action:before{content:"\f073"}
.fa.fa-dol-propal:before,.fa.fa-dol-supplier_proposal:before{content:"\f573"}
.fa.fa-dol-facture:before,.fa.fa-dol-invoice_supplier:before{content:"\f571"}
.fa.fa-dol-commande:before,.fa.fa-dol-order_supplier:before{content:"\f570"}
.fa.fa-dol-contrat:before{content:"\f1e6"}
.fa.fa-dol-ticket:before{content:"\f3ff"}
.fa.fa-dol-bank_account:before{content:"\f19c"}
.fa.fa-dol-member:before{content:"\f0c0"}
.fa.fa-dol-expensereport:before{content:"\f555"}
.fa.fa-dol-holiday:before{content:"\f5ca"}
.fa.fa-dol-cubes:before{content:"\f1b3"}

/* Print overlay modal */
.printModal{font-family:sans-serif;display:flex;text-align:center;font-weight:300;font-size:30px;left:0;top:0;position:absolute;color:#045fb4;width:100%;height:100%;background-color:hsla(0,0%,100%,.9)}
.printClose{position:absolute;right:10px;top:10px}
.printClose:before{content:"\00D7";font-family:Helvetica Neue,sans-serif;font-weight:100;line-height:1px;padding-top:.5em;display:block;font-size:2em;text-indent:1px;overflow:hidden;height:1.25em;width:1.25em;text-align:center;cursor:pointer}
.printSpinner{margin-top:3px;margin-left:-40px;position:absolute;display:inline-block;width:25px;height:25px;border:2px solid #045fb4;border-radius:50%;animation:spin .75s linear infinite}
.printSpinner:after,.printSpinner:before{left:-2px;top:-2px;display:none;position:absolute;content:"";width:inherit;height:inherit;border:inherit;border-radius:inherit}
.printSpinner,.printSpinner:after,.printSpinner:before{display:inline-block;border-color:#045fb4 transparent transparent;animation-duration:1.2s}
.printSpinner:before{transform:rotate(120deg)}
.printSpinner:after{transform:rotate(240deg)}
@keyframes spin{0%{transform:rotate(0deg)}to{transform:rotate(1turn)}}
CSS;

// ---- Minify (optional via ?min=1) ----------------------------------------------------------------
$minify = isset($_GET['min']) && (string)$_GET['min'] !== '0';
if ($minify) {
    // Very conservative minification
    $css = preg_replace('!/\*.*?\*/!s', '', $css); // strip comments
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{};:,])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    $css = trim($css);
}

// ---- Caching -------------------------------------------------------------------------------------
$lastmod_time = @filemtime(__FILE__) ?: time();
$lastmod_http = gmdate('D, d M Y H:i:s', $lastmod_time) . ' GMT';
$etag = '"' . md5(__FILE__ . $lastmod_time . $primary . $accent . $neutral . $radius . $helplink_enabled) . '"';

header('Cache-Control: public, max-age=86400, stale-while-revalidate=604800');
header('Last-Modified: ' . $lastmod_http);
header('ETag: ' . $etag);

if ((isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) ||
    (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && trim($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $lastmod_http)) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}

// ---- Output --------------------------------------------------------------------------------------
echo $css;
