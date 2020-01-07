<?php

namespace app\agent\controller;
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
            'mobile' => [ 'name' => '手机号码', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'nickname' => [ 'name' => '昵称', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
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
        $where['agent_id'] = $this->agent_id;
        if ( isset( $where['mobile'] ) && $where['mobile'] ) {
            $where['dlc_user.mobile'] = [ 'like', '%' . $where['mobile'] . '%' ];
            unset($where['mobile']);
        }
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset($where['nickname']);
        }
        if ( isset( $where['user_id'] ) && $where['user_id'] ) {
            $where['dlc_user.user_id'] = $where['user_id'];
            unset($where['user_id']);
        }
        
        $psize = input('psize')?input('psize'):10;
        $page = input('page')?input('page'):1;
        if(input('export')) {
            $list = Order::where($where)->with('user,device,agent,area,order_info')->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Order::join('dlc_user','dlc_order.user_id=dlc_user.user_id')->with('device,agent,area,order_info')->where($where)->order('order_id DESC')->page($page,$psize)->select();
        }
        $count = Order::join('dlc_user','dlc_order.user_id=dlc_user.user_id')->with('device,agent,area,order_info')->where($where)->order('order_id DESC')->count();
        $countPrice = Order::join('dlc_user','dlc_order.user_id=dlc_user.user_id')->with('device,agent,area,order_info')->where($where)->order('order_id DESC')->sum('price');


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assign('countPrice', $countPrice);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        if($this->request->param('dialog')) {
            return $this->fetch();
        } else {
            echo $this->fetch();
        }
    }

}