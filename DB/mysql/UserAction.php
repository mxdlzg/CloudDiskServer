<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:05
 */


require_once __ROOT__."/DB/mysql/DBStd.php";
require_once __ROOT__."/DB/mysql/DB.php";
require_once __ROOT__."/DB/mysql/DBKey.php";
require_once __ROOT__."/DB/model/entity/DBResult.php";


class DBUserResult extends DBResult {
    public $userExisted = false;
    public $passValid = false;
    public $startID = '';
    public $userID;

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
            $result = new DBUserResult(true,true);
            $userStartData = $db->instance->select("cd_user_start",[Start_Node_ID],["User_ID"=>$data[0]]);
            $result->startID = $userStartData[0][Start_Node_ID];
            $result->userID = $data[0]['User_ID'];
            return $result;
        }
        return new DBUserResult(false,false);
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
