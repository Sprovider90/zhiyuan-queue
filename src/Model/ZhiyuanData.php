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
            a. NAME AS position_name,
            b.area_name,
            d.`name`
        FROM
            devices c
        LEFT JOIN projects_positions a ON c.id = a.device_id
        LEFT JOIN projects_areas b ON a.area_id = b.id
        LEFT JOIN projects d ON b.project_id = d.id
        WHERE
            a.deleted_at IS NULL
        AND b.deleted_at IS NULL
        AND c.deleted_at IS NULL
        AND d.deleted_at IS NULL
        AND c.device_number = '".$deviceId."'";

        $db=new Orm();
        $rs=$db->getAll($sql);
        if(!empty($rs)){
            $result["pro_name"]=$rs[0]["name"];
            $result["position_name"]=$rs[0]["position_name"];
            $result["areas_name"]=$rs[0]["area_name"];
        }
        return $result;
    }

    /**
     * 设备所在的项目的所有用户
     * @return array
     */
    function getUsersFromDevNo($dev_no){
        $result=[];
        $sql="SELECT
        DISTINCT a.id
        FROM
        `users` a
        INNER JOIN `devices` b ON a.customer_id = b.customer_id
        WHERE
        b.device_number = '{$dev_no}'";
        $db=new Orm();
        $rs=$db->getAll($sql);
        if(!empty($rs)){
            $result=array_column($rs,"id");
        }
        return $result;
    }
    /**
     * 预警所在项目的所有用户
     */
    function getUsersFromWaring($warning_id){
        $result=[];
        $sql="select
            DISTINCT a.id
            from
            users a
            INNER JOIN projects b on a.customer_id=b.customer_id
            INNER JOIN warnigs c on b.id=c.project_id
            where c.id={$warning_id}";
        $db=new Orm();
        $rs=$db->getAll($sql);
        if(!empty($rs)){
            $result=array_column($rs,"id");
        }
        return $result;
    }

    /**
     * 账号类型为“数据中心”且有“项目管理-解决方案-回复”权限的用户
        账号类型为“客户平台”且有“项目管理-预警警报-发送消息”权限的用户
     *  int usertype 账号类型,1是数据中心2是客户平台
     * @return array
     */
    function getUsersFromPermissionsAndUserType($usertype,$permission_name)
    {
        $result=[];
        $sql="select
        DISTINCT a.id
        from
        users a
        INNER JOIN model_has_roles b on a.id = b.model_id
        INNER JOIN role_has_permissions c on b.role_id = c.role_id
        INNER JOIN permissions d on c.permission_id = d.id
        where d.name='{$permission_name}' and a.type={$usertype}";
        $db=new Orm();
        $rs=$db->getAll($sql);
        if(!empty($rs)){
            $result=array_column($rs,"id");
        }
        return $result;
    }

}