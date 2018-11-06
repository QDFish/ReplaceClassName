<?php
/**
 * Created by PhpStorm.
 * User: QDFish
 * Date: 2018/5/9
 * Time: 上午10:47
 */


require_once "ReplaceNameObj.php";
require_once "BlackList.php";
require_once "DataStore.php";


require_once "ReplaceNameObj.php";

//类名替换前缀
ReplaceNameObj::$prefix = 'XM';
ReplaceNameObj::$suffix = '';

//需要替换的路径集合
$path = ['/Users/zgzheng/peipei'];

//不需要替换的类名目录 isBlackWay == 1时生效
$blackPath = [
    "/Users/zgzheng/peipei/Pods",
];

//需要替换类名的目录 isBlackWay == 0 时生效
$whitePaths = [
  "/Users/zgzheng/peipei/HBPeiPei/HBAppCenter/HBCustomUI/HBButton",
];

//工程文件目录
$projPath = '/Users/zgzheng/peipei/HBPeiPei.xcodeproj/project.pbxproj';
//debug=1时走debug流程(用于快速排查错误,以及单元测试)
$debug = 0;

$isBlackWay = 0;


//--->只需要配置上面几项，自己必要的黑名单可以添加到blackList里面


//添加黑名单(主要是没有实现文件的纯头文件引用以及NS,UI开头的Category文件)
foreach ($path as $childPath) {
    searchAll($childPath);
}

if ($isBlackWay !== 1) {
    foreach ($blackPath as $childPath) {
        blackAll($childPath);
    }
}

$obj = new ReplaceNameObj($path, $projPath, $whitePaths);

$obj->storeBlackName();

if ($debug === 1) {
    
    $debugPaths = [
        '/Project/ForManGit/ForMan/ForMan/AppDelegate.mm'
    ];
    $obj->dealFilePathsWithClassNames($debugClassName , $debugPaths);
    
} else {

    if ($isBlackWay) {
        //处理路径中需要替换的文件,直接替换类名,并把需要更改内容的文件路径添加到缓存中
        $obj->dealClassNameFromPath();
    } else {
        $obj->dealClassNameForWhitePath();
    }


    //在本地写入需要替换的类名集合(这个主要是用来后续debug用,只保存这一次脚本的数据,运行下一次脚本后被替换)
    $obj->storeClassName();

    //替换相应的文件内容
    $obj->dealFilePaths();
    
    //替换工程文件里面相应的文件内容
    $obj->dealProjectPathForClassName();

//    $obj->renameDir();
}

