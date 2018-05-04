<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
     * ServerRespond constructor.
     */
    public function __construct()
    {

    }

    public static function doRespond($result){
        echo $result;
    }
}