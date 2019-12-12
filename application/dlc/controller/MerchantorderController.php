<?php
namespace app\dlc\controller;

use app\common\tool\Excel;
use app\common\model\Morder;
use app\common\model\Muser;

class MerchantorderController extends BaseController
{
	protected $title = '$title';
	protected $exportField = [];

	protected function search(){
		return [];
	}

	protected function assignOption ()
    {
        $this->assign( 'type', Morder::$typeOption );
        $this->assign('status',Morder::$statusOption);
    }


    protected function assignFromOption ()
    {
        $this->assign( 'muser', Muser::column( 'merchant_user_id,nickname' ) );
    }


	/**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        
        $this->loadSearchValue($search);
        $where = $this->buildSearchWhere($search);
        $psize = 10;
        $page = input('page')?input('page'):1;
        if(input('export')) {
            $list = Morder::where($where)->with( 'muser' )->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Morder::where($where)->with( 'muser' )->page($page,$psize)->order('order_id DESC')->select();
        }
        $count = Morder::where($where)->count();

        $this->assignOption();
        $this->assignFromOption();
        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch('merchantorder_index');
    }


    /**
     * 添加
     * @return array
     */
    public function add ()
    {
        $rq   = $this->request;
        if ( $rq->isPost() ) {
            try {
                $post  = $rq->post();
                Morder::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'merchantcoupon_form' );
    }


    // /**
    //  * 编辑
    //  * @return array
    //  */
    // public function edit ()
    // {
    //     $rq = $this->request;
    //     $id = $rq->param( 'id' );

    //     $model = Morder::get( $id );

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
    //     $this->assignFromOption();
    //     echo $this->fetch( 'merchantorder_form' );
    // }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Morder::get( $id );

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