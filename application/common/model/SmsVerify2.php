<?php
namespace app\common\model;

use think\Model;

class SmsVerify2 extends Model
{
    protected $resultSetType = 'collection';

    const SMS_TYPE_REGISTER = 0;    //注册
    const SMS_TYPE_MODIFY = 1;      //修改手机号码
    const SMS_TYPE_GETPASS = 2;     //找回密码

    const SMS_ACTIVE_TIME = 30000;    //有效时间300秒
    const SMS_SEND_INTERVAL = 60;    //两次发送短信的间隔
    const SMS_ERROR_NUMBER = 3;     //允许的错误次数


    /**
     * 生成验证码
     */
    protected static function createCode()
    {

        return  mt_rand(100000, 999999);
    }


    /**
     * 发送注册，短信验证码
     * @param $mobile  手机号
     * @param $text     内容
     * @return bool     是否发送成功
     * @throws \ErrorException
     */
    public static function register($mobile, $text) {
        if(\app\common\validate\BaseValidate::checkMobile($mobile) !== true) {
            throw new \ErrorException('请输入正确的手机号码!');
        }

        $user = User::get(['mobile' => $mobile]);

        if($user) {
            throw new \ErrorException($mobile . '已注册');
        }
        return static::sendCode($mobile, $text, self::SMS_TYPE_REGISTER);
    }


    /**
     * 发送找回密码，短信验证码
     * @param $mobile  手机号
     * @param $text     内容
     * @return bool     是否发送成功
     * @throws \ErrorException
     */
    public static function getPass($mobile, $text) {
        if(\app\common\validate\BaseValidate::checkMobile($mobile) !== true) {
            throw new \ErrorException('请输入正确的手机号码!');
        }

        $user = User::get(['mobile' => $mobile]);

        if(!$user) {
            throw new \ErrorException($mobile . '未注册');
        }

        return static::sendCode($mobile, $text, self::SMS_TYPE_GETPASS);
    }


    /**
     * 发送修改手机号码,短信验证码
     * @param $mobile  手机号
     * @param $text     内容
     * @return bool     是否发送成功
     * @throws \ErrorException
     */
    public static function modify($mobile, $text) {

        if(\app\common\validate\BaseValidate::checkMobile($mobile) !== true) {
            throw new \ErrorException('请输入正确的手机号码!');
        }

        $user = User::get(['mobile' => $mobile]);

        if($user) {
            throw new \ErrorException($mobile . '已存在');
        }

        return static::sendCode($mobile, $text, self::SMS_TYPE_MODIFY);
    }


    /**
     * 发送注册验证码
     * $mobile : 手机号码
     */
    protected static function sendCode($mobile, $text='', $type)
    {


        $code = static::createCode();
        $text = str_replace('#code', $code, $text);


        if( static::sendSms($mobile, $text) === false ) {
            throw new \ErrorException('验证码发送失败,请稍候再试!');
        }

        //保存数据

        $rst = static::get(['mobile' => $mobile, 'type' => $type]);

        if($rst && $rst->ctime + static::SMS_SEND_INTERVAL > time()) {
            throw new \ErrorException(static::SMS_SEND_INTERVAL/60 . '分钟内,只能发送一次验证码');
        }

        $rst = $rst ? $rst : new static();

        $rst->save([
            'mobile' => $mobile,
            'code'  => $code,
            'ctime' => time(),
            'type' => $type,
            'errors_number' => 0,
        ]);
        return true;
    }


    /**
     * 验证码错误次数加1
     */
    public function addErrorsNumber()
    {
        $this->errors_number++;
        $this->save();
    }


    /**
     * 验证注册验证码
     * @param $mobile : 手机号码
     * @param $code  ： 验证码
     */
    public static function verifyCode($mobile, $code, $type)
    {
        $model = static::get(['mobile' => $mobile, 'type' => $type]);
        if(!$model) {
            throw new \ErrorException('请先发送短信验证码!');
        }

        //验证有效时间
        if($model->ctime + static::SMS_ACTIVE_TIME < time()) {
            throw new \ErrorException('验证码已经失效!');
        }

        //验证错误次数
        if($model->errors_number >= self::SMS_ERROR_NUMBER) {
            throw new \ErrorException('错误次数达到'. self::SMS_ERROR_NUMBER .'次, 请重新获取!');
        }

        if($model->code != $code) {
            $model->addErrorsNumber();
            throw new \ErrorException('验证码错误,请重新输入!');
        }

        return $model;

    }
    

    /**
     * 发送短信
     * @param $mobile
     * @param $text
     */
    /*protected static function sendSms($mobile, $text)
    {
        $rst = zt_sms_send($mobile, '', $text);
        return $rst[0] == 1;
    }*/


    protected static function sendSms($mobile, $text)
    {
        // 配置信息
        $config = [
            'app_key'    => '*****',
            'app_secret' => '************',
        ];

        $client = new \Flc\Alidayu\Client(new \Flc\Alidayu\App($config));
        $req = new \Flc\Alidayu\Requests\AlibabaAliqinFcFlowCharge;

        $req->setPhoneNum('13312311231')
            ->setGrade('50')
            ->setOutRechargeId('111111');

        print_r($client->execute($req));
    }


}