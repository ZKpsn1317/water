<?php
namespace app\common\tool;

/**
 * 加工数据类
 * Class ProcessData
 * @package app\common\tool
 */
class ProcessData
{
    public static $rule = [

    ];

    /**
     * 处理数据入口
     * @param $data
     */
    public static function cooking(&$data)
    {
        $rule = static::$rule;
        foreach($rule as $field => $ms) {
            $ms = explode('|', $ms);
            foreach($ms as $m) {
                $m = explode(':', $m);
                $method = $m[0];    //处理方法
                $parameter = isset($m[1]) ? $m[1] : '';
                self::$method($field, $parameter, $data);  //$key 字段 , $parameter定义规则
            }
        }
    }


    //保存文件
    protected static function saveImage($field, $parameter, &$data)
    {
        $data[$field] = Upload::upload($field, false);
    }


    //保存文件
    protected static function saveImages($field, $parameter, &$data)
    {
        $data[$field] = Upload::uploads($field, false);
    }


    //把别一个字段值复制过来
    protected static function copy($field, $parameter, &$data)
    {
        $data[$field] = $data[$parameter];
    }


    //裁剪多张图片
    protected static function cuts($field, $parameter, &$data)
    {
        foreach($data[$field] as $key => $url) {
            static::cut($key, $parameter, $data[$field]);
        }
    }


    //裁剪图片
    protected static function cut($field, $parameter, &$data)
    {

        $url = $data[$field] ?: '';
        if(!$url) {
            return;
        }

        //是本地路径
        if($url[0] != DS) {
            $url = str_replace(\think\Request::instance()->domain(), '', $url);
        }

        $url = ROOT_PATH . str_replace(['/', '\\'], DS, $url);
        if(!is_file($url)) {
            return;
        }

        $image = \think\Image::open($url);
        $wh = explode(',', $parameter);

        $iwidth = $image->width();	    // 返回图片的宽度
        $iheight = $image->height();	// 返回图片的高度

        $width = $wh[0];	// 处理后的宽度
        $height = $wh[1];	// 处理后的高度

        if( $iwidth < $width && $iheight < $height) {
            $width = $iwidth;
            $height = $iheight;
        }

        if($iwidth == $iheight) {
            $w = $width <= $height ? $width : $height;   //裁剪的宽度
        } elseif($iwidth > $iheight) {
            $w = $height / ($iwidth/$iheight);
        } else{
            $w = $width / ($iheight/$iwidth);
        }


        if(isset($wh[2]) && $wh[2]) {
            //别存一份
            $pathinfo = pathinfo($url);
            $url = $pathinfo['dirname'] . DS . 'thumb_' .time() . mt_rand(10000, 99999) . '.' . $pathinfo['extension'];
            $image->thumb($width, $height)->crop($w, $w)->save($url);
            $data[$field] = str_replace([ROOT_PATH, DS], ['', '/'], $url);
        } else {
            $image->thumb($width, $height)->crop($w, $w)->save($url);
        }

    }
}