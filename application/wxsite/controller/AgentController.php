<?php
/**
 * Created by PhpStorm.
 * User: 韩令恺
 * Date: 2018/5/17 0017
 * Time: 16:12
 */

namespace app\wxsite\controller;

use app\common\model\DeviceAllotBind;
use app\common\model\Withdraw;
use \think\Db;
use think\Session;
use app\common\model\Device;
use app\common\model\AgentBank;
use app\common\model\Agent;
use app\common\model\FeedbackAgent;
use app\common\model\RewardLog;

class AgentController extends BaseController
{
    protected $agent_id;
    public function _initialize(){
        $rq = $this->request;
        $token = $rq->post('token');

        if(!$token) {
            $this->_return(-1, '请上传token');
        }

        $id = (int)str_replace('agent', '', static::decodeToken($token));
        if(!$id) {
            $this->_return(-1, 'token无效');
        }
        $this->agent_id = $id;

    }


    /**
     * 绑定设备
     */
    protected function bindDevice()
    {
        $rq = $this->request;
        $data = [
            'motherboard_code' => $rq->post('motherboard_code'),
            'macno' => $rq->post('macno'),
            'agent_id' => $this->agent_id,
        ];
        try{
            Device::bindDevice($data);
        }catch (\think\Exception $err) {
            $this->_return(0, $err->getMessage());
        }

        $this->_return(1, 'ok');

    }


    /**
     * 绑定设备
     */
    protected function unBindDevice()
    {
        $rq = $this->request;
        $data = [
            'macno' => $rq->post('macno'),
            'agent_id' => $this->agent_id,
        ];
        try{
            Device::unBindDevice($data);
        }catch (\think\Exception $err) {
            $this->_return(0, $err->getMessage());
        }

        $this->_return(1, 'ok');

    }


    /**
     * 绑定的设备列表
     */
    protected function deviceList()
    {
        $rq = $this->request;
        $page = $rq->post('page');
        $pagesize = $rq->post('pagesize/d');
        $pagesize = $pagesize < 0 ? 20 : $pagesize;

        $list = Device::where(['agent_id' => $this->agent_id])
                ->field('id,macno,address,status,motherboard_code,status_utime')
                ->page($page, $pagesize)
                ->select();



        $onLineDevice = DeviceAllotBind::where(['agent_id' => $this->agent_id, 'bindstatus' => 1])->column('device_id');
        foreach($list as $key => $vo) {
            //去掉，没绑定的设备
            if(!in_array($vo['id'], $onLineDevice)) {
                unset($list[$key]);
            }
        }

        $list1 = [];
        foreach($list as $vo) {
            $data = $vo->getData();
            $data['status'] = $vo->status;
            $list1[] = $data;
        }


        $this->_return(1, 'ok', [ 'list' => $list1]);
    }


    /**
     * 添加银行卡
     */
    public function addBank()
    {
        $rq = $this->request;

        try{
            $post = $rq->post();
            $post['agent_id'] = $this->agent_id;
            AgentBank::add($post);
        } catch (\think\Exception $err) {
            $this->_return(0, $err->getMessage());
        }

        $this->_return(1, 'ok');
    }


    /**
     * 删除银行卡
     */
    public function delBank()
    {
        $id = $this->request->post('id');
        $model = AgentBank::get(['bank_id' => $id, 'agent_id' => $this->agent_id]);

        if(!$model) {
            $this->_return(0, '银行卡不存在');
        }

        try{
            $model->del();
        } catch (\think\Exception $err) {
            $this->_return(0, $err->getMessage());
        }

        $this->_return(1, 'ok');
    }

    /**
     * 删除银行卡
     */
    public function setBankDefault()
    {
        $id = $this->request->post('id');
        $model = AgentBank::get(['bank_id' => $id, 'agent_id' => $this->agent_id]);

        if(!$model) {
            $this->_return(0, '银行卡不存在');
        }

        try{
            $model->setDefault();
        } catch (\think\Exception $err) {
            $this->_return(0, $err->getMessage());
        }

        $this->_return(1, 'ok');
    }


    /**绑定的银行卡列表*/
    public function bankList()
    {
        $list = AgentBank::where(['agent_id' => $this->agent_id])->order('bank_id DESC')->select();
        $this->_return(1, 'ok', ['list' => collection($list)->toArray()]);
    }



    /**
     * 提现
     */
    protected function withdraw()
    {
        $rq = $this->request;
        $bank_id = $rq->post('bank_id');
        $money = $rq->post('money/d');

        if ($money < 1) {
            $this->_return(0, '提现金额不能小于1');
        }

        $agent = Agent::get(['id' => $this->agent_id]);

        try {
            $agent->withdraw($money, $bank_id);
        } catch (\think\Exception $err) {

            $this->_return(0, $err->getMessage());
        }
        $this->_return(1, 'ok');

    }


    /**
     * 意见反馈
     */
    protected function feedback() {

        $rq = $this->request;

        $data = $rq->post();
        $data['agent_id'] = $this->agent_id;
        try{
            $model = FeedbackAgent::add($data);
            $this->_return(1, '提交成功');
        }catch (\think\Exception $err) {
            $this->_return(0, $err->getMessage());
        }


    }


