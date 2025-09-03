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
FILE: lib/mokodolitools.lib.php
VERSION: 02.05.05
BRIEF: Library with common functions for MokoDoliTools (admin header tabs).
PATH: htdocs/custom/mokodolitools/lib/mokodolitools.lib.php
NOTE:
VARIABLES:
========================================================================
*/

/**
 * Prepare admin pages header.
 *
 * @return array<int, array{0:string,1:string,2:string}>
 */
function mokodolitoolsAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load('mokodolitools@mokodolitools');

	$h = 0;
	$head = array();

	// Settings
	$head[$h][0] = dol_buildpath('/mokodolitools/admin/setup.php', 1);
	$head[$h][1] = $langs->trans('MOKODOLITOOLS_Settings');
	$head[$h][2] = 'settings';
	$h++;

	// Tools (Secure & Repair utilities live here)
	$head[$h][0] = dol_buildpath('/mokodolitools/admin/tools.php', 1);
	$head[$h][1] = $langs->trans('MOKODOLITOOLS_Tools');
	$head[$h][2] = 'tools';
	$h++;

	// About (includes changelog)
	$head[$h][0] = dol_buildpath('/mokodolitools/admin/about.php', 1);
	$head[$h][1] = $langs->trans('MOKODOLITOOLS_About');
	$head[$h][2] = 'about';
	$h++;

	// Extend with module-defined tabs
	complete_head_from_modules($conf, $langs, null, $head, $h, 'mokodolitools@mokodolitools');
	complete_head_from_modules($conf, $langs, null, $head, $h, 'mokodolitools@mokodolitools', 'remove');

	return $head;
}
