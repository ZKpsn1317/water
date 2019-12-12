<?php
namespace app\common\model;

use think\Model;
use app\common\model\Muser;

class MerchantCollect extends Model
{

    protected $table = 'dlc_merchant_collectionLog';
    public static $statusOption = [
        0 => '免费领取',
        1 => '付费领取',
    ];
    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function merchant ()
    {
        return $this->hasOne( 'merchant', 'id', 'merchant_id' );
    }
    public function user()
    {
        return $this->hasOne( 'Muser', 'merchant_user_id', 'uid' );
    }
}