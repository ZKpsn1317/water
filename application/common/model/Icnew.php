<?php
namespace app\common\model;

use think\Model;
use app\common\validate\IcValidate;

class Icnew extends Model
{
    public static function add($data)
    {
        /*$validate = new IcValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        $model = static::get(['car_number' => $data['car_number']]);
        if($model) {
            throw new \think\Exception('IC卡已被绑定,需要先解除绑定');
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('car_number,ctime,agent_id,resume')->save($data)) {
            throw new \think\Exception($model->getError());
        }
      
        return $model;
    }


    public function change($data)
    {
        /*$validate = new IcValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        /*
         *updated by zk
         *time 2019/09/07 
         *
         */
        if($this->allowField('car_number,ctime')->save($data) === false) {
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
    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }
    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }
}
