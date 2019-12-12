<?php

namespace app\agent\controller;
use app\common\model\UserCouponType;
use app\common\tool\Excel;
use app\common\model\Agent;

class UserCouponTypeController extends BaseController
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
            
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', UserCouponType::$status);
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
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Agent::where($where)->find();
            Excel::export($list, $this->exportField);
         } else {
            $list = Agent::where($where)->find();
         }
        
        
        $count = Agent::where($where)->count();
        $this->assignOption();
        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch('set_coupon_index');
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
                $post['agent_id'] = $this->agent_id;
                UserCouponType::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('set_meal_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit($type)
    {
        $model = Agent::get($this->agent_id);
        if($this->request->isPost())
        {
            try{
                $post = $this->request->post();
                $model->save($post);
                //$model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assign('type',$type);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('set_coupon_type_form');
    }
}