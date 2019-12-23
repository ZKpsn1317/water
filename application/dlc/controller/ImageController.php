<?php

namespace app\dlc\controller;

use app\common\model\User;
use app\common\model\Image;
use app\common\tool\Excel;


class ImageController extends BaseController
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
            'photoads_title' => [ 'name' => '标题名称', 'value' => '', 'type' => 'text', 'searchType' => '=' ],
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', Image::$statusOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
    
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Image::where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Image::where($where)->page($page,$psize)->select();
         }
        
        
        $count = Image::where($where)->count();
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
            //   var_dump($post);die;
                $post['ictime'] = time();
                $add = Image::insert( $post );
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
        $i_id = $rq->param('i_id');
        
        $model = Image::get($i_id);
        // dump($model);die;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                // dump($post);die;
                $a=$model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }

        $this->assign('i_id', $i_id);
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
        $i_id = $this->request->param('i_id');
        
        $model = Image::get($i_id);

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