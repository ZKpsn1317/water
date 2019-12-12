<?php
namespace app\common\model;

use think\Model;
use app\common\validate\RewardLogValidate;

class RewardLog extends Model
{
    public static function add($data)
    {
        $validate = new RewardLogValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('order_id,device_id,type,agent_num,platform_num,ctime,order_price,scale,agent_id')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new RewardLogValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('order_id,device_id,type,agent_num,platform_num,ctime,order_price,scale,agent_id')->save($data) === false) {
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
    
    public function order()
    {
        return $this->hasOne('order', 'order_id', 'order_id');
    }

	public function device()
    {
        return $this->hasOne('device', 'device_id', 'device_id');
    }

	public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

	

}
