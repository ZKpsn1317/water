<?php
namespace app\common\model;

use think\Model;
use app\common\validate\RegionValidate;

class Region extends Model
{
    public static function add($data)
    {
        $validate = new RegionValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('region_name')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new RegionValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('region_name')->save($data) === false) {
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
    
    public function agent()
    {
        return $this->hasOne('agent', 'region_id', 'region_id');
    }

	public function device()
    {
        return $this->hasOne('device', 'region_id', 'region_id');
    }

	

}
