<?php
namespace app\common\model;
use think\Model;

//用户类
class Kfzx extends Model
{
    public function change($data)
    {
        // dump($data);die;
        // $validate = new PhotoadsValidate();
        // if(!$validate->check($data)) {
        //     throw new \think\Exception($validate->getError());
        // }
        if($this->allowField('id,name,img,desc')->save($data) === false) {
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
}