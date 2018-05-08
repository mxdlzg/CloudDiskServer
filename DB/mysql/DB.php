<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/2
 * Time: 10:52
 */

require_once __ROOT__."/lib/Medoo.php";
require_once __ROOT__."/DB/model/entity/Config.php";

use Medoo\Medoo;

class DB
{
    public $instance;

    public function __construct()
    {
        $this->conn();
    }

    private function conn()
    {
        $this->instance = new Medoo([
            // required
            'database_type' => database_type,
            'database_name' => database_name,
            'server' => database_server,
            'username' => database_user,
            'password' => database_pass,

            // [optional]
            'charset' => 'utf8',
            'port' => database_port,

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
    }
}





