<?php
/**
 * Created by PhpStorm.
 * User: 韩令恺
 * Date: 2018/5/17 0017
 * Time: 16:12
 */

namespace app\wxsite\controller;



use app\common\model\Bucket;
use app\common\model\DeviceAisle;
use \think\Db;
use app\common\model\Device;
use think\Validate;
use app\common\model\User;
use app\common\model\WaterRecharge;
use hardware\WyjVendingMachine;
use app\common\model\WaterRechargeLog;
use app\common\model\GoodsRunning;
use app\common\model\WaterRechargeLogInfo;


class ReplenishmentController extends BaseController
{
    protected $replenishment_id;
    protected $replenishment;


    public function _initialize()
    {
        $rq = $this->request;

        $token = $rq->post('token');
        $user = WaterRecharge::get(['token' => $token]);
        if(!$user) {
            $this->_return(101, 'token无效');
        }

        $this->replenishment = $user;
        $this->replenishment_id = $user->water_recharge_id;

    }



    public function modifyPass()
    {

        $rq = $this->request;
        $validate = new Validate([
            'oldpass|旧密码' => 'require',
            'newpass1|新密码1' => 'require|min:6',
            'newpass2|新密码2' => 'require|confirm:newpass1',
        ]);

        $post = $rq->post();
        if(!$validate->check($post)) {
            $this->_return(0, $validate->getError());
        }

        $replenishment = $this->replenishment;
        if($replenishment->password != User::createPassword($post['oldpass'])) {
            $this->_return(0, '旧密码无效');
        }

        $replenishment->change(['password' => $post['newpass1']]);

        $this->_return(1, '修改成功');

    }


    /**
     * 设备列表
     */
    protected function deviceList()
    {
        $rq = $this->request;
        $page = $rq->post('page');
        $pagesize = $rq->post('pagesize/d');
        $pagesize = $pagesize < 0 ? 20 : $pagesize;

        $list = Device::where(['water_recharge_id' => $this->replenishment_id])
            ->page($page, $pagesize)
            ->select();


        $this->_return(1, 'ok', [ 'list' => $list ]);
    }


    /**
     * 设备详情
     */
    public function deviceInfo()
    {
        $device_id = $this->request->post('device_id');
        if(!$device_id) {
            $this->_return(0, '请上传参数');
        }



        $device = Device::where(['device_id' => $device_id,'agent_id' => $this->replenishment->agent_id])
            ->with('device_aisle.bucket,water_brand')
            ->find();

        if(empty($device)) {
            $this->_return(0, '你没有这个设备的操作权限');
        }

        $device_aisle = $device['device_aisle'];

        foreach($device_aisle as $key => $da) {
            if(empty($da->bucket)) {
                $device_aisle[$key]['bucket'] = json_decode('{
                                    "bucket_id": 0,
                                    "rfid": "",
                                    "device_id": 0,
                                    "agent_id": 0,
                                    "user_id": 0,
                                    "water_brand_id": 0,
                                    "status": 0,
                                    "area_id": 0,
                                    "ctime": ""
                                    }');
            }
        }

        $this->_return(1, 'ok', $device);
    }




    /**
     * 开门
     */
    public function openDoor()
    {
        $rq = $this->request;
        $device_id = $rq->post('device_id');
        $device_aisle_id = $rq->post('device_aisle_id');
        if(!$device_id) {
            $this->_return(0, '设备号不能为空');
        }

        $device = Device::get(['device_id' => $device_id]);
        if(!$device) {
            $this->_return(0, '设备不存在');
        }


        if($device->water_recharge_id != $this->replenishment_id){
            $this->_return(0, '您无权限操作这台设备');
        }

        if(!$device_aisle_id) {
            //如果不传通道ID，就开所有的门
            $aisle_numeber = DeviceAisle::where(['device_id' => $device_id])->column('aisle_num');
            $aisle_numeber = implode(',', $aisle_numeber);
        } else {
            $aisle_numeber = DeviceAisle::where(['device_aisle_id' => $device_aisle_id, 'device_id' => $device_id])->value('aisle_num');
        }

        if(!$aisle_numeber) {
            $this->_return(0, '设备无门可开');
        }

        $rechargeLog = WaterRechargeLog::didNotFinish($device_id, $this->replenishment_id);    //查当前是否有进行中的补货任务
        if(!$rechargeLog) {
            //没有创建一个
            $this->_return(0, '请选择创建补水记录');
;        }

        //创建硬件发送记录
        $runningLog = [
            'order_id' => $rechargeLog->water_recharge_log_id,
            'device_id' => $device_id,
            'running_type' => 3,
            'device_aisle' => $aisle_numeber,
            'rfid' => $device->motherboard_code,
            'async_result' => '',
        ];

        $runningLog = GoodsRunning::add($runningLog);

        $runningLog->synchro_result = WyjVendingMachine::machineCloudOpen('one_button_unlock',  $device->motherboard_code, $runningLog->id,  $aisle_numeber);
        $result = json_decode($runningLog->synchro_result);
        if($result && $result->code == 1) {
            $runningLog->status = 2;
        } else {
            $runningLog->status = 3;
        }
        $runningLog->save();

        if($runningLog->status != 2) {
            //发送失败
            $this->_return(0, '机器开门指令发送失败!');
        } else {
            $this->_return(1, '开门成功!');
        }



    }


