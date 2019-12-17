<?php
namespace app\common\model;

use think\Model;
use app\common\validate\AgentValidate;
use app\agent\model\AdminAgent;

class Agent extends Model
{

    public static $statusOption = [
        1 => '正常',
        2 => '禁用'
    ];
    
    public static function add($data)
    {
        $validate = new AgentValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if(AdminAgent::get(['username' => $data['username']])) {
            throw new \think\Exception($data['username'] . '已存在');
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField(true)->save($data)) {
            throw new \think\Exception($model->getError());
        }

        $model2 = new AdminAgent();
        $model2->save([
            'username' => $data['username'],
            'password' => md5($data['password']),
            'is_admin' => 1,
            'roleid' => 1,
            'agent_id' => $model->agent_id,
        ]);

        return $model;
    }


    public function change($data)
    {
        $validate = new AgentValidate();
        if(!$validate->edit()->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if($data['username'] != $this->username && AdminAgent::get(['username' => $data['username']])) {
            throw new \think\Exception($data['username'].'帐号已存在');
        }

        if(!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }

        $agentAdmin = AdminAgent::get(['agent_id' => $this->agent_id, 'is_admin'=>1]);
        $agentAdmin->username = $data['username'];
        if(!empty($data['password'])) {
            $agentAdmin->password = $data['password'];
        }
        $agentAdmin->status = $data['status'];
        $agentAdmin->save();

        if($this->allowField(true)->save($data) === false) {
            throw new \think\Exception($this->getError());
        }


    }
    public function actionchange($data){
        
        if($this->allowField('action_title,action_img,action_desc')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }
    public function waterchange($data){
        if($this->allowField('watersupply')->save($data) === false) {
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
    
    public function region()
    {
        return $this->hasOne('region', 'region_id', 'region_id');
    }


    public function devicenum()
    {
        return $this->hasOne('device', 'agent_id', 'agent_id')->group('agent_id')->field('agent_id,count(agent_id) as devicenum');
    }


    public function areanum()
    {
        return $this->hasOne('area', 'agent_id', 'agent_id')->group('agent_id')->field('agent_id,count(agent_id) as areanum');
    }


    public function bucketnum()
    {
        return $this->hasOne('bucket', 'agent_id', 'agent_id')->group('agent_id')->field('agent_id,count(agent_id) as bucketnum');
    }

	

}
