<?php
namespace app\common\model;

use think\Model;
use app\common\validate\SetMealValidate;

class SetMeal extends Model
{
    public static $giveOption = [
        0 => '不送',
        1 => '送',
    ];
    public static function add($data)
    {
        $validate = new SetMealValidate();
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
        $validate = new SetMealValidate();
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


    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }
    
    

}
