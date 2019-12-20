<?php
namespace app\common\model;

use app\common\tool\ProcessData;
use app\common\validate\UserguideValidate;
use think\Model;

//用户类
class Userguide extends Model
{
    public static $statusOption = [
        1 => '启用',
        2 => '停用',
    ];
    public function change($data)
    {
        // dump($data);die;
        $validate = new UserguideValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }
        if($this->allowField('guide_title,guide_desc')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    
}