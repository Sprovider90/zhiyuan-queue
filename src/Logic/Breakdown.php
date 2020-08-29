<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-23
 * Time: 17:09
 */

namespace Sprovider90\Zhiyuanqueue\Logic;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;
use Sprovider90\Zhiyuanqueue\Model\Orm;
/**
 * Class Breakdown
 * @package Sprovider90\Zhiyuanqueue\Logic
 * 故障排查数据源
 */

class Breakdown implements Icommand
{
    function run(){

        $doeds = array();
        $dirpath = "/data/yingjianbreakdown/";
        $rundate=date('Ymd');
        //$rundate="20200710";
        $dirpath .= $rundate;

        while (true) {
            if(date('Ymd')>$rundate){
                CliHelper::cliEcho($rundate."WarningSms任务处理完成");
                exit();
            }
            if (!is_dir($dirpath)) {
                CliHelper::cliEcho("当前目录下，目录 " . $dirpath . " 不存在 线程休眠1秒");
                usleep(1000 * 1000);
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
                    $this->deal($json_arr);
                    $endTime = microtime(true);
                    $runTime = round($endTime - $start_time,6) * 1000;
                    CliHelper::cliEcho("runtime-".$runTime);
                }

            }
            CliHelper::cliEcho("no new file come");
            usleep(1000 * 100);
        }

    }
    public function deal($yingjian)
    {
        $data=[];
        if(!empty($yingjian)){
            foreach ($yingjian as $k=>$v) {
                $tmp=[];
                $tmp["project_id"]=$v["projectId"];
                $tmp["device_id"]=$v["deviceId"];
                $tmp["type"]=$v["breakdownType"];
                $tmp["happen_time"]=$v["timestamp"];
                $tmp["created_at"]=date('Y-m-d H:i:s',time());
                $data[]=$tmp;
            }

            $this->saveToMysql($data);
        }

       return ;
    }
    function saveToMysql($data)
    {

        if(!empty($data)){
            $db=new Orm();
            $db->insert("breakdowns",$data);
        }

    }
}