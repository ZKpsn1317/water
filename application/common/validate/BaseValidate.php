<?php
namespace app\common\validate;

use think\Validate;
use app\common\tool\Upload;
use think\Db;

class BaseValidate extends Validate
{
    public static function checkMobile($phone) {
        if(preg_match("/^1\d{10}$/", $phone)){
            return true;
        }
        return '请输入正确的手机号码';
    }

    // 自定义验证规则
    public function exist($value, $rule, $data, $field, $title)
    {
        $rule = explode(',', $rule);
        return Db::name($rule[0])->where($rule[1], $value)->find() ? true : $title.'不存在';
    }


}


