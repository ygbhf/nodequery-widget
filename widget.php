<?php
header('Content-Type: image/png');

$next_update = file_get_contents("data/update.txt");

if ($next_update == false)
{
	$next_update = time();
}

if ($next_update > time())
{
	$image = @imagecreatefrompng("data/image.png");
	
	if ($image)
	{
		imagepng($image);
		imagedestroy($image);
	}
	
	return;
}

$config = json_decode(file_get_contents("conf.json"), true);
$serverName = $config["server_name"];
$targetURL = "https://nodequery.com/api/servers?api_key=" . $config["api_key"];

if (isset($_GET["server"]))
{
	$serverName = $_GET["server"];
}

// Retrieve the server list from NodeQuery.
$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
	curl_setopt($curl, CURLOPT_URL, $targetURL);

	$result = json_decode(curl_exec($curl), true);
curl_close($curl);

// Set the environment variable for GD.
putenv('GDFONTPATH=' . realpath('.'));

$image = imagecreatetruecolor(250, 80);
$fontName = "Roboto-Regular";
$statusText = "Status: " . $result["status"];

// Define a set of colors for use with this script.
$redColor = imagecolorallocate($image, 233, 14, 91);
$greenColor = imagecolorallocate($image, 15, 233, 32);
$whiteColor = imagecolorallocate($image, 255, 255, 255);

imagettftext($image, 14, 0, 16, 28, $whiteColor, $fontName, $serverName);

if ($result["status"] == "OK")
{
	imagettftext($image, 14, 0, 16, 62, $greenColor, $fontName, $statusText);
}
else
{
	imagettftext($image, 14, 0, 16, 62, $redColor, $fontName, $statusText);
}

// Output the PNG image and free up memory.
imagepng($image, "data/image.png");
imagepng($image);
imagedestroy($image);

file_put_contents("data/update.txt", time() + 30);
?>