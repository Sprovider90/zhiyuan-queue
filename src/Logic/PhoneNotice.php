<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:13
 */

namespace Sprovider90\Zhiyuanqueue\Logic;
use AlibabaCloud\Client\AlibabaCloud;
use Sprovider90\Zhiyuanqueue\Factory\Monolog;
class PhoneNotice implements Icommand
{
    /**
     * 根据预警消息数据以及其他用户配置信息表来计算出需不需要发送消息
     */
    function run(){
        //判断发送
        $this->sendsms();
    }
    public  function sendsms()
    {
        $mobile="";
        AlibabaCloud::accessKeyClient("", "")
            ->regionId('cn-hangzhou') // replace regionId as you need
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
                        'TemplateParam' => json_encode(["proshortname" =>"天上人间","pointname"=>"客厅","target"=>"甲醛"])
                    ],
                ])
                ->request();
            Monolog::getInstance()->info('shortmessage_response' . print_r(func_get_args(), true) . "result:" . print_r($result->toArray(), true));
        } catch (ClientException $e) {
            Monolog::getInstance()->error('shortmessage_response_err ' . print_r(func_get_args(), true) . " err:" . $e->getErrorMessage());
        } catch (ServerException $e) {
            Monolog::getInstance()->error('shortmessage_response_err ' . print_r(func_get_args(), true) . " err:" . $e->getErrorMessage());
        }
    }
}