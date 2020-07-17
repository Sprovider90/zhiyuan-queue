<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:41
 */
use Sprovider90\Zhiyuanqueue\Application;
require "../vendor/autoload.php";
$builder = new Application('zhiyuanqueue', '@package_version@');
$builder->run();
//$obj=new \Sprovider90\Zhiyuanqueue\Logic\PhoneNotice();
//$obj->sendsms();
//$obj=new \Sprovider90\Zhiyuanqueue\Logic\Message();
//$obj->test();
$obj=new \Sprovider90\Zhiyuanqueue\Logic\WarningSms();
$obj->run();