<!--
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
FILE: CHANGELOG.md
VERSION: 02.05.05
BRIEF: Changelog notes for the MokoDoliTools Dolibarr module
PATH: /CHANGELOG.md
========================================================================
-->

# CHANGELOG FOR MODULE MOKODOLITOOLS

## 02.05.05
- Consolidated security access
- Added missing language
- Removed Adminer

## 02.05.05
- 
- Rebrand from MokoCRM to MokoDoliTools
- Removed "$this->conflictwith"

## 02.05
- Added blank "index.php" and "index.html" in all folders to harden security
- Consolidated admin/changelog.php into "admin/about.php"
- Consolidated Secure and Repair tools to "/admin/tools.php"
- Improved Secure and Repair handling in "/admin/tools.php"
- Cleanup "/core/modules/modMokoDoliTools.class.php"
- Updated and organized language file
- Disabled modMailings (Mass Mailing), modWebsites, (Websites), modModuleBuilder (ModuleBuilder)
- Hide MokoDoliTools on entities not equal to 1
- Applied persistant settings across all entities
- Added "MOKODOLITOOLS_HELPLINK" constant to /admin/settings.php, default: "true"
- Added "MOKODOLITOOLS_HELPLINK_URL" constant to /admin/settings.php, default: "https://mokoconsulting.tech/search?q=CRM%3A%20"

## 02.04
- Changed Fontawesome 6 method from JS to CSS for compatiability
- Incorporated Dolibar custom FA icons

## 02.03
- Added Database Admin tool, based on Adminer
- Added Granular Permissions
- Changed module setup landing to About.php, because no Setup required
- Added confirmations to Secure tool
- Added Warnings to Database Admin and Repair tool
- -Minor CSS fixes

## 02.02
- Added Secure Page
- Added GeoIP Databases

## 02.00
- Consolidated Mokorepairlinks, MokoHelpLink, MokoDymoLables, Mokofontaweseome6 down to single module

## 1.0
 - Initial version
