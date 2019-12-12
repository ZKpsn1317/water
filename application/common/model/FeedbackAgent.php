<?php
namespace app\common\model;

use think\Model;
use app\common\validate\FeedbackAgentValidate;
use app\common\tool\ProcessData;

class FeedbackAgent extends Model
{


    const DAY_SEND_NUMBER = 10; //一天最多可以提交的数量


    public static function add($data) {


        if(!static::checkSendNumber($data['agent_id'])) {
            throw new \think\Exception('一天最多反馈' . self::DAY_SEND_NUMBER . '条记录');
        }

        $validate = new FeedbackAgentValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        ProcessData::$rule = [
            'img' => 'saveImages',
            'thumb_img' => 'copy:img|cuts:200,200,1'
        ];

        ProcessData::cooking($data);

        $data['img'] = json_encode($data['img']);
        $data['thumb_img'] = json_encode($data['thumb_img']);
        $data['ctime'] = time();

        $model = new static();
        if(!$model->allowField('agent_id,content,img,phone,ctime,dispose_time,thumb_img')->save($data)) {
            throw new \think\Exception($model->getError());
        }
        return $model;
    }


    public function change($data)
    {
        $data['dispose_time'] = time();
        if($this->allowField('dispose_content,status,dispose_time')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }


    /**
     * 检查一个用户一天提交的数据是否超过了限制
     * @param $user_id
     * @return bool
     */
    protected static function checkSendNumber($user_id) {
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        return static::where("agent_id='$user_id' AND ctime>='$beginToday' AND ctime<$endToday")->count() < self::DAY_SEND_NUMBER;
    }


    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i', $value) : '';
    }

    public function getDisposeTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i', $value) : '';
    }

    public function getImgAttr($value)
    {
        return json_decode($value);
    }

    public function getThumbImgAttr($value)
    {
        return json_decode($value);
    }


    public function del()
    {
        $this->delete();
    }


    public function agent()
    {
        return $this->hasOne('agent', 'id', 'agent_id');
    }

}
