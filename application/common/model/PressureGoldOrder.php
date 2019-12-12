<?php
namespace app\common\model;

use think\Model;
use app\common\validate\PressureGoldOrderValidate;

class PressureGoldOrder extends Model
{
    public static $statusOption = [
        1 => '正常',
        2 => '退款中',
        3 => '已退款',
        4 => '退款失败',
    ];

    public static function add($data)
    {
        $validate = new PressureGoldOrderValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('user_id,user_type_id,price,ctime,status,trade_number,pay_time,refund_time,user_type_name,bucket_num,agent_id,area_id')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new PressureGoldOrderValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('user_id,user_type_id,price,ctime,status,trade_number,pay_time,refund_time,user_type_name,bucket_num')->save($data) === false) {
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

    public function getRefundTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getPayTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    

    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }


    public function payOk($trade_number)
    {
        if($this->status != 1) {
            return;
        }

        //修改订单状态
        $this->status = 2;
        $this->trade_number = $trade_number;
        $this->pay_time = time();
        $this->save();

        //修改用户类型
        $user = $this->user;

        $user_ = UserWallet::get(['user_id' => $user->user_id, 'agent_id' => $this->agent_id ]);
        if(!$user_->getData('user_type_id')) {
            $user_->setAttr('user_type_id', $this->user_type_id);
        }

        $userType = $this->user_type;

        //记录押金
        UserPressureGold::add([
            'user_id' => $user->user_id,
            'recharge_order_id' => $this->pressure_gold_order_id,
            'price' => $this->price,
            'agent_id' => $this->agent_id,
            'hint' => $userType->hint,
        ]);

        //增加桶数
        $user_->bucket_num += $this->bucket_num;
        $user_->pressure_gold += $this->price;
        $user_->save();


    }


    public function userType()
    {
        return $this->hasOne('user_type', 'user_type_id', 'user_type_id');
    }


	public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

    public function area()
    {
        return $this->hasOne('area', 'area_id', 'area_id');
    }
}
