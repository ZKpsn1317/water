<?php
namespace app\common\model;

use think\Model;
use app\common\validate\UserTypeValidate;

class UserType extends Model
{
    public static $statusOption = [
        1 => '启用',
        2 => '停用',
    ];


    public static function add($data)
    {
        $validate = new UserTypeValidate();
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
        $validate = new UserTypeValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('user_type_name,pressure_gold,bucket_num,status,img,hint,sort')->save($data) === false) {
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


    public static function getValid($id)
    {
        return static::get(['user_type_id' => $id, 'status' => 1]);
    }


    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }
    
    

}
