<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 13:10
 */

require_once "../Global.php";
require_once "../lib/aliyun-oss-php-sdk-2.3.0.phar";
require_once __ROOT__ . "/DB/mysql/FileAction.php";
require_once __ROOT__ . "/DB/model/entity/Key.php";
require_once __ROOT__ . "/DB/model/entity/ActionType.php";
require_once "Respond.php";

use OSS\OssClient;
use OSS\Core\OssException;

class FileRequestRespond extends ServerRespond
{
    private $ossClient;
    private $bucket;

    public static function getDir($parentDirID)
    {
        $dirList = null;

        //Fetch dir from db
        if ($parentDirID != ""){
            $dirList = FileAction::scanDir($parentDirID,$_POST[Key::PARENT_PATH]);
        }else{
            $dirList = array();
        }

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_DIR, $dirList));
    }

    public static function getFile($parentDirID)
    {
        //Fetch dir from db
        $fileList = FileAction::scanFile($parentDirID,$_POST[Key::PARENT_PATH]);

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_FILE, $fileList));
    }


    public static function getAll($parentDirID)
    {
        $list = null;
        $parentDirPath = $_POST[Key::PARENT_PATH];
        if ($parentDirPath==""){
            $parentDirPath = "/";
        }

        if ($parentDirID != ""){
            //Fetch dir from db
            $dirList = FileAction::scanDir($parentDirID,$parentDirPath);

            //Fetch dir from db
            $fileList = FileAction::scanFile($parentDirID,$parentDirPath);

            $list = array_merge($dirList,$fileList);
        }else{
            $list = array();
        }

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_FILE_DIR, $list));
    }


    public static function createArray($type, $list)
    {
        $fileJsonArray = array();
        $fileJsonArray[Key::TYPE] = $type;

        //Generate
        $tmpArr = array();
        $arr = [Key::HAS_CHILD=>false,Key::CHILDREN=>[],Key::OPEN=>false];
        foreach ($list as $name) {
            array_push($tmpArr, array_merge($name,$arr));
        }
        $fileJsonArray[Key::DATA] = $tmpArr;

        return $fileJsonArray;
    }

    /**
     * Return zip url according $data(file md5 list)
     * @param $params ,json str , from client
     */
    public static function createDownloadLink($params)
    {
        //$decodeParams = json_decode($params,true);
        $decodeParams = $params;
        $data = $decodeParams[Key::DATA];

        //session
        session_write_close();

        try{
            //if cache existed
            $List = FileAction::getFileMd5Names($data);//TODO get md5(file and file which in dir)
            $cacheContentID = md5(join("",array_column($List,Node_True_ID)));
            if (($cacheResult = FileAction::cacheExist($cacheContentID)) != null){
                $ZipCacheResult = FileAction::getZipCache($cacheContentID);
                if ($ZipCacheResult != null){
                    self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_SUCCESS, Key::DATA => $ZipCacheResult[Oss_Request_Url], Key::MSG => "缓存检查完毕，链接返回"]);
                }else{
                    self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_SUCCESS, Key::DATA => $ZipCacheResult[Oss_Request_Url], Key::MSG => "缓存检查完毕，确认缓存存在，但缓存链接查询失败"]);
                }
            }else{
                $action = new FileRequestRespond();

                //File hash
                if (count($List)==0){
                    throw new Exception("无法查证文件哈希");
                }

                //throw new Exception("Test interrupt");
                //download
                $action->initOssClient();
                $action->downloadInServer($List);

                //add hash ,which from db
                //$data[Key::HASH] = array_column($List,Node_True_ID);
                $data[Key::Zip_Content_ID] = md5(join("",$data[Key::HASH]));

                //pack
                $zipName = $action->pack($data,$List);
                $zipPath = dirname(__FILE__) . "/tmp/zip/" .$zipName;
                $zipMd5 = md5_file($zipPath);
                $result = $action->pushToOSS($zipPath,$zipMd5);

                //return
                if ($result["info"]["http_code"] == 200) {
                    //If success, cache zip info into db
                    if (FileAction::cacheZIP($result,$zipMd5,$cacheContentID)){
                        self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_SUCCESS, Key::DATA => $result["oss-request-url"], Key::MSG => "打包完毕，链接返回"]);
                    }else{
                        self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_SUCCESS, Key::DATA => $result["oss-request-url"], Key::MSG => "打包完毕，链接返回（服务端缓存失败）"]);
                    }
                } else {
                    self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_FAIL, Key::DATA => "", Key::MSG => "获取链接失败," . $result["info"]["http_code"]]);
                }
            }

        }catch (Exception $exception){
            self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD,Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_FAIL, Key::DATA => "", Key::MSG => "内部错误，".$exception->getMessage()]);
        }

    }

    function initOssClient()
    {
        try {
            $this->bucket = bucketName;
            $this->ossClient = new OssClient(accessKeyId, accessKeySecret, serverUseEndpoint);
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    /**
     * @param $md5Names ,纯粹Name,Name的MD5摘要/哈希值
     */
    function downloadInServer($md5Names)
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => null,
        );
        for ($i = 0; $i < count($md5Names); $i++) {
            $name = $md5Names[$i][Node_True_ID];
            $download = [];
            if ($name != "" && !array_key_exists($name,$download)){
                $options[OssClient::OSS_FILE_DOWNLOAD] = "./tmp/" .$name ;
                if (!file_exists($options[OssClient::OSS_FILE_DOWNLOAD])){
                    $this->ossClient->getObject($this->bucket, ossAimDir . $name, $options);
                    $download[$name] = $name;
                }
            }
        }
    }

    /**
     * 将md5编码的文件按照目标path进行打包
     * @param $data
     * @param $List
     * @return string
     */
    function pack($data,$List)
    {
        $rootPath = "./tmp/";
        $zipName = md5($data[Key::Zip_Content_ID] . time()) . ".zip";
        $zipFilePath = "tmp/zip/" . $zipName;

        $dirs = $data[Key::DIRS];
        $files = $data[Key::FILES];
        $hash = array_column($List,Node_True_ID,Node_ID);

        $zip = new ZipArchive();
        $openRst = $zip->open($zipFilePath, ZipArchive::OVERWRITE | ZipArchive::CREATE);
        if ($openRst === TRUE) {
            $tpName = null;

            for ($i = 0; $i < count($dirs); $i++) {
                $tpName = $dirs[$i][Key::PARENT_PATH];
                $zip->addEmptyDir($tpName);
                self::packIntoDir($rootPath,$zip,$dirs[$i]);
            }
            for ($i = 0; $i < count($files); $i++) {
                $tpName = $rootPath . $hash[$files[$i][Key::NODE_ID]];
                if (is_file($tpName)) {
                    $zip->addFile($tpName, $files[$i][Key::PARENT_PATH]);
                }
            }
            $zip->close(); //关闭处理的zip文件
        }
        return $zipName;
    }

    private function packIntoDir($rootPath,$zip,$dir){
        $dirs = FileAction::scanDir($dir[Key::NODE_ID],$dir[Key::PARENT_PATH]);
        $files = FileAction::scanFile($dir[Key::NODE_ID],$dir[Key::PARENT_PATH]);
        for ($i = 0; $i < count($dirs); $i++) {
            $tpName = $dirs[$i][Key::PARENT_PATH];
            $zip->addEmptyDir($tpName);
            self::packIntoDir($rootPath,$zip,$dirs[$i]);
        }
        for ($i = 0; $i < count($files); $i++) {
            $tpName = $rootPath . $files[$i][Node_True_ID];
            if (is_file($tpName)) {
                $zip->addFile($tpName, $files[$i][Key::PARENT_PATH]);
            }
        }
    }

    /**
     * @param $zipName
     * @param $zipMd5
     * @return string
     */
    function pushToOSS($zipName,$zipMd5)
    {
        $object = ossZipAimDir . $zipMd5.".zip";
        try {
            $result = $this->ossClient->uploadFile($this->bucket, $object, $zipName);
            return $result;
        } catch (OssException $exception) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($exception->getMessage() . "\n");
            return null;
        }
    }


}