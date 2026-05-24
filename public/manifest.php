<?php
header('Content-Type: application/manifest+json');
header('Cache-Control: public, max-age=86400');
require_once __DIR__ . '/../config/config.php';
echo json_encode([
    'name'             => 'StockSense',
    'short_name'       => 'StockSense',
    'description'      => 'Home inventory, sorted.',
    'start_url'        => APP_URL . '/',
    'scope'            => APP_URL . '/',
    'display'          => 'standalone',
    'background_color' => '#FDF8F3',
    'theme_color'      => '#D97706',
    'orientation'      => 'portrait-primary',
    'icons'            => [
        ['src' => APP_URL . '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => APP_URL . '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => APP_URL . '/icons/apple-touch-icon.png', 'sizes' => '180x180', 'type' => 'image/png'],
    ],
], JSON_UNESCAPED_SLASHES);
