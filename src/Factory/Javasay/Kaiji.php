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

class Kaiji implements IDataTrategy
{
    function dealData($redis,$data){
        if(!empty($data)){
            $tmp=[];
            $tmp["project_id"]=$data["projectId"];
            $tmp["device_id"]=$data["deviceId"];
            $tmp["type"]=1;
            $tmp["happen_time"]=$data["timestamp"];
            $tmp["created_at"]=date('Y-m-d H:i:s',time());
            $this->saveToMysql($tmp);
        }
        return $this;
    }

    function saveToMysql($data)
    {
        if(!empty($data)){
            $db=new Orm();
            $db->update("devices", [
                "state" => 1,
                "updated_at"=>date('Y-m-d H:i:s',time())
            ], [
                "device_number" => $data["device_id"]
            ]);
            CliHelper::cliEcho("Kaiji ".$db->last());
        }
    }
}