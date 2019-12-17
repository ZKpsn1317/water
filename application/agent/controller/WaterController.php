<?php

namespace app\agent\controller;
use app\common\model\Action;
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
            'user_id' => ['name' => '用户id', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'ic_id'  => ['name' => 'id卡号', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'mobile'  => ['name' => '手机号', 'value' => '', 'type' => 'text', 'searchType' => '='],
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