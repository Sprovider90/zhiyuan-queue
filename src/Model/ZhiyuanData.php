<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 17:26
 */

namespace Sprovider90\Zhiyuanqueue\Model;


class zhiyuanData
{
    function getProNameFromDevNo(){
        return "项目1";
    }
    function getAreasNameFromDevNo(){
        return "地区1";
    }
    function getUsersFromDevNo(){
        return [1,2,3];
    }
    function getUsersFromPermissions()
    {
        return [1, 2, 3];
    }

}