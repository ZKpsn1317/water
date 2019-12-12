<?php
namespace app\common\model;

use think\Model;
use app\common\validate\IntentionValidate;

class Intention extends Model
{
    const DAY_SEND_NUMBER = 6; //一天最多可以提交的数量

    public static function add($data)
    {
        if(!static::checkSendNumber($data['user_id'])) {
            throw new \think\Exception('一天最多发送' . self::DAY_SEND_NUMBER . '条记录');
        }

        $validate = new IntentionValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $data['ctime'] = time();
        $model = new static();
        if(!$model->allowField('user_id,name,content,phone,email,ctime')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        /*$validate = new IntentionValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        $data['dispose_time'] = time();
        if($this->allowField('user_id,name,content,phone,email,ctime,dispose_content,status,dispose_time')->save($data) === false) {
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

    public function getDisposeTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
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
