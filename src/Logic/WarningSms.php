<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:13
 */

namespace Sprovider90\Zhiyuanqueue\Logic;
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

    function run(){
        $doeds = array();
        $max_waring_time=1000000000000;
        $dirpath = "/data/yingjian/";
        //$dirpath = "F:/yingjian/11/";
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

                CliHelper::cliEcho($rundate."WarningSms任务处理完成,开启新一天的计算");
                $doeds = array();
                $dirpath=str_replace($rundate,date('Ymd'),$dirpath);

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
                    $this->setProThresholdNow($json_arr[0]["timestamp"]);

                    $this->deal($json_arr);
                    $endTime = microtime(true);
                    $runTime = round($endTime - $start_time,6) * 1000;
                    CliHelper::cliEcho("runtime-".$runTime." ".$file);
                }

            }
            CliHelper::cliEcho("no new file come 线程休眠30秒");
            usleep(1000 * 1000 * 30);
        }

    }
    public function setProThresholdNow($kztime){
        $this->proThresholdNow=[];
        $db = new Orm();
        $sql = "SELECT
                    a.project_id,a.thresholds_name,a.thresholdinfo
                FROM
                    pro_thresholds_log AS a,
                    (
                        SELECT
                            project_id,
                            max(id) AS maxid
                        FROM
                            pro_thresholds_log
                        WHERE created_at<='".$kztime."'
                        GROUP BY
                            project_id
                    ) AS b
                WHERE
                    a.id = b.maxid";

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
        $this->saveToMysql($points);
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
    function saveToMysql($data)
    {
        $data=$this->TurnDataToMysql($data);
        if(!empty($data)){
            $db=new Orm();
            $db->insert("warnigs",$data);
        }

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