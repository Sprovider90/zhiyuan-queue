<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 15:45
 */

namespace Sprovider90\Zhiyuanqueue\Factory;


use Sprovider90\Zhiyuanqueue\Logic\Icommand;

class CommandFactory
{
    protected $command="";
    public function __construct(Icommand $obj)
    {
        $this->command=$obj;
    }

    function run(){
        $this->command->run();
    }
}