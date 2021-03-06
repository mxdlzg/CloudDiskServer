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
    const FILE_REQUEST_FILE_DIR = 8;
    const RENAME = 9;
    const CREATE_DIR = 10;
    const DETAIL = 11;
    const DELETE = 12;
    const MOVE = 13;
    const REGISTER = 14;
}

class Status{
    const TOKEN_INVALID = 1;
    const TOKEN_NOT_EXISTED = 2;
    const CREATE_DOWNLOAD_LINK_FAIL = 3;
    const CREATE_DOWNLOAD_LINK_SUCCESS = 4;
    const LOGIN_SUCCESS = 5;
    const HAS_LOGIN = 6;
    const LOGIN_FAIL = 7;
    const LOGOUT_SUCCESS = 8;
    const ALREADY_LOGOUT = 9;
    const LOGOUT_FAIL = 10;
    const SUCCESS = 11;
    const FAIL = 12;
}