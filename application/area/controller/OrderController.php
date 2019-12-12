<?php

namespace app\area\controller;
use app\common\model\Order;
use app\common\tool\Excel;

class OrderController extends BaseController
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
            'user_id' => ['name' => '用户ID', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'device_id' => ['name' => '设备ID', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'status' => ['name' => '订单状态', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge(['' => '请选择'], Order::$statusOption)],
			
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', Order::$statusOption);
        $this->assign('shippingStatus', Order::$shippingStatusOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        $where['area_id'] = $this->area_id;

        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Order::where($where)->with('user,device,agent,area,order_info')->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Order::where($where)->with('user,device,agent,area,order_info')->order('order_id DESC')->page($page,$psize)->select();
         }
        
        
        $count = Order::where($where)->count();
        $countPrice = Order::where($where)->sum('price');


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assign('countPrice', $countPrice);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
    }

}