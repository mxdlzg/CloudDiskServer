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
require_once "../DB/model/entity/ActionType.php";
require_once "../DB/model/entity/Key.php";

define("RESULT",['type','token','msg']);

class UserRequestRespond extends ServerRespond{
    /**
     * Do Login
     * @param $userName
     * @param $pass
     */
    static function doLogin($userName,$pass){
        $action = new UserRequestRespond();
        $result = array();
        $result[Key::TYPE] = ActionType::LOGIN_RESULT;

        if ($action->checkUser($userName,$pass)){
            $token = md5($userName);
            if (!isset($_SESSION[Key::TOKEN])){
                $_SESSION[Key::TOKEN] = $token;
                $result[Key::TOKEN] = $_SESSION["token"];
                $result[Key::MSG] = "新用户登陆";
            }else{
                $result[Key::MSG] = "用户已登录";
            }
        }else{
            $result[Key::MSG] = "用户验证失败";
        }
        $action->doRespond($result);
    }

    /**
     * Do logout
     * @param $token
     * @param $userName
     */
    static function doLogout($token,$userName){
        $respondAction = new UserRequestRespond();
        $result = array();
        $result[Key::TYPE] = ActionType::LOGOUT_RESULT;

        $serverToken = md5($userName);
        if ($token == $serverToken){
            if (isset($_SESSION[Key::TOKEN])){
                session_unset();
                session_destroy();
                $result[Key::MSG] = "注销成功";
            }else{
                $result[Key::MSG] = "用户已经注销";
            }
        }else{
            $result[Key::MSG] = "令牌匹配失败，未识别注销请求";
        }
        $respondAction->doRespond($result);
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
