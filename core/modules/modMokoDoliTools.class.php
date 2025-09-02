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
 along with this program. If not, see https://www.gnu.org/licenses/

FILE INFORMATION
 DEFGROUP:   Dolibarr
 INGROUP:    MokoDoliTools
 FILE:       core/modules/modMokoDoliTools.class.php
 VERSION:    02.05.02
 BRIEF:      Module descriptor for the MokoDoliTools Dolibarr module
 PATH:       htdocs/custom/mokodolitools/core/modules/modMokoDoliTools.class.php
 VARIABLES:
  - numero (int): Unique module id (reserve on Dolibarr wiki).
  - rights_class (string): Base key namespace for permissions/menus (e.g., $user->rights->mokodolitools->...).
  - family (string): Module family bucket; here 'mokoconsulting'.
  - module_position (string): Sort order within family (e.g., '01').
  - name (string): Human-friendly module name (class name without 'mod').
  - description / descriptionlong (string): Language keys resolved at runtime.
  - editor_name / editor_url / editor_squarred_logo (string): Vendor metadata.
  - version (string): Module version (xx.yy.zz).
  - const_name (string): llx_const key to store enable/disable state.
  - picto (string): Icon key ('logo@mokodolitools' uses img/logo.png).
  - module_parts (array): Global features injected by the module (see below).
  - config_page_url (string[]): Admin pages (module setup pages).
  - hidden (int|bool): If true/1, module hidden in setup.
  - depends / requiredby / conflictwith (string[]): Module relations.
  - langfiles (string[]): Language packs ('mokodolitools@mokodolitools' -> langs/en_US/mokodolitools.lang).
  - phpmin (array): Minimum PHP version (major, minor).
  - need_dolibarr_version (array): Minimum Dolibarr version (major, minor or -3 for >=).
  - rights (array[]): Permission matrix (see class comments).
  - menu (array[]): Menu entries (Admin Tools: Dbadmin, Tools).
*/

include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 * Module descriptor for MokoDoliTools.
 *
 * Registers module identity, global CSS/JS assets, permissions, and menus.
 * Matches project rules: no sql/, no class/, only en_US language pack.
 */
class modMokoDoliTools extends DolibarrModules
{
	/**
	 * Constructor. Defines names, constants, directories, permissions, menus.
	 *
	 * @param DoliDB $db Database handler.
	 */
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;

		// --- Identity -------------------------------------------------------
		$this->numero          = 185051;              // Reserve this id on Dolibarr wiki if distributing.
		$this->rights_class    = 'mokodolitools';           // Base rights key: $user->rights->mokodolitools->...
		$this->family          = 'mokoconsulting';    // Custom family bucket
		$this->module_position = '01';                // Sort order within that family
		$this->familyinfo      = array(
			'mokoconsulting' => array(
				'position' => '01',
				'label'    => $langs->trans("Moko Consulting")
			)
		);
		$this->name = preg_replace('/^mod/i', '', get_class($this)); // "MokoDoliTools"

		// --- Descriptions (language keys) ----------------------------------
		$this->description     = 'ModuleMokoDoliToolsDesc';
		$this->descriptionlong = 'ModuleMokoDoliToolsDescLong';

		// --- Vendor metadata ------------------------------------------------
		$this->editor_name          = 'Moko Consulting';
		$this->editor_url           = 'https://mokoconsulting.tech';
		$this->editor_squarred_logo = 'logo.png@mokodolitools'; // img/logo.png in module

