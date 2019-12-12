<?php

namespace app\dlc\controller;

use app\common\model\Adsource;
use app\common\model\Merchant;
use app\common\model\AdsourceLog;


class AdsourceController extends BaseController
{

    protected $title = '$title';

    protected $exportField = [];   //需要导出为excel时, 配置

    /**
     * 查询配置参数
     * @return array
     */
    protected function search ()
    {
        return [
            'ad_name' => [ 'name' => '广告名称', 'value' => '', 'type' => 'text', 'searchType' => '%%' ],
        ];
    }
    protected function searchLog(){
        return [
            'ad_name' => [ 'name' => '广告名称', 'value' => '', 'type' => 'text', 'searchType' => '%%' ],
            'nickname' => [ 'name' => '用户昵称', 'value' => '', 'type' => 'text', 'searchType' => '%%' ],
        ];
    }
    protected function assignOption ()
    {
        $this->assign( 'type', Adsource::$typeOption );
    }


    protected function assignFromOption ()
    {
        $this->assign( 'merchant', Merchant::column( 'id,name' ) );
    }

    /**
     * 通过场地ID获取设备列表
     * @author limingqiang
     * @Date:date
     * @param $areaIds
     * @return array
     */
    public function getDeviceListByareaIds ( $areaIds, $select = '' )
    {

        $html = '';
        if ( $areaIds ) {
            $select = explode(',', $select);
            $where['area_id'] = [ 'in', $areaIds ];

            $list               = Device::where( $where )->column( 'device_id,device_name' );
            $form               = \form\Form::form();
            $html               = $form->checkboxs( 'device_id[]', $list, $select, [ 'class' => 'form-control','data-bv-field' => 'area_id[]', 'data-placeholder' => '设备', 'style' => 'position:inherit;opacity:100;display:initial;left:0;' ] );
        }
        return [ 'code' => 1, 'content' => $html ];
    }



    /**
     * 列表
     */
    public function index ()
    {
        $search = $this->search();
        $this->loadSearchValue( $search );

        $where = $this->buildSearchWhere( $search );

        $psize = 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = Adsource::where( $where )->with( 'merchant' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = Adsource::where( $where )->with( 'merchant' )->page( $page, $psize )->select();  //order('ad_id DESC')->
        }

        $count = Adsource::where( $where )->count();
        foreach ($list as $key => $value) {
            $num = AdsourceLog::where('merchant_ad_id',$value->adsource_id)->count();
            $value['org_num'] = $num;
        }
        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', $this->exportField );
        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );

        echo $this->fetch();
    }

    /**
     * 添加
     * @return array
     */
    public function add ()
    {
        $rq   = $this->request;
        if ( $rq->isPost() ) {
            try {
                $post  = $rq->post();
                Adsource::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'adsource_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = Adsource::get( $id );

        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $model->change( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assign( 'id', $id );
        $this->assign( 'model', $model );
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'adsource_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Adsource::get( $id );

        if ( !$model ) {
            return ( [ 'status' => 0, 'msg' => '对象不存在' ] );
        }

        try {
            $model->del();
        } catch ( \think\Exception $err ) {
            return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
        }

        return ( [ 'status' => 1, 'msg' => '操作成功' ] );
    }

    public function log ()
    {
        $search = $this->searchLog();
        $this->loadSearchValue( $search );

        $where = $this->buildSearchWhere( $search );

        $psize = 10;
        $page  = input( 'page' ) ? input( 'page' ) : 1;
        if ( input( 'export' ) ) {
            $list = AdsourceLog::where( $where )->with( 'merchantad','user' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = AdsourceLog::where( $where )->with( 'merchantad','user' )->page( $page, $psize )->select();  //order('ad_id DESC')->
        }

        $count = AdsourceLog::where( $where )->count();

        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', $this->exportField );
        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );

        $this->assign( 'empty', '<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>' );

        echo $this->fetch('adsource_log');
    }
}