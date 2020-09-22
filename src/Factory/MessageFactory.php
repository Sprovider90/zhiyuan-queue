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
        $this->iMessageTrategy->getTemplateRealData($data);
    }
    function getUsersByStage($data){
        $this->iMessageTrategy->getUsersByStage($data);
    }
}