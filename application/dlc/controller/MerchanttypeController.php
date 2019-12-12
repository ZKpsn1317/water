<?php

namespace app\dlc\controller;
use app\common\tool\Excel;
use app\common\model\MerchantType;

class MerchanttypeController extends BaseController
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
            'name' => ['name' => '分类名称', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
        ];
    }
    protected function assignOption()
    {
        $this->assign('status', MerchantType::$statusOption);
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
            $list = MerchantType::where($where)->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = MerchantType::where($where)->page($page,$psize)->order('merchant_type_id DESC')->select();
        }
        $count = MerchantType::where($where)->count();

        $this->assignOption();
        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
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
                MerchantType::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('Merchanttype_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = MerchantType::get($id);
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
        $this->assignOption();
        $this->assign('id', $id);
        $this->assign('model', $model);
        echo $this->fetch('Merchanttype_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = MerchantType::get($id);

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