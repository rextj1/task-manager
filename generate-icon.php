<?php

$size = 1024;
$image = imagecreatetruecolor($size, $size);
imagesavealpha($image, true);
imagealphablending($image, true);
imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
imageantialias($image, true);

$teal = imagecolorallocate($image, 15, 118, 110);
$white = imagecolorallocate($image, 255, 255, 255);
$orange = imagecolorallocate($image, 245, 158, 11);
$muted = imagecolorallocate($image, 203, 213, 209);
$shadow = imagecolorallocatealpha($image, 10, 49, 46, 102);

$roundedRect = static function (int $left, int $top, int $right, int $bottom, int $radius, int $color) use ($image): void {
    imagefilledrectangle($image, $left + $radius, $top, $right - $radius, $bottom, $color);
    imagefilledrectangle($image, $left, $top + $radius, $right, $bottom - $radius, $color);
    foreach ([[$left + $radius, $top + $radius], [$right - $radius, $top + $radius], [$left + $radius, $bottom - $radius], [$right - $radius, $bottom - $radius]] as [$x, $y]) {
        imagefilledellipse($image, $x, $y, $radius * 2, $radius * 2, $color);
    }
};

$roundedRect(220, 205, 834, 865, 94, $shadow);
$roundedRect(205, 190, 819, 850, 94, $teal);
$roundedRect(235, 220, 789, 820, 68, $white);
$roundedRect(385, 148, 639, 290, 54, $teal);
imagefilledellipse($image, 715, 310, 46, 46, $orange);

foreach ([390, 535] as $top) {
    $roundedRect(315, $top, 397, $top + 82, 23, $teal);
}
$roundedRect(315, 680, 397, 762, 23, $teal);
$roundedRect(327, 692, 385, 750, 14, $white);

imagesetthickness($image, 15);
foreach ([431, 576] as $y) {
    imageline($image, 335, $y, 352, $y + 17, $white);
    imageline($image, 352, $y + 17, 381, $y - 17, $white);
}

imagesetthickness($image, 18);
foreach ([[455, 416, 696], [455, 561, 696], [455, 706, 645]] as [$left, $y, $right]) {
    imageline($image, $left, $y, $right, $y, $muted);
}

$output = __DIR__.'/public/icon.png';
imagepng($image, $output, 9);
imagedestroy($image);

fwrite(STDOUT, "Generated {$output}\n");
