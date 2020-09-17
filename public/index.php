<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:41
 */
use Sprovider90\Zhiyuanqueue\Application;
use Sprovider90\Zhiyuanqueue\Logic\Message;
require __DIR__."/../vendor/autoload.php";
define('PUBLIC_PATH', __DIR__."/");
define('APP_PATH', __DIR__."/../");

//.env
$dotenv =  Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//config
\Sprovider90\Zhiyuanqueue\Factory\Config::register(__DIR__."/../src/Config");


ini_set('date.timezone','Asia/Shanghai');

//run
$builder = new Application('zhiyuanqueue', '@package_version@');
$builder->run();
