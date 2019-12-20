<?php

namespace app\dlc\controller;

use app\common\model\User;
use app\common\model\Photoads;
use app\common\tool\Excel;
use app\common\validate\PhotoadsValidate;


class PhotoadsController extends BaseController
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
        $this->assign('status', Photoads::$statusOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        if ( isset( $where['photoads_title'] ) && $where['photoads_title'] ) {
            $where['dlc_photoads.photoads_title'] = [ 'like', '%' . $where['photoads_title'] . '%' ];
            unset($where['photoads_title']);
        }
      
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Photoads::where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Photoads::where($where)->page($page,$psize)->select();
         }
        
        
        $count = Photoads::where($where)->count();
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
        // echo "1";die;
        $rq = $this->request;
        if ( $rq->isPost() ) {
            try {
                $post = $rq->post();
                $validate = new PhotoadsValidate();
                if(!$validate->check($post)) {
                    throw new \think\Exception($validate->getError());
                }else{
                    // echo "111";die;
                    $post['photoads_ctime'] = time();
                    $add = Photoads::insert( $post );
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
        $photoads_id = $rq->param('photoads_id');
        
        $model = Photoads::get($photoads_id);
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

        $this->assign('photoads_id', $photoads_id);
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
        $photoads_id = $this->request->param('photoads_id');
        
        $model = Photoads::get($photoads_id);

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