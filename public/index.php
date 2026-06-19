<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// Get URI, strip query string
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/*
 * =====================================================================
 * PLANNED ROUTES (implement in issue #04)
 * =====================================================================
 *
 * GET  /                          -> views/dashboard.php
 * GET  /pasien                    -> views/pasien/index.php
 * GET  /pasien/create             -> views/pasien/create.php
 * POST /pasien/store              -> Pasien::create()
 * GET  /pasien/edit?id=RM-XXX     -> views/pasien/edit.php
 * POST /pasien/update             -> Pasien::update()
 * GET  /pasien/delete?id=RM-XXX   -> Pasien::delete()
 * GET  /dokter                    -> views/dokter/index.php
 * GET  /dokter/create             -> views/dokter/create.php
 * POST /dokter/store              -> Dokter::create()
 * GET  /dokter/edit?id=N          -> views/dokter/edit.php
 * POST /dokter/update             -> Dokter::update()
 * GET  /dokter/delete?id=N        -> Dokter::delete()
 * GET  /layanan                   -> views/layanan/index.php
 * GET  /layanan/create            -> views/layanan/create.php
 * POST /layanan/store             -> Layanan::create()
 * GET  /layanan/edit?id=N         -> views/layanan/edit.php
 * POST /layanan/update            -> Layanan::update()
 * GET  /layanan/delete?id=N       -> Layanan::delete()
 * GET  /pemeriksaan               -> views/pemeriksaan/index.php
 * GET  /pemeriksaan/create        -> views/pemeriksaan/create.php
 * POST /pemeriksaan/store         -> Pemeriksaan::create()
 * GET  /pemeriksaan/update_status -> views/pemeriksaan/update_status.php
 * POST /pemeriksaan/update_status -> Pemeriksaan::updateStatus()
 * GET  /pemeriksaan/delete        -> Pemeriksaan::delete()
 *
 * =====================================================================
 */

// Scaffold placeholder — routes will be wired in issue #04
echo "<h1>SILK-Swarakarna</h1><p>Router scaffold. Routes pending issue #04.</p><pre>URI: $uri</pre>";
