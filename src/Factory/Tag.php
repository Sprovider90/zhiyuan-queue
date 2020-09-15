<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-19
 * Time: 19:20
 */

namespace Sprovider90\Zhiyuanqueue\Factory;

use Sprovider90\Zhiyuanqueue\Model\Orm;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Helper\Tool;
class Tag
{
    protected $mysqlData=[];
    protected $db;
    const YOUZHI=1;
    const HEGE=2;
    const WURAN=3;
    function run($original_file=0){
        //判断发送
        $db = new Orm();
        if(empty($original_file)){//为了复盘数据
            $sql = "SELECT
            a.check_result,a.project_id,a.point_id,a.original_file,b.area_id
            FROM
                (
                    SELECT
                        *
                    FROM
                        `warnigs`
                    WHERE
                        waring_time = (
                            SELECT
                                max(waring_time)
                            FROM
                                warnigs
                        )
                ) a
            LEFT JOIN projects_positions b ON a.point_id = b.id";
        }else{
            $sql="SELECT
                a.check_result,a.project_id,a.point_id,a.original_file,b.area_id
            FROM
                `warnigs` a
            LEFT JOIN projects_positions b ON a.point_id = b.id
            WHERE
                a.original_file = '".$original_file."'";
        }

        $rs = $db->getAll($sql);

        if($rs) {


            $this->init($rs)->delOldData()->pointTag()->areaTag()->projectTag()->saveToMysql();
        }else{
            CliHelper::cliEcho(" no mysqldata");
        }

    }
    function init($data){
        foreach ($data as $k=>&$v){
            $v["check_result"]=json_decode($v["check_result"],true);
        }
        $this->mysqlData=$data;
        $this->db=new Orm();
        return $this;
    }
    function delOldData(){
//        $db=new Orm();
//        $db->del("tag",["original_file" => $this->mysqlData[0]["original_file"]]);
//        CliHelper::cliEcho($db->last());
        return $this;
    }
    function pointTag(){

        foreach ($this->mysqlData as $k=>&$v){
            $tmparr=array_values($v["check_result"]);
            if(in_array("wuran",$tmparr)){
                $v["pointTag"]=self::WURAN;
                continue;
            }
            if(in_array("hege",$tmparr)){
                $v["pointTag"]=self::HEGE;
                continue;
            }
            if(in_array("youzhi",$tmparr)){
                $v["pointTag"]=self::YOUZHI;
                continue;
            }
        }

        return $this;
    }
    function areaTag(){
        $this->mysqlData=Tool::arrayToArrayKey($this->mysqlData,"area_id",1);

        foreach ($this->mysqlData as $k=>&$v) {
            $arr = array_values(array_column($v, "pointTag"));
            foreach ($v as $kk=>&$vv) {
                if (in_array(self::WURAN, $arr)) {
                    $vv["areaTag"] = self::WURAN;
                    continue;
                }
                if (in_array(self::HEGE, $arr)) {
                    $vv["areaTag"] = self::HEGE;
                    continue;
                }
                if (in_array(self::YOUZHI, $arr)) {
                    $vv["areaTag"] = self::YOUZHI;
                    continue;
                }
            }
        }

        return $this;
    }
    function projectTag(){
        $this->mysqlData=Tool::arrayKeyToArr($this->mysqlData);
        $this->mysqlData=Tool::arrayToArrayKey($this->mysqlData,"project_id",1);
        foreach ($this->mysqlData as $k=>&$v) {
            $arr = array_values(array_column($v, "areaTag"));
            foreach ($v as $kk=>&$vv) {
                if (in_array(self::WURAN, $arr)) {
                    $vv["projectTag"] = self::WURAN;
                    continue;
                }
                if (in_array(self::HEGE, $arr)) {
                    $vv["projectTag"] = self::HEGE;
                    continue;
                }
                if (in_array(self::YOUZHI, $arr)) {
                    $vv["projectTag"] = self::YOUZHI;
                    continue;
                }
            }
        }
        return $this;
    }
    function saveToMysql()
    {
        $this->mysqlData=Tool::arrayKeyToArr($this->mysqlData);
        $save_data=[];
        foreach ($this->mysqlData as $k=>$v){
            $tmp=[];
            $tmp["created_at"]=date('Y-m-d H:i:s',time());
            $tmp["original_file"]=$v["original_file"];
            if(isset($v["projectTag"])){
                $tmp["model_id"]=$v["project_id"];
                $tmp["model_type"]=1;
                $tmp["air_quality"]=$v["projectTag"];
                $save_data[]=$tmp;
            }
            if(isset($v["areaTag"])){
                $tmp["model_id"]=$v["area_id"];
                $tmp["model_type"]=2;
                $tmp["air_quality"]=$v["areaTag"];
                $save_data[]=$tmp;
            }
            if(isset($v["pointTag"])){
                $tmp["model_id"]=$v["point_id"];
                $tmp["model_type"]=3;
                $tmp["air_quality"]=$v["pointTag"];
                $save_data[]=$tmp;
            }


        }

        if(!empty($save_data)){

            $this->insertOrUpdate($save_data);
            $this->db->insertAll("tag_log",$save_data);
        }


    }
    function insertOrUpdate($save_data){
        $insert=[];
        $update=[];
        foreach ($save_data as $k=>$v){
            $one=$this->db->find("tag","id",["model_id"=>$v["model_id"],"model_type"=>$v["model_type"]]);
            if(!empty($one)){
                $update[]=$v;
            }else{
                $insert[]=$v;
            }
        }

        if(!empty($insert)){
            $this->db->insertAll("tag",$insert);
        }
        if(!empty($update)){
            foreach ($update as $k=>$v){
                unset($v["created_at"]);
                $v["updated_at"]=date('Y-m-d H:i:s',time());
                $this->db->update("tag",$v,["model_id"=>$v["model_id"],"model_type"=>$v["model_type"]]);
            }
            $this->db->insertAll("tag",$insert);
        }
    }



}