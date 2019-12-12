<?php
namespace app\common\model;

use think\Model;
use app\common\validate\AreaValidate;
use app\area\model\AdminArea;

class Area extends Model
{
    public static function add($data)
    {
        $validate = new AreaValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        $data['password'] = md5($data['password']);
        if(!$model->allowField('area_name,area_address,agent_id,username,password,device_num,bucket_num,ctime')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        $model2 = new AdminArea();
        $model2->save([
            'username' => $data['username'],
            'password' => $data['password'],
            'ctime' => time(),
            'roleid' => 1,
            'is_admin' => 1,
            'area_id' => $model->area_id,
        ]);

        return $model;
    }


    public function change($data)
    {
        $validate = new AreaValidate();
        if(!$validate->edit()->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if($this->username != $data['username'] && static::get(['username' => $data['username']])) {
            throw new \think\Exception($data['username'] . '已经存在');
        }

        if(!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }

        $agentAdmin = AdminArea::get(['area_id' => $this->area_id, 'is_admin'=>1]);
        $agentAdmin->username = $data['username'];
        $agentAdmin->status = $data['status'];
        if(!empty($data['password'])) {
            $agentAdmin->password = $data['password'];
        }
        $agentAdmin->save();

        if($this->allowField('area_name,area_address,agent_id,username,password,device_num,bucket_num,ctime')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        AdminArea::where(['area_id' => $this->area_id])->delete();
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

	public function device()
    {
        return $this->hasMany('device', 'area_id', 'area_id');
    }

	

}
