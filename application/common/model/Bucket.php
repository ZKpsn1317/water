<?php
namespace app\common\model;

use think\Model;
use app\common\validate\BucketValidate;

class Bucket extends Model
{
    public static $statusOption = [
        1 => '有水',
        2 => '没水',
        3 => '未知',
    ];



    public static function add($data)
    {
        $validate = new BucketValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('rfid,device_id,user_id,water_brand_id,status,area_id,ctime')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new BucketValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('rfid,device_id,user_id,water_brand_id,status,area_id,ctime')->save($data) === false) {
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

	public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }

	public function area()
    {
        return $this->hasOne('area', 'area_id', 'area_id');
    }

    public function waterBrand()
    {
        return $this->hasOne('water_brand', 'water_brand_id', 'water_brand_id');
    }

	

}
