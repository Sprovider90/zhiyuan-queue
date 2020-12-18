<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-06-30
 * Time: 17:04
 */

namespace Sprovider90\Zhiyuanqueue\Factory\Javasay;

use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Model\Orm;

class Signal implements IDataTrategy
{
    function checkNoset($data){
        $result="";
        $arr=["signal"];
        foreach ($arr as $k=>$v){
            $tmp=[];
            if(!isset($data[$v])){
                $tmp[]=$v;
            }
        }
        if(!empty($tmp)){
            $result=implode(',',$tmp);
        }
        return $result;
    }
    function dealData($redis,$data){
        if(!empty($data)){
            if($whoNoSet=$this->checkNoset($data)){
                throw new \Exception($whoNoSet." no set");
            }
            $tmp=[];
            $tmp["project_id"]=$data["projectId"];
            $tmp["device_id"]=$data["deviceId"];
            $tmp["type"]=1;
            $tmp["happen_time"]=$data["timestamp"];
            $tmp["created_at"]=date('Y-m-d H:i:s',time());
            $tmp["signal"]=$data["signal"];
            $this->saveToMysql($tmp);
            //$this->toMessage($redis,$data["deviceId"],$data["battery"]);
        }
        return $this;
    }

    function saveToMysql($data)
    {
        if(!empty($data)){
            $db=new Orm();
            $db->update("devices", [
                "rssi" => $data["signal"],
                "updated_at"=>date('Y-m-d H:i:s',time())
            ], [
                "device_number" => $data["device_id"]
            ]);
            CliHelper::cliEcho("Signal ".$db->last());
        }
    }
//    function toMessage($redis,$deviceId,$battery)
//    {
//        if($battery<=20){
//            $arr=[];
//            $arr["stage"]=1006;
//            if($battery<=10) $arr["stage"]=1007;
//            $arr["dev_no"]=$deviceId;
//            $arr["time"]=date('Y-m-d H:i:s',time());
//
//            CliHelper::cliEcho("Soc ".json_encode($arr));
//            $redis->rpush('zhiyuan_database_messagelist',json_encode($arr));
//        }
//        return ;
//    }

}