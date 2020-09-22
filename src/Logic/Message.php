<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:12
 */

namespace Sprovider90\Zhiyuanqueue\Logic;


use Sprovider90\Zhiyuanqueue\Factory\Config;
use Sprovider90\Zhiyuanqueue\Factory\MessageDeal;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Exceptions\InvalidArgumentException;
/**
 * Class Message
 * @package Sprovider90\Zhiyuanqueue\Logic
 * 接收任何往redis写入的数据来生成系统消息数据
 */
class Message implements Icommand
{
    protected $client;
    function initRedisMysql(){
        $redisConfig=Config::get("Redis");
        $this->client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);

    }
    function test(){

        $this->client ->rpush('messagelist','{"stage":1001,"dev_no":"A001","time":"2020-09-14 17:07:41"}');
        $this->client ->rpush('messagelist','{"stage":1002,"dev_no":"A001","time":"2020-09-14 17:07:41"}');
        $this->client ->rpush('messagelist','{"stage":1003,"dev_no":"A001","warnig_id":"10058686","target_values":"甲醛，CO2","time":"2020-09-14 17:07:41"}');
        $this->client ->rpush('messagelist','{"stage":1004,"time":"2020-09-14 17:07:41"}');
        $this->client ->rpush('messagelist','{"stage":1005,"time":"2020-09-14 17:07:41"}');
        $this->client ->rpush('messagelist','{"stage":1006,"dev_no":"A001","time":"2020-09-14 17:07:41"}');
        $this->client ->rpush('messagelist','{"stage":1007,"dev_no":"A001","time":"2020-09-14 17:07:41"}');

    }
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $this->initRedisMysql();
        //$this->test();
        while (true) {

            $str=$this->client->lpop('messagelist');
            if (!empty($str)) {
                $data=json_decode($str,true);

                if(empty($data)){
                    CliHelper::cliEcho("data empty");
                }
                $this->dealsms($data);
            }
            CliHelper::cliEcho("sleep 1000ms");
            usleep(1000);

        }

        flush();
        ob_flush();
    }

    /**
     * 1.入库
     */
    function dealsms($data){
        CliHelper::cliEcho(json_encode($data,true)."     start");
        $message=new MessageDeal($data);
        try {
            $message->checkCommon()->requestCheck()->getRealData()->realDataCheck()->createContent()->getUsers()->usersCheck()->saveSms();
        }catch (\Exception $e){
            CliHelper::cliEcho(json_encode($data,true)."     ".$e->getMessage());
        }
        CliHelper::cliEcho(json_encode($data,true)."     end");
    }
}