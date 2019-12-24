<?php
namespace app\common\model;

use think\Model;
use app\common\validate\UserWalletLogValidate;

class UserWalletLog extends Model
{
    public static $typeOption = [
        1 => '充值',
        2 => '购水',
    ];

    public static $directionOption = [
        1 => '进帐',
        2 => '出帐',
    ];

    public static function add($data)
    {
        $validate = new UserWalletLogValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('user_id,type,num,relevance,direction,ctime,agent_id,orignnum')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new UserWalletLogValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('user_id,type,num,relevance,direction,ctime,agent_id')->save($data) === false) {
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
    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }
    public function wallet(){
        return $this->hasOne('user_wallet', 'user_id', 'user_id');
    }


	

}
