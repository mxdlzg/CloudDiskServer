<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 13:34
 */
require_once "DBKey.php";
require_once "DB.php";

class FileAction
{
    /**
     * @param $parentPath , Dir node ids     /dirID1/dirID2/dirID3/......
     * @return array
     */
    public static function scanDir($parentNodeID)
    {
        //db
        $db = new DB();

        //search
//        $nodeIDs = $db->instance->select(cd_tree,[Node_ID,Node_True_ID],[Node_Type=>"dir",Ancestor_Node_ID=>$parentNodeID]);
//        $nodeID_Name = $db->instance->select(cd_directory,[Directory_ID,Directory_Name],[Directory_ID=>$nodeIDs[Node_True_ID]]);

        $rst = $db->instance->select(cd_tree, [
            left_join . cd_directory => [Node_True_ID => Directory_ID],
        ], [
            cd_tree . dot . Node_ID."(".Key::NODE_ID.")",
            cd_directory . dot . Directory_Name."(".Key::NAME.")",
            cd_directory . dot . Type."(".Key::TYPE.")",
        ], [
            Node_Type => "dir",
            Ancestor_Node_ID => $parentNodeID,
        ]);
        return $rst;
    }

    public static function scanFile($parentNodeID)
    {
        $db = new DB();

        $rst = $db->instance->select(cd_tree, [
            left_join . cd_file => [Node_True_ID => File_ID],
        ], [
            cd_tree . dot . Node_ID."(".Key::NODE_ID.")",
            cd_file . dot . File_Name."(".Key::NAME.")",
            cd_file . dot . File_Type."(".Key::TYPE.")",
        ], [
            Node_Type => "file",
            Ancestor_Node_ID => $parentNodeID,
        ]);

        //return
        return $rst;
    }

    /**
     * @param $file_list ,fileIds
     * @return mixed
     */
    public static function getFileMd5Names($file_list)
    {
        $db = new DB();
        $dbResult = $db->instance->select(
            cd_tree,
            [
                Node_ID,
                Node_True_ID
            ],
            [
                Node_ID => array_column($file_list,Key::NODE_ID)
            ]
        );
        return $dbResult;
    }

    /**
     * @param $result, oss upload result
     * @param $params, json str
     */
    public static function cacheZIP($result, $params)
    {
        $clientParamsMD5 = md5($params);

        $db = new DB();

        $result = $db->instance->insert(
            cd_zip_cache,
            [
                Cache_ID=>$clientParamsMD5,
                Oss_Request_Url=>$result["oss-request-url"],
                Cache_Size=>$result["info"]["size_upload"]
            ]
        );
        return $result;
    }

    public static function cacheExist($params)
    {
        $clientParamsMD5 = md5($params);

        $db = new DB();

        $result = $db->instance->select(
            cd_zip_cache,
            [
                Oss_Request_Url,
                Cache_Size,
                Cache_Time
            ],
            [
                Cache_ID=>$clientParamsMD5,
            ]
        );
        if (count($result)>0){
            return $result;
        }else{
            return null;
        }
    }

    public static function putFile($file, $parentNodeID, $userID)
    {
        //TODO::插入文件，插入树节点关系
        $db = new DB();

        $fileResult = $db->instance->insert(
            cd_file,
            [
                File_Name=>$file["name"],
                File_Type=>$file['ext'],
                File_Size=>$file['size'],
                Uploader=>$userID,
                Description=>'',
                File_ID=>$file["md5"]
            ]
        );
        $treeResult = $db->instance->insert(
            cd_tree,
            [
                Node_True_ID=>$file['md5'],
                Node_Type=>'file',
                Ancestor_Node_ID=>$parentNodeID
            ]
        );
        if (count($fileResult)>0 && count($treeResult)>0){
            return true;
        }else{
            return false;
        }
    }

    public static function uploadAllowed($parentNodeID, $currentUser)
    {
        $db = new DB();
        $result = $db->instance->select(view_dir_user,
            [
                Belong_User_ID
            ],
            [
                User=>$currentUser,
                Node_ID=>$parentNodeID
            ]);
        if (count($result)>0){
            return true;
        }
        return false;
    }

    public static function fileExist($parentNodeID, $fileMd5)
    {
        $db = new DB();
        $result = $db->instance->select(view_file_treeAncestor,
            [
                Node_ID
            ],
            [
                Ancestor_Node_ID=>$parentNodeID,
                File_ID=>$fileMd5
            ]);
        if (count($result)>0){
            return true;
        }
        return false;
    }
}