<?php
// One-time script — run via SSH, then delete.
// php database/generate_icons.php

$outDir = __DIR__ . '/../public/icons';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$sizes = [
    ['size' => 512, 'file' => "$outDir/icon-512.png"],
    ['size' => 192, 'file' => "$outDir/icon-192.png"],
    ['size' => 180, 'file' => "$outDir/apple-touch-icon.png"],
];

foreach ($sizes as ['size' => $s, 'file' => $file]) {
    $img   = imagecreatetruecolor($s, $s);
    $amber = imagecolorallocate($img, 217, 119, 6);
    $white = imagecolorallocate($img, 255, 255, 255);
    $door  = imagecolorallocate($img, 180,  95,  4);   // darker amber for door/windows

    // Fill background
    imagefill($img, 0, 0, $amber);

    // — Roof triangle —
    // apex at top-centre, base slightly wider than house body
    imagefilledpolygon($img, [
        (int)($s * 0.50), (int)($s * 0.13),   // apex
        (int)($s * 0.13), (int)($s * 0.52),   // bottom-left
        (int)($s * 0.87), (int)($s * 0.52),   // bottom-right
    ], $white);

    // — House body —
    imagefilledrectangle($img,
        (int)($s * 0.20), (int)($s * 0.49),
        (int)($s * 0.80), (int)($s * 0.87),
        $white
    );

    // — Door —
    imagefilledrectangle($img,
        (int)($s * 0.40), (int)($s * 0.65),
        (int)($s * 0.60), (int)($s * 0.87),
        $door
    );

    // — Windows (two) —
    foreach ([0.26, 0.64] as $wx) {
        imagefilledrectangle($img,
            (int)($s * $wx),        (int)($s * 0.56),
            (int)($s * ($wx+0.12)), (int)($s * 0.72),
            $door
        );
    }

    imagepng($img, $file, 9);
    imagedestroy($img);
    echo "Created $file\n";
}
echo "Done.\n";
