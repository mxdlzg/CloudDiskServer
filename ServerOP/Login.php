<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:22
 */
require_once "Respond.php";
require_once "../DB/mysql/UserAction.php";
require_once "../DB/model/entity/DBResult.php";

class Login extends ServerRespond{
    /**
     * Do Login
     * @param $userName
     * @param $pass
     */
    static function doLogin($userName,$pass){
        $login = new Login();
        $result = "null";

        if ($login->checkUser($userName,$pass)){
            session_start();
            $_SESSION["token"] = md5($userName.$pass);
            $result = $_SESSION["token"];
        }else{
            $result = "用户验证失败";
        }

        $login->doRespond($result);
    }

    /**
     * Check User Info
     * @param $user
     * @param $pass
     * @return bool
     */
    function checkUser($user,$pass){
        $rst = DBUserAction::checkUser($user,$pass); #type: DBUserResult
        if ($rst->data->userExisted && $rst->data->passValid){
            return true;
        }
        return false;
    }
}
