<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:13
 */

namespace Sprovider90\Zhiyuanqueue\Logic;
use Sprovider90\Zhiyuanqueue\Factory\Config;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Model\Orm;
use Sprovider90\Zhiyuanqueue\Helper\Tool;
/**
 * Class WarningSms
 * @package Sprovider90\Zhiyuanqueue\Logic
 * 根据java方写入磁盘的数据来判断预警消息是否触发
 * 根据laravel任务写的项目每个阶段的预警监控指标
 */
class WarningSms implements Icommand
{
    protected $proThresholdNow=[];
    protected $file_name="";
    protected $zhibaos=["humidity","temperature","formaldehyde","PM25","CO2","TVOC"];
    protected $zhibaos_hash=["humidity"=>"湿度","temperature"=>"温度","formaldehydecd .."=>"甲醛","PM25"=>"PM25","CO2"=>"CO2","TVOC"=>"TVOC"];
    protected $redis;
    public function __construct()
    {
        $redisConfig=Config::get("Redis");
        $this->redis = new \Predis\Client('tcp://'.$redisConfig["host"].':'.$redisConfig["port"]);
    }

    function run(){
        $doeds = array();
        $max_waring_time=1000000000000;
        $dirpath = "/data/yingjian/";
        //$dirpath = "../testdata/yingjian/";
        $rundate=date('Ymd');
        $dirpath .= $rundate;

        //异常重启不重新处理
        $db=new Orm();
        $sql="SELECT
                max(waring_time) as max_waring_time
            FROM
                `warnigs` ";
        $rs = $db->getAll($sql);
        if($rs) {
            $max_waring_time=$rs[0]["max_waring_time"];
        }

        while (true) {
            if(date('Ymd')>$rundate){

                $doeds = array();
                $dirpath=str_replace($rundate,date('Ymd'),$dirpath);
                $rundate=date('Ymd');
                CliHelper::cliEcho($dirpath." 开启新一天的计算");

            }
            if (!is_dir($dirpath)) {
                CliHelper::cliEcho("当前目录下，目录 " . $dirpath . " 不存在 线程休眠1秒");
                usleep(1000 * 1000 * 1);
                continue;

            }

            $allfiles = scandir($dirpath);

            $files = array_diff($allfiles, $doeds);//差集
            $doeds = $allfiles;

            foreach ($files as $file) {
                $file = $dirpath . '/' . $file;
                if (is_file($file)) {


                    $start_time = microtime(true);
                    CliHelper::cliEcho($file." deal file start");
                    $filecontent=file_get_contents($file);
                    $json_arr=json_decode($filecontent,true);

                    if(empty($json_arr)){
                        CliHelper::cliEcho($file." content not is jsonData");
                    }

                    if($json_arr[0]["timestamp"]<=$max_waring_time){
                        CliHelper::cliEcho($json_arr[0]["timestamp"]." already deal!");
                        continue;
                    }
                    $this->file_name=$file;
                    //更新当前项目阈值
                    $this->setProStageThresholdNow();

                    $this->deal($json_arr);
                    CliHelper::cliEcho($file." deal file end");
                    $endTime = microtime(true);
                    $runTime = round($endTime - $start_time,6) * 1000;
                    CliHelper::cliEcho("runtime-".$runTime." ".$file);
                }

            }
            CliHelper::cliEcho("no new file come 线程休眠30秒");
            usleep(1000 * 1000 * 30);
        }

    }

    /**
     * 实时
     */
    public function setProStageThresholdNow(){
        $this->proThresholdNow=[];
        $db = new Orm();
        $sql = "SELECT
                a.id AS project_id,
                a.stage_id,
                c.`name` AS thresholds_name,
                CASE
            WHEN d.thresholdinfo IS NULL THEN
                'thresholds'
            ELSE
                'projects_thresholds'
            END AS fromwhere,
             CASE
            WHEN d.thresholdinfo IS NULL THEN
                c.thresholdinfo
            ELSE
                d.thresholdinfo
            END AS thresholdinfo
            FROM
                `projects` a
            LEFT JOIN projects_stages b ON a.stage_id = b.id
            LEFT JOIN thresholds c ON b.threshold_id = c.id
            LEFT JOIN projects_thresholds d ON a.stage_id = d.stage_id
            WHERE
                a. STATUS IN (4, 5, 6)
            AND a.stage_id IS NOT NULL
            AND b.deleted_at IS NULL
            AND c.thresholdinfo IS NOT NULL";

        $rs = $db->getAll($sql);
        if(!empty($rs)){
            $this->proThresholdNow=Tool::arrayToArrayKey($rs,"project_id");
        }else{
            CliHelper::cliEcho("no ProThresholdNow data");
        }

    }
    /**
     * 1.入库
     * 2.发送到消息服务
     */
    public function deal($yingjian){
        if(empty($this->proThresholdNow)){
            CliHelper::cliEcho("this proThresholdNow no data");
            return;
        }
        $kzarr=$this->proThresholdNow;

        $points=$this->dealKzData($kzarr)->mergeData($kzarr,$yingjian)->getTriggerPonits($yingjian);;
        $this->saveToMysqlAndMessage($points);



        //刷新标签数据
        $tag=new \Sprovider90\Zhiyuanqueue\Factory\Tag();
        $tag->run($this->file_name);

    }

