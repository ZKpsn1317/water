<?php

namespace app\dlc\controller;
use app\common\tool\Excel;
use app\common\model\Merchant;
use app\common\model\MerchantType;

class MerchantController extends BaseController
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
            'name' => ['name' => '商家名称', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
            'mobile' => ['name' => '联系方式', 'value' => '', 'type' => 'text', 'searchType' => '='],
        ];
    }
    //在表单中使用的列表选项
    protected function assignFormOption()
    {
        $this->assign('type', $this->array_merge(['' => '请选择'], MerchantType::column('merchant_type_id,merchant_name') ?: []));
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
            $list = Merchant::where($where)->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Merchant::where($where)->page($page,$psize)->order('id DESC')->select();
        }
        $count = Merchant::where($where)->count();


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
                Merchant::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignFormOption();
        //$this->assignOption();
        echo $this->fetch('merchant_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Merchant::get($id);
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
        $this->assignFormOption();
        $this->assign('id', $id);
        $this->assign('model', $model);
        echo $this->fetch('merchant_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Merchant::get($id);

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