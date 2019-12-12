<?php

namespace app\wxsite\controller;
use app\common\model\Bucket;
use app\common\model\DeviceAisle;
use app\common\model\Ic;
use app\common\model\User;
use app\common\model\UserWalletLog;
use think\Controller;
use think\Db;
use app\common\tool\WecahtOfficialAccount;
use app\common\model\Agent;
use app\common\model\SmsVerify;
use hardware\QslVendingMachine;
use app\common\model\Device;
use limx\tools\wx\JsSdk;
use app\common\model\ReplenishmentClerk;
use app\common\model\GoodsRunning;
use app\common\model\Order;
use app\common\model\Coupon;
use app\common\model\OrderInfo;
use app\common\model\StockNotice;
use app\common\model\PressureGoldOrder;
use app\common\model\SetMeal;
use app\common\model\RechargeOrder;
use app\common\model\WaterRecharge;
use hardware\WyjVendingMachine;
use app\common\model\Ad;
use think\Validate;


class TestController extends BaseController
{

    /**
     * 归还
     */
    public function giveBack()
    {

        $macno = $this->request->post('macno');
        $device = Device::get(['motherboard_code' => $macno]);

        if(!$device) {
            $this->_return(0, '设备不存在于系统中');
        }

        $aisle = DeviceAisle::get(['device_id' => $device->device_id, 'rfid' => '']);


        if(!$aisle) {
            $this->_return(0, '没有空柜可使用');
        }


        //添加开柜记录
        $runningLog  = [
            'running_type' => 2,
            'device_aisle' => $aisle->aisle_num,
            'device_id' => $device->device_id,
            'synchro_result' => '',
        ];
        $running = GoodsRunning::add($runningLog);

        //开柜
        $running->synchro_result = WyjVendingMachine::machineCloudOpen('return', $device->machine_code, $running->id, $aisle->aisle_num);
        $running->save();

        $result = json_decode($running->synchro_result);
        if($result && $result->code == 1) {
            $this->_return(1,'开门中');
        } else {
            $this->_return(0, '开门失败,请联系客服');
        }


    }

    /**
     * 硬件回调
     */
    public function devicenotify(){

        $rq = $this->request;
        $type = $rq->post('type');
        $serial = $rq->post('serial');
        if(!$serial) {
            return;
        }

        $model = GoodsRunning::get($serial);
        $state = $rq->post('state');

        //修改出货流水
        $model->async_result = json_encode($rq->post());
        $model->status = $state == 0 ? 2 : 3;
        $model->save();


        switch ($type)
        {
            case 'openDoor':




                if($model->status == 2)
                {
                    //修改订单
                    $order = $model->order;
                    $order->shipping_status = 1;
                    $order->status = 1;
                    $order->pay_time = time();
                    $order->shipping_time = time();
                    $order->save();

                    //更新设备的柜子状态
                    $aisle = DeviceAisle::get(['device_id' => $model->device_id, 'aisle_num' => $model->device_aisle]);
                    $aisle->rfid = '';
                    $aisle->save();


                    //更新水桶信息
                    $bucket = Bucket::get(['rfid' => $model->rfid]);
                    $bucket->user_id = $order->user_id;
                    $bucket->device_id = 0;
                    $bucket->save();


                    $orderInfo = $model->orderInfo;
                    $orderInfo->shipping_status = 1;
                    $orderInfo->shipping_time = time();
                    $orderInfo->save();


                    //减用户帐号金额, 及可用桶数
                    $user = $order->user;
                    $user->wallet -= $order->price;
                    $user->use_bucket_num++;
                    $user->save();


                    //机器加空框数，减水桶数
                    $device = $model->device;
                    $device->empty_frame_num++;
                    $device->bucket_num--;
                    $device->save();

                    //生成用户钱包日志
                    $logData = [
                        'user_id' => $user->user_id,
                        'type' => 2,
                        'num' => $order->price,
                        'relevance' => $order->order_id,
                        'direction' => 2,
                    ];
                    UserWalletLog::add($logData);
                }
                else
                {
                    //修改订单
                    $order = $model->order;
                    $order->shipping_status = 2;
                    $order->save();

                    //修改订单详情
                    $orderInfo = $model->orderInfo;
                    $orderInfo->shipping_status = 2;
                    $orderInfo->save();
                }

                //通知前端，需要知道是安卓，还是IOS
                break;

            case 'return':

                if($model->status == 2) {
                    //还货开门成功
                } else {
                    //还货开门失败

                }
                //通知前端
                break;

            case 'returnResult':


                if($model->status != 2) {

                    //失败了通知前端
                    $data = [
                        'code'=>0,
                        'msg' => '失败'
                    ];
                    $order = $model->order;
                    $udid = $order->user_id;


                } else {

                    //更新通道数据
                    $rfid = $rq->post('rfid');
                    $aisle = DeviceAisle::get(['device_id' => $model->device_id, 'aisle_num' => $model->device_aisle]);
                    $aisle->rfid = $rfid;
                    $aisle->save();


                    //修改设备信息
                    $device = $model->device;
                    $device->empty_bucket_num++;
                    $device->empty_frame_num--;
                    $device->save();


                    //修改订单详情信息
                    $orderInfo = OrderInfo::get(['rfid' => $rfid, 'shipping_status' => 1]);
                    $orderInfo->return_time = time();
                    $orderInfo->return_device_id = $device->device_id;
                    $orderInfo->shipping_status = 4;
                    $orderInfo->save();


                    //修改订单信息
                    $order = $orderInfo->order;
                    $order->status = 3;
                    $order->save();


                    //修改用户信息
                    $user = $order->user;
                    $user->use_bucket_num--;
                    $user->save();


                    //修改桶信息
                    $bucket = Bucket::get(['rfid' => $rfid]);
                    $bucket->user_id = 0;
                    $bucket->status = 2;
                    $bucket->device_id = $device->device_id;
                    $bucket->area_id = $device->area_id;
                    $bucket->save();


                    $data = [
                        'code'=>1,
                        'msg' => '成功'
                    ];

                    $udid = $order->user_id;

                    //websocket 通知前端
                    //WyjVendingMachine::noticeH5($data,$udid);

                }
                break;
        }

    }


    public function test()
    {
        set_time_limit(600);
        $room_order =  Db('room_order')->where(['leave_time'=>['LT',date('Y-m-d H:i:s',time()),'order_status'=>['in',array(1,2)]]])->select();
//        $de = new DeviceController();
//        $token = $de->refreshToken();
        foreach ($room_order as $k => $v){

            $a = Db('room_order')->where(['id'=>$v['id']])->update(['order_status'=>3]);
            $b = Db('room_reservation')->where(['rid'=>$v['id'],'status'=>['in',array(1,2)]])->update(['status'=>3]);
            $c = Db('room')->where(['id'=>$v['room_id']])->update(['order_status'=>1]);
//
        }
    }
}