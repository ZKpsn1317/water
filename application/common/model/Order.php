<?php
namespace app\common\model;

use think\Model;
use app\common\validate\OrderValidate;
use think\Db;

class Order extends Model
{
    public static $statusOption = [
        2 => '未支付',
        1 => '支付成功',
        3 => '订单完成'
    ];

    public static $shippingStatusOption = [
        3 => '未出货',
        2 => '出货失败',
        1 => '出货成功',
    ];

    public static function add($data)
    {
        $validate = new OrderValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('order_id,user_id,macno,address,device_id,agent_id,area_id,price,ctime,shipping_status,pay_time,shipping_time,return_time,return_device_id,client')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new OrderValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('order_id,user_id,macno,address,device_id,agent_id,area_id,price,ctime,shipping_status,pay_time,shipping_time,return_time,return_device_id,client')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    
    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getPayTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


    public function getShippingTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }

	public function device()
    {
        return $this->hasOne('device', 'device_id', 'device_id');
    }

	public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

	public function area()
    {
        return $this->hasOne('area', 'area_id', 'area_id');
    }

    public function orderInfo()
    {
        return $this->hasOne('order_info', 'order_id', 'order_id');
    }


    //不创建订单只反馈一些数据给用户看
    public static function createOrder2($user, $device_id)
    {
        try{

            if(!$device_id) {
                throw new \think\Exception('上传设备参数');
            }

            $device = Device::get($device_id);

            if(!$device) {
                throw new \think\Exception('设备ID无效');
            }
            if($device->bucket_num < 1) {
                throw new \think\Exception('设备没有水了,请换一台设备');
            }
            if(!$device->motherboard_code) {
                throw new \think\Exception('设备还未绑定机身码');
            }


            $user_ = UserWallet::get(['user_id' => $user->user_id, 'agent_id' => $device->agent_id]);

            if( !$user_->getData('user_type_id') ) {
                throw new \think\Exception('您未支付押金，请支付押金再购水');
            }

            if( $user_->bucket_num - $user_->use_bucket_num < 1)
            {
                if($user_->userType->repeat == 2) {
                    //这个用户类型不可以通过，再次缴纳押金，增加可以使用的桶数
                    throw new \think\Exception($user_->bucket_num ? '您好，请先还回空桶！' : '您好，您没有可借桶数');
                } else {
                    throw new \think\Exception('您有空桶未还，需要再次支付押金');
                }
            }




            $waterBrand = WaterBrand::get($device->water_brand_id);

            if(!$waterBrand) {
                throw new \think\Exception('水ID无效');
            }

            if($user_->wallet < $waterBrand->price) {
                throw new \think\Exception('帐户余额不足' . $waterBrand->price . '请先充值');
            }


            //$aisle = DeviceAisle::where(['device_id' => $device->device_id])->where('rfid', '<>', '')->find();
            //生成订单商品详情
            $bucket = Bucket::where(['device_id' => $device->device_id, 'status' =>1])->find();
            if(empty($bucket)) {
                throw new \think\Exception('无水可买');
            }
            $aisle = DeviceAisle::where(['device_id' => $device->device_id])->where(['rfid' => $bucket->rfid])->find();

            $orderInfoData = [
                'rfid' => $aisle->rfid,
                'water_brand_name' => $waterBrand->water_brand_name,
                'price' => $waterBrand->price,
                'img' => $waterBrand->image,
                'device_aisle' => $aisle->aisle_num,
            ];

            return $orderInfoData;

        }catch (\Exception $err) {
            throw $err;
        }
    }

