<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:41
 */
use Sprovider90\Zhiyuanqueue\Application;
if (file_exists(dirname(dirname(__FILE__)).'/vendor/autoload.php')) {
    require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
} else if (file_exists(dirname(__FILE__).'/../../../autoload.php')) {
    require_once dirname(__FILE__).'/../../../autoload.php';
} else {
    throw new Exception('Can not load composer autoloader; Try running "composer install".');
}
$builder = new Application('zhiyuanqueue', '@package_version@');
$builder->run();
//$obj=new \Sprovider90\ZhiyuanQueue\Logic\PhoneNotice();
//$obj->sendsms();
//$obj=new \Sprovider90\ZhiyuanQueue\Logic\Message();
//$obj->test();
//$obj=new \Sprovider90\ZhiyuanQueue\Logic\WarningSms();
//$obj->run();