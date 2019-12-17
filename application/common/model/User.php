<?php
namespace app\common\model;

use app\common\tool\ProcessData;
use think\Model;
use app\common\validate\UserValidate;

//用户类
class User extends Model
{

	public function addFreedbackNumber()
	{
		$this->fback_num++;
		$this->save();
	}


	public function addOrderNumber()
	{
		$this->order_num++;
		$this->save();
	}

	public function addCoupenNumber()
	{
		$this->coupen_num++;
		$this->save();
	}

	public function subCoupenNumber()
	{
		$this->coupen_num--;
		$this->save();
	}

	
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

//		$data['password'] = static::createPassword($data['password']);
		$data['ctime'] = time();
		$data['token'] = static::createToken($data['password']?:mt_rand(100000, 999999));
		$user = new static();

		$rst = $user->allowField('head_img,status,nickname,mobile,ctime,token,openid,type')->save($data);

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
	 * 验证token
	 * @param $token
	 */
	public static function verifyToken($token) {
		return static::get(['token' => $token]);
	}


	/**
	 * 验证帐号密码
	 * @param $mobile 手机号码
	 * @param $password	 密码
	 */
	public static function verifyAccount($mobile, $password) {

		return static::get([
			'mobile' => $mobile,
			'password'=> static::createPassword($password),
		]);

	}

	/**
	 * 调整用户状态
	 */
	public function changeStatus() {
		$this->status = $this->status == 1 ? 2 : 1;
		$this->save();
	}


	/**
	 * 修改用户密码
	 * @param $password
	 * @return bool
	 * @throws \ErrorException
	 */
	public function changePassword($password) {
		if(strlen($password) < 6) {
			throw new \ErrorException('密码不能少于6位!');
		}

		$this->password = $this->createPassword($password);

		if($this->save() === false) {

			throw new \ErrorException($this->getError());
		}

		return true;
	}


	/**
	 * 修改数据
	 * @param $data
	 */
	public function modify($data) {

		$validate = new UserValidate();
		if(!$validate->scene('edit')->check($data)) {
			throw new \ErrorException($validate->getError());
		}

		ProcessData::$rule = [
			'head_img' => 'saveImage|cut:150,150',
		];
		ProcessData::cooking($data);

		$this->head_img = $data['head_img'] ?: $this->head_img;
		$this->nickname = $data['nickname'] ?: $this->nickname;

		return $this->save();
	}


	/**
	 * 修改手机号码
	 * @param $mobile
	 */
	public function changeMobile($mobile) {

		$this->mobile = $mobile;
		if($this->save() === false) {
			throw new \ErrorException($this->getError());
		}
		return true;
	}
	public function GiveCoupon($fuid)
	{
		
	}

	public function getCtimeAttr($value)
	{
		return $value ? date('Y-m-d H:i:s', $value) : '';
	}


	public function referee()
	{
		return $this->hasOne('user', 'user_id', 'referee_id');
	}


	public function order()
	{
		return $this->hasMany('order', 'user_id', 'user_id');
	}

	public function userType()
	{
		return $this->hasOne('user_type', 'user_type_id', 'type');
	}


	public function giveBucket()
	{
		if($this->give_bucket_num) {
			return;
		}

		$this->give_bucket_num++;
		$this->bucket_num++;
		$this->save();
	}
	public function del()
    {
        $this->delete();
    }
	

}