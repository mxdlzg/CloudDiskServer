<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 13:34
 */

class FileAction{
    public static function scanDir($parentPath){
        return ["dir1","dir2","dir3"];
    }

    public static function scanFile($parentPath)
    {
        return ["file","file2","file3"];
    }
}