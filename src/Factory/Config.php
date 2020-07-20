<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-20
 * Time: 13:12
 */

namespace Sprovider90\Zhiyuanqueue\Factory;
use Sprovider90\Zhiyuanqueue\Config as Configbase;

class Config
{
    static $staticConfig = [];
    public static function register($path)
    {
        self::$staticConfig=new Configbase($path);
    }
    public static function get($key = '')
    {
        return self::$staticConfig[$key];
    }

}