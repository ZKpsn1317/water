<?php
namespace app\common\model;

use think\Model;
use app\common\validate\SetMealValidate;

class Coupon extends Model
{
    public $table = 'dlc_user_coupon';
    public static $status = [
        1 => '已使用',
        2 => '待使用',
    ];
    public static $types = [
        1 => '新会员注册成功赠送',
        2 => '邀请用户注册成功赠送',
        3 => '后台赠送',
    ];
    public static function add($data)
    {
        $validate = new SetMealValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField(true)->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new SetMealValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField(true)->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    
    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    } 
    public function user($value='')
    {
        return $this->hasOne('user','user_id','user_id');
    }
}
