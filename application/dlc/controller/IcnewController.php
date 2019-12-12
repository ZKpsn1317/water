<?php

namespace app\dlc\controller;
use app\common\model\Icnew;
use app\common\tool\Excel;
use app\common\model\Agent;

class IcnewController extends BaseController
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
            // 'nickname' => ['name' => 'id卡号', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'ic_id'  => ['name' => 'id卡号', 'value' => '', 'type' => 'text', 'searchType' => '='],
        ];
    }

    protected function assignOption()
    {
        
    }
    //在表单中使用的列表选项
    protected function assignFormOption()
    {
        
    }

    public function lll()
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
            $where['dlc_icnew.car_number'] = ['like','%' . $where['ic_id'] . '%'];
            unset( $where['ic_id'] );
        }

        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Icnew::where($where)->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Icnew::where($where)->page($page,$psize)->order('ic_id DESC')->select(); 
        }
        
        
        $count = Icnew::where($where)->count();


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
                Icnew::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch('icnew_add');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Icnew::get($id);

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
        $this->assignFormOption();
        echo $this->fetch('icnew_edit');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Icnew::get($id);

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