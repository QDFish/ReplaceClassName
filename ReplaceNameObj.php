<?php

/**
 * Created by PhpStorm.
 * User: QDFish
 * Date: 2018/5/9
 * Time: 上午9:11
 */

require_once 'BlackList.php';

class ReplaceNameObj
{
    //需要替换的文件目录
    private $paths;
    
    //需要替换工程目录
    private $projectPath;
    
    //需要替换的类名称
    private $classNames = array();
    
    //需要替换内容的文件目录
    private $filePaths = array();

   //前缀以及后缀
    public static $prefix;
    public static $suffix;

    public function __construct(array $paths, $projectPath)
    {
        $this->paths = $paths;
        $this->projectPath = $projectPath;
        
    }

    public function dealClassNameFromPath() {
        foreach ($this->paths as $path) {
            self::recursivePathForClassName($path);
        }
    }

    private function recursivePathForClassName($path) {

        $dirHandle = opendir($path);
        echo '处理目录' . $path . PHP_EOL;
        while (false !== ($fileName = readdir($dirHandle))) {
            if ($fileName === '.' || $fileName === '..') continue;

            $absoluteFilePath = $path . DIRECTORY_SEPARATOR . $fileName;
            if (is_dir($absoluteFilePath)) {
                self::recursivePathForClassName($absoluteFilePath);
                continue;
            }

            //不处理已经更改的文件(有些目录会导致二次打开相同的文件)
            if (strpos($fileName, self::$prefix) !== false && strpos($fileName, self::$suffix) !==false) {
                continue;
            }

            //.m文件(非Category文件),同时处理同名的.h文件
            if (preg_match("/^\w+\.m$/", $fileName)) {

                $realFileName = substr($fileName, 0, strlen($fileName) - 2);
                $fileName = substr($fileName, 0, strlen($fileName) - 2);

                if (!$this->isBlackClass($realFileName)) {

                    $this->classNames[] = $realFileName;
                    $realFileName = self::$prefix . $realFileName . self::$suffix;
                    rename($path . DIRECTORY_SEPARATOR . $fileName . '.m', $path . DIRECTORY_SEPARATOR . $realFileName . '.m');

                    if (file_exists($path . DIRECTORY_SEPARATOR . $fileName . '.h')) {
                        rename($path . DIRECTORY_SEPARATOR . $fileName . '.h', $path . DIRECTORY_SEPARATOR . $realFileName . '.h');

                    }

                    echo "重命名{$fileName}为{$realFileName}" . PHP_EOL;
                }

                $this->filePaths[] = $path . DIRECTORY_SEPARATOR . $realFileName . '.m';
                if (file_exists($path . DIRECTORY_SEPARATOR . $realFileName . '.h')) {
                    $this->filePaths[] = $path . DIRECTORY_SEPARATOR . $realFileName . '.h';
                }

                continue;
            }


            //处理只有.h的文件,不重命名,只加入修改内容目录
            $realName = substr($fileName, 0, strlen($fileName) - 2);
            if (preg_match("/^\w+\.h$/", $fileName) && !file_exists($path . DIRECTORY_SEPARATOR . $realName . '.m') && !file_exists($path . DIRECTORY_SEPARATOR . self::$prefix . $realName . self::$suffix . '.m')) {
                $this->filePaths[] = $path . DIRECTORY_SEPARATOR . $fileName;
                continue;
            }

            //Category文件加入处理内容目录
            if (preg_match("/^\w+\+\w+\.[hm]$/", $fileName)) {
                $this->filePaths[] = $path . DIRECTORY_SEPARATOR . $fileName;
            }

        }
    }
    
    //Debug跑该方法，通过本地存储的已经添加的需要替换的类名，以及需要单独测试的文件的目录
    public function  dealFilePathsWithClassNames($classNames, $filePaths) {
        $this->classNames = $classNames;
        $this->filePaths = $filePaths;
        foreach ($this->filePaths as $filePath) {
            $this->dealFileContent($filePath);
        }
    }
    
    public function dealFilePaths() {
        foreach ($this->filePaths as $filePath) {
            $this->dealFileContent($filePath);
        }
    }

    public function dealProjectPathForClassName() {
        $this->dealPorContent($this->projectPath);
    }

    //替换文件内容中被修改过的类名
    private function dealFileContent($path) {

        echo "正在替换{$path}文件内容" . PHP_EOL;
        $contents =  file_get_contents($path);
        $writeHandle = fopen($path, 'w+');
        foreach ($this->classNames as $className) {
            $contents = self::replace($contents, self::$prefix . $className . self::$suffix, $className);
        }
        fwrite($writeHandle, $contents);

        fclose($writeHandle);
    }

    //替换工程文件中被修改的类名（由于工程只修改.h,.m文件，所以分开写以防文件名与目录名称相同出现误修改目录名称的错误）
    private function dealPorContent($path) {
        echo "正在替换{$path}文件内容" . PHP_EOL;
        $contents =  file_get_contents($path);
        $writeHandle = fopen($path, 'w+');
        foreach ($this->classNames as $className) {
            $contents = self::replace($contents, self::$prefix . $className . self::$suffix . '.h', $className . '.h');
            $contents = self::replace($contents, self::$prefix . $className . self::$suffix . '.m', $className . '.m');
        }
        fwrite($writeHandle, $contents);

        fclose($writeHandle);
    }

    private function isBlackClass($className) {
        global $blackList;
        foreach ($blackList as $value) {
            if ($value === $className) {
                return true;
            }
        }

        return false;
    }

    //保存这一次脚本需要替换的类名，DEBUG用
    public function storeClassName() {
        $writeHandle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'DataStore.php', 'w+');
        fwrite($writeHandle, "<?php\n");
        fwrite($writeHandle, "\$debugClassName = [\n");
        foreach ($this->classNames as $className) {
            fwrite($writeHandle, "\t'$className',\n");
        }
        fwrite($writeHandle, "];\n");
    }
    
    //精确替换类名，如RoomInfo以及ChatRoomInfo的例子中，修改RoomInfo不会影响到ChatRoomInfo
    public static  function replace($content, $repalce, $search) {
        $pos = 0;
        while(false !== ($pos = strpos($content, $search, $pos))) {
            $hearC = '';
            $tailC = '';
            $length = strlen($content);
            $endPos = $pos + strlen($search);

            if ($pos === 0 && $endPos < $length) {
                $hearC = ' ';
                $tailC = substr($content, $pos + strlen($search), 1);
            } else if ($pos > 0 && $endPos < $length) {
                $hearC = substr($content, $pos - 1, 1);
                $tailC = substr($content, $endPos, 1);
            } else {
                $hearC = substr($content, $pos - 1, 1);
                $tailC = ' ';
            }

            if (preg_match("/\W/", $hearC) && preg_match("/\W/", $tailC)) {
                $content = substr_replace($content, $repalce, $pos, strlen($search));
            }

            $pos += strlen($search);
        }

        return $content;
    }

}
