<?php

namespace app\agent\controller;
use app\common\model\UserWallet;
use app\common\tool\Excel;
use app\common\model\Coupon;

class UserController extends BaseController
{

    protected $title = '$title';
    
    protected $exportField = [];   //需要导出为excel时, 配置
    
    /**
     * 查询配置参数
     * @return array
     */
    protected function search()
    {
        return [
            'user_id' => [ 'name' => '用户id', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'mobile' => [ 'name' => '手机号码', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'nickname' => [ 'name' => '昵称', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
        ];
    }

    protected function assignOption()
    {
        
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        $where['agent_id'] = $this->agent_id;
        if ( isset( $where['mobile'] ) && $where['mobile'] ) {
            $where['dlc_user.mobile'] = [ 'like', '%' . $where['mobile'] . '%' ];
            unset($where['mobile']);
        }
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset($where['nickname']);
        }
        if ( isset( $where['user_id'] ) && $where['user_id'] ) {
            $where['dlc_user.user_id'] = $where['user_id'];
            unset($where['user_id']);
        }
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = UserWallet::join('dlc_user','dlc_user_wallet.user_id=dlc_user.user_id')->field('dlc_user_wallet.*')->where($where)->order('id DESC')->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = UserWallet::join('dlc_user','dlc_user_wallet.user_id=dlc_user.user_id')
	    	->field('dlc_user_wallet.*')
                ->where($where)
                ->order('id DESC')
                ->page($page,$psize)
                ->select();
         }
         
        $count = UserWallet::join('dlc_user','dlc_user_wallet.user_id=dlc_user.user_id')->where($where)->count();

        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
    }

    /**
     * 添加
     * @return array
     */
    public function add()
    {
        $rq = $this->request;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                User::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('user_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = User::get($id);

        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }

        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('user_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = User::get($id);

        if(!$model) {
            return(array('status' => 0,'msg' => '对象不存在'));
        }

        try{
            $model->del();
        } catch (\think\Exception $err) {
            return(array('status' => 0,'msg' => $err->getMessage()));
        }

        return(array('status' => 1,'msg' => '操作成功'));
    }


    /**
     * 赠送桶
     */
    public function giveBucket()
    {
        $id = $this->request->param('id');

        $user = UserWallet::get(['user_id' => $id, 'agent_id' => $this->agent_id]);
        try{
   
            $user->giveBucket(1);
            return(array('status' => 1,'msg' => '赠送成功'));

        }catch (\think\Exception $err) {
            return(array('status' => 0,'msg' => $err->getMessage()));
        }

    }
    /**
     * 赠送优惠卷
     */
    public function giveCoupon()
    {
        $user_id = $this->request->param('user_id');
        $rq = $this->request;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $post['type'] = 3;
                Coupon::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assign('user_id',$user_id);
        if($this->request->param('dialog')) {
            return $this->fetch();
        } else {
            echo $this->fetch();
        }
    }
}