		// --- Version & state -----------------------------------------------
		$this->version    = '02.05.02';
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);  // e.g., MAIN_MODULE_MOKODOLITOOLS

		// --- Visual identity -----------------------------------------------
		$this->picto = 'logo@mokodolitools'; // Use module icon (module/img/logo.png)

		// --- Feature flags (with global CSS/JS) -----------------------------
		$this->module_parts = array(
			'triggers'          => 0,
			'login'             => 0,
			'substitutions'     => 0,
			'menus'             => 1,     // We add admin tools left menus.
			'tpl'               => 0,
			'barcode'           => 0,
			'models'            => 0,     // No PDF/ODT models.
			'printing'          => 0,
			'theme'             => 0,
			'css'               => array(
				'/mokodolitools/css/mokodolitools.css.php',
				'/mokodolitools/css/fontawesome/5.15.4/all.css.php',
				'/mokodolitools/css/fontawesome/6.7.2/all.css.php',
			),
			'js'                => array(
				'/mokodolitools/js/mokodolitools-helplink.js.php',
			),
			'hooks'             => array(),
			'moduleforexternal' => 0,
			'websitetemplates'  => 0,
			'captcha'           => 0,
		);

		// --- Admin pages (module setup pages) -------------------------------
		$this->config_page_url = array(
			'setup.php@mokodolitools',
		);

		// --- Visibility / dependencies -------------------------------------
		// Hide if disabled via const OR if current entity is not 1
		$currentEntity = isset($conf->entity) ? (int) $conf->entity : 1;
		$this->hidden  = (getDolGlobalInt('MODULE_MOKODOLITOOLS_DISABLED') || $currentEntity !== 1);

		$this->depends      = array();   // e.g., array('modSociete')
		$this->conflictwith = array();
		$this->requiredby   = array();   // (Removed dynamic discovery)

		// --- Lang packs (en_US only per project) ----------------------------
		$this->langfiles = array('mokodolitools@mokodolitools');

		// --- Requirements ---------------------------------------------------
		$this->phpmin                = array(7, 1);
		$this->need_dolibarr_version = array(19, -3);
		$this->need_javascript_ajax  = 0;

		// --- Activation messages -------------------------------------------
		$this->warnings_activation     = array();
		$this->warnings_activation_ext = array();

		// Ensure conf object exists even if module disabled
		if (!isModEnabled('mokodolitools')) {
			$conf->mokodolitools = new stdClass();
			$conf->mokodolitools->enabled = 0;
		}

		// --- Tabs / Dictionaries / Widgets / Cron (unused per rules) -------
		$this->tabs         = array();
		$this->dictionaries = array();
		$this->boxes        = array();
		$this->cronjobs     = array();

		// --- Permissions ----------------------------------------------------
		// Row shape: [0] id, [1] label(lang key), [2] unused, [3] grant to admin, [4] family, [5] action
		$this->rights = array();

		// Start rights index at 0
		$r = 0;

		// Base id block for rights (Dolibarr convention: module_id * 100)
		$base = ($this->numero * 100);

		// CONSOLIDATED: Tools access
		$this->rights[$r][0] = $base + $r;
		$this->rights[$r][1] = 'RightMokoDoliToolsToolsAccess';
		$this->rights[$r][2] = null;
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'tools';
		$this->rights[$r][5] = 'access';
		$r++;

		// Database Admin tool access
		$this->rights[$r][0] = $base + $r;
		$this->rights[$r][1] = 'RightMokoDoliToolsDbadminAccess';
		$this->rights[$r][2] = null;
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'dbadmin';
		$this->rights[$r][5] = 'access';
		$r++;

		// --- Menus ----------------------------------------------------------
		$this->menu = array();

		$m = $this->numero;

		// Admin Tools > Dbadmin
		$this->menu[$m++] = array(
			'fk_menu'   => 'fk_mainmenu=home,fk_leftmenu=admintools',
			'type'      => 'left',
			'titre'     => 'MenuMokoDoliToolsDbadmin',
			'mainmenu'  => '',
			'leftmenu'  => 'admintools',
			'url'       => '/custom/mokodolitools/admin/dbadmin.php?mainmenu=home&amp;leftmenu=admintools',
			'langs'     => 'mokodolitools@mokodolitools',
			'position'  => 1000 + $m,
			'enabled'   => '$conf->mokodolitools->enabled',
			'perms'     => '$user->rights->mokodolitools->dbadmin->access',
			'target'    => '',
			'user'      => 0,
		);

		// Admin Tools > Tools
		$this->menu[$m++] = array(
			'fk_menu'   => 'fk_mainmenu=home,fk_leftmenu=admintools',
			'type'      => 'left',
			'titre'     => 'MenuMokoDoliToolsTools',
			'mainmenu'  => '',
			'leftmenu'  => 'admintools',
			'url'       => '/custom/mokodolitools/admin/tools.php?mainmenu=home&amp;leftmenu=admintools',
			'langs'     => 'mokodolitools@mokodolitools',
			'position'  => 1000 + $m,
			'enabled'   => '$conf->mokodolitools->enabled',
			'perms'     => '$user->rights->mokodolitools->tools->access',
			'target'    => '',
			'user'      => 0,
		);
	}

	/**
	 * Called when module is enabled.
	 * Adds constants, boxes, permissions and menus into Dolibarr database.
	 * Also force-disables conflicting modules for **all entities** by setting entity=0,
	 * ensures GEOIP path uses conf.php variables, and persists MOKODOLITOOLS_* from entity 1 to global defaults.
	 *
	 * @param  string $options Options when enabling module ('', 'noboxes')
	 * @return int             1 if OK, <=0 if KO
	 */
	public function init($options = '')
	{
		// Reset permissions/menus before (re)init
		$this->remove($options);

		require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

		// --- Set GEOIP path using conf.php variables (entity=0) ---
		global $dolibarr_main_document_root, $dolibarr_main_url_root_alt;

		$docRoot = is_string($dolibarr_main_document_root) ? rtrim($dolibarr_main_document_root, "/") : '';
		$altRoot = $dolibarr_main_url_root_alt;

		// $dolibarr_main_url_root_alt can be an array; prefer entry ending with '/custom'
		if (is_array($altRoot)) {
			$picked = '';
			foreach ($altRoot as $entry) {
				if (preg_match('~/custom/?$~', (string) $entry)) {
					$picked = $entry;
					break;
				}
				if ($picked === '' && is_string($entry)) {
					$picked = $entry;
				}
			}
			$altRoot = $picked;
		}
		$altRoot = is_string($altRoot) ? trim($altRoot, "/") : 'custom';

		$geoipPath = $docRoot . '/' . $altRoot . '/mokodolitools/geoip/GeoLite2-Country.mmdb';
		$geoipPath = preg_replace('#/+#', '/', $geoipPath); // normalize duplicate slashes

		dolibarr_set_const($this->db, 'GEOIPMAXMIND_COUNTRY_DATAFILE', $geoipPath, 'chaine', 0, '', 0);

		// --- MokoDoliTools helplink settings (entity=0) ---
		dolibarr_set_const($this->db, 'MOKODOLITOOLS_HELPLINK', 1, 'yesno', 0, '', 0);
		dolibarr_set_const($this->db, 'MOKODOLITOOLS_HELPLINK_URL', 'https://mokoconsulting.tech/search?q=CRM%3A%20', 'chaine', 0, '', 0);

		// --- Persist entity 1 MOKODOLITOOLS_* settings into global defaults (entity=0) ---
		$this->persistEntity1SettingsToAllEntities();

		$sql = array();
		return $this->_init($sql, $options);
	}

	/**
	 * Called when module is disabled.
	 * Removes constants, boxes and permissions from database.
	 * Data directories are not deleted.
	 *
	 * @param  string $options Options when disabling module
	 * @return int             1 if OK, <=0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

	/**
	 * Copy any MOKODOLITOOLS_* constants set in entity=1 into entity=0 (global default),
	 * so settings chosen in entity 1 persist across all entities unless overridden.
	 */
	private function persistEntity1SettingsToAllEntities()
	{
		$sql = "SELECT name, value, type, visible, note
				  FROM ".MAIN_DB_PREFIX."const
				 WHERE entity = 1
				   AND name LIKE 'MOKODOLITOOLS\\_%' ESCAPE '\\\\'";

		$res = $this->db->query($sql);
		if (!$res) {
			return;
		}

		require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

		while ($obj = $this->db->fetch_object($res)) {
			$name    = (string) $obj->name;
			$value   = isset($obj->value) ? $obj->value : '';
			$type    = (!empty($obj->type) ? $obj->type : 'chaine');
			$visible = (isset($obj->visible) ? (int) $obj->visible : 0);
			$note    = (isset($obj->note) ? $obj->note : '');

			dolibarr_set_const($this->db, $name, $value, $type, $visible, $note, 0); // entity=0
		}

		$this->db->free($res);
	}
}
