# nodequery-widget

## Setup

Make sure the data/ directory is writeable (777) so that the image file can be written to.

#### ImageMagick

```apt-get install php5-imagick```  
```php5enmod imagick```

## Config

Edit conf.json and enter your NodeQuery API key. Optionally enter a default ``server_name`` for if no GET data is supplied to widget.php, and don't forget to change ``secret`` to something unique.

## Using

```<img src="http://yourwebsite.com/status/widget.php?server=YOUR_NODEQUERY_SERVER_NAME"/>```

#### Example Output

![Example](http://i.imgur.com/xGLkDW7.png)
