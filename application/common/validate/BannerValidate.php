<?php
namespace app\common\validate;

use think\Validate;

class BannerValidate extends Validate
{
    protected $rule =   [

        'title|标题'  => 'require',
		/*'image'  => '',
		'type'  => '',
		'data'  => '',
		'sort'  => '',
		'ctime'  => '',
		'imagelj'  => '',*/
		

    ];

}