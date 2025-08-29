<?php
/*
Copyright (C) 2024–2025 Moko Consulting <hello@mokoconsulting.tech>
This file is part of a Moko Consulting project.

SPDX-License-Identifier: GPL-3.0-or-later
This program is free software: you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or (at
your option) any later version. This program is distributed in the hope
that it will be useful, but WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

FILE INFORMATION
DEFGROUP: Dolibarr
INGROUP:  MokoCRM
FILE:     admin/adminer_iframe.php
VERSION:  02.05.00
BRIEF:    Run Adminer inside an iframe (or standalone). Skips login via plugin+POST and keeps creds out of URL.
PATH:     htdocs/custom/mokocrm/admin/adminer_iframe.php
NOTE:     Requires Adminer at /custom/mokocrm/adminer/adminer.php and the official plugin loader at /custom/mokocrm/adminer/plugins/plugin.php
*/

define('NOREQUIREMENU', 1);
define('NOREQUIREHTML', 1);

require dirname(__DIR__, 3) . '/main.inc.php';
if (empty($user) || !$user->admin) accessforbidden();

// ---- Paths
$adminerDir   = DOL_DOCUMENT_ROOT . '/custom/mokocrm/adminer';
$adminerFile  = $adminerDir . '/adminer.php';
$pluginsDir   = $adminerDir . '/plugins';
$pluginLoader = $pluginsDir . '/plugin.php';

if (!is_file($adminerFile)) {
    http_response_code(500);
    print '<div class="error">Adminer not found at: <code>' . dol_escape_htmltag($adminerFile) . '</code></div>';
    exit;
}
if (!is_file($pluginLoader)) {
    http_response_code(500);
    print '<div class="error">Adminer plugin loader not found at: <code>' . dol_escape_htmltag($pluginLoader) . '</code></div>';
    exit;
}

// ---- Dolibarr DB config
global $dolibarr_main_db_host, $dolibarr_main_db_port, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass, $dolibarr_main_db_type;

$dbType = strtolower($dolibarr_main_db_type ?? ($conf->db->type ?? 'mysqli'));
$host   = (string) ($dolibarr_main_db_host ?? $conf->db->host ?? 'localhost');
$port   = (string) ($dolibarr_main_db_port ?? $conf->db->port ?? '');
$userdb = (string) ($dolibarr_main_db_user ?? $conf->db->user ?? '');
$passdb = (string) ($dolibarr_main_db_pass ?? $conf->db->pass ?? '');
$namedb = (string) ($dolibarr_main_db_name ?? $conf->db->name ?? '');

$serverReal = $host . ($port !== '' ? (':' . $port) : '');
$driverKey  = in_array($dbType, array('pgsql','postgresql','pdo_pgsql'), true) ? 'pgsql' : 'server';

// ---- Allow same-origin framing (so it shows inside your DB Admin page)
@header_remove('X-Frame-Options');
@header_remove('Content-Security-Policy');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");

// ---- Always provide a benign driver GET to avoid Adminer’s own “add param” redirects.
//      This value is NOT used to connect; we inject real creds via plugin+POST.
if (empty($_GET[$driverKey])) {
    $_GET[$driverKey] = 'mask';
}

// ---- One-time POST auth to skip the Login page (no GET creds in URL)
if (empty($_SESSION['mokocrm_adminer_authed'])) {
    $_POST['auth'] = array(
        'driver'    => $driverKey,   // 'server' (MySQL/MariaDB) or 'pgsql'
        'server'    => $serverReal,
        'username'  => $userdb,
        'password'  => $passdb,
        'db'        => $namedb,
        'permanent' => 0,
    );
    // do NOT force REQUEST_METHOD; Adminer only checks $_POST['auth']
    $_SESSION['mokocrm_adminer_authed'] = 1;
}

/**
 * Adminer autologin plugin (plain object). The official AdminerPlugin (loader)
 * will EXTEND Adminer and delegate hooks here safely.
 */
class AdminerDolibarrAutologin
{
    protected $server; protected $username; protected $password; protected $database;
    public function __construct($s,$u,$p,$d){$this->server=$s;$this->username=$u;$this->password=$p;$this->database=$d;}

    public function name(){ return 'MokoCRM DBAdmin'; }

    /** @return array{0:string,1:string,2:string} */
    public function credentials(){ return array($this->server, $this->username, $this->password); }

    /** @return string|null */
    public function database(){ return $this->database ?: null; }

    /** @return bool */
    public function login($login, $password){ return true; }  // skip login form

    /** @return int|string|null */
    public function permanentLogin(){ return 0; }

    /** @return string|null */
    public function csp(){ return null; }

    /** @return array<string,string> */
    public function breadcrumbs(){ return array('' => ''); } // never NULL → no key(NULL)

    public function __call($name, $args){ return null; }  // safe no-op for other hooks
}

/**
 * Factory expected by Adminer. We load the OFFICIAL plugin loader here (it
 * defines class AdminerPlugin extends Adminer), then return an AdminerPlugin
 * that aggregates our autologin plugin (and any zero-arg plugins you place).
 */
function adminer_object()
{
    global $pluginLoader, $pluginsDir, $serverReal, $userdb, $passdb, $namedb;

    // Load official Adminer plugin loader (extends Adminer)
    include_once $pluginLoader;

    $plugins = array(new AdminerDolibarrAutologin($serverReal, $userdb, $passdb, $namedb));

    // Auto-load any extra Adminer* plugins with zero-arg constructors
    if (is_dir($pluginsDir)) {
        foreach (glob($pluginsDir . '/*.php') as $file) {
            if (basename($file) === 'plugin.php') continue;
            $before = get_declared_classes();
            include_once $file;
            $after  = get_declared_classes();
            $new    = array_diff($after, $before);
            foreach ($new as $class) {
                if (strpos($class, 'Adminer') !== 0) continue;
                try {
                    $ref = new ReflectionClass($class);
                    if ($ref->isInstantiable()) {
                        $ctor = $ref->getConstructor();
                        if (!$ctor || $ctor->getNumberOfRequiredParameters() === 0) {
                            $plugins[] = $ref->newInstance();
                        }
                    }
                } catch (Throwable $e) { /* ignore plugin instantiation errors */ }
            }
        }
    }

    // Return official wrapper (extends Adminer) so all hooks are safely delegated
    return new AdminerPlugin($plugins);
}

// ---- Run Adminer (it will call adminer_object())
include $adminerFile;

// Re-assert framing headers after output (defensive)
@header_remove('X-Frame-Options');
@header_remove('Content-Security-Policy');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");
