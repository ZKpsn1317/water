<?php

namespace app\dlc\controller;

use app\common\model\Ad;
use app\common\model\Agent;
use app\common\tool\Excel;
use app\common\model\Device;

class AdController extends BaseController
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
            'device_id' => [ 'name' => '设备ID', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
        ];
    }

    protected function assignOption ()
    {
        $this->assign( 'type', Ad::$typeOption );
        $this->assign( 'status', Ad::$statusOption );
    }


    protected function assignFromOption ()
    {
        $this->assign( 'agent', Agent::column( 'agent_id,agent_name' ) );
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
            $list = Ad::where( $where )->with( 'device,agent' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = Ad::where( $where )->with( 'device,agent' )->page( $page, $psize )->select();  //order('ad_id DESC')->
        }

        $count = Ad::where( $where )->count();

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
                $post              = $rq->post();
                $post['area_id']   = $post['area_id'] ? implode( ',', $post['area_id'] ) : '';
                $post['device_id'] = $post['device_id'] ? implode( ',', $post['device_id'] ) : '';
                Ad::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assign('areaHtml', '');
        $this->assign('deviceHtml', '');
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'ad_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = Ad::get( $id );

        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $post['area_id']   = $post['area_id'] ? implode( ',', $post['area_id'] ) : '';
                $post['device_id'] = $post['device_id'] ? implode( ',', $post['device_id'] ) : '';
                $model->change( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }

        $areaHtml = (new AreaController())->getAreaListByAgentId($model->getData('agent_id'), $model->getData('area_id'));


        $deviceHtml = $this->getDeviceListByareaIds($model->getData('area_id'), $model->getData('device_id'));


        $this->assign('areaHtml', $areaHtml['content']);
        $this->assign('deviceHtml', $deviceHtml['content']);
        $this->assign( 'id', $id );
        $this->assign( 'model', $model );
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'ad_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Ad::get( $id );

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
}