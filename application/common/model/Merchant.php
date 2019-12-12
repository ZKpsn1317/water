<?php
namespace app\common\model;

use think\Model;
//use app\common\validate\MerchantValidate;

class Merchant extends Model
{

	public function change($data)
    {
        /*$validate = new IcValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/


        if($this->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public static function add($data)
    {
      $model = new static();
        $data['ctime'] = time();
        if(!$model->save($data)) {
            throw new \think\Exception($model->getError());
        }
      
        return $model;
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