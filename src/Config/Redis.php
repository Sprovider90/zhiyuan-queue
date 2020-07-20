<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-20
 * Time: 19:13
 */
$config=[
    "host"=>$_ENV["redis_host"],
    "port"=>$_ENV["redis_port"],
];
return $config;
