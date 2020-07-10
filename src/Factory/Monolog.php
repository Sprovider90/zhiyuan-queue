<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 14:02
 */

namespace Sprovider90\ZhiyuanQueue\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Monolog
{
    protected static $log;
    private function __construct(){
        self::$log = new Logger('name');
        self::$log->pushHandler(new StreamHandler('/runtime.log', Logger::WARNING));
    }
    static function getInstance(){
        if(!self::$log){
            new self();
        }
        return self::$log;
    }
}