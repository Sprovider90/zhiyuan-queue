<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-07-20
 * Time: 11:20
 */

namespace Sprovider90\Zhiyuanqueue;


class Config implements \ArrayAccess
{
    protected $path="";
    protected $files=[];
    public function __construct($path)
    {
        $this->path=$path;
    }
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        if(empty($this->files[$offset])){
            $this->files[$offset]=require $this->path."/".$offset.".php";
        }
        return $this->files[$offset];
    }
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.

    }
}