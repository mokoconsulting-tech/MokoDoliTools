<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
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
 * \file    mokocrm/css/mokocrm.css.php
 * \ingroup mokocrm
 * \brief   CSS file for module MokoCRM.
 */

//if (!defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (!defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
//if (!defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
//if (!defined('NOCSRFCHECK'))   define('NOCSRFCHECK', 1);		// Should be disable only for special situation
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1); // File must be accessed by logon page so without login
}
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

session_cache_limiter('public');
// false or '' = keep cache instruction added by server
// 'public'  = remove cache instruction added by server
// and if no cache-control added later, a default cache delay (10800) will be added by PHP.

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/../main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/../main.inc.php";
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

// Load user to have $user->conf loaded (not done by default here because of NOLOGIN constant defined) and load permission if we need to use them in CSS
/*if (empty($user->id) && !empty($_SESSION['dol_login'])) {
	$user->fetch('',$_SESSION['dol_login']);
	$user->loadRights();
}*/


// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) {
	header('Cache-Control: max-age=10800, public, must-revalidate');
} else {
	header('Cache-Control: no-cache');
}
?>

.svg-inline--fa {
	color: unset!important;
}
/* Links opening in new tab/window */
a[target="_blank"]:after {
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
  content: "\f35d"; /* fa-up-right-from-square */
  margin-left: 0.3em;
  font-size: 0.8em;
  vertical-align: middle;
}
.fa.fa-dol-project:before {
	content: "\f542";
}
.fa.fa-weather-level1:before {
	font-family: "Font Awesome 5 Free"!important;
	content: "\f6c4";
  color: #bc9526;
}
.fa.fa-dol-action:before {
	content: "\f073";
}
.fa.fa-dol-propal:before,
.fa.fa-dol-supplier_proposal:before {
	content: "\f573";
}
.fa.fa-dol-facture:before,
.fa.fa-dol-invoice_supplier:before {
	content: "\f571";
}
.fa.fa-dol-project:before {
	content: "\f542";
}
.fa.fa-dol-commande:before,
.fa.fa-dol-order_supplier:before {
	content: "\f570";
}
.fa.fa-dol-contrat:before {
	content: "\f1e6";
}
.fa.fa-dol-ticket:before {
	content: "\f3ff";
}
.fa.fa-dol-bank_account:before {
	content: "\f19c";
}
.fa.fa-dol-member:before {
	content: "\f0c0";
}
.fa.fa-dol-expensereport:before {
	content: "\f555";
}
.fa.fa-dol-holiday:before {
	content: "\f5ca";
}
.fa.fa-dol-cubes:before {
	content: "\f1b3";
}

.printModal{font-family:sans-serif;display:flex;text-align:center;font-weight:300;font-size:30px;left:0;top:0;position:absolute;color:#045fb4;width:100%;height:100%;background-color:hsla(0,0%,100%,.9)}.printClose{position:absolute;right:10px;top:10px}.printClose:before{content:"\00D7";font-family:Helvetica Neue,sans-serif;font-weight:100;line-height:1px;padding-top:.5em;display:block;font-size:2em;text-indent:1px;overflow:hidden;height:1.25em;width:1.25em;text-align:center;cursor:pointer}.printSpinner{margin-top:3px;margin-left:-40px;position:absolute;display:inline-block;width:25px;height:25px;border:2px solid #045fb4;border-radius:50%;animation:spin .75s linear infinite}.printSpinner:after,.printSpinner:before{left:-2px;top:-2px;display:none;position:absolute;content:"";width:inherit;height:inherit;border:inherit;border-radius:inherit}.printSpinner,.printSpinner:after,.printSpinner:before{display:inline-block;border-color:#045fb4 transparent transparent;animation-duration:1.2s}.printSpinner:before{transform:rotate(120deg)}.printSpinner:after{transform:rotate(240deg)}@keyframes spin{0%{transform:rotate(0deg)}to{transform:rotate(1turn)}}