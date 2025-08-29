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
INGROUP: MokoCRM
FILE: README.md
VERSION: 02.05.01
BRIEF: Readme and upgrade notes for the MokoCRM Dolibarr module
PATH: htdocs/custom/mokocrm/README.md
NOTE: Updated to reflect project sync notes as of 2025-08-28.
========================================================================
-->

# MokoCRM — Dolibarr Module (v2.5) 🧩

> Core utilities and conventions for the Moko‑prefixed module suite on Dolibarr.

---

## Overview

MokoCRM provides shared conventions, assets, and admin utilities for all modules that start with the `Moko`/`moko` prefix. It standardizes file layout, language keys, visibility rules, and a few quality‑of‑life admin pages so the rest of the Moko family can stay light and consistent.

## Features ✨

* 🧱 Standardized structure for all Moko modules
* 🛠️ Unified admin pages (Setup, Tools with Secure & Repair, DB Admin, About)
* 🔒 Security hardening with blank index files in every folder
* 👥 Cross‑entity controls (global constants at entity 0; UI visible only on entity 1)
* ⚙️ Sensible defaults (disables Mailings/Websites/ModuleBuilder; toggleable Help Links)
* 🧭 GeoIP integration hook

---

## Requirements 🧰

* **Dolibarr**: 19.x or newer (module descriptor sets `$this->need_dolibarr_version = array(19, -3)`).
* **PHP**: 7.1+ (module descriptor sets `$this->phpmin = array(7, 1)`).

---

## License 📄

GPL‑3.0‑or‑later. See `LICENSE` and the header above.

---

## Support 💬

Questions or feature requests? Email **[hello@mokoconsulting.tech](mailto:hello@mokoconsulting.tech)**.
