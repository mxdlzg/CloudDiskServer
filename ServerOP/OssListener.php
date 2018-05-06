<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 15:29
 */
require_once "UserRequest.php";
require_once "../DB/model/entity/Key.php";

//SESSION
session_start();

//PARAMS
$type = $_POST[Key::CLIENT_TYPE];

if (!isset($_SESSION[Key::TOKEN])){
    $rst = array();
    $rst[Key::TYPE] = ActionType::LOGOUT;
    $rst[Key::STATUS] = Status::TOKEN_NOT_EXISTED;
    $rst[Key::MSG] = "请先登陆";
    ServerRespond::doRespond($rst);
    return;
}
if ($_SESSION[Key::TOKEN] != $_COOKIE[Key::TOKEN]){
    session_unset();
    session_destroy();
    $rst = array();
    $rst[Key::TYPE] = ActionType::LOGOUT;
    $rst[Key::STATUS] = Status::TOKEN_INVALID;
    $rst[Key::MSG] = "令牌匹配失败,请重新登陆";
    ServerRespond::doRespond($rst);
}

switch ($type){
    case "get_uploader_params":
        $ossKey = new OSSKey();
        $ossKey->generateKeys();
        break;
    default:
        $ossCallback = new OSSCallback();
        $ossCallback->callback();
        break;
}