<?php

namespace app\agent\controller;
use app\common\model\Area;
use app\common\tool\Excel;
use app\common\model\Agent;
use app\common\model\SetMeal;

class AreaController extends BaseController
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
            'area_name' => ['name' => '名称', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'username' => ['name' => '帐号', 'value' => '', 'type' => 'text', 'searchType' => '='],
			
        ];
    }

    protected function assignOption()
    {
    
    }

    protected function assignFormOption()
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

        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Area::where($where)->with('agent,device')->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Area::where($where)->with('agent,device')->page($page,$psize)->select();  //order('area_id DESC')->
         }
        
        
        $count = Area::where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
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
        $agent_id=$this->agent_id;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $post['agent_id'] = $this->agent_id;
                Area::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $where['agent_id'] = $agent_id;
        $setmeal = SetMeal::where( $where )->column('setmeal_id,setmeal_name');
        $this->assign('setmeal', $setmeal);
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch('area_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Area::get(['area_id' => $id, 'agent_id' => $this->agent_id]);

        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $post['agent_id'] = $this->agent_id;
                $model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $model->password = '';
        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch('area_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Area::get($id);

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