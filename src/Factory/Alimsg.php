<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-20
 * Time: 17:09
 */

namespace Sprovider90\Zhiyuanqueue\Factory;
use AlibabaCloud\Client\AlibabaCloud;
use Sprovider90\Zhiyuanqueue\Model\Orm;
class Alimsg
{

    public static function getSmsData($data){
        $db=new Orm();
        $project_info=$db->find("projects","name",["id"=>$data["project_id"]]);
        $point_info=$db->find("projects_positions","name",["id"=>$data["point_id"]]);
        //异常处理
        return [$data["notice_phone"],$project_info["name"],$point_info["name"]];
    }
    public static function sendsms($mobile,$proshortname,$pointname,$target_name)
    {
        $err_message="";
        $config=Config::get("Aliyun");

        AlibabaCloud::accessKeyClient($config["aliyun_Dysmsapi_key"], $config["aliyun_Dysmsapi_secret"])
            ->regionId('cn-hangzhou')
            ->asGlobalClient();

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $mobile,
                        'SignName' => "至源",
                        'TemplateCode' => "SMS_195870858",
                        'TemplateParam' => json_encode(["proshortname" =>"{$proshortname}","pointname"=>"{$pointname}","target"=>"{$target_name}"])
                    ],
                ])
                ->request();
            $result_arr=$result->toArray();
            if($result_arr["Message"]!=="OK"){
                $err_message=$result_arr["Code"];
            }
            Monolog::get()->info('shortmessage_response' . print_r(func_get_args(), true) . "result:" . print_r($result->toArray(), true));
        } catch (ClientException $e) {
            $err_message="ClientException";
            Monolog::get()->error(print_r(func_get_args(), true) . " result:" . $e->getErrorMessage());
        } catch (ServerException $e) {
            $err_message="ServerException";
            Monolog::get()->error(print_r(func_get_args(), true) . " result:" . $e->getErrorMessage());
        }

        return $err_message;
    }


}