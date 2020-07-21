<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 16:54
 */

namespace Sprovider90\Zhiyuanqueue\Model;
use Sprovider90\Zhiyuanqueue\Factory\Config;

class Redis
{
    protected $client;
    public function __construct()
    {
        $redisConfig=Config::get("Redis");
        $this->client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
    }

    function zadd($key,$score,$value){

        $this->client->zadd($key,$score,$value);

    }
    function rpush($arr){
        //$arr=["stage"=>1003,"time"=>time(),"dev_no"=>1,"target_values"=>"TVOC"];
        $this->client->rpush('messagelist', json_encode($arr));
    }
}