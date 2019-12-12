<?php
namespace app\common\model;

use think\Model;

class Mcoupon extends Model
{
	protected $table = 'dlc_merchant_coupon';
	public static $typeOption = [
        0 => '平台持有',
        1 => '商户持有',
    ];

    public static function add ( $data )
    {
        $model              = new static();
        $data['ctime']      = time();
        $data['due_time'] = strtotime( $data['due_time'] );

        if ( !$model->allowField( TRUE )->save( $data ) ) {
            throw new \think\Exception( $model->getError() );
        }

        return $model;
    }


    public function change ( $data )
    {

        $data['due_time'] = strtotime( $data['due_time'] );

        if ( $this->allowField( TRUE )->save( $data ) === FALSE ) {
            throw new \think\Exception( $this->getError() );
        }
    }

    public function del ()
    {
        $this->delete();
    }

    public function getCtimeAttr ( $value )
    {
        return $value ? date( 'Y-m-d H:i:s', $value ) : '';
    }

    public function getDueTimeAttr ( $value )
    {
        return $value ? date( 'Y-m-d H:i:s', $value ) : '';
    }

    public function merchant ()
    {
        return $this->hasOne( 'merchant', 'id', 'coupon_merchant' );
    }
}