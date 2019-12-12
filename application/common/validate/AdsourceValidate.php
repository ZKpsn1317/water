<?php
namespace app\common\validate;

use think\Validate;

class AdsourceValidate extends Validate
{
    protected $rule =   [

        'ad_type|类型'  => 'require',
		'ad_url|广告'  => 'require',
		//'device_id|设备'  => 'require',
		'start_time|开始时间'  => 'require',
		'end_time|结束时间'  => 'require',


    ];

}