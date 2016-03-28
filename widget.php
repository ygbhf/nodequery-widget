<?php
$im = imagecreatetruecolor(300, 150);
$text_color = imagecolorallocate($im, 233, 14, 91);
$font_id = 5;

imagestring($im, $font_id, 32, 32,  'A Simple Text String', $text_color);

header('Content-Type: image/png');

imagepng($im);

imagedestroy($im);
?>