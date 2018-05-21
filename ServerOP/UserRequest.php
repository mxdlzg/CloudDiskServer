<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:22
 */

require_once "../Global.php";
require_once "Respond.php";
require_once __ROOT__."/DB/mysql/UserAction.php";
require_once __ROOT__."/DB/model/entity/DBResult.php";
require_once __ROOT__."/DB/model/entity/ActionType.php";
require_once __ROOT__."/DB/model/entity/Key.php";

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
            $token = md5($userName);    //TODO::arithmetic
            if (!isset($_SESSION[Key::TOKEN])){
                //SET
                $_SESSION[Key::TOKEN] = $token;
                setcookie(Key::TOKEN,$token,time()+3600,"/");
                //Result
                $result[Key::TOKEN] = $_SESSION["token"];
                $result[Key::STATUS] = Status::LOGIN_SUCCESS;
                $result[Key::MSG] = "新用户登陆";
            }else{
                $result[Key::STATUS] = Status::HAS_LOGIN;
                $result[Key::MSG] = "用户已登录";
            }
        }else{
            $result[Key::STATUS] = Status::LOGIN_FAIL;
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
        $rst = DBUserAction::checkUser($user,md5($pass)); #type: DBUserResult
        if ($rst->data->userExisted && $rst->data->passValid){
            return true;
        }
        return false;
    }

}
