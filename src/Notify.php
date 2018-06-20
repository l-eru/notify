<?php

namespace L;

use Swoole\Event;
use Swoole\Server;

/**
 *
 */
class Notify
{
    const IN_MASK_CREATE = 1073742080;
    const IN_MASK_DELETE = 512;

    protected $fd;
    protected $pathname;

    public function __construct($pathname)
    {
        $this->fd = inotify_init();
        $this->pathname = $pathname;
    }

    protected function watch($pathname) 
    {
        if (is_dir($pathname)) {  
            $dir = dir($pathname);    

            inotify_add_watch($this->fd, $pathname, IN_ATTRIB|IN_CREATE|IN_DELETE|IN_MOVE);

            while ($fileName = $dir->read()) {
                if ($fileName === '.' || $fileName === '..' || !is_dir($dir->path . DIRECTORY_SEPARATOR . $fileName)) continue;

                $this->watch($dir->path . DIRECTORY_SEPARATOR . $fileName);
            }
        }
    }

 	/**
     * @param  Server $server
     */
    public function addEvent(Server $server)
    {
        $this->watch($this->pathname);

        Event::add($this->fd, function () use ($server){
            while($events = inotify_read($this->fd)) {
                foreach ($events as $event) {
                    if ($event['mask'] == self::IN_MASK_CREATE) {
                        $this->watch($this->pathname);
                    }
                }

                echo '>>>------swoole server prepare to reload------[' , date('Y-m-d H:i:s') , ']' , PHP_EOL;
                $server->reload();
                echo '<<<------swoole reload finish------------------' . PHP_EOL;
            }
        });
    }
}
