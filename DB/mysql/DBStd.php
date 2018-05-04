<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:02
 */

interface LoginStd
{
    public static function checkUser($userName,$pass);
    public function arithmetic($timeStamp,$randomStr);
}