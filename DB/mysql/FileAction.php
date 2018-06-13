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
     * @param $parentNodeID
     * @param $parentDirPath
     * @return array
     */
    public static function scanDir($parentNodeID,$parentDirPath)
    {
        //db
        $db = new DB();

        //search
//        $nodeIDs = $db->instance->select(cd_tree,[Node_ID,Node_True_ID],[Node_Type=>"dir",Ancestor_Node_ID=>$parentNodeID]);
//        $nodeID_Name = $db->instance->select(cd_directory,[Directory_ID,Directory_Name],[Directory_ID=>$nodeIDs[Node_True_ID]]);

        $rst = $db->instance->select(cd_tree, [
            left_join . cd_directory => [Node_True_ID => Directory_ID],
        ], [
            cd_tree . dot . Node_ID . "(" . Key::NODE_ID . ")",
            cd_directory . dot . Directory_Name . "(" . Key::NAME . ")",
            cd_directory . dot . Type . "(" . Key::TYPE . ")",
        ], [
            Node_Type => "dir",
            Ancestor_Node_ID => $parentNodeID,
        ]);
        for ($i = 0; $i<count($rst);$i++){
            $rst[$i][Key::PARENT_PATH] = $parentDirPath.$rst[$i][Key::NAME]."/";
        }
        return $rst;
    }

    public static function scanFile($parentNodeID,$parentDirPath)
    {
        $db = new DB();

        $rst = $db->instance->select(cd_tree, [
            left_join . cd_file => [Node_True_ID => File_ID],
        ], [
            cd_tree . dot . Node_ID . "(" . Key::NODE_ID . ")",
            cd_tree . dot . Node_True_ID . "(" . Node_True_ID . ")",
            cd_file . dot . File_Name . "(" . Key::NAME . ")",
            cd_file . dot . File_Type . "(" . Key::TYPE . ")",
        ], [
            Node_Type => "file",
            Ancestor_Node_ID => $parentNodeID,
        ]);
        for ($i = 0; $i<count($rst);$i++){
            $rst[$i][Key::PARENT_PATH] = $parentDirPath.$rst[$i][Key::NAME].".".$rst[$i][Key::TYPE];
        }
        //return
        return $rst;
    }

    public static function cacheExist($cacheContentID)
    {
        $db = new DB();
        $result = $db->instance->select(
            cd_zip_cache,
            [
                Oss_Request_Url,
                Cache_Size,
                Cache_Time
            ],
            [
                Cache_Content_ID => $cacheContentID,
            ]
        );
        if (count($result) > 0) {
            return $result;
        } else {
            return null;
        }
    }


    public static function putFile($file, $parentNodeID, $userID, $isQuick = false)
    {
        //TODO::插入文件，插入树节点关系
        $db = new DB();

        if (!$isQuick) {
            $fileResult = $db->instance->insert(
                cd_file,
                [
                    File_Name => $file["name"],
                    File_Type => $file['ext'],
                    File_Size => $file['size'],
                    Uploader => $userID,
                    Description => '',
                    File_ID => $file["md5"]
                ]
            );
        }
        $treeResult = $db->instance->insert(
            cd_tree,
            [
                Node_True_ID => $file['md5'],
                Node_Type => 'file',
                Ancestor_Node_ID => $parentNodeID
            ]
        );
        if (count($treeResult) > 0) {
            return true;
        } else {
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
                User => $currentUser,
                Node_ID => $parentNodeID
            ]);
        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    public static function fileExist($parentNodeID, $fileMd5)
    {
        $db = new DB();
        $result1 = $db->instance->select(view_file_treeancestor,
            [
                Node_ID
            ],
            [
                File_ID => $fileMd5
            ]);
        $result2 = $db->instance->select(view_file_treeancestor,
            [
                Node_ID
            ],
            [
                Ancestor_Node_ID => $parentNodeID,
                File_ID => $fileMd5
            ]);
        return ["fileExist" => (count($result1) > 0), "nodeExist" => (count($result2) > 0)];
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function getFileMd5Names($data)
    {
        $fileList = $data[Key::FILES];
        $dirList = $data[Key::DIRS];

        $db = new DB();

        //Files ID
        $dbResult = $db->instance->select(
            view_file_treeancestor,
            [
                Node_ID,
                Node_True_ID,
                File_Name,
                File_Type
            ],
            [
                Node_ID => array_column($fileList, Key::NODE_ID)
            ]
        );
//        for ($i=0;$i<count($dbResult);$i++){
//            $dbResult[$i][$dbResult[$i][Node_ID]] = $dbResult[$i][Node_True_ID];
//        }

        //Files in dir
        $result = [];
        foreach ($dirList as $item){
            $result = array_merge($result,self::scanFileMd5($db,$item[Key::NODE_ID]));
        }
        $result = array_merge($result,$dbResult);
        return $result;
    }

    /**
     * 递归查询目标文件夹中的文件ID
     * @param $db
     * @param $parentNodeID
     * @return null
     */
    private static function scanFileMd5($db, $parentNodeID)
    {
        $result = $db->instance->select(
            view_file_treeancestor,
            [
                Node_ID,
                Node_True_ID,
                File_Name,
                File_Type
            ],
            [
                Ancestor_Node_ID=>$parentNodeID,
                Node_Type=>"file"
            ]
        );
        $dirs = $db->instance->select(
            view_tree_dirname,
            [
                Node_ID,
                Node_True_ID,
                Directory_Name,
            ],
            [
                Ancestor_Node_ID=>$parentNodeID,
                Node_Type=>"dir"
            ]
        );
        foreach ($dirs as $item){
            $result = array_merge($result,self::scanFileMd5($db,$item[Key::NODE_ID]));
        }
//        for ($i=0;$i<count($result);$i++){
//            $result[$i][$result[$i][Node_ID]] = $result[$i][Node_True_ID];
//        }
        return $result;
    }

    /**
     * @param $result , oss upload result
     * @param $md5
     * @param $contentMd5
     * @return
     */
    public static function cacheZIP($result, $md5, $contentMd5)
    {
        $db = new DB();

        $result = $db->instance->insert(
            cd_zip_cache,
            [
                Cache_ID => $md5,
                Cache_Content_ID=>$contentMd5,
                Oss_Request_Url => $result["oss-request-url"],
                Cache_Size => $result["info"]["size_upload"]
            ]
        );
        return count($result)>0;
    }

    public static function getZipCache($cacheContentID)
    {
        $db = new DB();

        $result = $db->instance->select(
            cd_zip_cache,
            [
                Cache_ID ,
                Cache_Content_ID,
                Oss_Request_Url,
                Cache_Size
            ],
            [
                Cache_Content_ID=>$cacheContentID
            ]
        );
        if (count($result)>0){
            return $result[0];
        }
        return null;
    }
}