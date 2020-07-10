<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:41
 */
if (file_exists(dirname(dirname(__FILE__)).'/vendor/autoload.php')) {
    require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
} else if (file_exists(dirname(__FILE__).'/../../../autoload.php')) {
    require_once dirname(__FILE__).'/../../../autoload.php';
} else {
    throw new Exception('Can not load composer autoloader; Try running "composer install".');
}
$obj=new \Sprovider90\ZhiyuanQueue\Logic\PhoneNotice();
$obj->sendsms();
