<?php
namespace app\common\model;

use think\Model;
use app\common\validate\WaterRechargeValidate;

class WaterRecharge extends Model
{
    public static $statusOption = [
        1 => '正常',
        2 => '禁用',
    ];

    
    public static function add($data)
    {
        $validate = new WaterRechargeValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        $data['password'] = md5($data['password']);
        $data['token'] = User::createToken($data['password']);
        if(!$model->allowField('username,password,mobile,name,agent_id,ctime,status,token')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new WaterRechargeValidate();
        if(!$validate->edit()->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if(!empty($data['username']) && $data['username'] != $this->username && static::get(['username' => $data['username']])) {
            throw new \think\Exception($data['username'] . '已存在');
        }

        if(!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }



        if($this->allowField('username,password,mobile,name,agent_id,ctime,status,token')->save($data) === false) {
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
    
    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

	

}
