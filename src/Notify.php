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

    protected $pathLists;

    public function __construct()
    {
        $this->fd = inotify_init();
    }


    /**
     * add your path you want to listen
     *
     * <code>
     * # add one file
     * add('/path/to');
     *
     * # add multi files
     * add([
     *   '/path/to/1',
     *   '/path/to/2',
     *   ...,
     *   '/path/to/N',
     * ]);
     * </code>
     *
     */ 
    public function add($pathname)
    {
        if (is_array($pathname)) {
            foreach ($pathname as $path) {
                $this->pathLists[] = $path;
            }
        } elseif (is_string($pathname)) {
            $this->pathLists[] = $pathname;
        }
    }

    protected function watch(string $pathname) 
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
     * You must use add function to add path,
     * if path is empty, it is not working!
     *
     * @param  Server $server
     */
    public function listen(Server $server)
    {
        if (empty($this->pathLists)) return;

        foreach ($pathLists as $path) {
            $this->watch($path);
        }

        Event::add($this->fd, function () use ($server){
            while($events = inotify_read($this->fd)) {
                foreach ($events as $event) {
                    if ($event['mask'] == self::IN_MASK_CREATE) {
                        $this->watch($this->pathname);
                    }
                }

                echo '>>>[' . date('Y-m-d H:i:s') . ']' . '------swoole server prepare to reload--------' .  PHP_EOL;
                $server->reload();
                echo '<<<------swoole server reload finish-----------------------------------------------' . PHP_EOL;
            }
        });
    }
}
