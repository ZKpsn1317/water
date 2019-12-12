<?php

namespace app\agent\controller;

use app\common\model\Area;
use app\common\model\PressureGoldOrder;
use think\Db;

class PressureGodOrderStatisticsController extends BaseController
{


    /**
     * 查询配置参数
     * @return array
     */
    protected function search ()
    {
        return [
            'area_id' => [ 'name' => '场地', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], Area::column( 'area_id,area_name' ) ) ],
            'status'  => [ 'name' => '状态', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' => $this->array_merge( [ '' => '请选择' ], PressureGoldOrder::$statusOption ) ],
            'ctime'   => [ 'name' => '开始时间', 'value' => '', 'type' => 'date', 'searchType' => '>=', 'style' => 'max-width:300px;' ],
            'ctime2'  => [ 'key' => 'ctime', 'name' => '结束时间', 'value' => '', 'type' => 'date', 'searchType' => '<=', 'style' => 'max-width:300px;' ],
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
            $timeWhere[] = 'dlc_pressure_gold_order.ctime <= ' . $end_time;
        }

        if ( !empty( $search['ctime']['value'] ) ) {
            $start_time  = strtotime( $search['ctime']['value'] );
            $timeWhere[] = 'dlc_pressure_gold_order.ctime >=' . $start_time;
        }


        $timeWhere = implode( ' AND ', $timeWhere );
        $timeWhere = $timeWhere ? ' AND ' . $timeWhere : $timeWhere;


        if ( $timeWhere ) {
            $sArea   = isset( $where['area_id'] ) && $where['area_id'] ? 'AND dlc_pressure_gold_order.area_id=?' : '';
            $sStatus = '';
            if ( isset( $where['status'] ) && $where['status'] ) {
                $sStatus = 'AND dlc_pressure_gold_order.status=?';
            }

            $sql  = "SELECT sum(price) as priceSum,count(pressure_gold_order_id) as orderCount FROM dlc_pressure_gold_order where agent_id='{$this->agent_id}' {$timeWhere} {$sArea} {$sStatus}";
            $list = Db::query( $sql, array_values( $where ) );


            $sql  = "SELECT sum(price) as priceSum,count(pressure_gold_order_id) as orderCount,area_name FROM dlc_pressure_gold_order 
                    LEFT JOIN dlc_area ON dlc_pressure_gold_order.area_id = dlc_area.area_id WHERE
                    dlc_pressure_gold_order.agent_id='{$this->agent_id}' {$timeWhere} {$sArea} {$sStatus} GROUP BY dlc_pressure_gold_order.area_id  LIMIT $page_start,$page_end";
            $list = Db::query( $sql, array_values( $where ) );

            $count = "select count(priceSum) as count FROM (
                      SELECT sum(price) as priceSum,count(pressure_gold_order_id) as orderCount FROM dlc_pressure_gold_order WHERE 
                     dlc_pressure_gold_order.agent_id='{$this->agent_id}' {$timeWhere} {$sArea} {$sStatus} GROUP BY area_id
                    ) as fro";
            $count = Db::query( $count, array_values( $where ) )[0]['count'];

            $numberSql = "select sum(priceSum) as priceSum,SUM(orderCount) as orderCount FROM (
                      SELECT sum(price) as priceSum,count(pressure_gold_order_id) as orderCount FROM dlc_pressure_gold_order WHERE 
                       dlc_pressure_gold_order.agent_id='{$this->agent_id}' {$timeWhere} {$sArea} {$sStatus} GROUP BY agent_id
                    ) as fro";
            $number    = Db::query( $numberSql, array_values( $where ) );

        } else {
            $list   = [];
            $count  = 0;
            $number = [];
        }


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'title', $this->title );
        $this->assign( 'list', $list );
        $this->assign( 'number', $number[0] );
        $this->assign( 'area', Area::column( 'area_id,area_name' ) );


        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );

        echo $this->fetch();

    }
}


