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
        $this->client ->lpush('messagelist','{
    "stage": 1002,
    "pro_name": "自定义项目不要动",
    "areas_name": "必填区域",
    "dev_no": "A103",
    "time": "2020-09-14 17:07:41"
}');
    }
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $this->initRedisMysql();
        $this->test();
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
        $message=new MessageDeal($data);
        try {
            $message->checkCommon()->requestCheck()->getTemplateRealData()->contentCheck()->createContent()->usersCheck()->createUsers()->saveSms();
        }catch (\Exception $e){
            CliHelper::cliEcho(print_r($data,true).$e->getMessage());exit;
        }

    }
}