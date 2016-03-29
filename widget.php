<?php
header('Content-Type: image/png');

/* Require NodeQuery Libraries */
require_once("nodequery/Server.php");
require_once("nodequery/Client.php");
require_once("nodequery/Request.php");

/* Require Widget Classes */
require_once("class/WidgetConfig.php");
require_once("class/WidgetDatabase.php");
require_once("class/BaseTheme.php");

$config = new WidgetConfig("conf.json");
$database = new WidgetDatabase($config->get("database"));

/* Require Theme Class */
require_once("themes/" . $config->themeClass . ".php");

$database->add("servers", "");
$database->add("next_update", time());

$imageFileName = $config->get("image_file");
$nextUpdate = $database->get("next_update");

if ($nextUpdate > time())
{
	readfile($imageFileName);
	return;
}

$nodequery = new NodeQuery\Client($config->get("api_key"));
$servers = $nodequery->servers();

$database->set("next_update", time() + $config->get("interval"));
$database->save();

// Set the environment variable for GD.
putenv('GDFONTPATH=' . realpath('.'));

foreach ($servers["data"][0] as $data)
{
	if ($data["name"] == $config->serverName)
	{
		$server = $data;
	}
}

$theme = new $config->themeClass();
$theme->render($server, $config, $database);
$theme->canvas->setImageFormat('png');
$theme->canvas->writeImage($imageFileName);

/* Clean the output buffer before rendering. */
ob_clean();

echo $theme->canvas;
?>