    /**
     * 开门
     */
    public function openDoor2()
    {
        $rq = $this->request;
        $device_id = $rq->post('device_id');

        if(!$device_id) {
            $this->_return(0, '设备号不能为空');
        }

        $device = Device::get(['device_id' => $device_id]);
        if(!$device) {
            $this->_return(0, '设备不存在');
        }


        if($device->water_recharge_id != $this->replenishment_id){
            $this->_return(0, '您无权限操作这台设备');
        }


        $rfid = Bucket::where(['device_id' =>$device_id, 'status'=>1])->column('rfid');
        $rfid = implode(',',$rfid);

        $aisle_numeber = DeviceAisle::where(['device_id' =>$device_id])->where('rfid','not in',$rfid)->column('aisle_num');
        $aisle_numeber = implode(',', $aisle_numeber);


        if(!$aisle_numeber) {
            $this->_return(0, '设备无门可开');
        }

        $rechargeLog = WaterRechargeLog::didNotFinish($device_id, $this->replenishment_id);    //查当前是否有进行中的补货任务
        if(!$rechargeLog) {
            //没有创建一个
            $this->_return(0, '请选择创建补水记录');
            ;        }

        //创建硬件发送记录
        $runningLog = [
            'order_id' => $rechargeLog->water_recharge_log_id,
            'device_id' => $device_id,
            'running_type' => 3,
            'device_aisle' => $aisle_numeber,
            'rfid' => $device->motherboard_code,
            'async_result' => '',
        ];

        $runningLog = GoodsRunning::add($runningLog);

        $runningLog->synchro_result = WyjVendingMachine::machineCloudOpen('one_button_unlock',  $device->motherboard_code, $runningLog->id,  $aisle_numeber);
        $result = json_decode($runningLog->synchro_result);
        if($result && $result->code == 1) {
            $runningLog->status = 2;
        } else {
            $runningLog->status = 3;
        }
        $runningLog->save();

        if($runningLog->status != 2) {
            //发送失败
            $this->_return(0, '机器开门指令发送失败!');
        } else {
            $this->_return(1, '开门成功!');
        }



    }


    /**
     * 创建补货记录
     */
    public function createWaterRechargeLog()
    {
        $rq = $this->request;
        $device_id = $rq->post('device_id');

        if(!$device_id) {
            $this->_return(0, '设备号不能为空');
        }

        $device = Device::get(['device_id' => $device_id]);
        if(!$device) {
            $this->_return(0, '设备不存在');
        }


        if($device->water_recharge_id != $this->replenishment_id){
            $this->_return(0, '您无权限操作这台设备');
        }

        $rechargeLog = WaterRechargeLog::didNotFinish($device_id, $this->replenishment_id);

        if($rechargeLog) {
            $rechargeLog->finish();
            if($rechargeLog->number == 0) {
                $rechargeLog->delete();
            }
        }

        $rechargeLog = WaterRechargeLog::add([
            'device_id' => $device_id,
            'macno' => $device->macno,
            'address' => $device->device_address,
            'water_brand_id' => $device->water_brand_id,
            'water_recharge_id' => $this->replenishment_id,
            'water_brand_name' => $device->waterBrand->water_brand_name,
            'water_brand_image' => $device->waterBrand->image,
            'return_number' => $device->empty_bucket_num,
        ]);

        $this->_return(1, '创建成功');

    }


    /**
     * 补水完成
     */
    public function waterRechargeFinish()
    {
        $device_id = $this->request->post('device_id');
        $rechargeLog = WaterRechargeLog::didNotFinish($device_id, $this->replenishment_id);
        if($rechargeLog) {
            $rechargeLog->finish();
            if($rechargeLog->number == 0) {
                $rechargeLog->delete();
            }
        }

        $this->_return(1 ,'补水完成');
    }


    /**
     * 补水记录列表
     */
    public function rechargeLog()
    {
        $rq = $this->request;
        $page = $rq->post('page');
        $pagesize = $rq->post('pagesize');

        $list = WaterRechargeLog::where(['water_recharge_id' => $this->replenishment_id, 'status' => 1])->order('water_recharge_log_id DESC')->page($page,$pagesize)->select();

        $this->_return(1, 'ok', ['list' => $list]);
    }



    public function rechargeLogInfo()
    {
        $id = $this->request->post('id');
        $logModel = WaterRechargeLog::get(['water_recharge_log_id' => $id]);
        if(!$logModel) {
            $this->_return(0, '记录不存在');
        }

        $device = $logModel->device;

        $data = $logModel->getData();
        $data['ctime'] = date('Y-m-d H:i', $data['ctime']);
        $data['device'] = $device->getData();
        $data['water_brand'] = $device->waterBrand;
        $data['list'] = WaterRechargeLogInfo::where(['water_recharge_log_id' => $id])->select();

        $this->_return(1, 'ok', $data);
    }
}