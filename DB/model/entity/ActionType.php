<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/4
 * Time: 22:45
 */

class ActionType
{
    const LOGOUT = 0;
    const LOGIN = 1;
    const LOGIN_RESULT = 2;
    const LOGOUT_RESULT = 3;
    const FILE_REQUEST_DIR = 4;
    const FILE_REQUEST_FILE = 5;
    const FILE_DOWNLOAD = 6;
}

class Status{
    const TOKEN_INVALID = 1;
    const TOKEN_NOT_EXISTED = 2;
}