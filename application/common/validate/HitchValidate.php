<?php
namespace app\common\validate;

use think\Validate;

class HitchValidate extends Validate
{
    protected $rule =   [
		'type|问题类型'  => 'require',
		'device_id|设备'  => 'require',
		'content|内容'  => 'require',
		'macno|设备'   => 'require', 
    ];
     protected $scene = [
        'orign'  =>  ['type','device_id','content'],
        'new'  => ['type','manco','content'],
    ];
}