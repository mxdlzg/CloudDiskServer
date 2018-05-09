<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/6
 * Time: 13:34
 */
require_once "DBKey.php";

class FileAction{
    /**
     * @param $parentPath, Dir node ids     /dirID1/dirID2/dirID3/......
     * @return array
     */
    public static function scanDir($parentNodeID){
        //db
        $db = new DB();

        //search
//        $nodeIDs = $db->instance->select(cd_tree,[Node_ID,Node_True_ID],[Node_Type=>"dir",Ancestor_Node_ID=>$parentNodeID]);
//        $nodeID_Name = $db->instance->select(cd_directory,[Directory_ID,Directory_Name],[Directory_ID=>$nodeIDs[Node_True_ID]]);

        $rst = $db->instance->select(cd_tree,[
            left_join.cd_directory=>[Node_True_ID=>Directory_ID],
        ],[
            cd_tree.dot.Node_ID,
            cd_directory.dot.Directory_Name
        ],[
            Node_Type=>"dir",
            Ancestor_Node_ID=>$parentNodeID,
        ]);

        $result = array();
//        $result["node_id"] = $nodeIDs[Node_ID];
//        $result["directory_name"] = $nodeID_Name[Directory_Name];

        //return
        return $rst;
    }

    public static function scanFile($parentNodeID)
    {
        $db = new DB();

        $rst = $db->instance->select(cd_tree,[
            left_join.cd_file=>[Node_True_ID=>File_ID],
        ],[
            cd_tree.dot.Node_ID,
            cd_file.dot.File_Name,
            cd_file.dot.File_Type,
        ],[
            Node_Type=>"file",
            Ancestor_Node_ID=>$parentNodeID,
        ]);

        //return
        return $rst;
    }
}