<?php

namespace app\dlc\controller;
use app\common\model\Agent;
use app\common\model\SetMeal;
use app\common\tool\Excel;

class SetMealController extends BaseController
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
            'agent_id' => ['name' => '代理', 'value' => '', 'type' => 'select', 'searchType' => '=', 'style' => 'max-width:300px;', 'option' => $this->array_merge([''=>'请选择'], Agent::column('agent_id,agent_name'))]
        ];
    }

    protected function assignOption()
    {
        $this->assign('giveBucket', SetMeal::$giveOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        if ( isset( $where['agent_id'] ) && $where['agent_id'] ) {
            $where['dlc_set_meal.agent_id'] = $where['agent_id'];
            unset($where['agent_id']);
        }

        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = SetMeal::join('dlc_agent','dlc_set_meal.agent_id=dlc_agent.agent_id')->where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = SetMeal::join('dlc_agent','dlc_set_meal.agent_id=dlc_agent.agent_id')->where($where)->page($page,$psize)->select();
         }
        
        
        $count = SetMeal::join('dlc_agent','dlc_set_meal.agent_id=dlc_agent.agent_id')->where($where)->count();


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
                SetMeal::add($post);
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
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = SetMeal::get($id);

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
        echo $this->fetch('set_meal_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = SetMeal::get($id);

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