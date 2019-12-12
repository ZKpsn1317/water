<?php

namespace app\dlc\controller;

use think\Db;
use app\common\model\Area;

class RechargeStatisticsController extends BaseController
{


    /**
     * 查询配置参数
     * @return array
     */
    protected function search ()
    {

        return [
            'ctime'  => [ 'name' => '开始时间', 'value' => '', 'type' => 'date', 'searchType' => '>=', 'style' => 'max-width:300px;' ],
            'ctime2' => [ 'key' => 'ctime', 'name' => '结束时间', 'value' => '', 'type' => 'date', 'searchType' => '<=', 'style' => 'max-width:300px;' ],
        ];
    }

    public function index ()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );
	if(empty($search['ctime']['value']))$search['ctime']['value'] = date('Y-m-d', strtotime('-6 days'));
	if(empty($search['ctime2']['value']))$search['ctime2']['value'] = date('Y-m-d');

        $where = $this->buildSearchWhere( $search );


        $psize      = 10;
        $page       = input( 'page' ) ? input( 'page' ) : 1;
        $page_start = ( $page - 1 ) * 10;
        $page_end   = $page * 10;


        $start_time = $end_time = 0;


        $timeWhere = [];
        if ( !empty( $search['ctime2']['value'] ) ) {
            $end_time    = strtotime( $search['ctime2']['value'] + ' 23:59:59' );
            $timeWhere[] = 'dlc_recharge_order.ctime <= ' . $end_time;
        }

        if ( !empty( $search['ctime']['value'] ) ) {
            $start_time  = strtotime( $search['ctime']['value'] );
            $timeWhere[] = 'dlc_recharge_order.ctime >=' . $start_time;
        }


        $timeWhere = implode( ' AND ', $timeWhere );
        $timeWhere = $timeWhere ? ' AND ' . $timeWhere : $timeWhere;


        if ( $timeWhere ) {
            $sql  = "SELECT sum(price) as priceSum,count(recharge_order_id) as orderCount,dlc_agent.agent_name FROM dlc_recharge_order LEFT JOIN dlc_agent ON dlc_recharge_order.agent_id = dlc_agent.agent_id  where  dlc_recharge_order.status=2 $timeWhere  GROUP BY dlc_recharge_order.agent_id LIMIT $page_start,$page_end";
            $list = Db::query( $sql );

            $countSql = "SELECT COUNT(priceSum) as count FROM (SELECT sum(price) as priceSum,count(recharge_order_id) as orderCount,dlc_agent.agent_name FROM dlc_recharge_order LEFT JOIN dlc_agent ON dlc_recharge_order.agent_id = dlc_agent.agent_id  where dlc_recharge_order.status=2 $timeWhere  GROUP BY dlc_recharge_order.agent_id) as a";
            $count    = Db::query( $countSql )[0]['count'];

            $numberSql = "SELECT SUM(priceSum) as priceSum,SUM(orderCount) as orderCount FROM (SELECT sum(price) as priceSum,count(recharge_order_id) as orderCount,dlc_agent.agent_name FROM dlc_recharge_order LEFT JOIN dlc_agent ON dlc_recharge_order.agent_id = dlc_agent.agent_id  where dlc_recharge_order.status=2 $timeWhere  GROUP BY dlc_recharge_order.agent_id) as count";
            $number    = Db::query( $numberSql );
        } else {
            $number = [];
            $list   = [];
            $count  = 0;
        }


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'title', $this->title );
        $this->assign( 'list', $list );
        $this->assign( 'number', $number[0] );

        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );

        echo $this->fetch();

    }
}


