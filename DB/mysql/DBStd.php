<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:02
 */

interface LoginStd
{
    /**
     * @param $userName
     * @param $pass
     * @return mixed
     */
    public static function checkUser($userName,$pass);

    /**
     * @param $timeStamp
     * @param $randomStr
     * @return mixed
     */
    public function arithmetic($timeStamp,$randomStr);
}