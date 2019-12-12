<?php
namespace app\common\tool;

/**
 * 请求类
 * Class Request
 * @package app\common\tool
 */
class Request
{

    public static function client(){
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            return 'ios';
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            return 'android';
        }else if(strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger')){
            return 'wxbrowser';
        } else {
            return 'other';
        }
    }


    /**
     * 判断客户端是不是安卓
     */
    public static function isAndroid()
    {
        return static::client() == 'android';
    }
}