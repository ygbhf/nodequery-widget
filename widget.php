<?php
header('Content-Type: image/png');

/* Require NodeQuery Libraries */
require_once("nodequery/Server.php");
require_once("nodequery/Client.php");
require_once("nodequery/Request.php");

/* Require Widget Classes */
require_once("class/WidgetConfig.php");
require_once("class/WidgetDatabase.php");

$config = new WidgetConfig("conf.json");
$database = new WidgetDatabase($config->get("database"));

$database->add("servers", "");
$database->add("next_update", time());

$imageFileName = $config->get("image_file");
$nextUpdate = $database->get("next_update");

if ($nextUpdate > time())
{
	readfile($imageFileName);
	return;
}

$serverName = $config->serverName;
$nodequery = new NodeQuery\Client($config->get("api_key"));
$servers = $nodequery->servers();

$database->set("next_update", time() + $config->get("interval"));
$database->save();

// Set the environment variable for GD.
putenv('GDFONTPATH=' . realpath('.'));

foreach ($servers["data"][0] as $server)
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
$canvas->readImage($config->get("background")); 

$draw = new ImagickDraw();
$draw->setFont($config->get("main_font"));
$draw->setFontSize(16);
$draw->setStrokeAntialias(true);
$draw->setTextAntialias(true);

$metrics = DrawText($canvas, $draw, $canvas->getImageWidth() / 2, 10, $serverName, $whiteColor, true);

$draw->setFontSize(14);

if (isset($serverObject))
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
$canvas->writeImage($imageFileName);

/* Clean the output buffer before rendering. */
ob_clean();

echo $canvas;
?>