<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:12
 */

namespace Sprovider90\ZhiyuanQueue\Logic;

/**
 * Class Message
 * @package Sprovider90\ZhiyuanQueue\Logic
 * 接收任何往redis写入的数据来生成系统消息数据
 */
class Message implements Icommand
{
    function test_rpush(){
        $client = new \Predis\Client('tcp://127.0.0.1:63790');

        $client->rpush('messagelist', time());



    }
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (true) {
            $client = new \Predis\Client('tcp://127.0.0.1:63790');
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