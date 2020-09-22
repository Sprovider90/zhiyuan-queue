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

class Stage1004 implements IMessageTrategy
{
    protected $zhiyuandata;
    public function __construct()
    {
        $this->zhiyuandata=new ZhiyuanData();
    }
    function getTemplateRealData($data){
        return $data;
    }
    function getUsersByStage($data){
        return $this->zhiyuandata->getUsersFromPermissionsAndUserType(1,"项目管理-解决方案-回复");
    }
}