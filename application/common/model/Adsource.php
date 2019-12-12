<?php

namespace app\common\model;

use think\Model;
use app\common\validate\AdsourceValidate;

class Adsource extends Model
{
    protected $table = 'dlc_merchant_ad';
    public static $typeOption = [
        1 => '视频广告',
        2 => '商家活动页广告'
    ];

    public static $statusOption = [
        1 => '投放中',
        2 => '未投放'
    ];

    public static function add ( $data )
    {
        $validate = new AdsourceValidate();
        if ( !$validate->check( $data ) ) {
            throw new \think\Exception( $validate->getError() );
        }

        $model              = new static();
        $data['ctime']      = time();
        $data['start_time'] = strtotime( $data['start_time'] );
        $data['end_time']   = strtotime( $data['end_time'] );

        if ( !$model->allowField( TRUE )->save( $data ) ) {
            throw new \think\Exception( $model->getError() );
        }

        return $model;
    }


    public function change ( $data )
    {
        $validate = new AdsourceValidate();
        if ( !$validate->check( $data ) ) {
            throw new \think\Exception( $validate->getError() );
        }

        $data['start_time'] = strtotime( $data['start_time'] );
        $data['end_time']   = strtotime( $data['end_time'] );

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

    public function getStartTimeAttr ( $value )
    {
        return $value ? date( 'Y-m-d', $value ) : '';
    }

    public function getEndTimeAttr ( $value )
    {
        return $value ? date( 'Y-m-d', $value ) : '';
    }

    public function merchant ()
    {
        return $this->hasOne( 'merchant', 'id', 'ad_merchant' );
    }
}
