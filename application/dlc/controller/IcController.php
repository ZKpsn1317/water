<?php

namespace app\dlc\controller;
use app\common\model\Ic;
use app\common\tool\Excel;

class IcController extends BaseController
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
    
    }


    public function lll()
    {
        
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


        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Ic::field( 'dlc_ic.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_ic.user_id=dlc_user.user_id' )->where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Ic::field( 'dlc_ic.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_ic.user_id=dlc_user.user_id' )->where($where)->page($page,$psize)->order('ic_id DESC')->select();
         }
        
        
        $count = Ic::field( 'dlc_ic.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_ic.user_id=dlc_user.user_id' )->where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
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
                Ic::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('ic_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Ic::get($id);

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
        echo $this->fetch('ic_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Ic::get($id);

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