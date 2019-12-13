<?php

namespace app\agent\controller;
use app\common\model\UserWalletLog;
use app\common\tool\Excel;

class UserWalletLogController extends BaseController
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
            'nickname' => ['name' => '用户名称', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'mobile'  => ['name' => '手机号', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'user_id'  => ['name' => '用户id', 'value' => '', 'type' => 'text', 'searchType' => '='],

        ];
    }

    protected function assignOption()
    {
        $this->assign('type', UserWalletLog::$typeOption);
        $this->assign('direction', UserWalletLog::$directionOption);

    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        //用户名查询
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset( $where['nickname'] );
        }
        //手机号查询
        if ( isset( $where['mobile'] ) && $where['mobile'] ) {
            $where['dlc_user.mobile'] = ['like','%' . $where['mobile'] . '%'];
            unset( $where['mobile'] );
        }
        if ( isset( $where['user_id'] ) && $where['user_id'] ) {
            $where['dlc_user.user_id'] = $where['user_id'];
            unset($where['user_id']);
        }
        //id查询
        if ( isset( $where['id'] ) && $where['id'] ) {
            $where['dlc_user_wallet_log.id'] = ['like','%' . $where['id'] . '%'];
            unset( $where['id'] );
        }
        $where['dlc_user_wallet_log.agent_id'] = $this->agent_id;
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = UserWalletLog::field( 'dlc_user_wallet_log.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_user_wallet_log.user_id=dlc_user.user_id' )->where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = UserWalletLog::field( 'dlc_user_wallet_log.*,dlc_user.nickname,dlc_user_wallet.wallet' )->join( 'dlc_user', 'dlc_user_wallet_log.user_id=dlc_user.user_id' )->join('dlc_user_wallet','dlc_user_wallet_log.user_id=dlc_user_wallet.user_id')->where($where)->order('id DESC')->page($page,$psize)->select();  
            // var_dump($list);die;
         }
         
        
        $count =  UserWalletLog::field( 'dlc_user_wallet_log.*,dlc_user.nickname,dlc_user_wallet.wallet' )->join( 'dlc_user', 'dlc_user_wallet_log.user_id=dlc_user.user_id' )->join('dlc_user_wallet','dlc_user_wallet_log.user_id=dlc_user_wallet.user_id')->where($where)->count();
        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        if($this->request->param('dialog')) {
            return $this->fetch();
        } else {
            echo $this->fetch();
        }
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
                UserWalletLog::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('user_wallet_log_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = UserWalletLog::get($id);

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
        echo $this->fetch('user_wallet_log_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = UserWalletLog::get($id);

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
}