<?php


namespace Sprovider90\Zhiyuanqueue\Factory;
use Sprovider90\Zhiyuanqueue\Factory\Message\IMessageTrategy;

class MessageFactory
{
    protected $iMessageTrategy;
    function __construct(IMessageTrategy $iMessageTrategy)
    {
        $this->iMessageTrategy=$iMessageTrategy;
    }
    function getTemplateRealData($data){
        return $this->iMessageTrategy->getTemplateRealData($data);
    }
    function getUsersByStage($data){
        return $this->iMessageTrategy->getUsersByStage($data);
    }
}