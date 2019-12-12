<?php
namespace app\common\model;

use think\Model;
use app\common\validate\WaterBrandValidate;

class WaterBrand extends Model
{
    public static function add($data)
    {
        $validate = new WaterBrandValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('water_brand_name,volume,producing_area,price,ctime,image,agent_id')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new WaterBrandValidate();
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
    
    public function bucket()
    {
        return $this->hasMany('bucket', 'water_brand_id', 'water_brand_id');
    }

	public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

}
