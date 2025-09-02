# CHANGELOG FOR MODULE MOKODOLITOOLS

## 02.05.02
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
