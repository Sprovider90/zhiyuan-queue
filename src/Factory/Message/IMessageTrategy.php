<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-06-30
 * Time: 17:02
 */

namespace Sprovider90\Zhiyuanqueue\Factory\Message;


interface IMessageTrategy
{
    function getTemplateRealData($data);
    function getUsersByStage($data);
}