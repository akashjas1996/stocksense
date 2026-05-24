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

function itemEmoji(string $name, string $nameEn = ''): string {
    $s = strtolower($name . ' ' . $nameEn);
    if (preg_match('/\b(dal|lentil|chana|matar|gram|urad|moong|arhar|toor|rehar|chane)\b/', $s)) return '🫘';
    if (preg_match('/\b(oil|tel|ghee)\b/', $s)) return '🫙';
    if (preg_match('/\b(aata|atta|maida|flour|suji|semolina|besan|sattu|rava)\b/', $s)) return '🌾';
    if (preg_match('/\b(chawal|rice|poha|chowmin|noodle)\b/', $s)) return '🍚';
    if (preg_match('/\b(sugar|cheeni|jaggery|gud|gur|shakkar)\b/', $s)) return '🍯';
    if (preg_match('/\b(kaju|cashew|badam|almond|kismish|raisin|peanut|moongphali|magaj|seed)\b/', $s)) return '🥜';
    if (preg_match('/\b(pepper|mirch|chilli|elaich|cardamom|dalchini|cinnamon|laung|clove|jeera|cumin|dhaniya|coriander|ajwain|sauf|fennel|tej|bay|javitri|mace|nutmeg|jaiphal|mustard|sarson)\b/', $s)) return '🌿';
    if (preg_match('/\b(mixture|namkeen|snack|chana|roasted)\b/', $s)) return '🥨';
    return '📦';
}

function containerIcon(string $type): string {
    return match($type) {
        'fridge'  => '❄️',
        'freezer' => '🧊',
        'shelf'   => '🗂',
        'cabinet' => '🚪',
        'drawer'  => '🗃',
        'basket'  => '🧺',
        default   => '📦',
    };
}

function roomEmoji(string $name): string {
    $s = strtolower($name);
    if (str_contains($s, 'kitchen'))  return '🍳';
    if (str_contains($s, 'pantry'))   return '🥫';
    if (str_contains($s, 'store') || str_contains($s, 'storage')) return '📦';
    if (str_contains($s, 'bedroom') || str_contains($s, 'room'))  return '🚪';
    return '🏠';
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
