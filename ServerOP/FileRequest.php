<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 13:10
 */

require_once "../lib/aliyun-oss-php-sdk-2.3.0.phar";

use OSS\OssClient;
use OSS\Core\OssException;

class FileRequestRespond extends ServerRespond{
    private $ossClient;
    private $bucket;

    public static function getDir($parentPath){
        //Fetch dir from db
        $dirList = FileAction::scanDir($parentPath);

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_DIR,$dirList));
    }

    public static function getFile($parentPath)
    {
        //Fetch dir from db
        $fileList = FileAction::scanFile($parentPath);

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_FILE,$fileList));
    }

    public static function createArray($type,$list){
        $fileJsonArray = array();
        $fileJsonArray[Key::TYPE] = $type;

        //Generate
        $tmpArr = array();
        foreach ($list as $name){
            array_push($tmpArr,$name);
        }
        $fileJsonArray[Key::DATA] = $tmpArr;

        return $fileJsonArray;
    }

    /**
     * Return zip url according $data(file md5 list)
     * @param $data ,File md5
     */
    public static function createDownloadLink($data)
    {
        $action = new FileRequestRespond();
        $action->initOssClient();

        //session
        session_write_close();

        //download
        $nameList = $action->downloadInServer($data);
        $zipName = $action->pack($nameList);
        $ossUrl = $action->pushToOSS($zipName);

        self::doRespond([Key::TYPE=>ActionType::FILE_DOWNLOAD,Key::DATA=>$ossUrl,Key::MSG=>"打包完毕，链接返回"]);
    }

    function initOssClient(){
        try {
            $this->bucket = bucketName;
            $this->ossClient = new OssClient(accessKeyId, accessKeySecret, serverUseEndpoint);
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    function downloadInServer($objectBucketPathList){
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => null,
        );
        $names = array();
        try{
            foreach ($objectBucketPathList as $objectBucketPath){
                $options[OssClient::OSS_FILE_DOWNLOAD] = "./tmp/".substr($objectBucketPath,strripos($objectBucketPath,"/"));
                array_push($names,$options[OssClient::OSS_FILE_DOWNLOAD]);
                $this->ossClient->getObject($this->bucket, $objectBucketPath);
            }
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return [];
        }
        return $names;
    }

    function pack($names){
        $zipFileName = "";
        //TODO::

        return $zipFileName;
    }

    function pushToOSS($zipName){
        //TODO::
    }

}