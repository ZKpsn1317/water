<?php
namespace app\common\model;

use think\Model;
use app\common\validate\OrderInfoValidate;

class OrderInfo extends Model
{
    public static $shippingStatusOption = [
        3 => '未出货',
        2 => '出货失败',
        1 => '出货成功'
    ];

    public static function add($data)
    {
        $validate = new OrderInfoValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('order_info_id,rfid,water_brand_name,price,order_id,img,shipping_status,return_time,return_device_id')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new OrderInfoValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('order_info_id,rfid,water_brand_name,price,order_id,img,shipping_status,return_time,return_device_id')->save($data) === false) {
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

    public function getReturnTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getShippingTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


    public function order()
    {
        return $this->hasOne('order', 'order_id', 'order_id');
    }


    /**
     * 关联出货记录
     */
    public function deliveryOrdern()
    {
        return $this->hasOne('goods_running', 'order_info_id', 'order_info_id');
    }

	

}
