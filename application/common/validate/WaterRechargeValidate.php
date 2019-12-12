<?php
namespace app\common\validate;

use think\Validate;

class WaterRechargeValidate extends BaseValidate
{
    protected $rule =   [
        'username|帐号'  => 'require|unique:water_recharge|checkMobile',
		'password|密码'  => 'require|min:6',
		'name|姓名'  => 'require',
    ];

	public function edit()
	{
		$this->rule = [
			'username|帐号'  => 'checkMobile',
			'password|密码'  => 'min:6',
		];
		return $this;
	}

}