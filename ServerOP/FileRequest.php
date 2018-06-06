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
            $dirList = FileAction::scanDir($parentDirID);
        }else{
            $dirList = array();
        }

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_DIR, $dirList));
    }

    public static function getFile($parentDirID)
    {
        //Fetch dir from db
        $fileList = FileAction::scanFile($parentDirID);

        //Respond
        self::doRespond(self::createArray(ActionType::FILE_REQUEST_FILE, $fileList));
    }


    public static function getAll($parentDirID)
    {
        $list = null;

        if ($parentDirID != ""){
            //Fetch dir from db
            $dirList = FileAction::scanDir($parentDirID);

            //Fetch dir from db
            $fileList = FileAction::scanFile($parentDirID);

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
        $decodeParams = json_decode($params,true);
        $data = $decodeParams[Key::DATA];

        //session
        session_write_close();

        try{
            //if cache existed
            if (($cacheResult = FileAction::cacheExist($params)) != null){
                self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_SUCCESS, Key::DATA => $cacheResult[Oss_Request_Url], Key::MSG => "缓存检查完毕，链接返回"]);
            }else{
                $action = new FileRequestRespond();
                $action->initOssClient();

                //File hash
                $List = FileAction::getFileMd5Names($data[Key::FILES]);  //TODO get md5 names

                //download
                $action->downloadInServer($List);

                //add hash ,which from db
                $data[Key::HASH] = array_column($List,Node_True_ID,Node_ID);

                //pack
                $zipName = $action->pack($data);
                $result = $action->pushToOSS($zipName);

                //return
                if ($result["info"]["http_code"] == 200) {
                    self::doRespond([Key::TYPE => ActionType::FILE_DOWNLOAD, Key::STATUS=>Status::CREATE_DOWNLOAD_LINK_SUCCESS, Key::DATA => $result["oss-request-url"], Key::MSG => "打包完毕，链接返回"]);

                    //If success, cache zip info into db
                    FileAction::cacheZIP($result,$params);
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
            $options[OssClient::OSS_FILE_DOWNLOAD] = "./tmp/" . $md5Names[$i][Node_True_ID];
            $this->ossClient->getObject($this->bucket, ossAimDir . $md5Names[$i][Node_True_ID], $options);
        }
    }

    /**
     * 将md5编码的文件按照目标path进行打包
     * @param $data
     * @return string
     */
    function pack($data)
    {
        $rootPath = "./tmp/";
        $zipName = md5($data[Key::FILES][0][Key::PATH] . time()) . ".zip"; //TODO :: 生成唯一的zip编码
        $zipFilePath = "tmp/zip/" . $zipName;

        $dirs = $data[Key::DIRS];
        $files = $data[Key::FILES];
        $hash = $data[Key::HASH];

        $zip = new ZipArchive();
        $openRst = $zip->open($zipFilePath, ZipArchive::OVERWRITE | ZipArchive::CREATE);
        if ($openRst === TRUE) {
            $tpName = null;

            for ($i = 0; $i < count($dirs); $i++) {
                $tpName = $dirs[$i][Key::PATH] . $dirs[$i][Key::NAME];
                $zip->addEmptyDir($tpName);
            }
            for ($i = 0; $i < count($files); $i++) {
                $tpName = $rootPath . $hash[$files[$i][Key::NODE_ID]];
                if (is_file($tpName)) {
                    $zip->addFile($tpName, $files[$i][Key::PATH] . $files[$i][Key::NAME]);
                }
            }
            $zip->close(); //关闭处理的zip文件
        }
        return $zipName;
    }

    /**
     * @param $zipName
     * @return string
     */
    function pushToOSS($zipName)
    {
        $object = ossZipAimDir . $zipName;
        try {
            $result = $this->ossClient->uploadFile($this->bucket, $object, dirname(__FILE__) . "/tmp/zip/" . $zipName);
            return $result;
        } catch (OssException $exception) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($exception->getMessage() . "\n");
            return null;
        }
    }


}