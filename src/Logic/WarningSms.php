<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:13
 */

namespace Sprovider90\ZhiyuanQueue\Logic;
use Sprovider90\ZhiyuanQueue\Helper\CliHelper;

/**
 * Class WarningSms
 * @package Sprovider90\ZhiyuanQueue\Logic
 * 根据java方写入磁盘的数据来判断预警消息是否触发
 */
class WarningSms implements Icommand
{
    function run(){

        $doeds = array();
        $dirpath = "./../testyinjian/";
        $dirpath .= date('Ymd');

        while (true) {

            if (!is_dir($dirpath)) {
                CliHelper::cliEcho("当前目录下，目录 " . $dirpath . " 不存在 线程休眠100毫秒");
                usleep(1000 * 100);
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
                    $json_arr=json_decode($filecontent);
                    if(empty($json_arr)){
                        CliHelper::cliEcho($file." content not is jsonData");
                    }
                    $this->deal();
                    $endTime = microtime(true);
                    $runTime = round($endTime - $start_time,6) * 1000;
                    CliHelper::cliEcho("runtime-".$runTime);
                }

            }
            CliHelper::cliEcho("no new file come");
            usleep(1000 * 100);
        }

    }

    /**
     * 1.入库
     * 2.发送到消息服务
     */
    public function deal(){

    }
}