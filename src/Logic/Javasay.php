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

class Javasay implements Icommand
{
    protected $client;
    function initRedisMysql(){
        $redisConfig=Config::get("Redis");
        $this->client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
    }
    function test(){
        //正常场景
//        $this->client ->lpush('javasay','{"event":"1","monitorId":"39","projectId":"29","deviceId":"D101","battery":"30","timestamp":"2020-09-14 16:50:00"}');
//        $this->client ->lpush('javasay','{"event":"2","monitorId":"39","projectId":"29","deviceId":"A103","timestamp":"2020-09-14 16:50:00"}');
//        $this->client ->lpush('javasay','{"event":"3","monitorId":"39","projectId":"29","deviceId":"A103","timestamp":"2020-09-14 16:50:00"}');
        //异常场景
//        $this->client ->lpush('javasay','{"event":"3"}');
//        $this->client ->lpush('javasay','{"event":"1","monitorId":"39","projectId":"29","deviceId":"D101","timestamp":"2020-09-14 16:50:00"}');
    }
    function checkNoset($data){
        $result="";
        $arr=["event","monitorId","projectId","deviceId","timestamp"];
        $tmp=[];
        foreach ($arr as $k=>$v){
            if(!isset($data[$v])){
                $tmp[]=$v;
            }
        }
        if(!empty($tmp)){
            $result=implode(',',$tmp);
        }
        return $result;
    }
    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $this->initRedisMysql();
        $this->test();
        while (true) {

            $str=$this->client ->lpop('javasay');
            if (!empty($str)) {
                $data=json_decode($str,true);

                if(empty($data)){
                    CliHelper::cliEcho("data not is json");
                    continue;
                }
                if($whoNoSet=$this->checkNoset($data)){
                    CliHelper::cliEcho($whoNoSet." no set");
                    continue;
                }
                if(!in_array($data["event"],[1,2,3])){
                    CliHelper::cliEcho("event is not right");
                    continue;
                }
                $dealTrategy="";

                switch ($data["event"])
                {
                    case 1:
                        $dealTrategy="Soc";
                    break;
                    case 2:
                        $dealTrategy="DataException";
                    break;
                    case 3:
                        $dealTrategy="DataLoss";
                    break;
                }
                $class_name="Sprovider90\Zhiyuanqueue\Factory\Javasay\\".$dealTrategy;
                $fa=new \Sprovider90\Zhiyuanqueue\Factory\JavasayFactory(new $class_name);
                try{
                    $fa->run($this->client,$data);
                }catch (\Exception $e) {
                    CliHelper::cliEcho($e->getMessage());
                }

            }
            CliHelper::cliEcho("no data sleep 1000ms");
            usleep(1000);
        }
        flush();
        ob_flush();
    }

}