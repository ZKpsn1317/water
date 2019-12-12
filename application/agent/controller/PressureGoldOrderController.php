<?php

namespace app\agent\controller;

use app\common\model\Area;
use app\common\model\PressureGoldOrder;
use app\common\tool\Excel;

class PressureGoldOrderController extends BaseController
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
            'nickname' => [ 'name' => '用户昵称', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'area_id'                => [ 'name' => '场地', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], Area::column( 'area_id,area_name' ) ) ],
            'status'                 => [ 'name' => '状态', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], PressureGoldOrder::$statusOption ) ]
        ];
    }

    protected function assignOption ()
    {
        $this->assign( 'status', PressureGoldOrder::$statusOption );
    }


    /**
     * 列表
     */
    public function index ()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );

        $where             = $this->buildSearchWhere( $search );
        $where['agent_id'] = $this->agent_id;
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset( $where['nickname'] );
        }
        if ( isset( $where['status'] ) && $where['status'] ) {
            $where['dlc_pressure_gold_order.status'] = $where['status'];
            unset( $where['status'] );
        }
        if ( isset( $where['area_id'] ) && $where['area_id'] ) {
            $where['dlc_pressure_gold_order.area_id'] = $where['area_id'];
            unset( $where['area_id'] );
        }

        $psize = input( 'psize' ) ? input( 'psize' ) : 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = PressureGoldOrder::join( 'dlc_user', 'dlc_pressure_gold_order.user_id=dlc_user.user_id' )->where( $where )->order( 'pressure_gold_order_id DESC' )->with( 'user' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = PressureGoldOrder::join( 'dlc_user', 'dlc_pressure_gold_order.user_id=dlc_user.user_id' )->where( $where )->order( 'pressure_gold_order_id DESC' )->with( 'user' )->page( $page, $psize )->select();  //order('pressure_gold_order_id DESC')->
        }

        $count = PressureGoldOrder::join( 'dlc_user', 'dlc_pressure_gold_order.user_id=dlc_user.user_id' )->where( $where )->count();


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', $this->exportField );
        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );
        if ( $this->request->param( 'dialog' ) ) {
            return $this->fetch();
        } else {
            echo $this->fetch();
        }
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
                PressureGoldOrder::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        echo $this->fetch( 'pressure_gold_order_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = PressureGoldOrder::get( $id );

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
        echo $this->fetch( 'pressure_gold_order_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = PressureGoldOrder::get( $id );

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