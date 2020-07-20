<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 14:02
 */

namespace Sprovider90\Zhiyuanqueue\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Monolog
{
    static $log = [];
    public static function register($name,$path)
    {
        self::$log = new Logger($name);
        self::$log->pushHandler(new StreamHandler($path."/runtime.log", Logger::INFO));
    }
    public static function get()
    {
        return self::$log;
    }

}
