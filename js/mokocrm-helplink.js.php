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
INGROUP: MokoCRM
FILE: helplink-rewriter.js.php
VERSION: 02.05.01
BRIEF: Rewrites Dolibarr wiki links to a custom search URL, controlled by settings.
PATH: htdocs/custom/mokocrm/js/helplink-rewriter.js.php
NOTE: Uses MOKOCRM_HELPLINK (enable) and MOKOCRM_HELPLINK_URL (base).
VARIABLES:
========================================================================
*/

// Dolibarr boot flags
define('NOREQUIREUSER', 1);
define('NOREQUIREDB', 0);       // We need DB to read conf->global
define('NOREQUIRESOC', 1);
define('NOREQUIRETRAN', 1);
define('NOCSRFCHECK', 1);
define('NOTOKENRENEWAL', 1);
define('NOLOGIN', 1);
define('NOREQUIREMENU', 1);
define('NOREQUIREHTML', 1);
define('NOREQUIREAJAX', 1);

// Load Dolibarr environment (robust path detection)
$res = 0;
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
		$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
}
$tmp  = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] === $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, $i + 1).'/main.inc.php'))   $res = @include substr($tmp, 0, $i + 1).'/main.inc.php';
if (!$res && $i > 0 && file_exists(substr($tmp, 0, $i + 1).'/../main.inc.php')) $res = @include substr($tmp, 0, $i + 1).'/../main.inc.php';
if (!$res && file_exists('../../main.inc.php'))   $res = @include '../../main.inc.php';
if (!$res && file_exists('../../../main.inc.php')) $res = @include '../../../main.inc.php';
if (!$res) { header('Content-Type: application/javascript; charset=UTF-8'); die('/* main.inc.php include failed */'); }

global $conf;

// --- Settings -----------------------------------------------------------
$default_base = 'https://mokoconsulting.tech/search?q=CRM%3A%20';

$enabled_val = isset($conf->global->MOKOCRM_HELPLINK) ? (string)$conf->global->MOKOCRM_HELPLINK : '1';
$enabled_str = strtolower(trim($enabled_val));
$enabled = in_array($enabled_str, array('1','true','yes','on','y'), true);

$raw_base = (isset($conf->global->MOKOCRM_HELPLINK_URL) && is_string($conf->global->MOKOCRM_HELPLINK_URL) && $conf->global->MOKOCRM_HELPLINK_URL !== '')
		? trim($conf->global->MOKOCRM_HELPLINK_URL)
		: $default_base;

if (!filter_var($raw_base, FILTER_VALIDATE_URL)) {
		$raw_base = $default_base;
}

// --- Headers ------------------------------------------------------------
header('Content-Type: application/javascript; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
if (empty($dolibarr_nocache)) {
		header('Cache-Control: max-age=3600, public, must-revalidate');
} else {
		header('Cache-Control: no-cache');
}
?>

/* MokoCRM HelpLink Rewriter (settings-aware) */
(function () {
	"use strict";
	const CONFIG = {
		enabled: <?php echo $enabled ? 'true' : 'false'; ?>,
		base: <?php echo json_encode($raw_base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
	};

	if (!CONFIG.enabled) {
		// Feature disabled via MOKOCRM_HELPLINK
		return;
	}

	const WIKI_RX = /^https?:\/\/wiki\.dolibarr\.org\/index\.php\/?/i;
	const MARK_ATTR = "data-mokocrm-wiki-rewritten";

	function rewriteLinks(scope) {
		const root = scope && scope.querySelectorAll ? scope : document;
		const candidates = root.querySelectorAll('a[href^="http://wiki.dolibarr.org/index.php/"], a[href^="https://wiki.dolibarr.org/index.php/"]');

		candidates.forEach((link) => {
			if (link.getAttribute(MARK_ATTR) === "1") return;

			const href = link.getAttribute("href") || "";
			if (!WIKI_RX.test(href)) return;

			// Extract wiki title, normalize underscores to spaces
			let topic = href.replace(WIKI_RX, "");
			try { topic = decodeURIComponent(topic); } catch (e) { /* ignore decode errors */ }
			topic = topic.replace(/_/g, " ");

			try {
				// Build a safe URL with (or without) existing q= prefix value
				const u = new URL(CONFIG.base);
				const prefix = u.searchParams.get("q") || "";
				u.searchParams.set("q", prefix + topic);

				link.setAttribute("href", u.toString());
				link.setAttribute(MARK_ATTR, "1");
			} catch (e) {
				// If base is somehow not absolute (shouldn’t happen), fall back to simple concat
				const sep = CONFIG.base.includes("?") ? (CONFIG.base.includes("q=") ? "" : (CONFIG.base.endsWith("&") ? "" : "&q=")) : "?q=";
				link.setAttribute("href", CONFIG.base + sep + encodeURIComponent(topic));
				link.setAttribute(MARK_ATTR, "1");
			}
		});
	}

	// Initial pass
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", function () { rewriteLinks(document); });
	} else {
		rewriteLinks(document);
	}

	// Observe dynamic DOM changes (Ajax, SPA-like updates)
	const mo = new MutationObserver((mutations) => {
		for (const m of mutations) {
			for (const n of m.addedNodes) {
				if (n && n.nodeType === 1) rewriteLinks(n);
			}
		}
	});
	mo.observe(document.documentElement || document.body, { childList: true, subtree: true });
})();
