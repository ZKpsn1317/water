<?php

namespace app\agent\controller;

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
            $timeWhere[] = 'ctime <= ' . $end_time;
        }

        if ( !empty( $search['ctime']['value'] ) ) {
            $start_time  = strtotime( $search['ctime']['value'] );
            $timeWhere[] = 'ctime >=' . $start_time;
        }


        $timeWhere = implode( ' AND ', $timeWhere );
        $timeWhere = $timeWhere ? ' AND ' . $timeWhere : $timeWhere;


        if ( $timeWhere ) {
            $sql = "SELECT sum(price) as priceSum,count(recharge_order_id) as orderCount FROM dlc_recharge_order where agent_id='{$this->agent_id}' {$timeWhere} AND status=2";
            //var_dump( $sql );
            $list  = Db::query( $sql );
            $count = 0;

        } else {
            $list  = [];
            $count = 0;
        }


        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'title', $this->title );
        $this->assign( 'list', $list );


        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );

        echo $this->fetch();

    }
}


