<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 14:20
 */
$config=[
    "commonCheck"=>["stage","time"],
    "1001"=>["name"=>"设备离线","template"=>"【{pro_name}-{areas_name}-{dev_no}】已离线，请检查设备电量或联系客服。","url"=>"ActId=0","type"=>1,"requestCheck"=>["dev_no"],"templateContentCheck"=>["pro_name","areas_name"]],
    "1002"=>["name"=>"数据异常","template"=>"【{pro_name}-{areas_name}-{dev_no}】未正常接收数据，请联系客服。","url"=>"ActId=0","type"=>1,"requestCheck"=>["dev_no"],"templateContentCheck"=>["pro_name","areas_name"]],
    "1003"=>["name"=>"触发了预警条件","template"=>"【{pro_name}-{dev_no}-{target_values}】超标，请检查现场情况。","url"=>"ActId=1&dev_no={dev_no}&warnig_id={warnig_id}","type"=>2,"requestCheck"=>["warnig_id","dev_no","target_values"],"templateContentCheck"=>["pro_name"]],
    "1004"=>["name"=>"预报预警列表产生了一条新的消息","template"=>"有新的预警咨询信息，请及时回复。","url"=>"ActId=2&warnig_id={warnig_id}","type"=>2,"requestCheck"=>["warnig_id"]],
    "1005"=>["name"=>"解决方案列表产生了一条新的消息","template"=>"预警咨询有新的回复，请及时查看。","url"=>"ActId=3&warnig_id={warnig_id}","type"=>2,"requestCheck"=>["warnig_id"]],
    "1006"=>["name"=>"设备电量低于20%","template"=>"【{pro_name}-{areas_name}-{position_name}】设备电量低，请及时查看。","url"=>"ActId=0","type"=>1,"requestCheck"=>["dev_no"],"templateContentCheck"=>["pro_name","areas_name","position_name"]],
    "1007"=>["name"=>"设备电量低于10%","template"=>"【{pro_name}-{areas_name}-{position_name}】设备电量过低，请及时查看。","url"=>"ActId=0","type"=>1,"requestCheck"=>["dev_no"],"templateContentCheck"=>["pro_name","areas_name","position_name"]],
];
return $config;
