<?php
header('Content-Type: image/png');

$config = json_decode(file_get_contents("conf.json"), true);
$secretFile = "data/". $config["secret"] . ".dat";
$updateFile = "data/update.txt";
$serverData = file_get_contents($secretFile);
$nextUpdate = file_get_contents($updateFile);

if ($nextUpdate == false || $serverData == false)
{
	$nextUpdate = time();
}

if ($nextUpdate > time())
{
	$image = @imagecreatefrompng("data/image.png");
	
	if ($image)
	{
		imagepng($image);
		imagedestroy($image);
	}
	
	return;
}

$serverName = $config["server_name"];
$targetURL = "https://nodequery.com/api/servers?api_key=" . $config["api_key"];

if (isset($_GET["server"]))
{
	$serverName = $_GET["server"];
}

if ($serverName == false || $serverName == "")
{
	$serverName = "unknown.server.com";
}

// Retrieve the server list from NodeQuery.
$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
	curl_setopt($curl, CURLOPT_URL, $targetURL);

	$serverData = curl_exec($curl);
curl_close($curl);

// Write the server data to our secret file.
file_put_contents($secretFile, $serverData);
file_put_contents($updateFile, time() + 30);

// Decode the server data into a JSON object.
$serverData = json_decode($serverData, true);

// Set the environment variable for GD.
putenv('GDFONTPATH=' . realpath('.'));

$fontName = "Roboto-Regular";

foreach ($serverData["data"][0] as $server)
{
	if ($server["name"] == $serverName)
	{
		$serverObject = $server;
	}
}

// Create a set of colors to use for the widget.
$redColor = new ImagickPixel('#FF0000');
$greenColor = new ImagickPixel('#00FF00');
$whiteColor = new ImagickPixel('#FFFFFF');

function DrawText($image, $draw, $x, $y, $text, $color, $centerH = false)
{
	$metrics = $image->queryFontMetrics($draw, $text);
	
	if ($centerH)
	{
		$x = $x - ($metrics['textWidth'] / 2);
	}
	
	$draw->setFillColor($color);
	$draw->annotation($x, $metrics['ascender'] + $y, $text);
	
	return $metrics;
}

/*
	Method was gratefully taken from the following places:
	http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
	http://php.net/manual/de/function.filesize.php
*/

function FormatBytes($bytes, $precision = 2)
{ 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 
	
    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

// Create the canvas by loading in our background image.
$canvas = new Imagick();
$canvas->readImage('background.png'); 

$draw = new ImagickDraw();
$draw->setFont('Roboto-Regular.ttf');
$draw->setFontSize(16);
$draw->setStrokeAntialias(true);
$draw->setTextAntialias(true);

$metrics = DrawText($canvas, $draw, $canvas->getImageWidth() / 2, 10, $serverName, $whiteColor, true);

$draw->setFontSize(14);

if ($serverObject)
{
	if ($serverObject["status"] == "active")
	{
		DrawText($canvas, $draw, $canvas->getImageWidth() / 2, 32, "Online (" . $serverObject["availability"] . ")", $greenColor, true);
	}
	else
	{
		DrawText($canvas, $draw, $canvas->getImageWidth() / 2, 32, "Offline", $redColor, true);
	}
	
	$draw->setFontSize(12);
	
	DrawText($canvas, $draw, 16, 56, "RAM: " . FormatBytes($serverObject["ram_usage"], 2) . " / " . FormatBytes($serverObject["ram_total"], 2), $whiteColor);
	DrawText($canvas, $draw, 16, 74, "CPU: " . $serverObject["load_percent"] . "%", $whiteColor);
}
else
{
	DrawText($canvas, $draw, $canvas->getImageWidth() / 2, 32, "ERROR", $redColor, true);
}

$canvas->drawImage($draw);
$canvas->setImageFormat('png');
$canvas->writeImage("data/image.png");

/* Clean the output buffer before rendering. */
ob_clean();

echo $canvas;
?>