<?php
/**
 * Created by PhpStorm.
 * User: mxdlz
 * Date: 2018/5/10
 * Time: 17:27
 */

$var = json_decode("{
    \"topdir\": \"/nd1/nd2/\",
    \"dirs\": [
        {
            \"nodeid\": \"nd1\",
            \"path\": \"/Root/TestDir/Whaterver/\"
        },
        {
            \"nodeid\": \"nd2\",
            \"path\": \"/Root/TestDir/Whaterver/\"
        }
    ],
    \"files\": [
        {
            \"nodeid\": \"nd3\",
            \"path\": \"/Root/TestDir/Whaterver/\"
        },
        {
            \"nodeid\": \"nd4\",
            \"path\": \"/Root/TestDir/Whaterver/\"
        },
        {
            \"nodeid\": \"nd5\",
            \"path\": \"/Root/TestDir/Whaterver/\"
        }
    ],
    \"organized\": false
}",true);

$rst = array_column($var["files"],"nodeid");
echo "end";