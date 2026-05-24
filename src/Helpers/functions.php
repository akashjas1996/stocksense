<?php

/**
 * Format grams into a human-readable string.
 * < 1000       → "750 g"
 * >= 1000      → "1.5 kg"
 * >= 1,000,000 → "1.2 t"
 */
function formatWeight(int $grams): string {
    if ($grams < 1000) {
        return "{$grams} g";
    }
    if ($grams < 1_000_000) {
        $kg = $grams / 1000;
        return rtrim(rtrim(number_format($kg, 2), '0'), '.') . ' kg';
    }
    $t = $grams / 1_000_000;
    return rtrim(rtrim(number_format($t, 2), '0'), '.') . ' t';
}

/**
 * Returns a CSS class based on expiry date proximity.
 * 'expired', 'expiring-soon', or '' (fine)
 */
function expiryStatus(?string $expiryDate): string {
    if (!$expiryDate) return '';
    $today  = new DateTime();
    $expiry = new DateTime($expiryDate);
    $diff   = (int) $today->diff($expiry)->format('%r%a');  // negative = past
    if ($diff < 0)                return 'expired';
    if ($diff <= EXPIRY_WARN_DAYS) return 'expiring-soon';
    return '';
}

function generateUuid(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function redirect(string $path): never {
    header('Location: ' . APP_URL . $path);
    exit;
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) redirect('/auth/login');
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function flash(string $key, string $message = ''): string {
    if ($message !== '') {
        $_SESSION['flash'][$key] = $message;
        return '';
    }
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}
