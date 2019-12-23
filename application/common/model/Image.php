<?php
namespace app\common\model;

use app\common\tool\ProcessData;
use app\common\validate\PhotoadsValidate;
use think\Model;

//用户类
class Image extends Model
{
    public static $statusOption = [
        1 => '显示',
        0 => '不显示',
    ];
    public function change($data)
    {
        // dump($data);die;
        // $validate = new PhotoadsValidate();
        // if(!$validate->check($data)) {
        //     throw new \think\Exception($validate->getError());
        // }
        if($this->allowField('i_img')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    public function getICtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
}