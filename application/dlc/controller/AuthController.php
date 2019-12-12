<?php

namespace app\dlc\controller;
use app\common\model\Auth;


class AuthController extends BaseController
{

    protected $title = '$title';
    protected $status = [1=>'通过', 2=>'待审核', 3=>'拒绝', ''=>'全部'];
    /**
     * 查询配置参数
     * @return array
     */
    protected function search()
    {

        return [
            'user_id' => ['name' => '用户ID', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'name' => ['name' => '姓名', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'auth_status' => ['name' => '状态', 'type'=>'select', 'value' => 2, 'searchType' => '=', 'option'=>$this->status]
        ];
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
        $list = Auth::where($where)->page($page,$psize)->select();
        $count = Auth::where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);

        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
    }




    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Auth::get($id);

        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->auth($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }

        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assign('status', $this->status);
        echo $this->fetch('auth_form');
    }



}