<?php

namespace app\dlc\controller;
use think\Db;
use app\common\model\Agent;
use app\common\model\Area;

class OrderStatisticsController extends BaseController
{
    public $group = [
        'agent_id' => '代理',
        'device_id' => '设备',
        'area_id' => '场地',
    ];
    /**
     * 查询配置参数
     * @return array
     */
    protected function search()
    {
        $agent = Agent::column('agent_id,agent_name');
        $area = Area::column('area_id,area_name');
        return [
            'dlc_order_agent_id' => ['name' => '代理', 'value' => '', 'type' => 'select', 'searchType' => '=', 'style' => 'max-width:300px;', 'option' => $this->array_merge([''=>'请选择'], $agent)],
            'dlc_order_area_id' => ['name' => '场地', 'value' => '', 'type' => 'select', 'searchType' => '=', 'style' => 'max-width:300px;', 'option' => $this->array_merge([''=>'请选择'], $area)],
            'ctime' => ['name' => '开始时间', 'value' => '', 'type' => 'date', 'searchType' => '>=', 'style' => 'max-width:300px;'],
            'ctime2' => ['key' => 'ctime','name' => '结束时间', 'value' => '', 'type' => 'date', 'searchType' => '<=',  'style' => 'max-width:300px;'],
            'group' => ['name' => '统计类型', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge(['' => '请选择'], $this->group)],
        ];
    }

    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);
	if(empty($search['group']['value']))$search['group']['value'] = 'agent_id';
	if(empty($search['ctime']['value']))$search['ctime']['value'] = date('Y-m-d', strtotime('-6 days'));
	if(empty($search['ctime2']['value']))$search['ctime2']['value'] = date('Y-m-d');

        $where = $this->buildSearchWhere($search);
        $group = empty($search['group']['value']) ? '' : $search['group']['value'];
        unset($where['ctime']);
        unset($where['agent']);

        $psize = 10;
        $page = input('page')?input('page'):1;
        $page_start = ($page-1)*10;
        $page_end = $page * 10;


        $start_time = $end_time = 0;

        $timeWhere = [];
        if(!empty($search['ctime2']['value']))
        {
            $end_time = strtotime($search['ctime2']['value'] + ' 23:59:59');
        }

        if(!empty($search['ctime']))
        {
            $start_time = strtotime($search['ctime']['value']);
        }


        if($start_time) {
            $timeWhere[] = 'dlc_order.ctime >=' .$start_time;
        }

        if($end_time) {
            $timeWhere[] = 'dlc_order.ctime <= ' . $end_time;
        }

        $aaWhere = [];
        if(!empty($search['dlc_order_agent_id']['value'])) {
            $aaWhere[] = 'dlc_order.agent_id = "' . intval($search['dlc_order_agent_id']['value']) . '"';
        }

        if(!empty($search['dlc_order_area_id']['value'])) {
            $aaWhere[] = 'dlc_order.area_id = "' . intval($search['dlc_order_area_id']['value']) . '"';
        }
        $aaWhere = implode(' AND ', $aaWhere);
        $aaWhere = $aaWhere ? ' AND ' . $aaWhere : '';
     

        $timeWhere = implode(' AND ', $timeWhere);
        $timeWhere = $timeWhere ? ' AND ' . $timeWhere : $timeWhere;

        if($timeWhere && $group && in_array($group, array_keys($this->group))) {
            $sql = "SELECT dlc_order.*,dlc_agent.agent_name,dlc_area.area_name,sum(price) as priceSum,count(order_id) as orderCount FROM dlc_order LEFT JOIN dlc_agent ON dlc_order.agent_id = dlc_agent.agent_id LEFT JOIN dlc_area ON dlc_order.area_id = dlc_area.area_id  where shipping_status = 1 $timeWhere $aaWhere group by $group LIMIT $page_start,$page_end";
            $list = Db::query($sql);

            $numberSql = "SELECT SUM(priceSum) AS priceSum,SUM(orderCount) AS orderCount FROM($sql) as count ";
            $number = Db::query($numberSql);

            $sql = "SELECT count(*) AS count FROM (SELECT order_id FROM dlc_order where shipping_status = 1 $timeWhere $aaWhere group by agent_id) AS order1";
            $count = Db::query($sql);
            $count = $count[0]['count'];
        } else {
            $list = [];
            $count = 0;
            $number = [];
        }




        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('title', $this->title);
        $this->assign('list', $list);
        $this->assign('number', $number[0]);


        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();

    }
}


