<?php
namespace app\common\model;

use think\Model;
use app\common\validate\UserPressureGoldValidate;

class UserPressureGold extends Model
{
    public static function add($data)
    {
        $validate = new UserPressureGoldValidate();
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
        $validate = new UserPressureGoldValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('user_id,recharge_order_id,price,ctime,agent_id')->save($data) === false) {
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

    public function getRtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }

    public function order()
    {
        return $this->hasOne('pressure_gold_order', 'pressure_gold_order_id', 'recharge_order_id');
    }


    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id')->field('agent_name,agent_id');
    }

    public function userType()
    {
        return $this->hasOne('user_type', 'user_type_id', 'user_type_id');
    }


    public function userWallet()
    {
    }

}