    public static function createOrder($user, $device_id)
    {
        try{

            if(!$device_id) {
                throw new \think\Exception('上传设备参数');
            }

            $device = Device::get($device_id);
            if(!$device) {
                throw new \think\Exception('设备ID无效');
            }
            if($device->bucket_num < 1) {
                throw new \think\Exception('设备没有水了,请换一台设备');
            }
            if(!$device->motherboard_code) {
                throw new \think\Exception('设备还未绑定机身码');
            }
            if(empty($device->agent_id)) {
                throw new \think\Exception('设备还未绑定代理');
            }

            $user_ = UserWallet::get(['user_id' => $user->user_id, 'agent_id' => $device->agent_id]);

            if(empty($user_)) {
                throw new \think\Exception('余额不足，请先在手机上充值!');
            }
          
            if( $user_->user_type_id == 0 && $user_->bucket_num == 0) {
                throw new \think\Exception('您未支付押金，请支付押金再购水');
            }

            if( $user_->bucket_num - $user_->use_bucket_num  < 1)
            {
                if($user_->userType->repeat == 2) {
                    //这个用户类型不可以通过，再次缴纳押金，增加可以使用的桶数
                    throw new \think\Exception($user_->bucket_num ? '您好，请先还回空桶！' : '您好，您没有可借桶数');
                } else {
                    throw new \think\Exception('您有空桶未还，需要再次支付押金');
                }
            }



            $waterBrand = WaterBrand::get($device->water_brand_id);

            if(!$waterBrand) {
                throw new \think\Exception('水ID无效');
            }


            if($user_->wallet < $waterBrand->price) {
                throw new \think\Exception('帐户余额不足' . $waterBrand->price . '请先充值');
            }

            //创建订单
            Db::startTrans();

            $orderData = [
                'user_id' => $user->user_id,
                'macno' => $device->macno,
                'address' => $device->device_address,
                'device_id' => $device->device_id,
                'agent_id' => $device->agent_id,
                'area_id' => $device->area_id,
                'price' => $waterBrand->price,
                'status' => 2,
                'client' => \app\common\tool\Request::isAndroid() ? 2 : 1
            ];
            $order = Order::add($orderData);

            //生成订单商品详情
            $bucket = Bucket::where(['device_id' => $device->device_id, 'status' =>1,])->order('use_time asc')->find();

            if(empty($bucket)) {
                throw new \think\Exception('无水可买');
            }
            $aisle = DeviceAisle::where(['device_id' => $device->device_id])->where(['rfid' => $bucket->rfid])->find();

            $orderInfoData = [
                'rfid' => $aisle->rfid,
                'water_brand_name' => $waterBrand->water_brand_name,
                'price' => $waterBrand->price,
                'order_id' => $order->order_id,
                'img' => $waterBrand->image,
            ];
            $orderInfo = OrderInfo::add($orderInfoData);


            //创建硬件发送记录
            $runningLog = [
                'order_id' => $order->order_id,
                'rfid' => $aisle->rfid,
                'device_id' => $device['device_id'],
                'running_type' => 1,
                'device_aisle' => $aisle->aisle_num,
                'order_info_id' => $orderInfo->order_info_id,
                'async_result' => '',
            ];

            $runningLog = GoodsRunning::add($runningLog);
            Db::commit();

            //发送命令到机器云
            $runningLog->synchro_result = \hardware\WyjVendingMachine::machineCloudOpen('openDoor', $device->motherboard_code, $runningLog->id, $aisle->aisle_num);
            $result = json_decode($runningLog->synchro_result);

            //用于测试 --- 后面需要删除
            /*$runningLog->synchro_result = '{"code":1}';
            $result = json_decode($runningLog->synchro_result);*/

            if($result && $result->code == 1) {
                $runningLog->status = 2;
            } else {
                $runningLog->status = 3;
            }
            $runningLog->save();


            if($runningLog->status != 2) {
                //发送失败
                throw new \think\Exception('机器开门失败');
            }

            return ['order' => $order, 'orderInfo' => $orderInfo, 'runningLog' => $runningLog ];

        }catch (\Exception $err) {
            Db::rollback();
            throw $err;
        }
    }


    public function payOrder()
    {
        $device = $this->device;
        if(!$device) {
            throw new \think\Exception('设备ID无效');
        }
        if(!$device->agent_id) {
            throw new \think\Exception('设备未设置代理,不能销售');
        }
        if($device->bucket_num < 1) {
            throw new \think\Exception('设备没有水了,请换一台设备');
        }
        if(!$device->motherboard_code) {
            throw new \think\Exception('设备还未绑定机身码');
        }

        $user = $this->user;
        $user_ = UserWallet::get(['user_id' => $user->user_id, 'agent_id' => $device->agent_id]);
        if( !$user_->user_type_id || !$user_->bucket_num ) {
            throw new \think\Exception('您未支付押金，请支付押金再购水');
        }


        if( $user_->bucket_num - $user_->use_bucket_num < 1)
        {
            if($user_->userType->repeat == 2) {
                //这个用户类型不可以通过，再次缴纳押金，增加可以使用的桶数
                throw new \think\Exception($user_->bucket_num ? '您好，请先还回空桶！' : '您好，您没有可借桶数');
            } else {
                throw new \think\Exception('您有空桶未还，需要再次支付押金');
            }
        }


        if($user_->wallet < $user_->price) {
            throw new \think\Exception('帐户余额不足' . $this->price . '请先充值');
        }

        //生成订单商品详情
        $aisle = DeviceAisle::where(['device_id' => $device->device_id])->where('rfid', '<>', '')->find();

        //创建硬件发送记录
        $runningLog = [
            'order_id' => $this->order_id,
            'rfid' => $aisle->rfid,
            'device_id' => $device['device_id'],
            'running_type' => 1,
            'device_aisle' => $aisle->aisle_num,
            'order_info_id' => $this->orderInfo->order_info_id,
            'async_result' => '',
        ];

        $runningLog = GoodsRunning::add($runningLog);


        //发送命令到机器云
        $runningLog->synchro_result = \hardware\WyjVendingMachine::machineCloudOpen('openDoor', $device->motherboard_code, $runningLog->id, $aisle->aisle_num);
        $result = json_decode($runningLog->synchro_result);

        if($result && $result->code == 1) {
            $runningLog->status = 2;
        } else {
            $runningLog->status = 3;
        }
        $runningLog->save();


        if($runningLog->status != 2) {
            //发送失败
            throw new \think\Exception('机器开门失败');
        }

        return ['order' => $this, 'orderInfo' => $this->orderInfo, 'runningLog' => $runningLog ];

    }


	

}
