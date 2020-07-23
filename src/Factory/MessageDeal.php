<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 16:38
 */

namespace Sprovider90\Zhiyuanqueue\Factory;

use Sprovider90\Zhiyuanqueue\Exceptions\InvalidArgumentException;
use Sprovider90\Zhiyuanqueue\Helper\Tool;
use Sprovider90\Zhiyuanqueue\Model\Orm;
use Sprovider90\Zhiyuanqueue\Model\Redis;
use Sprovider90\Zhiyuanqueue\Model\zhiyuanData;

class MessageDeal
{
    protected $type;
    protected $content;
    protected $rev_users;
    protected $smsData;
    protected $smsRedisData;
    protected $messageTemplate;
    public function __construct($data)
    {
        $this->smsData=$data;
        $this->messageTemplate=Config::get("MessageTemplate");
        return $this;
    }
    function checkCommon(){
        if(empty($this->smsData["stage"])){
            new InvalidArgumentException("stage is null");
        }
        if(empty($this->smsData["time"])){
            new InvalidArgumentException("time is null");
        }
        if(!in_array($this->smsData["stage"],array_keys($this->messageTemplate))){
            new InvalidArgumentException("stage is err");
        }
        return $this;
    }
    function createAndCheckStageData(){
        $this->type=$this->messageTemplate[$this->smsData["stage"]]["type"];
        $this->type=$this->messageTemplate[$this->smsData["stage"]]["type"];
        $this->getOtherData($this->smsData);
        $this->content=Tool::combine_template($this->smsData,$this->messageTemplate[$this->smsData["stage"]]["template"]);
        $this->rev_users=$this->getUsersByStage($this->smsData);
        return $this;
    }
    function checkStageContent(){

        if(strpos($this->content,'${') !== false){

            new InvalidArgumentException("content is err");
        }

        return $this;
    }
    function checkUsers()
    {
        if(empty($this->rev_users)){
            new InvalidArgumentException("rev_users is null");
        }
        return $this;
    }

    function saveSms(){
        $time=time();
        $db=new Orm();
        $data=[];
        $data["type"]=$this->type;
        $data["content"]=$this->content;
        $data["rev_users"]=json_encode($this->rev_users);
        $data["send_time"]=date('Y-m-d H:i:s',$this->smsData['time']);
        $data["created_at"]=date('Y-m-d H:i:s',$time);

        $this->smsRedisData['sms_id']=$db->insert("message",$data);
        $this->smsRedisData['sms_time']=$time;
        $this->smsRedisData['smstype']=$this->type;
        $this->smsRedisData['user_ids']=$this->rev_users;
        return $this;
    }
    function saveUserSms(){
        $user_ids=$this->smsRedisData["user_ids"];
        $smstype=$this->smsRedisData["smstype"];
        $sms_id=$this->smsRedisData["sms_id"];
        $sms_time=$this->smsRedisData["sms_time"];
        $redis=new Redis();
        foreach ($user_ids as $k=>$user_id) {
            $redis->zadd("smsuser:" . $user_id, $smstype . $sms_time, $sms_id);
        }
        return $this;
    }
private function getOtherData(&$data){
        $zhiyuandata=new zhiyuanData();
        switch ($data["stage"])
        {
            case 1001:
                if(empty($data["dev_no"])){
                    new InvalidArgumentException("dev_no is null");
                }
                $data["areas_name"]=$zhiyuandata->getAreasNameFromDevNo();
                $data["pro_name"]=$zhiyuandata->getProNameFromDevNo();
            break;
            case 1002:
                if(empty($data["dev_no"])){
                    new InvalidArgumentException("dev_no is null");
                }
                $data["areas_name"]=$zhiyuandata->getAreasNameFromDevNo();
                $data["pro_name"]=$zhiyuandata->getProNameFromDevNo();
            case 1003:
                if(empty($data["dev_no"])){
                    new InvalidArgumentException("dev_no is null");
                }
                if(empty($data["target_values"])){
                    new InvalidArgumentException("target_values is null");
                }
                $data["pro_name"]=$zhiyuandata->getProNameFromDevNo();
            break;
            default:

        }
        return ;
    }
    private  function getUsersByStage(&$data){
        $users=[];
        $zhiyuandata=new zhiyuanData();
        switch ($data["stage"])
        {
            case 1001:
                $users=$zhiyuandata->getUsersFromDevNo($data["dev_no"]);
                break;
            case 1002:
                $users=$zhiyuandata->getUsersFromDevNo($data["dev_no"]);
            case 1003:
                $users=$zhiyuandata->getUsersFromDevNo($data["dev_no"]);
                break;
            case 1004:
                $users=$zhiyuandata->getUsersFromPermissions(["数据中心回复权限","解决方案回复权限"]);
                break;
            case 1005:
                $users=$zhiyuandata->getUsersFromPermissions(["客户平台发送消息权限","预报预警发送消息权限"]);
                break;
            default:
        }
        return $users;
    }

}