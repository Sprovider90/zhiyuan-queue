<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:13
 */

namespace Sprovider90\Zhiyuanqueue\Logic;
use Sprovider90\Zhiyuanqueue\Model\Orm;
use Sprovider90\Zhiyuanqueue\Factory\NotNotice;
use Sprovider90\Zhiyuanqueue\Helper\CliHelper;


class PhoneNotice implements Icommand
{
    /**
     * 根据预警消息数据以及其他用户配置信息表来计算出需不需要发送消息
     */
    function run(){
        //判断发送
        while (true) {
            $db = new Orm();
            $sql = "SELECT
                         a.*,b.id as projects_waring_setting_id,b.remind_time,b.percentage,b.notice_start_time,b.notice_end_time,notice_phone
                    FROM
                        `warnigs` a
                    LEFT JOIN projects_waring_setting b ON a.project_id = b.project_id
                    WHERE

                        a.id > (
                            SELECT
                                IFNULL(MAX(warnigs_id),0)
                            FROM
                            
                                phonenotice
                        ) order by a.id asc limit 10;";

//            $sql = "SELECT
//                         a.*,b.id as projects_waring_setting_id,b.remind_time,b.percentage,b.notice_start_time,b.notice_end_time,notice_phone
//                    FROM
//                        `warnigs` a
//                    LEFT JOIN projects_waring_setting b ON a.project_id = b.project_id
//                    WHERE
//
//                        a.id =517889 order by a.id asc limit 1;";
            $rs = $db->getAll($sql);
            if(!empty($rs)){
                foreach ($rs as $k=>$v){
                    if($v) {
                        $notNotice=new NotNotice($v);
                        list($is_send,$no_send_reason)=$notNotice->init()->CheckData()->isPercentage()->noBetweenNoticeTime()->frequency()->notice()->getResult();

                        $data=$this->TurnDataToMysql($v,$is_send,$no_send_reason);
                        $this->saveToMysql($data);
                    }else{
                        CliHelper::cliEcho(" no data sleep 1s");
                        sleep(1);
                    }
                    CliHelper::cliEcho("sleep 100ms");
                    usleep(100);
                }
            }
        }
    }
    function saveToMysql($data)
    {

        if(!empty($data)){
            $db=new Orm();
            $db->insert("phonenotice",$data);
        }

    }
    function TurnDataToMysql($data,$is_send,$no_send_reason)
    {
        $result=[];
        $result["warnigs_id"]=$data["id"];
        $result["projectsetting_kz_json"]=json_encode($data);
        $result["is_send"]=$is_send;
        $result["project_id"]=$data["project_id"];
        $result["no_send_reason"]=$no_send_reason;
        $result["created_at"]=date('Y-m-d H:i:s',time());
        return $result;
    }


}