<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:05
 */


require_once __ROOT__."/DB/mysql/DBStd.php";
require_once __ROOT__."/DB/mysql/DB.php";

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

    /**
     * @param $userName
     * @param $encryptedPass
     * @return mixed
     */
    public static function checkUser($userName, $encryptedPass)
    {
        $db = new DB();
        $data = $db->instance->select("cd_user",["User_ID"],["User"=>$userName,"Pass_En"=>$encryptedPass]);

        if (count($data) == 1){
            return new DBResult(new DBUserResult(true,true));
        }
        return new DBResult(new DBUserResult(false,false));
    }

    public static function addUser($userName,$encryptedPass){
        $db = new DB();
        $result = $db->instance->insert("cd_user", [
            "User" => $userName,
            "Pass_En" => $encryptedPass,
        ]);
    }

    /**
     * @param $timeStamp
     * @param $randomStr
     * @return mixed
     */
    public function arithmetic($timeStamp, $randomStr)
    {
        // TODO: Implement arithmetic() method.
        return null;
    }
}
