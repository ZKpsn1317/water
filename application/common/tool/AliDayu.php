<?php
namespace app\common\tool;

class AliDayu
{
    protected static $sendDevice;

    public static function send($mobile, $tmp, $option)
    {
        $config = [
            'access_key' => 'LTAIq248Z6yIUdxu',
            'access_secret' => 'NyZuRVXDSujPuFvQzA47TkGP45Bwaq',
            'sign_name' => '网邻云柜',
        ];

        if(!static::$sendDevice) {
            $sendDevice = static::$sendDevice  = new \Mrgoon\AliSms\AliSms();
        } else {
            $sendDevice = static::$sendDevice;
        }

        $response = $sendDevice->sendSms($mobile, $tmp, $option, $config);
        if(isset($response->Code) && $response->Code == 'isv.BUSINESS_LIMIT_CONTROL') {
            throw new \Exception('发送过于频繁请稍候再试');
        }
        return isset($response->Code) && $response->Code == 'OK';
    }

}