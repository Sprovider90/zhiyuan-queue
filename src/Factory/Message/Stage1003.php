<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-06-30
 * Time: 17:01
 */
namespace Sprovider90\Zhiyuanqueue\Factory\Message;

use Sprovider90\Zhiyuanqueue\Exceptions\InvalidArgumentException;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Model\ZhiyuanData;

class Stage1003 implements IMessageTrategy
{
    protected $zhiyuandata;
    protected $zhibaos_hash=["humidity"=>"湿度","temperature"=>"温度","formaldehyde"=>"甲醛","PM25"=>"PM25","CO2"=>"CO2","TVOC"=>"TVOC"];
    public function __construct()
    {
        $this->zhiyuandata=new ZhiyuanData();
    }
    function getTemplateRealData($data){

        $rs=$this->zhiyuandata->getProNameAreasNameFromDevNo($data["dev_no"]);
        $data["pro_name"]=$rs["pro_name"];
        $data["project_id"]=$rs["project_id"];
        $data["monitor_id"]=$rs["monitor_id"];
        $data["position_name"]=$rs["position_name"];
        $data["target_values"]=$this->get_target_values_info($data["target_values"],$data["warnig_id"]);
        $data["target_values"]=$this->turn_target_values($data["target_values"]);

        return $data;
    }
    protected function turn_target_values($target_values){
        $result=$target_values;
        foreach ($this->zhibaos_hash as $k=>$v){
            $result=str_replace($k,$v,$result);
        }
         return $result;
    }
    protected function get_target_values_info($target_values,$warnig_id){
        $result="";
        $originaldata=$this->get_originaldata($warnig_id);
        if(empty($originaldata)){
            CliHelper::cliEcho("originaldata is empty");
            return $result;
        }
        $target_values_arr=explode(',',$target_values);
        foreach ($target_values_arr as $k=>&$v){
                $v=$v."：".$originaldata[$v]."(标准".$originaldata["proTrigger_".$v][1].")，超标；";
        }
        if(!empty($target_values_arr)){
            $result=implode("",$target_values_arr);
        }

        return $result;
    }
    protected function get_originaldata($warnig_id){
        $result=[];
        $originaldata=$this->zhiyuandata->get_warnig_info($warnig_id);
        if(!empty($originaldata)){
            $arr=json_decode($originaldata,true);
            $result=$arr[0];
        }
        return $result;
    }
    function getUsersByStage($data){
        return $this->zhiyuandata->getUsersFromWaring($data["warnig_id"]);
    }
}