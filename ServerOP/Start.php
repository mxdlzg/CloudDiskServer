<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:29
 */
require_once "UserRequest.php";

//SESSION
session_start();

//PARAMS
$type = $_POST["type"];

switch ($type){
    case "login":
        UserRequestRespond::doLogin($_POST["user"],$_POST["pass"]);
        break;
    case "logout":
        UserRequestRespond::doLogout($_POST["token"],$_POST["user"]);
        break;
    default:break;
}