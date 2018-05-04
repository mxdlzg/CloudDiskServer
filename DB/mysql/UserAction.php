<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:05
 */

require_once "DBStd.php";

class DBUserResult{
    public $userExisted = false;
    public $passValid = false;

    /**
     * DBUserResult constructor.
     * @param bool $userExisted
     * @param bool $passValid
     */
    public function __construct($userExisted, $passValid)
    {
        $this->userExisted = $userExisted;
        $this->passValid = $passValid;
    }
}

class DBUserAction implements LoginStd{
    public static function checkUser($userName, $pass)
    {
        if ($userName == "mxdlzg" && $pass == "qqtang159"){
            return new DBResult(new DBUserResult(true,true));
        }
        return new DBResult(new DBUserResult(false,false));
    }

    public function arithmetic($timeStamp, $randomStr)
    {
        // TODO: Implement arithmetic() method.
    }
}