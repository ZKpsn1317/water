<?php
namespace app\common\model;

use think\Model;
use app\common\validate\WaterRechargeLogValidate;

class WaterRechargeLog extends Model
{
    public static function add($data)
    {
        $validate = new WaterRechargeLogValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        $data['waterctime']=time();
        if(!$model->allowField(true)->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new WaterRechargeLogValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
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
    
    public function device()
    {
        return $this->hasOne('device', 'device_id', 'device_id');
    }

	public function waterBrand()
    {
        return $this->hasOne('water_brand', 'water_brand_id', 'water_brand_id');
    }


    //取当前补货员，补货中的任务
    public static function didNotFinish($device_id, $water_recharge_id)
    {
        return static::get(['device_id' => $device_id, 'water_recharge_id' => $water_recharge_id, 'status' => 2]);
    }



    public function waterRecharge()
    {
        return $this->hasOne('water_recharge', 'water_recharge_id', 'water_recharge_id');
    }


    /**
     * 确认补水
     */
    public function finish()
    {
        $count = WaterRechargeLogInfo::where(['water_recharge_log_id' => $this->water_recharge_log_id])->count();
        $this->number = $count;
        $this->status = 1;
        $this->save();
        return true;
    }


    /**
     * 补水记录
     */
    public function rechargeLog()
    {

    }
	

}
