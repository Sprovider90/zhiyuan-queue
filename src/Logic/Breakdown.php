<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-23
 * Time: 17:09
 */

namespace Sprovider90\Zhiyuanqueue\Logic;
use Sprovider90\Zhiyuanqueue\Factory\Config;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Model\Orm;


/**
 * Class Breakdown
 * @package Sprovider90\Zhiyuanqueue\Logic
 * 故障排查数据源
 */

class Breakdown implements Icommand
{
    protected $client;
    protected $db;
    function initRedisMysql(){
        $redisConfig=Config::get("Redis");
        $this->client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);

        $this->db=new Orm();

    }
    function test(){
        $this->client ->lpush('breakdown','{"monitorId":"39","breakdownType":"2","breakdownInfo":"数据异常","updateTime":"2020-09-14 01:00:02","projectId":"29","deviceId":"A103","status":1,"timestamp":"2020-09-14 16:50:00.000"}');
    }
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $this->initRedisMysql();
        //$this->test();
        while (true) {

            $str=$this->client ->lpop('breakdown');
            if (!empty($str)) {
                $data=json_decode($str,true);

                if(empty($data)){
                    CliHelper::cliEcho("data not is json");
                }
                $this->deal($data);
            }
            CliHelper::cliEcho("sleep 1000ms");
            usleep(1000);

        }

        flush();
        ob_flush();
    }


    public function deal($yingjian)
    {

        if(!empty($yingjian)){
            if($yingjian["breakdownType"]==2){
                $this->toMessage($yingjian["deviceId"]);
            }
            $tmp=[];
            $tmp["project_id"]=$yingjian["projectId"];
            $tmp["device_id"]=$yingjian["deviceId"];
            $tmp["type"]=$yingjian["breakdownType"];
            $tmp["happen_time"]=$yingjian["timestamp"];
            $tmp["created_at"]=date('Y-m-d H:i:s',time());

            $this->saveToMysql($tmp);
        }

       return $this;
    }
    function saveToMysql($data)
    {
        if(!empty($data)){
            $this->db->insert("breakdowns",$data);
        }

    }
    function toMessage($deviceId)
    {
        $arr=[];
        $arr["stage"]=1002;
        $arr["dev_no"]=$deviceId;
        $arr["time"]=date('Y-m-d H:i:s',time());
        $this->client ->rpush('messagelist',json_encode($arr));
        return ;

    }

    
}