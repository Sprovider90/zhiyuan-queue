<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-06-30
 * Time: 17:01
 */
namespace Sprovider90\Zhiyuanqueue\Factory\Message;

use Sprovider90\Zhiyuanqueue\Exceptions\InvalidArgumentException;
use Sprovider90\Zhiyuanqueue\Model\zhiyuanData;

class Stage1002 implements IMessageTrategy
{
    protected $zhiyuandata;
    public function __construct()
    {
        $this->zhiyuandata=new zhiyuanData();
    }
    function getTemplateRealData($data){
        $rs=$this->zhiyuandata->getProNameAreasNameFromDevNo($data["dev_no"]);
        $data["areas_name"]=$rs["areas_name"];
        $data["pro_name"]=$rs["pro_name"];
        return $data;
    }
    function getUsersByStage($data){
        return $this->zhiyuandata->getUsersFromDevNo($data["dev_no"]);
    }
}