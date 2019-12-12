<?php
namespace app\common\model;

use think\Model;
use app\common\validate\UserTypeValidate;

class UserWallet extends Model
{
    public function userType()
    {
        return $this->hasOne('user_type', 'user_type_id', 'user_type_id');
    }


    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id')->field('agent_id,agent_name');
    }


    public function giveBucket($number)
    {

        $this->give_bucket_num += $number;
        $this->bucket_num += $number;
        $this->save();
        
    }

    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }

}