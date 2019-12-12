<?php
namespace app\common\model;

use think\Model;
use app\common\validate\HitchValidate;
use app\common\tool\ProcessData;

class Hitch extends Model
{
    const DAY_SEND_NUMBER = 10;
    public static $statusOption = [
        1 => '已处理',
        2 => '未处理',
    ];

    public static function add($data)
    {
        $validate = new HitchValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        //$data['images'] = implode(',', $data['images']);

        $device = Device::get(['device_id' => $data['device_id']]);
        $data['macno'] = $device->macno;
        $data['address'] = $device->device_address;
        $data['agent_id'] = $device->agent_id;

        static::checkSendNumber($data['user_id']);


        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('user_id,type,macno,address,content,images,ctime,status,handling_time,processing_result,agent_id')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        /*$validate = new HitchValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        if(!$this->handling_time) {
            $this->handling_time = time();
        }

        if($this->allowField('user_id,type,macno,address,content,images,ctime,status,handling_time,processing_result')->save($data) === false) {
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

    public function getImagesAttr($value)
    {
        return $value ? explode(',', $value) : '';
    }


    public function getHandlingTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }

    /**
     * 检查一个用户一天提交的数据是否超过了限制
     * @param $user_id
     * @return bool
     */
    protected static function checkSendNumber($user_id) {
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        return static::where("user_id='$user_id' AND ctime>='$beginToday' AND ctime<$endToday")->count() < self::DAY_SEND_NUMBER;
    }
	

}
