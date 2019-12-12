<?php
namespace app\common\model;

use think\Model;
//use app\common\validate\MerchantValidate;

class MerchantType extends Model
{
    public static $statusOption = [
        0 => '免费领取',
        1 => '付费领取',
    ];
	public function change($data)
    {
        /*$validate = new IcValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/
        if($data['type'] == 0){
            $data['buy_times'] = 0;
        }else{
            $data['wx_id_times'] = 0;
            $data['time_times'] = 0;
            $data['agent_times'] = 0;
        }

        if($this->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public static function add($data)
    {
      $model = new static();
        $data['createtime'] = time();
        if(!$model->save($data)) {
            throw new \think\Exception($model->getError());
        }
      
        return $model;
    }

    public function del()
    {
        $this->delete();
    }
    
    public function getCreatetimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


}