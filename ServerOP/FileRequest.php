<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 13:10
 */

require_once "../Global.php";
require_once "../lib/aliyun-oss-php-sdk-2.3.0.phar";
require_once __ROOT__."/DB/mysql/FileAction.php";

use OSS\OssClient;
use OSS\Core\OssException;

class FileRequestRespond extends ServerRespond{
    private $ossClient;
    private $bucket;

    public static function getDir($parentDirID){
        //Fetch dir from db
        $dirList = FileAction::scanDir($parentDirID);

        //result
//        $result = array();

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_DIR,$dirList));
    }

    public static function getFile($parentDirID)
    {
        //Fetch dir from db
        $fileList = FileAction::scanFile($parentDirID);

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
        $nameList = FileAction::scanDir(null);  //TODO get true names
        $pathList = FileAction::scanDir(null);  //TODO get / Path
        $action->downloadInServer($data,$nameList);
        $zipName = $action->pack($pathList,$nameList);
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

    /**
     * @param $objectBucketPathList
     * @param $md5Names ,纯粹Name,Name的MD5摘要
     */
    function downloadInServer($objectBucketPathList, $md5Names){
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => null,
        );
        try{
            for ($i = 0;$i<count($objectBucketPathList);$i++){
                $options[OssClient::OSS_FILE_DOWNLOAD] = "./tmp/".$md5Names[$i];
                $this->ossClient->getObject($this->bucket, $objectBucketPathList[$i]);
            }
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
        }
    }

    /**
     * 将md5编码的文件按照目标path进行打包
     * @param $pathList ,将要打包使用的路径和名称
     * @param $nameList ,摘要名称
     * @return string
     */
    function pack($pathList,$nameList){
        $rootPath = "./tmp/";
        $zipFilePath = "./tmp/zip/".md5($pathList[0].$pathList[count($pathList)/2]); //TODO :: 生成唯一的zip编码

        $zip=new ZipArchive();
        if($zip->open($zipFilePath, ZipArchive::OVERWRITE)=== TRUE){
            $tpName = null;
            for ($i = 0; $i<count($pathList); $i++){
                $tpName = $rootPath.$nameList[$i];
                if (is_file($tpName)){
                    $zip->addFile($pathList[$i],$tpName);
                }
            }
            $zip->close(); //关闭处理的zip文件
        }
        return $zipFilePath;
    }

    /**
     * Upload zip to OSS
     * @param $zipName ,Zip Name
     */
    function pushToOSS($zipName){
        $object = ossZipAimDir.$zipName;
        try{
            $result = $this->ossClient->uploadFile($this->bucket,$object,$zipName);
        }catch (OssException $exception){
            printf(__FUNCTION__ . ": FAILED\n");
            printf($exception->getMessage() . "\n");
            return;
        }
    }

}