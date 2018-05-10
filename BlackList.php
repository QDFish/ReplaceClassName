<?php
/**
 * Created by PhpStorm.
 * User: QDFish
 * Date: 2018/5/9
 * Time: 下午12:01
 */



$blackList = [
    'ViewController',
    'View',
    'main',
    'Label',
    'TextField',
    'TextView',
    'ImageView',
    'Image',
    'HBUIStyle',
    'HBAppDefine',
    'HBAppDefine',
    'HBHomeDefine',
    'HBLiveDefine',
    'LiveFrogDefine',
    'SVProgressHUD'
];




function searchAll($path) {
    global $blackList;
    $dirHandle = opendir($path);
    while (false !== ($fileName = readdir($dirHandle))) {
        if ($fileName === '.' || $fileName === '..') continue;

        $absolutePath = $path . DIRECTORY_SEPARATOR . $fileName;
        if (is_dir($absolutePath)) {
            searchAll($absolutePath);
            continue;
        }

        //UIKit Foundation
        if (strpos($fileName, '.h') !== false && (strpos($fileName, 'NS') !== false || strpos($fileName, 'UI') !== false)) {
            $blackName = substr($fileName, 2, strlen($fileName) - 4);
            $blackList[] = $blackName;
        } else if (!preg_match("/^(NS|UI)\w+/", $fileName) && preg_match("/^\w+\+\w+\.m$/", $fileName)) {
            $strs = explode('+', $fileName);
            $blackList[] = $strs[0];

        }
    }
}

