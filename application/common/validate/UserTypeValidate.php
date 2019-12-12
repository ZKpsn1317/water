<?php
namespace app\common\validate;

use think\Validate;

class UserTypeValidate extends Validate
{
    protected $rule =   [

        'user_type_name|名称'  => 'require',
		'pressure_gold|押金'  => 'require',
		'bucket_num|桶数'  => 'require|number|>:0',
		'img|图标'  => 'require',
		'sort|排序'  => 'number',
    ];

}