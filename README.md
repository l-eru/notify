Requirements
=======================


require [php-inotify](http://pecl.php.net/package/inotify)

Install
=======================

+ You can download the src/Notify.php and include it in your php file;

Or

+ using composer

	+ add vcs in your *composer.json*
	```json
	"repositories": [
        {
            "type": "vcs",
            "url": "http://github.com/l-eru/notify"
        }
    ]
	```
    
    + run composer to require it
    ```shell
    $ composer require l-eru/notify
    ```

    + require autoload in your php file
    
    ```php
    require 'vendor/autoload.php';
    ```
    
How To Use?
==========================


```php
$server = new Swoole\Http\Server('0.0.0.0', 9501);

$server->on('workerStart', function ($server)) {
    
    /**
     * when you changed the file in your app path,
     * swoole http server reload.
     * 
     */
    $notify = new L\Notify($yourAppPath);
    
    $notify->addEvent($server);
});
```
