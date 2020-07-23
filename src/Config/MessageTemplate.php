<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 14:20
 */
$config=[
    "1001"=>["name"=>"设备离线","template"=>"【{pro_name}-{areas_name}-{dev_no}】已离线，请检查设备电量或联系客服。","url"=>"ActId=0","type"=>1,"check"=>["pro_name","areas_name","dev_no"]],
    "1002"=>["name"=>"数据异常","template"=>"【{pro_name}-{areas_name}-{dev_no}】未正常接收数据，请联系客服。","url"=>"ActId=0","type"=>1,"check"=>["pro_name","areas_name","dev_no"]],
    "1003"=>["name"=>"触发了预警条件","template"=>"【{pro_name}-{dev_no}-{target_values}】超标，请检查现场情况。","url"=>"ActId=1","type"=>2,"check"=>["pro_name","target_values","dev_no"]],
    "1004"=>["name"=>"预报预警列表产生了一条新的","template"=>"有新的预警咨询信息，请及时回复。","url"=>"ActId=2","type"=>2],
    "1005"=>["name"=>"解决方案列表产生了一条新的","template"=>"预警咨询有新的回复，请及时查看。","url"=>"ActId=3","type"=>2]
];
return $config;
