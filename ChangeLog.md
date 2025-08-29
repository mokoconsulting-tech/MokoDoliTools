# CHANGELOG FOR MODULE MOKOCRM

## 2.5
- Added blank "index.php" and "index.html" in all folders to harden security
- Consolidated admin/changelog.php into "admin/about.php"
- Consolidated Secure and Repair tools to "/admin/tools.php"
- Improved Secure and Repair handling in "/admin/tools.php"
- Cleanup "/core/modules/modMokoCRM.class.php"
- Updated and organized language file
- Disabled modMailings (Mass Mailing), modWebsites, (Websites), modModuleBuilder (ModuleBuilder)
- Hide MokoCRM on entities not equal to 1
- Applied persistant settings across all entities
- Added "MOKOCRM_HELPLINK" constant to /admin/settings.php, default: "true"
- Added "MOKOCRM_HELPLINK_URL" constant to /admin/settings.php, default: "https://mokoconsulting.tech/search?q=CRM%3A%20"

## 2.4
- Changed Fontawesome 6 method from JS to CSS for compatiability
- Incorporated Dolibar custom FA icons

## 2.3
- Added Database Admin tool, based on Adminer
- Added Granular Permissions
- Changed module setup landing to About.php, because no Setup required
- Added confirmations to Secure tool
- Added Warnings to Database Admin and Repair tool
- -Minor CSS fixes

## 2.2
- Added Secure Page
- Added GeoIP Databases

## 2.0
- Consolidated Mokorepairlinks, MokoHelpLink, MokoDymoLables, Mokofontaweseome6 down to single module

## 1.0
 - Initial version
