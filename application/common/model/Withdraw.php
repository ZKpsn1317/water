<?php
namespace app\common\model;

use think\Model;
use app\common\validate\WithdrawValidate;

class Withdraw extends Model
{
    public static function add($data)
    {
        $validate = new WithdrawValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('agent_id,bank_name,bank_account_name,bank_account,bank_branch,bank_mobile,num,ctime,status,handle_time,remarks')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new WithdrawValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('agent_id,bank_name,bank_account_name,bank_account,bank_branch,bank_mobile,num,ctime,status,handle_time,remarks')->save($data) === false) {
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
