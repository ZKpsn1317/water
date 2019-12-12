<?php
namespace app\common\model;

use think\Model;
use app\common\validate\DeliveryOrdernValidate;

class DeliveryOrdern extends Model
{
    public static function add($data)
    {
        $validate = new DeliveryOrdernValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('order_id,row,col,aisle_num,shipping_status,order_info_id,ctime')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new DeliveryOrdernValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('order_id,row,col,aisle_num,shipping_status,order_info_id,ctime')->save($data) === false) {
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

	public function orderInfo()
    {
        return $this->hasOne('order_info', 'order_info_id', 'order_info_id');
    }

	

}
