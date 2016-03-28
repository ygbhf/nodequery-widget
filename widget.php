<?php
header('Content-Type: image/png');

$next_update = file_get_contents("data/update.txt");

if ($next_update == false)
{
	$next_update = time();
}

if ($next_update > time())
{
	$im = @imagecreatefrompng("data/image.png");
	
	if ($im)
	{
		imagepng($im);
		imagedestroy($im);
	}
	
	return;
}

$im = imagecreatetruecolor(300, 150);
$text_color = imagecolorallocate($im, 233, 14, 91);
$font_id = 5;

imagestring($im, $font_id, 32, 32,  'A Simple Text String: ' . $next_update, $text_color);

imagepng($im, "data/image.png");
imagepng($im);

imagedestroy($im);

file_put_contents("data/update.txt", time() + 30);
?>