<?php

namespace app\agent\controller;

use app\common\model\WaterRecharge;
use app\common\model\WaterRechargeLog;
use app\common\tool\Excel;

class WaterRechargeLogController extends BaseController
{

    protected $title = '$title';

    protected $exportField = [];   //需要导出为excel时, 配置

    /**
     * 查询配置参数
     * @return array
     */
    protected function search ()
    {
        return [
            'device_id' => [ 'name' => '设备ID', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'name'      => [ 'name' => '补水员', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'ctime'     => [ 'name' => '开始时间', 'value' => '', 'type' => 'date', 'searchType' => '>=', 'style' => 'max-width:300px;' ],
            'ctime2'    => [ 'key' => 'ctime', 'name' => '结束时间', 'value' => '', 'type' => 'date', 'searchType' => '<=', 'style' => 'max-width:300px;' ],
        ];
    }

    protected function assignOption ()
    {

    }


    /**
     * 列表
     */
    public function index ()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );

        $where = $this->buildSearchWhere( $search );

        //时间筛选
        if ( !empty( $search['ctime']['value'] ) && !empty( $search['ctime2']['value'] ) ) {
            $where['dlc_water_recharge_log.ctime'] = [ 'between time', [ $search['ctime']['value'], $search['ctime2']['value'] . ' 23:59:59' ] ];
        } else if ( !empty( $search['ctime']['value'] ) ) {
            $where['dlc_water_recharge_log.ctime'] = [ '>= time', $search['ctime']['value'] ];
        } else if ( !empty( $search['ctime2']['value'] ) ) {
            $where['dlc_water_recharge_log.ctime'] = [ '<= time', $search['ctime2']['value'] . ' 23:59:59' ];
        }

        $where['dlc_water_recharge_log.status'] = 1;

        //补水员筛选
        if ( isset( $where['name'] ) && $where['name'] ) {
            $where['dlc_water_recharge.name'] = [ 'like', '%' . $where['name'] . '%' ];
            unset( $where['name'] );
        }

        $wrid  = WaterRecharge::where( [ 'agent_id' => $this->agent_id ] )->column( 'water_recharge_id' );
        $psize = 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( $wrid ) {
            $where['dlc_water_recharge_log.water_recharge_id'] = [ 'IN', $wrid ];
            if ( input( 'export' ) ) {
                $list = WaterRechargeLog::where( $where )->with( 'device,water_brand' )->select();
                Excel::export( $list, $this->exportField );
            } else {
                $list = WaterRechargeLog::where( $where )->field('water_recharge.name,dlc_water_recharge_log.*')->with( 'device,water_brand' )->join( 'water_recharge', 'dlc_water_recharge.water_recharge_id = dlc_water_recharge_log.water_recharge_id', 'left' )->page( $page, $psize )->order('water_recharge_log_id DESC')->select();
            }
            $count = WaterRechargeLog::where( $where )->join( 'water_recharge', 'dlc_water_recharge.water_recharge_id = dlc_water_recharge_log.water_recharge_id', 'left' )->count();

            $number = WaterRechargeLog::field('sum(dlc_water_recharge_log.number) as number,sum(dlc_water_recharge_log.return_number) as return_number')->where( $where )->join( 'water_recharge', 'dlc_water_recharge.water_recharge_id = dlc_water_recharge_log.water_recharge_id', 'left' )->find();
        } else {
            $list  = [];
            $count = 0;
            $number = [];
        }


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assign( 'number', $number );
        $this->assignOption();
        $this->assign( 'hasExport', $this->exportField );
        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );
        echo $this->fetch();
    }

    /**
     * 添加
     * @return array
     */
    public function add ()
    {
        $rq = $this->request;
        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                WaterRechargeLog::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        echo $this->fetch( 'water_recharge_log_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = WaterRechargeLog::get( $id );

        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $model->change( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }

        $this->assign( 'id', $id );
        $this->assign( 'model', $model );
        $this->assignOption();
        echo $this->fetch( 'water_recharge_log_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = WaterRechargeLog::get( $id );

        if ( !$model ) {
            return ( [ 'status' => 0, 'msg' => '对象不存在' ] );
        }

        try {
            $model->del();
        } catch ( \think\Exception $err ) {
            return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
        }

        return ( [ 'status' => 1, 'msg' => '操作成功' ] );
    }
}