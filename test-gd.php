<?php
// Test isolasi: apakah GD di server ini bisa fill warna dengan benar?
// Jalankan: php test-gd.php

$canvas = imagecreatetruecolor(200, 200);
$red = imagecolorallocate($canvas, 255, 0, 0);
imagefilledrectangle($canvas, 0, 0, 200, 200, $red);
imagepng($canvas, __DIR__.'/test-red.png');
imagedestroy($canvas);

echo "File tersimpan: test-red.png\n";

// Baca lagi, cek warna pixel di tengah
$check = imagecreatefrompng(__DIR__.'/test-red.png');
$rgb = imagecolorat($check, 100, 100);
$colors = imagecolorsforindex($check, $rgb);
imagedestroy($check);

echo "Warna pixel tengah (harusnya R=255, G=0, B=0):\n";
print_r($colors);

echo "\nVersi GD:\n";
print_r(gd_info());
