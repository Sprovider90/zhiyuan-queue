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

class DevicesOff implements IDataTrategy
{

    function dealData($redis,$data){
        if(!empty($data)){
            $tmp=[];
            $tmp["project_id"]=$data["projectId"];
            $tmp["device_id"]=$data["deviceId"];
            $tmp["type"]=3;
            $tmp["happen_time"]=$data["timestamp"];
            $tmp["created_at"]=date('Y-m-d H:i:s',time());
            $this->saveToMysql($tmp);
            $this->toMessage($redis,$data["deviceId"]);
            $this->updateToMysql($tmp);
        }
        return $this;
    }

    function saveToMysql($data)
    {
        if(!empty($data)){
            $db=new Orm();
            $db->insert("breakdowns",$data);
            CliHelper::cliEcho($db->last());
        }
    }
    function toMessage($redis,$deviceId)
    {
        $arr=[];
        $arr["stage"]=1001;
        $arr["dev_no"]=$deviceId;
        $arr["time"]=date('Y-m-d H:i:s',time());
        CliHelper::cliEcho("DevicesOff ".json_encode($arr));
        $redis->rpush('zhiyuan_database_messagelist',json_encode($arr));
        return ;
    }
    function updateToMysql($data)
    {
        if(!empty($data)){
            $db=new Orm();
            $db->update("devices", [
                "run_status" => 2,
                "state" => 2,
                "updated_at"=>date('Y-m-d H:i:s',time())
            ], [
                "device_number" => $data["device_id"]
            ]);
            CliHelper::cliEcho("DevicesOff ".$db->last());
        }
    }
}