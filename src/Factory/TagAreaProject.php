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
class TagAreaProject
{
    const YOUZHI=1;
    const HEGE=2;
    const WURAN=3;
    protected $mysqlData=[];
    protected $type="";
    function getData(){
        $sql="select * from tag where model_type=3 and area_id is not null";
        $rs = (new Orm())->getAll($sql);

        if($rs) {
            $this->mysqlData=$rs;

        }else{
            CliHelper::cliEcho(" no Areadata");
        }
        return $this;
    }
    function tag($type)
    {
        $this->type=$type;
        $this->mysqlData=Tool::arrayToArrayKey($this->mysqlData,$type,1);

        foreach ($this->mysqlData as $k=>&$v) {
            $arr = array_values(array_column($v, "air_quality"));

            if (in_array(self::WURAN, $arr)) {
                $v["air_quality"] = self::WURAN;
                continue;
            }
            if (in_array(self::HEGE, $arr)) {
                $v["air_quality"] = self::HEGE;
                continue;
            }
            if (in_array(self::YOUZHI, $arr)) {
                $v["air_quality"] = self::YOUZHI;
                continue;
            }

        }

        return $this;
    }
    function saveToMysql()
    {

        //$this->mysqlData = Tool::arrayKeyToArr($this->mysqlData);

        $save_data = [];
        foreach ($this->mysqlData as $k => $v) {
            $tmp = [];
            $tmp["created_at"] = date('Y-m-d H:i:s', time());
            $tmp["original_file"] = '';
            $tmp["model_id"] = $k;
            $tmp["model_type"] = $this->type=="area_id"?2:1;
            $tmp["air_quality"] = $v["air_quality"];
            $save_data[] = $tmp;
        }
        return $save_data;
    }
}