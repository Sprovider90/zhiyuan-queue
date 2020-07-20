<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:12
 */

namespace Sprovider90\Zhiyuanqueue\Logic;

use Sprovider90\Zhiyuanqueue\Factory\Config;

/**
 * Class Message
 * @package Sprovider90\Zhiyuanqueue\Logic
 * 接收任何往redis写入的数据来生成系统消息数据
 */
class Message implements Icommand
{
    function test_rpush(){
        $redisConfig=Config::get("Redis");
        $client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
        $client->rpush('messagelist', time());



    }
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $redisConfig=Config::get("Redis");
        while (true) {
            $client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
            $str=$client->lpop('messagelist');
            if (!empty($str) && is_array($str)) {
                $data=json_decode($str);
                if(empty($data)){
                    echo "data empty";
                }
                $this->dealsms($data);
            }
        }

        flush();
        ob_flush();
    }

    /**
     * 1.入库
     */
    function dealsms(){

    }
}