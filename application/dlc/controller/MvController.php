<?php

namespace app\dlc\controller;

use app\common\model\Agent;
use app\common\model\Mv;
use app\common\model\Act;
use app\common\tool\Excel;

class MvController extends BaseController
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
        ];
    }

    protected function assignOption ()
    {
        $this->assign( 'type', Mv::$typeOption );
        $this->assign( 'status', Mv::$statusOption );
    }

    protected function assignFromOption ()
    {
        $this->assign( 'act', Act::column( 'id,color' ) );
        $this->assign("agent",Agent::column("agent_id,agent_name"));
    }


    public function getActListByActId ( $actId, $select = '' )
    {
        $html = '';
        if ( $actId ) {
            $select = explode(',', $select);
            $where['act_id'] = $actId;
            $where['status']   = 1;
            $list = Mv::where( $where )->column('id,color');
            $form = \form\Form::form();
            $html = $form->checkboxs('id[]', $list, $select,['class'=>'form-control' ,'data-bv-field' => 'id[]', 'data-placeholder' => '视频','style' => 'position:inherit;opacity:100;display:initial;left:0;']);
        }
        return ['code'=>1,'content'=>$html];


    }

    public function getAgentListByAgentId ( $agentId, $select = '' )
    {
        $html = '';
        if ( $agentId ) {
            $select = explode(',', $select);
            $where['agent_id'] = $agentId;
            $where['status']   = 1;
            $list = Mv::where( $where )->column('agent_id,agent_name');
            $form = \form\Form::form();
            $html = $form->checkboxs('id[]', $list, $select,['class'=>'form-control' ,'data-bv-field' => 'id[]', 'data-placeholder' => '视频','style' => 'position:inherit;opacity:100;display:initial;left:0;']);
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
            $list = Mv::where( $where )->select();
            Excel::export( $list, $this->exportField );
        } else {
            $list = Mv::where( $where )->page( $page, $psize )->order('id DESC')->select();  //order('ad_id DESC')->
        }

        $count = Mv::where( $where )->count();

        $this->assign( 'searchHtml', $this->createSerachHtml( $search ) );
        $this->assign( 'list', $list );
        $this->assign( 'title', $this->title );
        $this->assignOption();
        $this->assign( 'hasExport', $this->exportField );
        $this->getPage( $count, $psize, 'App-loader', '列表', 'App-search' );
        $this->assignFromOption();

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
                $post['mold']   = $post['type'];
                $post['image'] = $post['url'];
                unset($post['type'],$post['url']);
                Mv::add( $post );
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        $this->assignFromOption();
        echo $this->fetch( 'mv_form' );
    }


    /**
     * 编辑
     * @return array
     */
    public function edit ()
    {
        $rq = $this->request;
        $id = $rq->param( 'id' );

        $model = Mv::get( $id );

        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $post['mold']   = $post['type'];
                $post['image'] = $post['url'];
                unset($post['type'],$post['url']); 
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
        echo $this->fetch( 'mv_form' );
    }


    /**
     * 删除
     * @return array
     */
    public function del ()
    {
        $id    = $this->request->param( 'id' );
        $model = Mv::get( $id );

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