<?php
namespace app\common\model;

use think\Model;

class Muser extends Model
{

	
    protected $table = 'dlc_merchant_user';

    public static $statusOption = [
        1 => '男',
        2 => '女',
    ];
    /**
     *
     * @param $data
     * @return static
     * @throws \ErrorException
     */
    public static function add($data)
    {
        $validate = new UserValidate();
        if(!$validate->check($data)) {
            throw new \ErrorException($validate->getError());
        }

//      $data['password'] = static::createPassword($data['password']);
        $data['ctime'] = time();
        $data['token'] = static::createToken($data['password']?:mt_rand(100000, 999999));
        $user = new static();

        $rst = $user->allowField('head_img,nickname,mobile,ctime,token,openid')->save($data);

        if(!$rst) {
            throw new \ErrorException($user->getError());
        }

        return $user;
    }


    /**
     * 生成token
     * @param $other 额外的数据, 增加令复杂度
     */
    public static function createToken($other = '')
    {
        $token =  md5(microtime() . mt_rand(100000, 999999));
        return $token . ($other ? md5($other) : md5(mt_rand(100000, 999999)));
    }


    /**
     * 生成加密码 的 密码
     * @param $password
     */
    public static function createPassword($password)
    {
        return md5($password);
    }

    /**
     * 修改性别
     * @param $mobile
     */
    public function changeSex($sex) {

        $this->sex = $sex;
        $this->utime = time();
        if($this->save() === false) {
            throw new \ErrorException($this->getError());
        }
        return true;
    }
    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    public function getUtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

}