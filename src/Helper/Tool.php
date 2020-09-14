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
                $template = str_replace('{' . $key . '}', $value, $template);
            }
        }
        $result = $template;
        return $result;
    }
    public static function arrayToArrayKey($arr, $field, $group = 0)
    {
        $array = [];
        if (empty($arr)) {
            return $array;
        }
        if ($group == 0) {

            foreach ($arr as $v) {
                if (array_key_exists($field, $v)) {
                    $array[$v[$field]] = $v;
                }
            }
        } else {
            foreach ($arr as $v) {
                if (array_key_exists($field, $v)) {
                    $array[$v[$field]][] = $v;
                }
            }
        }

        return $array;
    }
    public static function arrayKeyToArr($arr)
    {
        $array = [];
        foreach ($arr as $v) {
            foreach ($v as $vv) {
                $array[]=$vv;
            }
        }
        return $array;
    }
}