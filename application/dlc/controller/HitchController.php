<?php

namespace app\dlc\controller;
use app\common\model\Hitch;
use app\common\tool\Excel;

class HitchController extends BaseController
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
            'nickname' => ['name' => '用户名称', 'value' => '', 'type' => 'text', 'searchType' => '='],
			
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', Hitch::$statusOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset( $where['nickname'] );
        }

        $psize = input('psize')?input('psize'):10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Hitch::field( 'dlc_hitch.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_hitch.user_id=dlc_user.user_id' )->where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Hitch::field( 'dlc_hitch.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_hitch.user_id=dlc_user.user_id' )->where($where)->order('hitch_id DESC')->page($page,$psize)->select();  //
         }
        
        
        $count = Hitch::field( 'dlc_hitch.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_hitch.user_id=dlc_user.user_id' )->where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
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

    /**
     * 添加
     * @return array
     */
    public function add()
    {
        $rq = $this->request;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                Hitch::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('hitch_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Hitch::get($id);

        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }

        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('hitch_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Hitch::get($id);

        if(!$model) {
            return(array('status' => 0,'msg' => '对象不存在'));
        }

        try{
            $model->del();
        } catch (\think\Exception $err) {
            return(array('status' => 0,'msg' => $err->getMessage()));
        }

        return(array('status' => 1,'msg' => '操作成功'));
    }
}