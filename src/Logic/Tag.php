<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-10
 * Time: 13:13
 */

namespace Sprovider90\Zhiyuanqueue\Logic;

class Tag implements Icommand
{
    /**
     * 给主体打标签（此入口作为修复数据用）
     */
    function run(){
        $tag=new \Sprovider90\Zhiyuanqueue\Factory\Tag();
        $tag->run();
        return 1;
    }
}