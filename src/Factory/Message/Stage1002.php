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
    function getTemplateRealData($data){
        $zhiyuandata=new zhiyuanData();
        if(empty($data["dev_no"])){
            new InvalidArgumentException("dev_no is null");
        }
        $rs=$zhiyuandata->getProNameAreasNameFromDevNo($data["dev_no"]);
        if(empty($rs)){
            new InvalidArgumentException("dev_no result is null");
        }
        if(empty($rs["pro_name"])){
            new InvalidArgumentException("pro_name is null");
        }
        if(empty($rs["areas_name"])){
            new InvalidArgumentException("areas_name is null");
        }
        $data["areas_name"]=$rs["areas_name"];
        $data["pro_name"]=$rs["pro_name"];
        return $data;
    }
    function getUsersByStage($data){
        
    }
}