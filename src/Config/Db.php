<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-20
 * Time: 11:18
 */


$config=[
    "database_name"=>$_ENV["db_database_name"],
    "server"=>$_ENV["db_server"],
    "username"=>$_ENV["db_username"],
    "password"=>$_ENV["db_password"],
    "port"=>$_ENV["db_port"],
];
return $config;
