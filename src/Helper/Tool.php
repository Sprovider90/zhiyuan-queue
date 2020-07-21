<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-21
 * Time: 17:23
 */

namespace Sprovider90\Zhiyuanqueue\Helper;


class Tool
{
    public static function combine_template($param, $template)
    {
        $result = "";
        if (!empty($param)) {
            foreach ($param as $key => $value) {
                $template = str_replace('${' . $key . '}', $value, $template);
            }
        }
        $result = $template;
        return $result;
    }
}