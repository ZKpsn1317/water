<?php
namespace app\common\validate;

use think\Validate;

class RepairmanValidate extends BaseValidate
{
    protected $rule =   [
        'username|帐号'  => 'require',
        'mobile'  => 'require|checkMobile',
		'password|密码'  => 'require|min:6',
		'nickname|昵称'  => 'require',
    ];

	public function edit()
	{
		$this->rule = [
			'username|帐号'  => 'require',
			'password|密码'  => 'min:6',
		];
		return $this;
	}

}