<?php
namespace app\common\model;

use think\Model;
use app\common\validate\GoodsRunningValidate;

class GoodsRunning extends Model
{
    public static $statusOption = [
        1 => '未回调',
        2 => '执行成功',
        3 => '执行失败'
    ];

    public static $runningTypeOption = [
        1 => '柜子出货开门',
        2 => '柜子还货开门',
        3 => '开门补水',
    ];

    public static function add($data)
    {
        $validate = new GoodsRunningValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();

        if(!$model->allowField('id,order_id,rfid,running_type,device_aisle,status,ctime,synchro_result,async_result,device_id,order_info_id')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new GoodsRunningValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('id,order_id,rfid,running_type,device_aisle,status,ctime,synchro_result,async_result,device_id,order_info_id')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    
   


    public function device()
    {
        return $this->hasOne('device', 'device_id', 'device_id');
    }


    public function orderInfo()
    {
        return $this->hasOne('order_info', 'order_info_id', 'order_info_id');
    }


    public function order()
    {
        return $this->hasOne('order', 'order_id', 'order_id');
    }
    

}
