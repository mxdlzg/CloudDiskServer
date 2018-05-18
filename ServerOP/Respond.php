<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/3
 * Time: 21:23
 */
interface Res{
    function respond($result);
}

class ServerRespond{
    /**
     * Write json response to user browser
     * @param $result
     */
    public static function doRespond($result){
        header("Content-Type: text/html; charset=utf-8");
        header("Access-Control-Allow-Origin: *");

        echo json_encode($result);
    }

    /**
     * Create general response
     * @param $nameArr
     * @param $valueArr
     * @return array
     */
    static function createResult($nameArr,$valueArr){
        $result = array();
        if ($valueArr!=null && count($valueArr) == count($nameArr)){
            for ($index = 0;$index<count($nameArr);$index++){
                if ($valueArr[$index] != null){
                    $result[$nameArr[$index]] = $valueArr[$index];
                }
            }
        }
        return $result;
    }
}