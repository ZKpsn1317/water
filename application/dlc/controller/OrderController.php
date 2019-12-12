<?php

namespace app\dlc\controller;

use app\common\model\Order;
use app\common\tool\Excel;

class OrderController extends BaseController
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
            'macno'    => [ 'name' => '设备编号', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'address'  => [ 'name' => '地址', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'status'   => [ 'name' => '订单状态', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], Order::$statusOption ) ],

        ];
    }

    protected function assignOption ()
    {
        $this->assign( 'status', Order::$statusOption );
        $this->assign( 'shippingStatus', Order::$shippingStatusOption );
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

        if ( isset( $where['macno'] ) && $where['macno'] ) {
            $where['dlc_order.macno'] = [ 'like', '%' . $where['macno'] . '%' ];
            unset( $where['macno'] );
        }
        if ( isset( $where['address'] ) && $where['address'] ) {
            $where['dlc_order.address'] = [ 'like', '%' . $where['address'] . '%' ];
            unset( $where['address'] );
        }
        if ( isset( $where['status'] ) && $where['status'] ) {
            $where['dlc_order.status'] = $where['status'];
            unset( $where['status'] );
        }

        $psize = input( 'psize' ) ? input( 'psize' ) : 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = Order::field( 'dlc_order.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_order.user_id=dlc_user.user_id' )->where( $where )->with( 'user,device,agent,area,order_info' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = Order::field( 'dlc_order.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_order.user_id=dlc_user.user_id' )->where( $where )->with( 'user,device,agent,area,order_info' )->order( 'order_id DESC' )->page( $page, $psize )->select();  //
        }

        $count = Order::field( 'dlc_order.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_order.user_id=dlc_user.user_id' )->where( $where )->count();


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
                Order::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        echo $this->fetch( 'order_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = Order::get( $id );

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
        echo $this->fetch( 'order_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Order::get( $id );

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