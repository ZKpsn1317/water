<?php

namespace app\dlc\controller;
use app\common\model\Agent;
use app\common\model\Region;
use app\common\tool\Excel;

class AgentController extends BaseController
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
            'username' => ['name' => '代理帐号', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
			'agent_name' => ['name' => '名称', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
        ];
    }

    protected function assignOption()
    {
        $this->assign('region', $this->array_merge(['' => '请选择'], Region::column('region_id,region_name')));
        $this->assign('status', Agent::$statusOption);
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
            $list = Agent::where($where)->with('region')->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Agent::where($where)->with('region,devicenum,areanum,bucketnum')->page($page,$psize)->order('agent_id DESC')->select();
         }


        $count = Agent::where($where)->count();

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
                Agent::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('agent_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Agent::get($id);

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

        $model->password = '';
        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('agent_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Agent::get($id);

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