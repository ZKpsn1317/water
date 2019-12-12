<?php
namespace app\common\model;

use think\Model;
use app\common\validate\WaterRechargeLogInfoValidate;

class WaterRechargeLogInfo extends Model
{
    public static function add($data)
    {
        /*$validate = new WaterRechargeLogInfoValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        $existLog = static::get(['water_recharge_log_id' => $data['water_recharge_log_id'], 'aisle_num' => $data['aisle_num']]);
        if($existLog) {
            $existLog->delete();
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('water_recharge_log_id,rfid,row,col,aisle_num')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new WaterRechargeLogInfoValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('water_recharge_log_id,rfid,row,col,aisle_num')->save($data) === false) {
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
    
    public function waterRechargeLog()
    {
        return $this->hasOne('water_recharge_log', 'water_recharge_log_id', 'water_recharge_log_id');
    }

	

}
