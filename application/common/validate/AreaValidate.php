<?php
namespace app\common\validate;

use think\Validate;

class AreaValidate extends Validate
{
    protected $rule =   [
        'area_name|名称'  => 'require',
		'area_address|地址'  => 'require',
		'agent_id|代理'  => 'require',
		'username|帐号'  => 'require|unique:area',
		'password|密码'  => 'require|min:6',
    ];


    public function edit()
    {
        $this->rule = [
            'area_name|名称'  => 'require',
            'area_address|地址'  => 'require',
            'agent_id|代理'  => 'require',
            'username|帐号'  => 'require',
            'password|密码'  => 'min:6',
        ];
        return $this;
    }

    protected $message  =   [
        'password.requireWith'   => '请输入密码',
        'username.requireWith' => '请输入帐号',
    ];

}