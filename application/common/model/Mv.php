<?php

namespace app\common\model;

use think\Model;
use app\common\validate\AdValidate;

class Mv extends Model
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

        $model              = new static();
        $data['ctime']      = time();
        $data['start_time'] = strtotime( $data['start_time'] );
        $data['end_time']   = strtotime( $data['end_time'] );

        if ( !$model->save( $data ) ) {
            throw new \think\Exception( $model->getError() );
        }

        return $model;
    }


    public function change ( $data )
    {

        $data['start_time'] = strtotime( $data['start_time'] );
        $data['end_time']   = strtotime( $data['end_time'] );

        if ( $this->save( $data ) === FALSE ) {
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

    public function act(){
        return $this->hasOne("act","id","act_id");
    }

    public function agent(){
        return $this->hasOne("agent","agent_id","agent_id");
    }



}
