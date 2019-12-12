<?php

namespace app\dlc\controller;

use app\common\model\RechargeOrder;
use app\common\tool\Excel;

class RechargeOrderController extends BaseController
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
            'status'   => [ 'name' => '状态', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], RechargeOrder::$statusOption ) ],
        ];
    }

    protected function assignOption ()
    {
        $this->assign( 'status', RechargeOrder::$statusOption );
    }


    /**
     * 列表
     */
    public function index ()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );

        $where = $this->buildSearchWhere( $search );
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset( $where['nickname'] );
        }
        if ( isset( $where['status'] ) && $where['status'] ) {
            $where['dlc_recharge_order.status'] = $where['status'];
            unset( $where['status'] );
        }

        $psize = input( 'psize' ) ? input( 'psize' ) : 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = RechargeOrder::field( 'dlc_recharge_order.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_recharge_order.user_id=dlc_user.user_id' )->where( $where )->order( 'recharge_order_id DESC' )->with( 'user' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = RechargeOrder::field( 'dlc_recharge_order.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_recharge_order.user_id=dlc_user.user_id' )->where( $where )->order( 'recharge_order_id DESC' )->with( 'user' )->page( $page, $psize )->select();  //order('recharge_order_id DESC')->
        }

        $count = RechargeOrder::field( 'dlc_recharge_order.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_recharge_order.user_id=dlc_user.user_id' )->where( $where )->count();


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', !empty( $this->exportField ) );
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
                RechargeOrder::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        echo $this->fetch( 'recharge_order_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = RechargeOrder::get( $id );

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
        echo $this->fetch( 'recharge_order_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = RechargeOrder::get( $id );

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