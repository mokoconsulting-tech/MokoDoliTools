<?php
/*
 * Copyright (C) 2025 Jonathan Miller <jmiller@mokoconsulting.tech>
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
 * Library JavaScript to rewrite Dolibarr wiki links to custom search URLs.
 */

// Dolibarr boot flags
define('NOREQUIREUSER', 1);
define('NOREQUIREDB', 0);
define('NOREQUIRESOC', 1);
define('NOREQUIRETRAN', 1);
define('NOCSRFCHECK', 1);
define('NOTOKENRENEWAL', 1);
define('NOLOGIN', 1);
define('NOREQUIREMENU', 1);
define('NOREQUIREHTML', 1);
define('NOREQUIREAJAX', 0);

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/../main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $db, $conf;

// Load help URL from DB
if (empty($search_url_base)) {
	$search_url_base = 'https://mokoconsulting.tech/search?q=CRM%3A%20';
}
$search_url_base = rtrim($search_url_base, '?&');

// JS Headers
header('Content-Type: application/javascript');
if (empty($dolibarr_nocache)) {
	header('Cache-Control: max-age=3600, public, must-revalidate');
} else {
	header('Cache-Control: no-cache');
}
?>

/* MokoCRM JS: Rewrites Dolibarr wiki links to a custom search destination */
document.addEventListener("DOMContentLoaded", function () {
  const anchors = document.querySelectorAll('a[href]');
  const wikiHttp  = "http://wiki.dolibarr.org/index.php/";
  const wikiHttps = "https://wiki.dolibarr.org/index.php/";
  const searchURLBase = <?php echo json_encode($search_url_base); ?>;

  anchors.forEach(link => {
	const href = link.getAttribute('href');
	let query = null;

	if (href.startsWith(wikiHttp)) {
	  query = href.substring(wikiHttp.length);
	} else if (href.startsWith(wikiHttps)) {
	  query = href.substring(wikiHttps.length);
	}

	if (query) {
	  const queryWithSpaces = query.replace(/_/g, ' ');
	  link.setAttribute('href', searchURLBase + encodeURIComponent(queryWithSpaces));
	}
  });
});
