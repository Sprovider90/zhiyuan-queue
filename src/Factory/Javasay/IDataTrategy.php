<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-06-30
 * Time: 17:02
 */

namespace Sprovider90\Zhiyuanqueue\Factory\Javasay;


interface IDataTrategy
{
    function dealData($redis,$data);
}