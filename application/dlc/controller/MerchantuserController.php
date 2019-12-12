<?php

namespace app\dlc\controller;

use app\common\model\Muser;
use app\common\tool\Excel;
use app\common\model\MerchantCollect;

class MerchantuserController extends BaseController
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
        ];
    }
    protected function assignOption ()
    {
         $this->assign('sex', Muser::$statusOption);
    }


    /**
     * 列表
     */
    public function index ()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );

        $where = $this->buildSearchWhere( $search );

        $psize = 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = Muser::where( $where )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = Muser::where( $where )->page( $page, $psize )->select();  //
        }


        $count = Muser::where( $where )->count();


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
     * 编辑
     * @return array
     */
    // public function edit ()
    // {
    //     $rq = $this->request;
    //     $id = $rq->param( 'id' );

    //     $model = Muser::get( $id );

    //     if ( $rq->isPost() ) {
    //         try {
    //             $post = $rq->post();
    //             $model->change( $post );
    //         } catch ( \think\Exception $err ) {
    //             return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
    //         }
    //         return ( [ 'status' => 1, 'msg' => '操作成功' ] );
    //     }

    //     $this->assign( 'id', $id );
    //     $this->assign( 'model', $model );
    //     $this->assignOption();
    //     echo $this->fetch( 'user_form' );
    // }


    // /**
    //  * 删除
    //  * @return array
    //  */
    // public function del ()
    // {
    //     $id    = $this->request->param( 'id' );
    //     $model = Muser::get( $id );

    //     if ( !$model ) {
    //         return ( [ 'status' => 0, 'msg' => '对象不存在' ] );
    //     }

    //     try {
    //         $model->del();
    //     } catch ( \think\Exception $err ) {
    //         return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
    //     }

    //     return ( [ 'status' => 1, 'msg' => '操作成功' ] );
    // }
    public function log()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );

        $where = $this->buildSearchWhere( $search );

        $psize = 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = MerchantCollect::where( $where )->with( 'merchant','user' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = MerchantCollect::where( $where )->with( 'merchant','user' )->page( $page, $psize )->select();  //order('ad_id DESC')->
        }
        $count = MerchantCollect::where( $where )->count();
        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', $this->exportField );
        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );
        $this->assign('type',MerchantCollect::$statusOption);
        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );

        echo $this->fetch('merchant_collectlog');
    }
}