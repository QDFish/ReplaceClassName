<?php
/**
 * Created by PhpStorm.
 * User: QDFish
 * Date: 2018/5/9
 * Time: 下午12:01
 */


//黑名单目录，添加自己屏蔽的类名，被屏蔽的类名不做重命名操作，但是仍然会作内容替换工作
//黑名单类名会在脚本运行中增加，屏蔽掉以下条件的类名
//1. UI跟NS开头的系统文件类名，比如NSString，则将String添加进入黑名单
//2，所有的非NS跟非UI开头的Category类名，比如HBLabel+HB，则把HBLabel添加进入黑名单
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

