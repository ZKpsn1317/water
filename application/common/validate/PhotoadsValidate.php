<?php
namespace app\common\validate;

use think\Validate;

class PhotoadsValidate extends Validate
{
    protected $rule =   [
        'photoads_title|标题'  => 'require',
        'photoads_stitle|副标题'  => 'require',
        'photoads_desc|内容'  => 'require',
        'photoads_status|状态'  => 'require',
        'photoads_sort|排序'  => 'require',
        'photoads_img|图片'  => 'require',
    ];

}