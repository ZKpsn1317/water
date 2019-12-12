<?php
namespace app\common\validate;

use think\Validate;

class AgentValidate extends Validate
{
    protected $rule =   [
        'agent_name|代理名称'  => 'require',
		'region_id|区域'  => 'require',
		'address|地址'  => 'require',
		'username|帐号'  => 'require',
		'password|密码'  => 'require|min:6',
    ];

	public function edit()
	{
		$this->rule = [
			'agent_name|代理名称'  => 'require',
			'region_id|区域'  => 'require',
			'address|地址'  => 'require',
			'username|帐号'  => 'require',
			'password|密码'  => 'min:6',
		];
		return $this;
	}

}