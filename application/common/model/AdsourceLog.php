<?php
namespace app\common\model;

use think\Model;
use app\common\model\Muser;

class AdsourceLog extends Model
{

    protected $table = 'dlc_merchant_log';

    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function merchantad ()
    {
        return $this->hasOne( 'adsource', 'adsource_id', 'merchant_ad_id' );
    }
    public function user()
    {
        return $this->hasOne( 'Muser', 'merchant_user_id', 'uid' );
    }
}