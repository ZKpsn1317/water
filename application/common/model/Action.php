<?php
namespace app\common\model;

use think\Model;

class Action extends Model
{
    public function change($data)
    {
        $validate = new UserTypeValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('action_title,action_url,action_desc,action_img')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }
}