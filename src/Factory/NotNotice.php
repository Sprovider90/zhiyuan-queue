<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-19
 * Time: 19:20
 */

namespace Sprovider90\Zhiyuanqueue\Factory;


use Sprovider90\Zhiyuanqueue\Model\Orm;

class NotNotice
{

    protected  $is_send;
    protected  $is_continue=1;
    /**
     * @var
     * 1项目没有设置预警或者设置得不完整（阻断流程）
     * 2预警列表源数据错误（阻断流程）
     * 3不在允许通知的时间范围
     * 4提醒频率超出
     * 5预警提醒百分比不满足
     * 6发送时短信为空
     * 7发送时项目名为空
     * 8发送时监测点为空
     * 9发送时某次发送阿里反馈有问题，详情查看日志
     */
    protected $no_send_reason;
    protected $target_name="";
    protected $data;
    protected $zhibaos=["湿度"=>"humidity","温度"=>"temperature","甲醛"=>"formaldehyde","PM25"=>"PM25","CO2"=>"CO2","TVOC"=>"TVOC"];
    public function __construct($data)
    {
        $this->data=$data;
    }

    function init(){
        $this->is_send=1;
        $this->no_send_reason=[];

        return $this;
    }
    function CheckData(){
        if(!$this->is_continue){
            return $this;
        }
        $needCheck=["notice_end_time","project_id","notice_phone"];
        foreach ($needCheck as $k=>$v) {
            if(empty($this->data[$v])){
                $this->is_send=0;
                $this->no_send_reason[]=1;
                $this->is_continue=0;
                break;
            }
        }

        $this->data["originaldata_arr"]=json_decode($this->data["originaldata"],true);
        if(empty($this->data["originaldata_arr"])){
            $this->is_send=0;
            $this->no_send_reason[]=6;
            $this->is_continue=0;
        }
        return $this;
    }

    function isPercentage(){
        if(!$this->is_continue){
            return $this;
        }
        $originaldata_arr=$this->data["originaldata_arr"];
        $originaldata_arr=$originaldata_arr[0];

        foreach ($this->zhibaos as $k=>$v) {
            //$v="formaldehyde";

            if(isset($originaldata_arr["proTrigger_".$v]) && $originaldata_arr["proTrigger_".$v]!==NULL){
                if(bccomp($originaldata_arr[$v],$originaldata_arr["proTrigger_".$v][1]*$this->data["percentage"],3)>=0){
                    $this->is_send=1;
                    $this->no_send_reason=[];
                    $this->target_name.=$k.",";
                }

            }
        }
        if($this->target_name) $this->target_name=substr($this->target_name,0,-1);
        if(empty($this->target_name)){
            $this->is_send=0;
            $this->no_send_reason[]=5;
        }
        return $this;

    }

    function noBetweenNoticeTime(){
        if(!$this->is_continue){
            return $this;
        }

        $nowhi=date("Hi",time());

        if(!($this->data["notice_start_time"]<=$nowhi&&$nowhi<$this->data["notice_end_time"])){
            $this->is_send=0;
            $this->no_send_reason[]=3;

        }
        return $this;
    }
    function frequency(){
        if(!$this->is_continue){
            return $this;
        }
        //判断X分钟内是否有发送成功过
        if(!empty($this->data["remind_time"])){
            $db=new Orm();
            $project_id=$this->data["project_id"];
            $xminsTime=date("Y-m-d H:i:s",strtotime("-".$this->data["remind_time"]." minutes"));;
            $sql="SELECT * FROM `phonenotice` where project_id={$project_id} and is_send=1 and created_at>{$xminsTime}";
            $rs=$db->getAll($sql);
            if(!empty($rs)){
                $this->is_send=0;
                $this->no_send_reason[]=4;
            }
        }

        return $this;
    }
    function notice(){
        if($this->is_send==1){

            list($mobiles,$proshortname,$pointname)=Alimsg::getSmsData($this->data);

            if(empty($proshortname)){
                $this->is_send=0;
                $this->no_send_reason[]=7;
            }
            if(empty($pointname)){
                $this->is_send=0;
                $this->no_send_reason[]=8;
            }
            $mobile_arr=explode(",",$mobiles);
            if(empty($mobile_arr)){
                $this->is_send=0;
                $this->no_send_reason[]=6;
            }
            if($this->is_send==1){

                if($_ENV["phonesms_onoff"] == "on"){
                    foreach ($mobile_arr as $k=>$v){
                        $this->target_name=str_replace(",","",$this->target_name);
                        //echo $this->target_name;exit;
                        $err_message=Alimsg::sendsms($v,$proshortname,$pointname,$this->target_name);
                        if(!empty($err_message)){
                            $this->is_send=0;
                            $this->no_send_reason[]=9;
                        }
                    }
                }
            }

        }
        return $this;
    }
    function getResult(){
        $no_send_reason=implode(',',$this->no_send_reason)?:0;
        return [$this->is_send,$no_send_reason];
    }



}