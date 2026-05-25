<?php
/**
 * Runtime configuration — reads every value from environment variables.
 * Set these in docker-compose.yml (local dev) or Cloud Run secrets (production).
 * Never hardcode credentials here.
 */

// ── Database ──────────────────────────────────────────────────────────────────
// For Cloud SQL via Unix socket set DB_HOST to:
//   /cloudsql/PROJECT_ID:REGION:INSTANCE_NAME
// For a regular TCP host (local Docker / external MySQL) set it to the IP/hostname.
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'stocksense');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────────────────────────
define('APP_NAME', getenv('APP_NAME') ?: 'StockSense');
define('APP_URL',  rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/'));

// ── Session & expiry ──────────────────────────────────────────────────────────
define('SESSION_LIFETIME', (int) (getenv('SESSION_LIFETIME') ?: 60 * 60 * 24 * 30));
define('EXPIRY_WARN_DAYS', (int) (getenv('EXPIRY_WARN_DAYS') ?: 7));

// ── AI ────────────────────────────────────────────────────────────────────────
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
