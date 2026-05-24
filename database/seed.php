<?php
$pdo = new PDO('mysql:host=DB_HOST_REDACTED;dbname=DB_NAME_REDACTED;charset=utf8mb4',
    'DB_NAME_REDACTED', 'DB_PASS_REDACTED',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$today = '2026-05-24';

function uuid(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
}

function item(PDO $pdo, string $name): int {
    $s = $pdo->prepare('SELECT id FROM items WHERE LOWER(name)=LOWER(?)');
    $s->execute([$name]);
    $r = $s->fetch();
    if ($r) return $r['id'];
    $pdo->prepare('INSERT INTO items (name) VALUES (?)')->execute([$name]);
    return (int)$pdo->lastInsertId();
}

function inv(PDO $pdo, string $name, int $roomId, ?int $containerId, int $grams, string $date, ?string $notes = null): void {
    $id = item($pdo, $name);
    $pdo->prepare('INSERT INTO inventory (item_id,room_id,container_id,quantity_grams,arrival_date,notes) VALUES (?,?,?,?,?,?)')
        ->execute([$id, $roomId, $containerId, $grams, $date, $notes]);
    $loc = $notes ? " ($notes)" : '';
    echo "  + $name — {$grams}g{$loc}\n";
}

// ── ROOMS ──────────────────────────────────────────────────────────────────
$roomNames = ['Kitchen', 'Room 02', 'Room 03'];
$R = [];
foreach ($roomNames as $n) {
    $pdo->prepare('INSERT INTO rooms (name, qr_code) VALUES (?,?)')->execute([$n, uuid()]);
    $R[$n] = (int)$pdo->lastInsertId();
    echo "Room '$n' → id {$R[$n]}\n";
}

// ── CONTAINERS ─────────────────────────────────────────────────────────────
$containerDefs = [
    // [name, room_key, type, shelf_note]
    ['Bosch Carton',  'Room 02', 'basket',  null],
    ['Almarih',       'Room 02', 'cabinet', null],
    ['Purple Box',    'Room 02', 'basket',  null],
    ['Container 04',  'Kitchen', 'other',   'Top Shelf'],
    ['Container 05',  'Kitchen', 'other',   'Top Shelf'],
    ['Container 06',  'Room 02', 'other',   null],
    ['Container 07',  'Room 02', 'other',   null],
    ['Container 08',  'Kitchen', 'other',   'Last Shelf'],
    ['Container 09',  'Kitchen', 'other',   'Top/Last Shelf'],
    ['Container 10',  'Kitchen', 'other',   'Last Shelf'],
];
$C = [];
foreach ($containerDefs as [$name, $room, $type, $note]) {
    $pdo->prepare('INSERT INTO containers (room_id,name,type,qr_code) VALUES (?,?,?,?)')
        ->execute([$R[$room], $name, $type, uuid()]);
    $C[$name] = (int)$pdo->lastInsertId();
    $label = $note ? " [$note]" : '';
    echo "Container '$name' in '$room'{$label} → id {$C[$name]}\n";
}

// ── INVENTORY ──────────────────────────────────────────────────────────────

echo "\n── Bosch Carton (Room 02) ──\n";
$r = $R['Room 02']; $c = $C['Bosch Carton'];
inv($pdo, 'Sugar',       $r, $c, 1000, $today);
inv($pdo, 'Dhaniya',     $r, $c, 1000, $today);
inv($pdo, 'Jeera',       $r, $c, 1000, $today);
inv($pdo, 'Sauf',        $r, $c, 1000, $today);
inv($pdo, 'Sattu',       $r, $c, 1000, $today, 'Packet 1/2');
inv($pdo, 'Sattu',       $r, $c, 1000, $today, 'Packet 2/2');
inv($pdo, 'Besan',       $r, $c,  750, $today, 'Packet 1/4');  // 4 pkts × 750g = 3 KG
inv($pdo, 'Besan',       $r, $c,  750, $today, 'Packet 2/4');
inv($pdo, 'Besan',       $r, $c,  750, $today, 'Packet 3/4');
inv($pdo, 'Besan',       $r, $c,  750, $today, 'Packet 4/4');
inv($pdo, 'Gari',        $r, $c,  600, $today, '6 pcs');
inv($pdo, 'Rehar Dal',   $r, $c, 4000, $today);
inv($pdo, 'Tadka Daal',  $r, $c, 1000, $today);

echo "\n── Almarih (Room 02) ──\n";
$c = $C['Almarih'];
inv($pdo, 'Kismish',     $r, $c, 3000, $today);
inv($pdo, 'Kaju',        $r, $c, 2000, $today);
inv($pdo, 'Badam',       $r, $c, 4000, $today);

echo "\n── Purple Box (Room 02) ──\n";
$c = $C['Purple Box'];
inv($pdo, 'Magaj',        $r, $c,  500, $today);
inv($pdo, 'Ajwain',       $r, $c,  500, $today);
inv($pdo, 'Nutmeg',       $r, $c,  250, $today);
inv($pdo, 'Safed Golki',  $r, $c,  250, $today);
inv($pdo, 'Badi Elaich',  $r, $c,  400, $today);
inv($pdo, 'Javitri',      $r, $c,  250, $today);
inv($pdo, 'Cinnamon',     $r, $c,  500, $today);
inv($pdo, 'Chhoti Elaich',$r, $c,  300, $today);
inv($pdo, 'Black Pepper', $r, $c, 1000, $today);
inv($pdo, 'Laung',        $r, $c,  500, $today);
inv($pdo, 'Tej Patta',    $r, $c,  200, $today, 'Packet 1/2');
inv($pdo, 'Tej Patta',    $r, $c,  200, $today, 'Packet 2/2');

echo "\n── Container 06 (Room 02) ──\n";
$c = $C['Container 06'];
inv($pdo, 'Yellow Mustard', $r, $c,  500, $today);
inv($pdo, 'Kabuli Chana',   $r, $c, 1000, $today);
inv($pdo, 'Chana Dal',      $r, $c,  500, $today);

echo "\n── Container 07 (Room 02) ──\n";
$c = $C['Container 07'];
inv($pdo, 'Gud Jaggery & Masala Jaggery', $r, $c, 2000, $today);

echo "\n── Container 04 (Kitchen — Top Shelf) ──\n";
$r = $R['Kitchen']; $c = $C['Container 04'];
inv($pdo, 'Maida',                  $r, $c, 1000, $today);
inv($pdo, 'Safed Matar',            $r, $c,  500, $today, 'Ghugni Matar');
inv($pdo, 'Chana Dal',              $r, $c, 3000, $today);
inv($pdo, 'Moong Dal',              $r, $c,  500, $today);

echo "\n── Container 05 (Kitchen — Top Shelf) ──\n";
$c = $C['Container 05'];
inv($pdo, 'Arhar Dal',              $r, $c, 4000, $today);
inv($pdo, 'Sarva Sresth Mixture',   $r, $c, 2000, $today);

echo "\n── Container 08 (Kitchen — Last Shelf) ──\n";
$c = $C['Container 08'];
inv($pdo, 'Black Grams',            $r, $c, 3000, $today);

echo "\n── Container 09 (Kitchen) ──\n";
$c = $C['Container 09'];
inv($pdo, 'Peanuts',       $r, $c,  200, $today);
inv($pdo, 'Chowmin',       $r, $c,  500, $today, 'Packet 1/3');
inv($pdo, 'Chowmin',       $r, $c,  500, $today, 'Packet 2/3');
inv($pdo, 'Chowmin',       $r, $c,  500, $today, 'Packet 3/3');
inv($pdo, 'Roasted Chana', $r, $c,  750, $today, 'Packet 1/2');
inv($pdo, 'Roasted Chana', $r, $c,  750, $today, 'Packet 2/2');
inv($pdo, 'Red Poha',      $r, $c,  500, $today);

echo "\n── Container 10 (Kitchen — Last Shelf) ──\n";
$c = $C['Container 10'];
inv($pdo, 'Suji', $r, $c, 500, $today, 'Packet 1/4');
inv($pdo, 'Suji', $r, $c, 500, $today, 'Packet 2/4');
inv($pdo, 'Suji', $r, $c, 500, $today, 'Packet 3/4');
inv($pdo, 'Suji', $r, $c, 500, $today, 'Packet 4/4');

// ── INDEPENDENT (no container) ─────────────────────────────────────────────

echo "\n── Room 03 — independent ──\n";
$r = $R['Room 03'];
inv($pdo, 'Mustard Oil',    $r, null, 15000, $today, 'Tank 1/2 — 15L');
inv($pdo, 'Mustard Oil',    $r, null, 15000, $today, 'Tank 2/2 — 15L');
inv($pdo, 'Groundnut Oil',  $r, null, 15000, $today, '15L');

echo "\n── Kitchen — independent ──\n";
$r = $R['Kitchen'];
inv($pdo, 'Keshav Bhog Aata', $r, null, 25000, $today, '25 KG bag');
inv($pdo, 'Arwa Chawal',      $r, null, 25000, $today, '25 KG bag');

echo "\nDone. All inventory inserted.\n";
