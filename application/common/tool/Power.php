<?php
namespace app\common\tool;
use think\Cache;

/**
 * 权限类
 * Class Power
 * @package app\common\tool
 */
class Power
{
    protected static $allOaths = [];     //存放权限数组
    protected static $key = '';

    /**
     * 检测权限
     * @param $requestPath
     * @return bool
     */
    public static function check($requestPath) {
        //dump(static::$allOaths);exit;
        if(static::$allOaths === false) {
            echo '请重新登入!';die;
        }
        $requestPath = str_replace('_', '', $requestPath);
        return in_array(strtolower($requestPath), static::$allOaths);
    }


    /**
     * 设置键
     * @param $key
     */
    public static function setKey($key) {
        static::$key = $key;
    }


    /**
     * 缓存权限
     * @param $oaths
     */
    public static function cacheAllOaths($uid, $oaths)
    {
        $os = [];
        foreach($oaths as $row) {
            if(!$row['url']) {
                continue;
            }
            $os[] = str_replace('_', '', $row['url']);
        }
        Cache::set('allOaths_' . static::$key . $uid, $os);     //只有权限的缓存
        Cache::set('allOaths' . static::$key . $uid, $oaths);   //有图标，菜单名，及权限的缓存
    }


    /**
     * 取权限 有图标，菜单名，及权限的缓存
     * @return mixed
     */
    public static function getAllOaths($uid)
    {
        return Cache::get('allOaths'.static::$key . $uid) ?: [];
    }


    /**
     * 取权限
     * @return mixed
     */
    public static function getAllOaths_($uid)
    {
        return Cache::get('allOaths_'.static::$key . $uid);
    }



    public static function loadOaths($uid)
    {
        $oaths = static::getAllOaths_($uid);
        static::$allOaths = $oaths;
    }

}