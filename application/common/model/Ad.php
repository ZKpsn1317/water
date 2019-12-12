<?php

namespace app\common\model;

use think\Model;
use app\common\validate\AdValidate;

class Ad extends Model
{
    public static $typeOption = [
        1 => '图片',
        2 => '视频'
    ];

    public static $statusOption = [
        1 => '投放中',
        2 => '未投放'
    ];

    public static function add ( $data )
    {
        $validate = new AdValidate();
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
        $validate = new AdValidate();
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

    public function device ()
    {
        return $this->hasOne( 'device', 'device_id', 'device_id' );
    }

    public function agent ()
    {
        return $this->hasOne( 'agent', 'agent_id', 'agent_id' );
    }

    public function getAreaIdAttr ( $value )
    {
        $list = Area::where( [ 'area_id' => [ 'in', $value ] ] )->column( 'area_name' );
        return implode( ',', $list );
    }

    public function getDeviceIdAttr ( $value )
    {
        $res = '';
        if ( $value ) {
            $res = Device::where( [ 'device_id' => [ 'in', $value ] ] )->column( 'device_name' );
            $res = $res ? implode( ',', $res ) : '';
        }
        return $res;
    }


}
