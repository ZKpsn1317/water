<?php

namespace app\area\controller;

use think\Db;

class OrderStatisticsController extends BaseController
{
    public $group = [
        'agent_id'  => '代理',
        'device_id' => '设备',
        'area_id'   => '场地',
    ];

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
	if(empty($search['group']['value']))$search['group']['value'] = 'area_id';
	if(empty($search['ctime']['value']))$search['ctime']['value'] = date('Y-m-d', strtotime('-6 days'));
	if(empty($search['ctime2']['value']))$search['ctime2']['value'] = date('Y-m-d');

        $where = $this->buildSearchWhere( $search );
        $group = empty( $search['agent']['value'] ) ? : '';

        unset( $where['ctime'] );
        unset( $where['agent'] );

        $psize      = 10;
        $page       = input( 'page' ) ? input( 'page' ) : 1;
        $page_start = ( $page - 1 ) * 10;
        $page_end   = $page * 10;


        $start_time = $end_time = 0;


        if ( !empty( $search['ctime2']['value'] ) ) {
            $end_time = strtotime( $search['ctime2']['value'] + ' 23:59:59' );
        }

        if ( !empty( $search['ctime']['value'] ) ) {
            $start_time = strtotime( $search['ctime']['value'] );
        }

        $timeWhere = [];
        if ( $start_time ) {
            $timeWhere[] = 'ctime >=' . $start_time;
        }

        if ( $end_time ) {
            $timeWhere[] = 'ctime <= ' . $end_time;

        }

        $timeWhere = implode( ' AND ', $timeWhere );
        $timeWhere = $timeWhere ? ' AND ' . $timeWhere : $timeWhere;


        if ( $timeWhere && $group && in_array( $group, $this->group ) ) {
            $sql  = "SELECT *,sum(price) as priceSum,count(order_id) as orderCount FROM dlc_order where area_id = {$this->area_id} AND shipping_status = 1 $timeWhere group by device_id LIMIT $page_start,$page_end";
            $list = Db::query( $sql );

            $sql   = "SELECT count(*) AS count FROM (SELECT order_id FROM dlc_order where area_id = {$this->area_id} AND shipping_status = 1 $timeWhere group by device_id) AS order1";
            $count = Db::query( $sql );
            $count = $count[0]['count'];

            $sql    = "SELECT SUM(priceSum) as priceSum,SUM(orderCount) as orderCount FROM(
                        SELECT *,sum(price) as priceSum,count(order_id) as orderCount FROM dlc_order where area_id = {$this->area_id} AND shipping_status = 1 $timeWhere group by device_id
                     ) count";
            $number = Db::query( $sql );
            $number = $number[0];
        } else {
            $list   = [];
            $count  = 0;
            $number = [];
        }


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'title', $this->title );
        $this->assign( 'list', $list );
        $this->assign( 'number', $number );

        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );
        echo $this->fetch();

    }
}


