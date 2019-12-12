<?php

namespace app\dlc\controller;

use app\common\model\Area;
use app\common\tool\Excel;
use app\common\model\Agent;

class AreaController extends BaseController
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
            'area_name' => [ 'name' => '名称', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
            'username'  => [ 'name' => '帐号', 'value' => '', 'type' => 'text', 'searchType' => '=' ],

        ];
    }

    protected function assignOption ()
    {

    }

    protected function assignFormOption ()
    {
        $this->assign( 'agent', Agent::column( 'agent_id,agent_name' ) );
    }


    /**
     * 通过代理ID获取场地列表
     * @author limingqiang
     * @Date:date
     * @param $agentId
     * @return array
     */
    public function getAreaListByAgentId ( $agentId, $select = '' )
    {
        $html = '';
        if ( $agentId ) {
            $select = explode(',', $select);
            $where['agent_id'] = $agentId;
            $where['status']   = 1;
            $list = Area::where( $where )->column('area_id,area_name');
            $form = \form\Form::form();
            $html = $form->checkboxs('area_id[]', $list, $select,['class'=>'form-control' ,'data-bv-field' => 'area_id[]', 'data-placeholder' => '场地','style' => 'position:inherit;opacity:100;display:initial;left:0;']);
        }
        return ['code'=>1,'content'=>$html];


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
            $list = Area::where( $where )->with( 'agent,device' )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = Area::where( $where )->with( 'agent,device' )->page( $page, $psize )->select();  //order('area_id DESC')->
        }


        $count = Area::where( $where )->count();


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
        $rq = $this->request;
        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                Area::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch( 'area_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = Area::get( $id );

        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $model->change( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $model->password = '';
        $this->assign( 'id', $id );
        $this->assign( 'model', $model );
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch( 'area_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Area::get( $id );

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