<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-19
 * Time: 15:09
 */

namespace Sprovider90\ZhiyuanQueue\Model;

use Medoo\Medoo;
use Sprovider90\Zhiyuanqueue\Factory\Config;

class Orm
{
    protected $database;
    public function __construct()
    {
        $configDb=Config::get("Db");
        $this->database = new Medoo([
            'database_type' => 'mysql',
            'database_name' => $configDb["database_name"],
            'server' => $configDb["server"],
            'username' => $configDb["username"],
            'password' => $configDb["password"],
            'port' => $configDb["port"],
            'charset' => 'utf8',
        ]);

    }
    function insert($table_name,$data=[]){
        $this->database->insert($table_name,$data);
        return $this->database->id();
    }
    function getAll($sql){

        return $this->database->query($sql)->fetchAll();
    }
    function find($table_name,$feilds="*",$where=[]){
        return $this->database->get($table_name, [
            $feilds
        ], $where);
    }
    function del($table_name,$where=[]){
        return $this->database->delete($table_name,$where);
    }
}