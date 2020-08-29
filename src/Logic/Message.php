<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:12
 */

namespace Sprovider90\Zhiyuanqueue\Logic;

use Sprovider90\Zhiyuanqueue\Exceptions\InvalidArgumentException;
use Sprovider90\Zhiyuanqueue\Factory\Config;
use Sprovider90\Zhiyuanqueue\Factory\MessageDeal;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;

/**
 * Class Message
 * @package Sprovider90\Zhiyuanqueue\Logic
 * 接收任何往redis写入的数据来生成系统消息数据
 */
class Message implements Icommand
{
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $redisConfig=Config::get("Redis");
        while (true) {
            $client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
            $str=$client->lpop('messagelist');
            if (!empty($str)) {
                $data=json_decode($str,true);

                if(empty($data)){
                    CliHelper::cliEcho("data empty");
                }
                $this->dealsms($data);
            }
            CliHelper::cliEcho("sleep 100ms");
            usleep(100);

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
            $message->checkCommon()->createAndCheckStageData()->checkStageContent()->checkUsers()->saveSms();
        }catch (InvalidArgumentException $e){

            CliHelper::cliEcho(print_r($data,true).$e->getMessage());
        }

    }
}