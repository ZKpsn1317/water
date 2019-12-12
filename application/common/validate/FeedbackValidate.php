<?php
namespace app\common\validate;



class FeedbackValidate extends BaseValidate
{
    protected $rule =   [
        'user_id|用户' => 'require',
        'content|内容' => 'require|max:1024',
        /*'phone|手机号码' => 'checkMobile',*/
    ];


}