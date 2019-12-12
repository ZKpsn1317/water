<?php
namespace app\common\model;

use think\Model;
//use app\common\validate\ActValidate;

class Act extends Model
{

	public function change($data)
    {
        /*$validate = new ActValidate();
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

    public function merchant ()
    {
        return $this->hasOne( 'merchant', 'id', 'merchant_id' );
    }


}