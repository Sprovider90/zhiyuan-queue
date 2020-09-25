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
    public function __construct()
    {
        $this->zhiyuandata=new ZhiyuanData();
    }
    function getTemplateRealData($data){
        $rs=$this->zhiyuandata->getProNameAreasNameFromDevNo($data["dev_no"]);
        $data["pro_name"]=$rs["pro_name"];
        $data["project_id"]=$rs["project_id"];
        $data["monitor_id"]=$rs["monitor_id"];
        return $data;
    }
    function getUsersByStage($data){
        return $this->zhiyuandata->getUsersFromWaring($data["warnig_id"]);
    }
}