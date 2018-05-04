<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:14
 */

class DBResult
{
    public $code = 0;
    public $msg = "default";
    public $data = null;

    /**
     * DBResult constructor.
     * @param null $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}