<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:29
 */
require_once "UserRequest.php";
require_once "FileRequest.php";
require_once "../DB/model/entity/Key.php";
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//SESSION
session_start();

//PARAMS
$type = $_POST[Key::CLIENT_TYPE];

if ($type === "login") {
    UserRequestRespond::doLogin($_POST["user"], $_POST["pass"]);
} else if ($type === "register") {
    UserRequestRespond::doRegister($_POST["user"],$_POST["password"],$_POST["invitation"]);
} else {
    if (!isset($_SESSION[Key::TOKEN])) {
        //echo count($_SESSION);
        $rst = array();
        $rst[Key::TYPE] = ActionType::LOGOUT;
        $rst[Key::STATUS] = Status::TOKEN_NOT_EXISTED;
        $rst[Key::MSG] = "请先登陆";
        ServerRespond::doRespond($rst);
        return;
    }
    if ($_SESSION[Key::TOKEN] != $_COOKIE[Key::TOKEN]) {
        session_unset();
        session_destroy();
        $rst = array();
        $rst[Key::TYPE] = ActionType::LOGOUT;
        $rst[Key::STATUS] = Status::TOKEN_INVALID;
        $rst[Key::MSG] = "令牌匹配失败,请重新登陆";
        ServerRespond::doRespond($rst);
        return;
    }
    switch ($type) {
        case "login":
            break;
        case "register":
            break;
        case "logout":
            UserRequestRespond::doLogout($_COOKIE["token"], $_COOKIE["user"]);
            break;
        case "getDir":
            FileRequestRespond::getDir($_POST[Key::PARENT_DIR_ID]);
            break;
        case "getFile":
            FileRequestRespond::getFile($_POST[Key::PARENT_DIR_ID]);
            break;
        case "getAll":
            FileRequestRespond::getAll($_POST[Key::PARENT_DIR_ID]);
            break;
        case "getDirAndFile":
            FileRequestRespond::getAll($_POST[Key::PARENT_DIR_ID]);
            break;
        case "download":
            FileRequestRespond::createDownloadLink($_POST[Key::DATA]);
            break;
        case "rename":
            FileRequestRespond::itemRename($_POST[Key::DATA]);
            break;
        case "createDir":
            FileRequestRespond::createDir($_POST[Key::DATA]);
            break;
        case "detail":
            FileRequestRespond::getItemDetail($_POST[Key::DATA]);
            break;
        case "delete":
            FileRequestRespond::deleteItems($_POST[Key::DATA]);
            break;
        case "move":
            FileRequestRespond::moveItems($_POST[Key::DATA]);
            break;
        default:
            echo json_decode("{type:-1,msg:'无对应操作'}");
            break;
    }
}

