<?php

spl_autoload_register( function($class) {

        $dirs = ['/lib', '/controller'];
        foreach($dirs as $dir) {
                $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(
                                __DIR__ . '/..' . $dir,
                                FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
                        ), RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach($iterator as $pathname => $info) {
                        //print $pathname . "<br>";
                        if( basename($pathname) == $class . '.php') {
                                require $pathname;
                                break 2;
                        }
                }
        }
}, true, true);


function redis_factory() {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
}
