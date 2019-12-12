<?php
namespace app\common\model;

use app\common\tool\ProcessData;
use think\Model;
use app\common\validate\VisitorValidate;

//用户类
class Visitor extends Model
{

	
	/**
	 *
	 * @param $data
	 * @return static
	 * @throws \ErrorException
	 */
	public static function add($data)
	{
		$validate = new VisitorValidate();
		if(!$validate->check($data)) {
			throw new \ErrorException($validate->getError());
		}

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
	 * 验证token
	 * @param $token
	 */
	public static function verifyToken($token) {
		return static::get(['token' => $token]);
	}



	/**
	 * 修改数据
	 * @param $data
	 */
	public function modify($data) {

		$validate = new VisitorValidate();
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


	public function getCtimeAttr($value)
	{
		return $value ? date('Y-m-d H:i:s', $value) : '';
	}


}