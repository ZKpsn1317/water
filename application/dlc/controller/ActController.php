<?php

namespace app\dlc\controller;
use app\common\tool\Excel;
use app\common\model\Act;
use app\common\model\Merchant;

class ActController extends BaseController
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

    protected function assignFromOption ()
    {
        $this->assign( 'merchant', Merchant::column( 'id,name' ) );
    }

    public function getMerchantListByMerchantId ( $merchantId, $select = '' )
    {
        $html = '';
        if ( $merchantId ) {
            $select = explode(',', $select);
            $where['merchant_id'] = $merchantId;
            $where['status']   = 1;
            $list = Act::where( $where )->column('id,name');
            $form = \form\Form::form();
            $html = $form->checkboxs('id[]', $list, $select,['class'=>'form-control' ,'data-bv-field' => 'id[]', 'data-placeholder' => '活动','style' => 'position:inherit;opacity:100;display:initial;left:0;']);
        }
        return ['code'=>1,'content'=>$html];


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
            $list = Act::where($where)->select();
            //dump($list);exit;
            Excel::export($list, $this->exportField);
         } else {
            $list = Act::where($where)->page($page,$psize)->order('id DESC')->select();
            // $list = collection($list)->toArray();

         }
            
        
        $count = Act::where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');
        $this->assignFromOption();

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
                Act::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignFromOption();
        echo $this->fetch('act_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Act::get($id);
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
        $this->assignFromOption();
        echo $this->fetch('act_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Act::get($id);

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