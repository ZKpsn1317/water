<?php
namespace app\common\model;

use think\Model;

class Morder extends Model
{
	protected $table = 'dlc_merchant_order';
	public static $typeOption = [
        1 => '饮用水',
        2 => '优惠券',
    ];

    public static $statusOption = [
    	1 => '未支付',
    	2 => '支付完成',
    	3 => '订单完成',
    ];

    public static function add ( $data )
    {
        $model              = new static();
        $data['ctime']      = time();
        $data['pay_time'] = strtotime( $data['pay_time'] );

        if ( !$model->allowField( TRUE )->save( $data ) ) {
            throw new \think\Exception( $model->getError() );
        }

        return $model;
    }

    public static function change( $data ){
        
        $data['pay_time'] = strtotime( $data['pay_time'] );
        if ( !$this->allowField( TRUE )->save( $data ) ) {
            throw new \think\Exception( $model->getError() );
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

    public function getPayTimeAttr ( $value )
    {
        return $value ? date( 'Y-m-d H:i:s', $value ) : '';
    }

    public function muser ()
    {
        return $this->hasOne( 'muser', 'merchant_user_id', 'user_id' );
    }
}