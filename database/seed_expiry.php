<?php
/**
 * Expected shelf life from purchase date (2026-05-25), stored properly in airtight containers.
 * Sources: FSSAI guidelines, USDA food storage charts, common Indian pantry practice.
 *
 * Rules applied:
 * - Whole spices last significantly longer than ground
 * - Nuts/seeds: 6 months at room temp (oils go rancid faster in Indian heat)
 * - Flours: 1 yr sealed; whole wheat shorter than refined
 * - Pulses/legumes: 1 year for best quality (safe longer but nutritional value drops)
 * - Oils: 1 year sealed
 * - Rice: up to 3 years polished white
 * - Sugar: indefinite, but 3 years is practical
 */

require_once __DIR__ . '/../config/config.php';
$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$bought = '2026-05-25';

function expiryDate(string $bought, int $months): string {
    return date('Y-m-d', strtotime("+{$months} months", strtotime($bought)));
}

// [item_id => [months_shelf_life, reason]]
$expiries = [
     1 => [36,  'Sugar — indefinite, 3 yr practical'],
     2 => [24,  'Coriander seeds (whole) — 2 yr'],
     3 => [36,  'Cumin seeds (whole) — 3 yr'],
     4 => [24,  'Fennel seeds — 2 yr'],
     5 => [ 6,  'Sattu — 6 mo (roasted flour, oxidises)'],
     6 => [12,  'Besan — 1 yr sealed'],
     7 => [12,  'Gari — 1 yr'],
     8 => [12,  'Rehar Dal — 1 yr (best quality)'],
     9 => [12,  'Tadka Daal — 1 yr'],
    10 => [12,  'Raisins — 1 yr sealed'],
    11 => [ 6,  'Cashews — 6 mo room temp (oils rancid)'],
    12 => [12,  'Almonds — 1 yr room temp'],
    13 => [ 6,  'Watermelon seeds — 6 mo'],
    14 => [24,  'Carom seeds (whole) — 2 yr'],
    15 => [36,  'Nutmeg (whole) — 3 yr'],
    16 => [36,  'White peppercorns (whole) — 3 yr'],
    17 => [24,  'Black cardamom (whole) — 2 yr'],
    18 => [24,  'Mace — 2 yr'],
    19 => [36,  'Cinnamon sticks (whole) — 3 yr'],
    20 => [24,  'Green cardamom (whole) — 2 yr'],
    21 => [36,  'Black pepper (whole) — 3 yr'],
    22 => [36,  'Cloves (whole) — 3 yr'],
    23 => [24,  'Bay leaves — 2 yr'],
    24 => [24,  'Yellow mustard seeds — 2 yr'],
    25 => [24,  'Kabuli chana (dried) — 2 yr'],
    26 => [12,  'Chana dal — 1 yr'],
    27 => [12,  'Jaggery — 1 yr (can crystallise after)'],
    28 => [12,  'Maida — 1 yr sealed'],
    29 => [12,  'Safed matar — 1 yr'],
    30 => [12,  'Moong dal — 1 yr'],
    31 => [12,  'Arhar dal — 1 yr'],
    32 => [ 3,  'Namkeen mixture — 3 mo (fried, oily)'],
    33 => [12,  'Urad/black gram — 1 yr'],
    34 => [ 6,  'Peanuts — 6 mo room temp'],
    35 => [12,  'Noodles — 1 yr sealed'],
    36 => [ 6,  'Roasted chana — 6 mo'],
    37 => [12,  'Red poha — 1 yr'],
    38 => [12,  'Suji/semolina — 1 yr sealed'],
    39 => [12,  'Mustard oil — 1 yr sealed'],
    40 => [12,  'Groundnut oil — 1 yr sealed'],
    41 => [ 6,  'Whole wheat atta — 6 mo (germ oxidises)'],
    42 => [36,  'White rice — 3 yr polished'],
];

$stmt = $pdo->prepare(
    'UPDATE inventory SET expiry_date = ? WHERE item_id = ? AND expiry_date IS NULL'
);

printf("%-4s  %-38s  %-6s  %s\n", 'ID', 'Item', 'Months', 'Expiry');
printf("%s\n", str_repeat('-', 70));

foreach ($expiries as $itemId => [$months, $reason]) {
    $expiry = expiryDate($bought, $months);
    $stmt->execute([$expiry, $itemId]);
    $updated = $stmt->rowCount();
    printf("%2d    %-36s  %3d mo  %s  (%d rows)\n",
        $itemId, explode(' — ', $reason)[0], $months, $expiry, $updated);
}

echo "\nDone.\n";
