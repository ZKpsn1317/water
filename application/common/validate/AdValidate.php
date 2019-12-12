<?php
namespace app\common\validate;

use think\Validate;

class AdValidate extends Validate
{
    protected $rule =   [

        'type|类型'  => 'require',
		'url|广告'  => 'require',
		//'device_id|设备'  => 'require',
		'start_time|开始时间'  => 'require',
		'end_time|结束时间'  => 'require',


    ];

}