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


    function run (){
        if (ob_get_level()) {
            ob_end_clean();
        }
        $redisConfig=Config::get("Redis");
        while (true) {
            $client = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
            $str=$client->lpop('breakdown');
            if (!empty($str)) {
                $data=json_decode($str,true);

                if(empty($data)){
                    CliHelper::cliEcho("data not is json");
                }
                $this->deal($data);
            }
            CliHelper::cliEcho("sleep 100ms");
            usleep(100);

        }

        flush();
        ob_flush();
    }


    public function deal($yingjian)
    {
        $data=[];
        if(!empty($yingjian)){
            $yingjian_arr[0]=$yingjian;
            foreach ($yingjian_arr as $k=>$v) {
                $tmp=[];
                $tmp["project_id"]=$v["projectId"];
                $tmp["device_id"]=$v["deviceId"];
                $tmp["type"]=$v["breakdownType"];
                $tmp["happen_time"]=$v["timestamp"];
                $tmp["created_at"]=date('Y-m-d H:i:s',time());
                $data[]=$tmp;
            }

            $this->saveToMysql($data);
        }

       return $this;
    }
    function saveToMysql($data)
    {

        if(!empty($data)){
            $db=new Orm();
            $db->insert("breakdowns",$data);
        }

    }
// 数组转换
    
}