    function dealKzData(&$kzarr)
    {
        foreach ($kzarr as $k=>&$v) {
            $tmparr=json_decode($v["thresholdinfo"],true);
            foreach ($tmparr as $tmparrk=>&$tmparrv) {
                $tmparrv=explode("~",$tmparrv);
            }
            $v["thresholdinfo"]=$tmparr;
        }

        return $this;
    }
    function mergeData($kzarr,&$yingjian)
    {
        foreach ($yingjian as $k=>&$v){
            if(!isset($kzarr[$v["projectId"]]["thresholdinfo"])){
                CliHelper::cliEcho($v["projectId"]." no thresholdinfo!");
                continue;
            }
            foreach ($this->zhibaos as $k_zhibiao =>$v_zhibiao){
                $v["proTrigger_".$v_zhibiao]=NULL;
            }

            if(isset($kzarr[$v["projectId"]]["thresholdinfo"])){

                foreach ($kzarr[$v["projectId"]]["thresholdinfo"] as $kz_k=>$kz_v) {
                    $v["proTrigger_".$kz_k]=$kz_v;
                }
            }
            if(isset($kzarr[$v["projectId"]]["thresholds_name"])){
                $v["proTrigger_thresholds_name"]=$kzarr[$v["projectId"]]["thresholds_name"];
            }

        }
        return $this;
    }
    function getTriggerPonits($yingjian){

        $result=[];
        if(!empty($yingjian)){
            foreach ($yingjian as $yingjian_k=>$yingjian_v) {

                foreach ($yingjian_v as $k => $v) {
                    if(!in_array($k, $this->zhibaos)){
                        continue;
                    }
                    //数据无法检测
                    if(empty($yingjian_v["proTrigger_" . $k]) || $yingjian_v["proTrigger_" . $k][0] == NULL || $yingjian_v["proTrigger_" . $k][1] == NULL){
                        $result[$yingjian_v["projectId"]."-".$yingjian_v["monitorId"]][] = array_merge($yingjian_v, ["check_result"=>[$k=>"noset"]]);
                        continue;
                    }
                    //触发预警消息列表&&判定指标的空气质量
                    //污染
                    if (in_array($k, $this->zhibaos) && $yingjian_v["proTrigger_" . $k][1] !== NULL) {
                        if(bccomp($yingjian_v[$k],$yingjian_v["proTrigger_" . $k][1],3)>=0) {
                            $result[$yingjian_v["projectId"] . "-" . $yingjian_v["monitorId"]][] = array_merge($yingjian_v, ["check_result" => [$k => "wuran"]]);
                            continue;
                        }
                    }
                    //合格
                    if (in_array($k, $this->zhibaos) && $yingjian_v["proTrigger_" . $k][0] !== NULL) {
                        if(bccomp($yingjian_v[$k],$yingjian_v["proTrigger_" . $k][0],3)>=0) {
                            $result[$yingjian_v["projectId"] . "-" . $yingjian_v["monitorId"]][] = array_merge($yingjian_v, ["check_result" => [$k => "hege"]]);
                            continue;
                        }
                    }
                    //优质
                    if (in_array($k, $this->zhibaos) && $yingjian_v["proTrigger_" . $k][0] !== NULL && $yingjian_v[$k] < $yingjian_v["proTrigger_" . $k][0]) {
                        if(bccomp($yingjian_v[$k],$yingjian_v["proTrigger_" . $k][0],3)<0) {
                            $result[$yingjian_v["projectId"] . "-" . $yingjian_v["monitorId"]][] = array_merge($yingjian_v, ["check_result" => [$k => "youzhi"]]);
                            continue;
                        }
                    }
                }

            }
        }
        return $result;
    }
    function message($device_id,$warnig_id,$threshold_keys)
    {
        if($threshold_keys && $warnig_id) {
            $arr = [];
            $arr["stage"] = 1003;
            $arr["dev_no"] = $device_id;
            $arr["warnig_id"] = $warnig_id;
            $arr["target_values"] = $threshold_keys;
            $arr["time"] = date('Y-m-d H:i:s', time());
            $this->redis->rpush('zhiyuan_database_messagelist', json_encode($arr));
        }
    }
    function saveToMysqlAndMessage($data)
    {
        $data=$this->TurnDataToMysql($data);

        if(!empty($data)){

            $db=new Orm();
            foreach ($data as $k=>$v){
                $id=$db->insert("warnigs",$v);
                $this->message($v["device_id"],$id,$v["threshold_keys"]);
            }

        }
        return $data;
    }
    function TurnDataToMysql($data)
    {
        $result=[];
        if(!empty($data)){
            foreach ($data as $k=>$v) {
                $tmp=[];
                $tmp_threshold_keys=[];
                $tmp_check_result=[];
                foreach ($v as $v_k=>$v_v) {

                    if($v_k==0){
                        $tmp["project_id"]=$v_v["projectId"];
                        $tmp["point_id"]=$v_v["monitorId"];
                        $tmp["device_id"]=$v_v["deviceId"];
                        $tmp["waring_time"]=$v_v["timestamp"];
                        $tmp["thresholds_name"]=$v_v["proTrigger_thresholds_name"];
                        $tmp["created_at"]=date('Y-m-d H:i:s',time());
                        $tmp["originaldata"]=json_encode($v);

                    }
                    if(isset($v_v["check_result"])){

                        foreach ($v_v["check_result"] as $kk=>$vv){
                            $tmp_check_result[$kk]=$vv;
                            if($vv=="wuran"){
                                $tmp_threshold_keys[]=$kk;
                            }
                        }

                    }

                }

                $tmp["original_file"]=$this->file_name;
                $tmp["threshold_keys"]=implode(',',$tmp_threshold_keys);
                $tmp["check_result"]=json_encode($tmp_check_result);

                $result[]=$tmp;
            }
        }
        return $result;
    }

}