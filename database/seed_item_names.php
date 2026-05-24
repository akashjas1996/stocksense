<?php
require_once __DIR__ . '/../config/config.php';
$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// [id => [name_en, name_hi]]
// name_hi = common Hinglish spellings / alternate names (space-separated so all match in LIKE search)
$names = [
     1 => ['Sugar',                        'Cheeni Shakkar Chini'],
     2 => ['Coriander Seeds',              'Dhaniya Dhania Dhanya'],
     3 => ['Cumin Seeds',                  'Jeera Zeera Jira'],
     4 => ['Fennel Seeds',                 'Saunf Sauf Sonf'],
     5 => ['Roasted Gram Flour',           'Sattu'],
     6 => ['Chickpea Flour / Gram Flour',  'Besan Chane Ka Atta'],
     7 => ['Cassava / Tapioca Pieces',     'Gari Sabudana'],
     8 => ['Pigeon Pea Lentil',            'Rehar Dal Arhar Toor Tur Dal'],
     9 => ['Tempering Lentil',             'Tadka Daal Dal'],
    10 => ['Raisins',                      'Kishmish Kismish Munakka'],
    11 => ['Cashews',                      'Kaju Kaaju'],
    12 => ['Almonds',                      'Badam Badaam'],
    13 => ['Watermelon Seeds',             'Magaj Magaz Tarbooj Ke Beej'],
    14 => ['Carom Seeds',                  'Ajwain Ajwan Omam'],
    15 => ['Nutmeg',                       'Jaiphal Jayfal'],
    16 => ['White Peppercorns',            'Safed Golki Safed Mirch Golki'],
    17 => ['Black Cardamom',               'Badi Elaichi Badi Elaich Kali Elaichi'],
    18 => ['Mace',                         'Javitri Javetri'],
    19 => ['Cinnamon',                     'Dalchini Dal Chini Taj'],
    20 => ['Green Cardamom',               'Chhoti Elaichi Choti Elaich Hari Elaichi'],
    21 => ['Black Pepper',                 'Kali Mirch Kaali Mirch'],
    22 => ['Cloves',                       'Laung Lavang Long'],
    23 => ['Bay Leaves',                   'Tej Patta Tejpatta'],
    24 => ['Yellow Mustard Seeds',         'Peeli Sarson Sarso Rai'],
    25 => ['White Chickpeas / Garbanzo',   'Kabuli Chana Safed Chana Chole'],
    26 => ['Split Chickpea Lentil',        'Chana Dal Chane Ki Dal'],
    27 => ['Jaggery',                      'Gur Gud Gurh'],
    28 => ['Refined Flour / All-Purpose',  'Maida Maide'],
    29 => ['White Peas',                   'Safed Matar Ghugni Matar Mutter'],
    30 => ['Split Mung Bean Lentil',       'Moong Dal Mung Dal'],
    31 => ['Pigeon Pea / Toor Dal',        'Arhar Dal Toor Dal Tur Rahad'],
    32 => ['Snack Mix / Namkeen',          'Mixture Namkeen Sarva Sresth'],
    33 => ['Black Gram / Urad',            'Kali Dal Urad Dal Sabut Urad'],
    34 => ['Peanuts / Groundnuts',         'Moongphali Mungfali Sing'],
    35 => ['Noodles / Chow Mein',          'Chowmin Noodles Mein'],
    36 => ['Roasted Chickpeas',            'Bhuna Chana Daalia Roasted Chana'],
    37 => ['Red Flattened Rice',           'Laal Poha Red Poha Chidwa'],
    38 => ['Semolina / Rava',              'Suji Sooji Rava Rawa'],
    39 => ['Mustard Oil',                  'Sarson Ka Tel Sarso Ka Tel'],
    40 => ['Groundnut Oil / Peanut Oil',   'Moongphali Ka Tel Mungfali Tel'],
    41 => ['Whole Wheat Flour',            'Gehun Ka Aata Atta Gehu'],
    42 => ['White Rice / Non-Sticky Rice', 'Arwa Chawal Sela Chawal Chawal'],
];

$stmt = $pdo->prepare('UPDATE items SET name_en = ?, name_hi = ? WHERE id = ?');
foreach ($names as $id => [$en, $hi]) {
    $stmt->execute([$en, $hi, $id]);
    printf("  %2d  %-38s | %s\n", $id, $en, $hi);
}
echo "\nDone.\n";
