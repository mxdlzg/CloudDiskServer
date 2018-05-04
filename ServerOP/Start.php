<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:29
 */
spl_autoload_register(function ($class_name) {
    require_once $class_name . '.php';
});

$type = $_POST["type"];

switch ($type){
    case "login":
        Login::doLogin($_POST["user"],$_POST["pass"]);
        break;
    default:break;
}