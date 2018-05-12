<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/11
 * Time: 17:12
 */

require_once "FileRequest.php";
require_once "../DB/model/entity/Key.php";

$params = "{
    \"clientType\":\"download\",
    \"data\":{
        \"topdir\": \"/nd1/nd2/\",
        \"dirs\": [
            
        ],
        \"files\": [
            {
                \"nodeId\": \"nd6\",
                \"path\": \"/Root/TestDir/Whaterver/\",
                \"name\":\"File1.txt\"
            },
            {
                \"nodeId\": \"nd7\",
                \"path\": \"/Root/TestDir/Whaterver/\",
                \"name\":\"File2.txt\"
            },
            {
                \"nodeId\": \"nd8\",
                \"path\": \"/Root/TestDir/Whaterver/\",
                \"name\":\"File3.txt\"
            }
        ],
        \"organized\": false
    }
}";



echo FileRequestRespond::createDownloadLink($params);