<?php

namespace app\dlc\controller;

use app\common\model\User;
use app\common\tool\Excel;

class UserController extends BaseController
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
            'mobile' => [ 'name' => '手机号码', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
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
        if ( isset( $where['mobile'] ) && $where['mobile'] ) {
            $where['mobile'] = [ 'like', '%' . $where['mobile'] . '%' ];
        }

        $psize = 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = User::where( $where )->order( 'user_id DESC' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = User::where( $where )->order( 'user_id DESC' )->page( $page, $psize )->select();  //
        }


        $count = User::where( $where )->count();


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', !empty( $this->exportField ) );
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
                User::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        echo $this->fetch( 'user_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = User::get( $id );

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
        echo $this->fetch( 'user_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = User::get( $id );

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


    /**
     * 赠送桶
     */
    public function giveBucket ()
    {
        $id = $this->request->param( 'id' );

        $user = User::get( [ 'user_id' => $id ] );
        try {

            $user->giveBucket();
            return ( [ 'status' => 1, 'msg' => '赠送成功' ] );

        } catch ( \think\Exception $err ) {
            return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
        }

    }
}