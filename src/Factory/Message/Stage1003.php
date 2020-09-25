<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-06-30
 * Time: 17:01
 */
namespace Sprovider90\Zhiyuanqueue\Factory\Message;

use Sprovider90\Zhiyuanqueue\Exceptions\InvalidArgumentException;
use Sprovider90\Zhiyuanqueue\Model\ZhiyuanData;

class Stage1003 implements IMessageTrategy
{
    protected $zhiyuandata;
    protected $zhibaos_hash=["humidity"=>"湿度","temperature"=>"温度","formaldehydecd"=>"甲醛","PM25"=>"PM25","CO2"=>"CO2","TVOC"=>"TVOC"];
    public function __construct()
    {
        $this->zhiyuandata=new ZhiyuanData();
    }
    function getTemplateRealData($data){
        $rs=$this->zhiyuandata->getProNameAreasNameFromDevNo($data["dev_no"]);
        $data["pro_name"]=$rs["pro_name"];
        $data["project_id"]=$rs["project_id"];
        $data["monitor_id"]=$rs["monitor_id"];
        $data["target_values"]=$this->turn_target_values($data["target_values"]);
        return $data;
    }
    protected function turn_target_values($target_values){
        $result=$target_values;
        foreach ($this->zhibaos_hash as $k=>$v){
            $result=str_replace($k,$v,$result);
        }
        print_r($result);exit;
        return $result;
    }
    function getUsersByStage($data){
        return $this->zhiyuandata->getUsersFromWaring($data["warnig_id"]);
    }
}