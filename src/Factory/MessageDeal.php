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
        foreach ($this->messageTemplate["commonCheck"] as $k=>$v){
            if(empty($this->smsData[$v])){
                throw new InvalidArgumentException($v." is null");
            }
        }
        if(!in_array($this->smsData["stage"],array_keys($this->messageTemplate))){
            throw new InvalidArgumentException("stage is err");
        }
        return $this;
    }
    function requestCheck(){
        foreach ($this->messageTemplate[$this->smsData["stage"]]["requestCheck"] as $k=>$v){
            if(empty($this->smsData[$v])){
                throw new InvalidArgumentException($v." is null");
            }
        }
        return $this;
    }
    function getTemplateRealData(){
        $this->type=$this->messageTemplate[$this->smsData["stage"]]["type"];
        $class_name="Sprovider90\Zhiyuanqueue\Factory\Message\\"."Stage".$this->smsData["stage"];
        $fa=new MessageFactory(new $class_name);
        $this->smsData=$fa->getTemplateRealData($this->smsData);
        return $this;
    }
    function contentCheck(){
        foreach ($this->messageTemplate[$this->smsData["stage"]]["templateContentCheck"] as $k=>$v){
            if(empty($this->smsData[$v])){
                throw new InvalidArgumentException($v." is null");
            }
        }
        return $this;
    }
    function createContent(){
        $this->content=Tool::combine_template($this->smsData,$this->messageTemplate[$this->smsData["stage"]]["template"]);
        return $this;
    }
    function usersCheck()
    {
        if(empty($this->rev_users)){
            throw new InvalidArgumentException("rev_users is null");
        }
        return $this;
    }
    function createUsers(){
        $this->type=$this->messageTemplate[$this->smsData["stage"]]["type"];
        $class_name="Sprovider90\Zhiyuanqueue\Factory\Message\\"."Stage".$this->smsData["stage"];
        $fa=new MessageFactory(new $class_name);
        $this->rev_users=$fa->getUsersByStage($this->smsData);
        return $this;
    }
    function saveSms(){
        $time=time();
        $db=new Orm();
        foreach ($this->rev_users as $key => $value) {
            # code...
            $data=[];
            $data["type"]=$this->type;
            $data["content"]=$this->content;
            $data["rev_users"]=json_encode($this->rev_users);
            $data["user_id"]=$value;
            $data["send_time"]=$this->smsData['time'];
            $data["created_at"]=date('Y-m-d H:i:s',$time);

            $this->smsRedisData['sms_id']=$db->insert("message",$data);
        }
        return $this;
    }

}