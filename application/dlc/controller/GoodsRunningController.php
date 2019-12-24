<?php

namespace app\dlc\controller;
use app\common\model\GoodsRunning;
use app\common\tool\Excel;

class GoodsRunningController extends BaseController
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
            'order_id' => ['name' => '订单id', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'running_type'   => [ 'name' => '订单类型', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], GoodsRunning::$runningTypeOption ) ],
            'rfid'  => ['name' => '水桶号', 'value' => '', 'type' => 'text', 'searchType' => '='],
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', GoodsRunning::$statusOption);
        $this->assign('runningType', GoodsRunning::$runningTypeOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        //订单id查询
        if ( isset( $where['order_id'] ) && $where['order_id'] ) {
            $where['dlc_goods_running.order_id'] =$where['order_id'];
            unset($where['order_id']);
        }
        //订单类型
        if ( isset( $where['running_type'] ) && $where['running_type'] ) {
            $where['dlc_goods_running.running_type'] = $where['running_type'];
            unset( $where['running_type'] );
        }
        //水桶号查询
         if ( isset( $where['rfid'] ) && $where['rfid'] ) {
            $where['dlc_goods_running.rfid'] =$where['rfid'];
            unset($where['rfid']);
        }

        $where = $this->buildSearchWhere($search);

        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = GoodsRunning::where($where)->order('id DESC')->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = GoodsRunning::where($where)->order('id DESC')->page($page,$psize)->select();  //
         }
        
        
        $count = GoodsRunning::where($where)->count();


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
                GoodsRunning::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('goods_running_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = GoodsRunning::get($id);

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
        echo $this->fetch('goods_running_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = GoodsRunning::get($id);

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