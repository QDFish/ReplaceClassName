<?php
/**
 * Created by PhpStorm.
 * User: QDFish
 * Date: 2018/5/9
 * Time: 下午12:01
 */

require_once "ReplaceNameObj.php";

//黑名单目录，添加自己屏蔽的类名，被屏蔽的类名不做重命名操作，但是仍然会作内容替换工作
//黑名单类名会在脚本运行中增加，屏蔽掉以下条件的类名
//1、所有的非NS跟非UI开头的Category类名，比如HBLabel+HB，则把HBLabel添加进入黑名单
//2、xib文件加入黑名单
//3、已经有前缀的文件加入黑名单,为了方便调试
$blackList = [
    'HBUIStyle',
    'HBAppDefine',
    'HBAppDefine',
    'HBHomeDefine',
    'HBLiveDefine',
    'LiveFrogDefine',
    'SVProgressHUD',
    'FMGlobalConfigModel',
    'UITableViewRowAction',
    'FMMessageBadgeView',
    'SYFavoriteButton',
    'FMAttentionRedPointView',
    'FMRightImageButton',
    'FMMediaView',
    'FMVerticalSpeLineView',
    'FMSepertorLineView'
];

$classDirList = [];

function blackAll($path) {
    global $blackList;
    $dirHandle = opendir($path);
    if (!$dirHandle) {
        echo 'not file';
        return;
    }
    while (false !== ($fileName = readdir($dirHandle))) {
        if ($fileName === '.' || $fileName === '..') continue;

        $absolutePath = $path . DIRECTORY_SEPARATOR . $fileName;
        if (is_dir($absolutePath)) {
            blackAll($absolutePath);
            continue;
        }

        if (preg_match("/^\w+\.[mh]$/", $fileName)) {
            $blackName = substr($fileName, 0, strlen($fileName) - 2);
            $blackList[] = $blackName;
        }
    }
}


function searchAll($path) {
    global $blackList;
    global $classDirList;
    $prefix = ReplaceNameObj::$prefix;
    $suffix = ReplaceNameObj::$suffix;
    $dirHandle = opendir($path);
    while (false !== ($fileName = readdir($dirHandle))) {
        if ($fileName === '.' || $fileName === '..') continue;

        $absolutePath = $path . DIRECTORY_SEPARATOR . $fileName;
        if (is_dir($absolutePath)) {
            $classDirList[] = $absolutePath;
            searchAll($absolutePath);
            continue;
        }

        if (!preg_match("/^(NS|UI)\w+/", $fileName) && preg_match("/^\w+\+\w+\.m$/", $fileName)) {
            $strs = explode('+', $fileName);
            $blackList[] = $strs[0];

        } else if (preg_match("/\w+\.xib$/", $fileName)) {
            $blackName = substr($fileName, 0, strlen($fileName) -4);
            $blackList[] = $blackName;

        } else if (preg_match("/^($prefix)\w+($suffix)\.[mh]$/", $fileName)) {
            $blackName = substr($fileName, 0, strlen($fileName) - 2);
            $blackList[] = $blackName;
        }
    }
}

