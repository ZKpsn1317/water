<?php

namespace app\agent\controller;
use app\common\model\Ic;
use app\common\tool\Excel;
use app\common\model\Agent;
use app\common\model\User;
use app\common\model\UserWallet;

class IcController extends BaseController
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
            'user_id' => ['name' => '用户id', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'ic_id'  => ['name' => 'id卡号', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'mobile'  => ['name' => '手机号', 'value' => '', 'type' => 'text', 'searchType' => '='],
        ];
    }

    protected function assignOption()
    {
        $this->assign('agent_id',$this->agent_id);
    }
    //在表单中使用的列表选项
    protected function assignFormOption()
    {
        
    }

    /**
     * author  zk
     * time 2019/09/07
     * used by submit
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);
        
        $where = $this->buildSearchWhere($search);
        if ( isset( $where['ic_id'] ) && $where['ic_id'] ) {
            $where['dlc_ic.car_number'] = ['like','%' . $where['ic_id'] . '%'];
            unset( $where['ic_id'] );
        }
         //用户id
        if ( isset( $where['user_id'] ) && $where['user_id'] ) {
            $where['dlc_user.user_id'] =$where['user_id'];
            unset($where['user_id']);
        } 

        //手机号查询
        if ( isset( $where['mobile'] ) && $where['mobile'] ) {
            $where['dlc_user.mobile'] = ['like','%' . $where['mobile'] . '%'];
            unset( $where['mobile'] );
        }
        $where['dlc_user_wallet.agent_id'] = $this->agent_id;

        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Ic::join('dlc_user_wallet','dlc_ic.user_id=dlc_user_wallet.user_id')->where($where)->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Ic::join('dlc_user_wallet','dlc_ic.user_id=dlc_user_wallet.user_id')->join('dlc_user','dlc_user.user_id=dlc_ic.user_id')->field('dlc_user_wallet.*,dlc_ic.*,dlc_user.mobile')->where($where)->page($page,$psize)->order('ic_id DESC')->select(); 
        }
    
        $count = Ic::join('dlc_user_wallet','dlc_ic.user_id=dlc_user_wallet.user_id')->join('dlc_user','dlc_user.user_id=dlc_ic.user_id')->field('dlc_user_wallet.*,dlc_ic.*,dlc_user.mobile')->where($where)->count();

        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', !empty($this->exportField));
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
                //同步注册一个用户
                $user = User::get(['mobile' => $post['mobile']]);
                if(!$user){
                    $data = [
                        'mobile' => $post['mobile'],
                        'nickname' => $post['car_number'],
                        'passord'  => $post['car_number'],
                    ];
                    $user = User::add($data);
                    $post['user_id'] = $user['user_id'];
                    //同步生成用户钱包记录
                    $wallet_user = UserWallet::get( [ 'agent_id' => $post['agent_id'], 'openid' => $post['car_number'] ] );
                    if ( !$wallet_user ) {
                        //新用户添加到数据库
                        $wallet_data = [
                            'give_bucket_num' => $post['num'],
                            'wallet' =>  $post['wallet'],
                            'user_id'  => $user['user_id'],
                            'agent_id' => $post['agent_id'],
                            'openid'   => $post['car_number'],
                            'user_type_id' => 13,
                        ];
                        ( new UserWallet() )->save( $wallet_data );
                    }
                }else{
                    $post['user_id'] = $user['user_id'];
                }
                Ic::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch('ic_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');

        $model = Ic::get($id);
        
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->change($post);
                //修改余额
                ( new UserWallet() )->save(['wallet' => $post['wallet'],'give_bucket_num' => $post['num']],['user_id' => $model->user_id]);
                  //修改手机
                ( new User() )->save(['mobile' => $post['mobile']],['user_id' => $model->user_id]);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        $this->assignFormOption();
        echo $this->fetch('ic_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Ic::get($id);

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