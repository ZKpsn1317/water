<?php

namespace app\common\tool;

class Log
{
    public static function  write($pay_type,$content){
        if(is_object($content) || is_array($content)) {
            $content = json_encode($content);
        }

        $filename =ROOT_PATH . DS . 'dingding_log' . DS .$pay_type.date('Y-m-d').'.txt';
        $Ts=fopen($filename,"a+");

        fputs($Ts,"执行日期："."\r\n".date('Y-m-d H:i:s',time()).  ' ' . "\n" .$content."\n");
        fclose($Ts);
    }
}

