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

//类名替换前缀
ReplaceNameObj::$prefix = 'ABC';
ReplaceNameObj::$suffix = 'XZY';

//需要替换的路径集合
$path = ['/Users/zgzheng/peipei/HBPeiPei', '/Users/zgzheng/peipei/HBCommon'];
//工程文件目录
$projPath = '/Users/zgzheng/peipei/HBPeiPei.xcodeproj/project.pbxproj';
//debug=1时走debug流程(用于快速排查错误,以及单元测试)
$debug = 0;

//添加黑名单(主要是没有实现文件的纯头文件引用以及NS,UI开头的Category文件)
foreach ($path as $childPath) {
    searchAll($childPath);
}
searchAll('/Applications/Xcode.app/Contents/Developer/Platforms/iPhoneOS.platform/Developer/SDKs/iPhoneOS.sdk/System/Library/Frameworks/Foundation.framework/Headers');
searchAll('/Applications/Xcode.app/Contents/Developer/Platforms/iPhoneOS.platform/Developer/SDKs/iPhoneOS.sdk/System/Library/Frameworks/UIKit.framework/Headers');


$obj = new ReplaceNameObj($path, $projPath);

if ($debug === 1) {
    
    $debugPaths = [
        '/Users/zgzheng/peipei/HBPeiPei/HBLive/Classes/Cover/Focus/View/ABCLiveHallFollowListNotStarCellXZY.h'
    ];
    $obj->dealFilePathsWithClassNames($debugClassName , $debugPaths);
    
} else {
    //处理路径中需要替换的文件,直接替换类名,并把需要更改内容的文件路径添加到缓存中
    $obj->dealClassNameFromPath();

    //在本地写入需要替换的类名集合(这个主要是用来后续debug用,只保存这一次脚本的数据,运行下一次脚本后被替换)
    $obj->storeClassName();

    //替换相应的文件内容
    $obj->dealFilePaths();
    
    //替换工程文件里面相应的文件内容
    $obj->dealProjectPathForClassName();
}
