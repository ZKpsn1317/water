<?php
namespace app\common\basemodel;

use think\Model;

/**
 * 角色基础类
 * Class RoleOath
 * @package app\common\basemodel
 */
class Role extends Model
{
    public static $oathClass = '\app\dlc\model\RoleOath';
    /**
     * 取一个角色可以拥有的权限
     * @param $role_id
     */
    public static function getRoleOath($role_id)
    {
        $role = static::get(['role_id' => $role_id]);

        $query = new static::$oathClass();

        if($role->is_admin == 0 && $role->auth == '') {
            //不是超级管理员，也没有设置权限
            return [];
        }


        if($role->is_admin == 0){
            $query->where('oath_id', 'IN', $role->auth);
        }

        
        $list =  $query->select();

        foreach ($list as $k => $row) {
            $auth_name = $row['url'];
            if(!$auth_name) {
                continue;
            }

            if(strpos($auth_name,"?")) {
                $auth_name = explode("?", $auth_name)[0];
            }

            $auth_name = strtolower($auth_name);

            $list[$k]['url'] = $auth_name;
        }

        return $list;
    }
}