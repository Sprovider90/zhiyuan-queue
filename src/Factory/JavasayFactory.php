<?php


namespace Sprovider90\Zhiyuanqueue\Factory;
use Sprovider90\Zhiyuanqueue\Factory\Javasay\IDataTrategy;

class JavasayFactory
{
    protected $iDataTrategy;
    function __construct(IDataTrategy $iDataTrategy)
    {
        $this->iDataTrategy=$iDataTrategy;
    }
    function run($redis,$data){
        $this->iDataTrategy->dealData($redis,$data);
    }

}