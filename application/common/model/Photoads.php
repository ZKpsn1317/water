<?php
namespace app\common\model;

use app\common\tool\ProcessData;
use app\common\validate\PhotoadsValidate;
use think\Model;

//用户类
class Photoads extends Model
{
    public static $statusOption = [
        1 => '显示',
        0 => '不显示',
    ];
    public function change($data)
    {
        $validate = new PhotoadsValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }
        if($this->allowField('photoads_title,photoads_stitle,photoads_desc,photoads_status,photoads_sort,photoads_img')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    public function getPhotoadsCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

}