<?php

namespace app\dlc\controller;

use app\common\model\User;
use app\common\model\Userguide;
use app\common\tool\Excel;

class UserguideController extends BaseController
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
            'guide_title' => [ 'name' => '标题名称', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', Userguide::$statusOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        if ( isset( $where['guide_title'] ) && $where['guide_title'] ) {
            $where['dlc_userguide.guide_title'] = [ 'like', '%' . $where['guide_title'] . '%' ];
            unset($where['guide_title']);
        }
      
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Userguide::where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Userguide::where($where)->page($page,$psize)->select();
         }
        
        
        $count = Userguide::where($where)->count();
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
    public function add ()
    {
        $rq = $this->request;
        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $post['guide_ctime'] = time();
                $add = Userguide::insert( $post );
                if($post['guide_title'] == ''){
                    return ( [ 'status' => 0, 'msg' => '标题不能为空' ] );
                }
                if($post['guide_desc'] == ''){
                    return ( [ 'status' => 0, 'msg' => '内容不能为空' ] );
                }
            } catch ( \think\Exception $err ) {
                return ( [ 'status' => 0, 'msg' => $err->getMessage() ] );
            }
            return ( [ 'status' => 1, 'msg' => '操作成功' ] );
        }
        $this->assignOption();
        echo $this->fetch( 'add' );
    }


   /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $guide_id = $rq->param('guide_id');
     
        $model = Userguide::get($guide_id);
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

        $this->assign('guide_id', $guide_id);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('add');
    }

      /**
     * 删除
     * @return array
     */
    public function del()
    {
        $guide_id = $this->request->param('guide_id');
        
        $model = Userguide::get($guide_id);

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