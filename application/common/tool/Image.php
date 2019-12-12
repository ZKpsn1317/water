<?php
namespace app\common\tool;

/**

 * Class Excel
 * @package app\common\tool
 */
class Image
{
    protected static $tmpDir;

    /**
     * 给图片加文字
     * @param $file
     * @param $code
     */
    public static function addText($file, $code)
    {
        $imagesize = getimagesize($file);

        $fontLeft = ($imagesize[0] - 20*strlen($code)) /2;

        $info = getimagesize($file); // 获取图片信息
        $type = image_type_to_extension($info[2],false); // 获取图片扩展名
        $fun  = "imagecreatefrom{$type}"; // 构建处理图片方法名-关键是这里
        $image = $fun($file); // 调用方法处理

        $font = ROOT_PATH.'extend/verify/Verify/ttfs/2.ttf'; // 字体文件

        $color = imagecolorallocate($image,0,0,0); // 文字颜色
        imagettftext($image, $imagesize[1]/100*5, 0, $fontLeft, $imagesize[1], $color, $font, $code); // 创建文字
        imagepng($image, $file);
        imagedestroy($image);
    }


    /**
     * @param $url      创建二维码的URL
     * @param bool $saveFile    false为这保存，直接输出浏览器，如果不是false保存成文件
     */
    public static function createQrcode($url, $saveFile = false)
    {
        require_once("vendor/QRcode.php");
        $errorCorrectionLevel = "H";
        $matrixPointSize = "8";
        ob_clean();//这个一定要加上，清除缓冲区
        \QRcode::png(urldecode($url), $saveFile, $errorCorrectionLevel, $matrixPointSize, 4, false);
    }


    /**
     * 创建有文件的二维码
     * @param $url
     * @param $text
     */
    public static function createQrText($url, $text)
    {
        $file = ROOT_PATH . 'public' . DS . 'qrcode' . time() . mt_rand(10000, 90000);
        static::createQrcode($url, $file);
        static::addText($file, $text);
        header('Content-type: image/png');
        echo file_get_contents($file);
        unlink($file);
    }

    /**
     * 批量创建二维码
     * @param $urlArr   URL 数组， key 为文件名, 值 为URL
     * @param bool $addkeyText  是否把 文件名写入到二维码中
     */
    public static function createQrcodes($urlArr, $addkeyText = true)
    {
        $tmpDir = static::createTmpDir();
        foreach($urlArr as $key => $url) {
            $file = $tmpDir . DS . $key . '.png';
            static::createQrcode($url, $file);
            $addkeyText && static::addText($file, $key);
        }
    }


    /**
     * 创建批量二维码，并打包成ZIP
     * @param $urlArr
     */
    public static function createQrZip($urlArr)
    {
        if(empty($urlArr) || !is_array($urlArr)) {
            return;
        }

        static::createQrcodes($urlArr);

        Zip::dir(static::$tmpDir);

        static::removeDir(static::$tmpDir);
    }


    /**
      删除一个文件夹目录
     * @param $path
     */
    public static function removeDir($path)
    {
        //如果是目录则继续
        if (!is_dir($path)) {
            return;
        }

        $p = scandir($path);
        foreach ($p as $val) {

            if ($val == "." || $val == "..") {
                continue;
            }

            if (is_dir($path . $val)) {
                static::removeDir($path . $val . DS);
            } else {
                unlink($path . $val);
            }
        }
        @rmdir($path);


    }


    /**
     * 创建临时文件
     */
    protected static function createTmpDir()
    {
        $dir = ROOT_PATH . 'public' . DS  . 'uploads' . DS . 'qrcode' . time() . mt_rand(1000, 9999) . DS;
        mkdir($dir, 0775);
        static::$tmpDir = $dir;
        return $dir;
    }


}