    /**
     * 意见反馈列表
     */
    protected function feedbackList()
    {
        $rq = $this->request;
        $page = $rq->post('page');
        $pagesize = $rq->post('pagesize');
        $list = FeedbackAgent::where(['agent_id' => $this->agent_id])
            ->order('id DESC')
            ->page($page, $pagesize)
            ->select();

        $this->_return(1, 'ok', ['list' => collection($list)->toArray()]);
    }


    /**
     * 意见反馈评价
     */
    protected function feedbackGrade()
    {
        $rq = $this->request;
        $id = $rq->post('id');
        $grade = $rq->post('grade');

        $feedback = FeedbackAgent::get(['id' => $id, 'agent_id' => $this->agent_id]);
        if(!$feedback) {
            $this->_return(0, 'id错误');
        }

        if($feedback->status != 1) {
            $this->_return(0, '后台还没处理,不能进行评价');
        }

        if($feedback->grade) {
            $this->_return(0, '该记录已评价');
        }

        if(!in_array($grade, [1, 2, 3])) {
            $this->_return(0, '请选择评分');
        }

        $feedback->grade = $grade;
        $feedback->save();

        $this->_return(1, '谢谢您的评分!');
    }


    /**
     * 提现列表
     */
    protected function withdrawList()
    {

        $rq = $this->request;
        $page = $rq->post('page');
        $pagesize = $rq->post('pagesize');
        $list = Withdraw::where(['user_id' => $this->agent_id])
            ->order('id DESC')
            ->page($page, $pagesize)
            ->select();

        $data = [];
        foreach($list as $vo) {
            $data[] = [
                'id' => $vo['id'],
                'ctime' => date('m-d', $vo->getData('ctime')),
                'type' => '1',
                'status' => $vo['status'],
                'content' => "您的尾号" . substr($vo['bank_account'], -4) . '的储蓄卡帐户'. date('m月d日H时i分s秒') . '申请提现¥' . $vo['num'] . "[{$vo['bank_name']}]",
            ];
        }

        $this->_return(1, 'ok', ['list' => $data]);
    }


    /**
     * 提现详情
     */
    protected function withdrawInfo()
    {
        $rq = $this->request;
        $id = $rq->post('id');
        $model = Withdraw::get(['id' => $id, 'user_id' => $this->agent_id]);

        $this->_return(1, 'ok', $model);
    }


    /**赠送收益列表**/
    protected function rewardLog()
    {

        $rq = $this->request;
        $scope = $rq->post('scope');
        if($scope == 1) {
            $startTime = strtotime(date('Y-m-d'));
        } elseif($scope == 2) {
            //当前日期
            $sdefaultDate = date("Y-m-d");
            //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
            $first=1;
            //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
            $w=date('w',strtotime($sdefaultDate));
            //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
            $startTime=strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days');
        } elseif($scope == 3) {
            $startTime = strtotime(date('Y-m'));
        } else {
            $this->_return(0, '请上传正确的范围参数');
        }


        $type = $rq->post('type');
        if(!in_array($type, [1,2,3,4])) {
            $this->_return(0, '请上传正确的类型');
        }

        $where['agent_id'] = ['=', $this->agent_id];
        $where['type'] = $type;
        $where['ctime'] = ['>=', $startTime];

        $result = RewardLog::census($where);

        $this->_return(1, 'ok', $result);

    }


    /**
     * 代理端 设备数量，今日，昨日收益
     */
    protected function info()
    {
        //设备总数量，及在线数量
        $devices = Device::where(['agent_id' => $this->agent_id])
                    ->field('id,container_no,status')
                    ->select();
        $deviceCount = count($devices);
        $onLineCount = 0;

        foreach($devices as $d) {
            if($d['status']) {
                $onLineCount++;
            }
        }

        //昨日营收
        $time = strtotime(date('Y-m-d'));
        $where['agent_id'] = ['=', $this->agent_id];
        $where['ctime'] = ['>=', $time-3600*24];
        $where['ctime'] = ['<', $time];
        $result = RewardLog::census($where);
        $yesterdayCountIncome = $result['countIncome'];
        $yesterdayCountOrder = $result['countOrder'];

        //今日收益
        $time = strtotime(date('Y-m-d'));
        $where['agent_id'] = ['=', $this->agent_id];
        $where['ctime'] = ['>=', $time];
        $result = RewardLog::census($where);
        $todayCountIncome = $result['countIncome'];
        $todayCountOrder = $result['countOrder'];

        $this->_return(1, 'ok', [
            'deviceCount' => $deviceCount,
            'onLineCount' => $onLineCount,
            'yesterdayCountIncome' => $yesterdayCountIncome,
            'yesterdayCountOrder' => $yesterdayCountOrder,
            'todayCountIncome'=> $todayCountIncome,
            'todayCountOrder' => $todayCountOrder,
        ]);
    }




}
