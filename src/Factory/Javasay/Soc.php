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

class Soc implements IDataTrategy
{
    function checkNoset($data){
        $result="";
        $arr=["battery"];
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
            $tmp["soc"]=$data["battery"];
            $this->saveToMysql($tmp);
        }
        return $this;
    }

    function saveToMysql($data)
    {
        if(!empty($data)){
            $db=new Orm();
            $db->update("devices", [
                "soc" => $data["soc"],
                "updated_at"=>date('Y-m-d H:i:s',time())
            ], [
                "device_number" => $data["device_id"]
            ]);
            CliHelper::cliEcho($db->last());
        }
    }

}