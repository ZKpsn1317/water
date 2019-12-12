<?php
namespace app\common\validate;

use think\Validate;

class DeviceValidate extends Validate
{
    protected $rule =   [
        'water_brand_id|水类型'  => 'require',
		'macno|设备编号'  => 'require|unique:device',
    ];
    

}