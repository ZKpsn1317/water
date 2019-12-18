<?php

namespace app\agent\controller;
use app\common\model\Action;
use app\common\model\Device;
use app\common\tool\Excel;
use app\common\model\Agent;
use app\common\model\User;
use app\common\model\UserWallet;

class WaterController extends BaseController
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
            'device_name' => ['name' => '设备名称', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
			'macno' => ['name' => '设备编号', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
            'bucket_num' => ['name' => '有水桶数小于', 'value' => '', 'type' => 'text', 'searchType' => '<'],
			
        ];
    }

    protected function assignOption()
    {
        $this->assign('agent_id',$this->agent_id);
    }
    //在表单中使用的列表选项
    protected function assignFormOption()
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
        $where['agent_id'] = $this->agent_id;
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Device::where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Device::where($where)->page($page,$psize)->order('device_id DESC')->select();  
         }
        
        
        $count = Device::where($where)->count();


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
     * 编辑
     * @return array
     */
    public function supply()
    {
        // echo "hello word";die;
        $rq = $this->request;
        $agent_id=$this->agent_id;

        // $id = $rq->param('id');

        $model = Agent::get($agent_id);
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->waterchange($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        $this->assignFormOption();
        echo $this->fetch('upd');
    }


    
}