<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 17:26
 */

namespace Sprovider90\Zhiyuanqueue\Model;
use Sprovider90\Zhiyuanqueue\Model\Orm;

class zhiyuanData
{
    function getProNameAreasNameFromDevNo($deviceId){
        $result=[];
        $sql="SELECT
                c.device_number,
                b.area_name,
                d.`name`
            FROM
                devices c
            LEFT JOIN projects_positions a ON c.id = a.device_id
            LEFT JOIN projects_areas b ON a.area_id = b.id
            LEFT JOIN projects d ON b.project_id = d.id
            WHERE
                c.device_number = '".$deviceId."'";
        $db=new Orm();
        $rs=$db->getAll($sql);
        if(!empty($rs)){
            $result["areas_name"]=$rs[0]["area_name"];
            $result["pro_name"]=$rs[0]["name"];
        }
        return $result;
    }

    function getUsersFromDevNo(){
        return [1,2,3];
    }
    function getUsersFromPermissions()
    {
        return [1, 2, 3];
    }

}