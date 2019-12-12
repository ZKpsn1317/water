<?php
namespace app\common\tool;
use think\Request;

/**
 * 上传类
 * Class Upload
 * @package app\common\tool
 */
class Upload
{
    /**
     * 保存多图片
     * @param  string  $name [description] 文件内容
     * @param  integer $type [description] 文件类型
     * @return [type]        [description]
     */
    public static function uploads($name = '', $addDomain = true){
        // 获取表单上传文件
        $files = request()->file($name);

        $data = [];
        if(is_array($files)) {
            foreach($files as $file){
                $imgPath = self::upload($file, $addDomain);
                if($imgPath) {
                    $data[] = $imgPath;
                }
            }
        }
        return $data;
    }




    /**
     * 保存单图片
     * @param $file
     * @return string
     */
    public static function upload($file, $addDomain = true){

        $file = is_string($file) ? request()->file($file) : $file;
        if(!$file) {
            return '';
        }

        $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads/imgs');
        $domain = \think\Request::instance()->domain();
        if ($info) {
            $url = '/public/uploads/imgs/'.date("Ymd") .'/'.$info->getFilename();
            return  $addDomain ? $domain . $url : $url ;
        } else {
            return '';
        }
    }


    //保存多个文件
    public static function ups($name=''){
        // 获取表单上传文件
        $files = request()->file($name);

        $data = array();
        if(is_array($files)) {
            foreach($files as $file){
                $imgPath = self::upload($file);
                if($imgPath) {
                    $data[] = $imgPath;
                }
            }
        }
        return $data;
    }


    /**
     * 保存一个文件
     * @param $file
     * @return string
     */
    public static function up($file)
    {
        $file = is_string($file) ? request()->file($file) : $file;

        if(!$file) {
            return '';
        }

        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/imgs');
       
        if ($info) {
            $url = '/public/uploads/imgs/'.date("Ymd") .'/'.$info->getFilename();
            return  $url ;
        } else {
            return '';
        }
    }



    /**
     * 给多图加域名
     * @param $imagesPath
     */
    public static function imagesAddDomain(&$imagesPath, $domain) {
        foreach($imagesPath as $index => $imgPath) {
            $imagesPath[$index] = static::imageAddDomain($imgPath, $domain);
        }
    }


    /**
     * 给单图加域名
     * @param $imagePath
     * @return string
     */
    public static function imageAddDomain($imagePath, $domain='') {
        if(!$imagePath) {
            return '';
        }
        if($domain == '') {
            $domain = \think\Request::instance()->domain();
        }
        return substr($imagePath, 0, 4) == 'http' ? $imagePath : $domain . $imagePath;
    }

}