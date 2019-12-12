<?php
namespace app\common\validate;

use think\Validate;

class BaseValidate extends Validate
{
    public static function checkMobile($phone) {
        if(preg_match("/^1\d{10}$/", $phone)){
            return true;
        }
        return '请输入正确的手机号码';
    }
}