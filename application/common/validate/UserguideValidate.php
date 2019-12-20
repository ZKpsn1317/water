<?php
namespace app\common\validate;

use think\Validate;

class UserguideValidate extends Validate
{
    protected $rule =   [

        'guide_title|标题'  => 'require',
		'guide_desc|内容'  => 'require',
    ];

}