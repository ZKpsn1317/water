<?php
namespace app\common\model;

use think\Model;
use app\common\validate\UserRefundValidate;

class UserRefund extends Model
{
    public static $statusOption = [
        1 => '未处理',
        2 => '已处理'
    ];

    public static function add($data)
    {
        $validate = new UserRefundValidate();
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
        $validate = new UserRefundValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if(!$this->handling_time) {
            $this->handling_time = time();
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

    public function getHandlingTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    
    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }

	public function rechargeOrder()
    {
        return $this->hasOne('recharge_order', 'recharge_order_id', 'recharge_order_id');
    }

	

}
