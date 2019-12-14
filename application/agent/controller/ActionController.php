<?php

namespace app\agent\controller;
use app\common\model\Action;
use app\common\tool\Excel;
use app\common\model\Agent;
use app\common\model\User;
use app\common\model\UserWallet;

class ActionController extends BaseController
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
    /* public function index()
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
 */


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $agent_id=$this->agent_id;

        // $id = $rq->param('id');

        $model = Agent::get($agent_id);
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
              
                $model->actionchange($post);
                
                
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        $this->assignFormOption();
        echo $this->fetch('upd');
    }


    
}