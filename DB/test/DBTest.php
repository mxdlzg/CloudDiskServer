<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/2
 * Time: 10:52
 */
require_once './model/entity/Medoo.php';

// Using Medoo namespace
use Medoo\Medoo;

$database = new Medoo([
    // required
    'database_type' => 'mysql',
    'database_name' => 'clouddisk',
    'server' => 'localhost',
    'username' => 'root',
    'password' => '',

    // [optional]
    'charset' => 'utf8',
    'port' => 3306,

    // [optional] Table prefix
    //'prefix' => 'PREFIX_',

    // [optional] Enable logging (Logging is disabled by default for better performance)
    'logging' => true,

    // [optional] MySQL socket (shouldn't be used with server and port)
    'socket' => '/tmp/mysql.sock',

    // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ],

    // [optional] Medoo will execute those commands after connected to the database for initialization
    'command' => [
        'SET SQL_MODE=ANSI_QUOTES'
    ]
]);

$users = $database->select("cd_user","ID",["ID[<]" => 3,"ID[>]"=>1]);

echo $users[0];


