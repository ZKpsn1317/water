<?php
namespace app\agent\model;

use think\Model;
use app\common\validate\RepairmanValidate;

class AdminAgent extends Model
{

	public static function add($data)
    {
        $validate = new RepairmanValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        $data['password'] = md5($data['password']);
        $data['roleid'] = 2;
        $data['is_admin'] = 0;
        //$data['token'] = User::createToken($data['password']);
        if(!$model->allowField('username,password,mobile,nickname,agent_id,ctime,status,roleid,is_admin')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }
    public function getCtimeAttr ( $value )
    {
        return $value ? date( 'Y-m-d H:i:s', $value ) : '';
    }
    public function change($data)
    {
        $validate = new RepairmanValidate();
        if(!$validate->edit()->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if(!empty($data['username']) && $data['username'] != $this->username && static::get(['username' => $data['username']])) {
            throw new \think\Exception($data['username'] . '已存在');
        }

        if(!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }



        if($this->allowField('username,password,mobile,nickname,agent_id,ctime,status')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
}