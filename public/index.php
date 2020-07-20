<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:41
 */
use Sprovider90\Zhiyuanqueue\Application;
use Sprovider90\Zhiyuanqueue\Logic\Message;
require "../vendor/autoload.php";
define('PUBLIC_PATH', __DIR__."/");
define('APP_PATH', __DIR__."/../");

//.env
$dotenv =  Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//config
\Sprovider90\Zhiyuanqueue\Factory\Config::register("../src/Config");

//test
$mes=new Message();
$mes->test_rpush();exit;
//run
$builder = new Application('zhiyuanqueue', '@package_version@');
$builder->run();
