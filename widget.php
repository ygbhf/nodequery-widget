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

$config = json_decode(file_get_contents("conf.json"), true);
$server_name = $config["server_name"];

if (isset($_GET["server"]))
{
	$server_name = $_GET["server"];
}

$url = "https://nodequery.com/api/servers?api_key=" . $config["api_key"];

$ch = curl_init();

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
curl_setopt($ch, CURLOPT_URL, $url);

$result = json_decode(curl_exec($ch), true);

curl_close($ch);

$im = imagecreatetruecolor(300, 150);
$text_color = imagecolorallocate($im, 233, 14, 91);
$font_id = 5;

imagestring($im, $font_id, 32, 32,  'Status: ' . $result["status"], $text_color);

imagepng($im, "data/image.png");
imagepng($im);
imagedestroy($im);

file_put_contents("data/update.txt", time() + 30);
?>