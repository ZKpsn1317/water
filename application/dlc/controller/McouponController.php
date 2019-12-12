<?php
namespace app\dlc\controller;

use app\common\model\Mcoupon;
use app\common\model\Merchant;

class McouponController extends BaseController
{
	protected $title = '$title';
	protected $exportField = [];

	protected function search(){
		return [];
	}

	protected function assignOption ()
    {
        $this->assign( 'type', Mcoupon::$typeOption );
    }


    protected function assignFromOption ()
    {
        $this->assign( 'merchant', Merchant::column( 'id,name' ) );
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
            $list = Mcoupon::where($where)->with( 'merchant' )->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Mcoupon::where($where)->with( 'merchant' )->page($page,$psize)->order('coupon_id DESC')->select();
        }
        $count = Mcoupon::where($where)->count();

        $this->assignOption();
        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch('merchantcoupon_index');
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
                if ($post['type']==0) {
                    unset($post['coupon_merchant']);
                }
                Mcoupon::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'merchantcoupon_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = Mcoupon::get( $id );

        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                if ($post['type']==0) {
                    $post['coupon_merchant']="";
                }
                $model->change( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assign( 'id', $id );
        $this->assign( 'model', $model );
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'merchantcoupon_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Mcoupon::get( $id );

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