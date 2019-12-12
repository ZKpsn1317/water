<?php
/**
 * Created by PhpStorm.
 * User: 韩令恺
 * Date: 2018/5/17 0017
 * Time: 16:12
 */

namespace app\wxsite\controller;

use app\common\model\DeviceAisle;
use app\common\model\SetMeal;
use app\common\model\UserPressureGold;
use \think\Db;
use app\common\model\SmsVerify;
use app\common\model\User;
use app\common\tool\Upload;
use app\common\model\Order;
use app\common\model\Device;
use app\common\model\OrderInfo;
use app\common\tool\WecahtOfficialAccount;
use app\common\model\Intention;
use app\common\model\WaterBrand;
use app\common\model\UserType;
use app\common\model\PressureGoldOrder;
use app\common\model\UserWalletLog;
use app\common\model\DeliveryOrdern;
use app\common\model\RechargeOrder;
use app\common\model\UserRefund;
use think\Session;
use app\common\model\Ic;
use app\common\model\Bucket;
use think\Validate;
use app\common\model\Hitch;
use app\common\tool\ProcessData;
use app\common\model\UserWallet;
use app\common\model\Agent;
use app\common\model\UserCouponType;
use app\common\model\Coupon;

class SettingController extends BaseController
{
    protected $user;

    public function _initialize ()
    {
        header("Access-Control-Allow-Origin:*");
        $rq = $this->request;

        $token = $rq->post( 'token' );
        $user  = User::get( [ 'token' => $token ] );
        if ( !$user ) {
            $this->_return( 101, 'token无效' );
        }

        $this->user    = $user;
        $this->user_id = $user->user_id;


    }


    /**
     * 修改手机号码
     */
    protected function changeMobile ()
    {
        $rq    = $this->request;
        $phone = $rq->post( 'phone' );
        $code  = $rq->post( 'code' );
        $fuid  = $rq->post( 'fuid' );
        try {
            if ( !$phone || !$code ) {
                throw new \think\Exception( '请输入手机、验证码!' );
            }

            //验证验证码
            $code = SmsVerify::verifyCode( $phone, $code, SmsVerify::SMS_TYPE_MODIFY );

            //修改密码
            $this->user->changeMobile( $phone );
            $code->delete();                    //删除验证码

            //判断邀请用户是否添加优惠卷
            $agent = UserWallet::where('user_id',$fuid)->order('ctime_u')->find();
            if($agent){
                $coupon = Agent::where('agent_id',$agent->agent_id)->find();
                if($coupon->inva_coupon){
                    Coupon::add([
                        'agent_id' => $agent->agent_id,
                        'name'  => '邀请注册成功赠送优惠卷',
                        'user_id' => $fuid,
                        'price'  => $coupon->inva_couponv,
                        'type'  => 2,
                        'status' => 2,
                        'stime' => $coupon->inva_coupont,
                    ]);
                }
            }
            $this->_return( 1, '修改成功' );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }
    }

    /**
     * 修改手机号码
     */
    protected function getCoupon()
    {
        $rq    = $this->request;
        $user = $this->user;
        $agent_id = $rq->post('agent_id');
        if($agent_id){
            $coupon = Coupon::where('user_id',$user->user_id)->where('agent_id',0)->whereOr('agent_id',$agent_id)->order('status asc')->select();
        }else{
            $coupon = Coupon::where('user_id',$user->user_id)->order('status asc')->select();
        }
        foreach ($coupon as $key => $value) {
            if($value->utime == 0){
                if(time() >= $value->getData('ctime') + $value->stime * 86400){
                    $value->status = 3;
                }
            }
        }
        $this->_return( 1, '获取成功',$coupon );
    }
    /**
     * 发送修改支付密码验证码
     */
    protected function sendSetPassCode ()
    {

        $user = $this->user;
        if ( !$user->mobile ) {
            $this->_return( 0, '请先绑定手机号码' );
        }


        try {

            $tmp = 'SMS_143635221';
            $rst = SmsVerify::setPass( $user->mobile, $tmp );

            if ( $rst === FALSE ) {
                throw new \think\Exception( '验证码发送失败,请稍后再试!' );
            }

            $this->_return( 1, '发送成功' );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }

    }

    /**
     * 设置支付密码
     */
    public function setPass ()
    {

        $post = $this->request->post();

        try {

            $validate = new Validate( [
                'pass|密码'  => 'require|min:6',
                'code|验证码' => 'require',
            ] );

            if ( !$validate->check( $post ) ) {
                throw new \think\Exception( $validate->getError() );
            }

            $user = $this->user;

            //验证验证码
            $code = SmsVerify::verifyCode( $user->mobile, $post['code'], SmsVerify::SMS_TYPE_GETPASS );

            //修改密码
            $user->pay_pass = User::createPassword( $post['pass'] );
            $user->save();
            $code->delete();                    //删除验证码

            $this->_return( 1, '修改成功' );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }
    }


    /**
     * 取用户信息
     */
    protected function getUserInfo ()
    {
        $data = User::where( [ 'user_id' => $this->user->user_id ] )
            ->field( 'head_img,nickname,mobile' )
            ->find();

        if ( !$data ) {
            $this->_return( 0, '未找到资料' );
        }

        $user               = $this->user;
        $list = Bucket::where( [ 'user_id' => $user->user_id ] )
            ->field( 'rfid,0 AS is_give ' )
            ->select();
        $use_bucket_count = count( $list );
        $count = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->sum( 'give_bucket_num' );
        $bucket_count = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->sum( 'bucket_num' );
        $use_bucket_num = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->sum( 'use_bucket_num' );
        //判断分享是否开启
        $coupon = UserCouponType::where('coupon_type_id',2)->find();
        $data['coupon'] = $coupon->status;
        $data['head_img']   = Upload::imageAddDomain( $data['head_img'], $this->request->domain() );
        $data['bind_phone'] = $user->mobile ? '1' : '0';       //是否绑定了手机号码
        $data['identity']   = $user->getData( 'type' );             //用户类型
        $data['userType']   = $user->userType ? : [];
        $data['wallet']     = $user->wallet;
        $data['bucket_count'] = $bucket_count;
        $data['use_bucket_num'] = $bucket_count-$count;
        $data['user_id'] = $user->user_id;
        $this->_return( 1, 'ok', $data );
    }


    /**
     * 余额接口
     */
    public function wallet ()
    {
        $list = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->with( 'agent' )->field( 'agent_id,wallet' )->select();

        $agent_list = $list ? [] : Agent::field( 'agent_id,agent_name' )->select();

        $this->_return( 1, '获取成功', [ 'list' => $list, 'agent_list' => $agent_list ] );
    }


    /**
     * 代理列表
     */
    public function agentList ()
    {
        $list = UserWallet::join( 'dlc_agent', 'dlc_agent.agent_id = dlc_user_wallet.agent_id' )
            ->field( 'dlc_agent.agent_id,dlc_agent.agent_name,pressure_gold' )
            ->where( [ 'user_id' => $this->user->user_id ] )
            ->order( 'id desc' )
            ->select();

        $data = [];
        foreach ( $list as $vo ) {

            $data[] = [ 'agent' => $vo, 'price' => $vo['pressure_gold'], 'agent_id' => $vo['agent_id'] ];


        }

        /*$list = Agent::join('dlc_user_wallet', "dlc_agent.agent_id = dlc_user_wallet.agent_id")
            ->where(['dlc_user_wallet.user_id' => $this->user->user_id])
            ->field('dlc_agent.agent_id,agent_name')
            ->select();*/

        $this->_return( 1, '获取成功', [ 'list' => $data ] );
    }


    /**
     * 可选身份列表
     */
    protected function identityList ()
    {
        $agent_id = $this->request->post( 'agent_id' );
        if ( !$agent_id ) {
            $this->_return( 0, '请上传代理ID' );
        }
        $list = UserType::where( [ 'status' => 1, 'agent_id' => $agent_id ] )->order( 'sort DESC' )->select();
        foreach ( $list as $key => $row ) {
            $list[$key]['img'] = Upload::imageAddDomain( $row['img'] );
        }
    if(!$list)$this->_return(0, '没有可选择类型, 请联系管理员');
        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * 可选身份列表
     */
    protected function identity ()
    {
        $id              = $this->request->post( 'id' );
        $userType        = UserType::get( [ 'user_type_id' => $id ] );
        $userType['img'] = Upload::imageAddDomain( $userType['img'] );
        $this->_return( 1, 'ok', $userType );
    }


    /**
     * 支付押金
     */
    protected function payRessureGold ()
    {
        $rq       = $this->request;
        $id       = $rq->post( 'id' );
        $deviceId = $rq->post( 'device_id' );


        try {
            if ( !$id ) {
                throw new \think\Exception( '请绑定用户类型' );
            }
            if ( !$deviceId ) {
                //throw new \think\Exception( '请上传设备ID' );
            }

            $userType = UserType::getValid( $id );

            if ( !$userType ) {
                throw new \think\Exception( '用户类型不存在' );
            }

            $user = $this->user;

            $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $userType->agent_id ] );

            $agent = $userType->agent;

            if ( $user_->getData( 'user_type_id' ) && $userType->repeat == 2 ) {
                throw new \think\Exception( '您不能通过再次缴纳押金增加可以使用的桶数' );
            }

            //通过设备ID获取场地ID
            $device = Device::get( $deviceId );
            $areaId = $device ? $device->area_id : 0;

            //添加押金订单
            $orderData = [
                'user_id'        => $user->user_id,
                'user_type_id'   => $userType->user_type_id,
                'price'          => $userType->pressure_gold,
                'user_type_name' => $userType->user_type_name,
                'bucket_num'     => $userType->bucket_num,
                'agent_id'       => $userType->agent_id,
                'area_id'        => $areaId,
            ];

            $order = PressureGoldOrder::add( $orderData );

            $paydata = [
                'openid'       => $user_->openid,
                'body'         => '山西桶装水',
                'out_trade_no' => 'rg' . $order->pressure_gold_order_id,
                'total_fee'    => $order->price,
                'notify_url'   => config( 'pay_ressure_gold_nofity_url' )
            ];

            $wxConfig = [
                'wx_mchid' => $agent->wx_mchid,
                'wx_appid' => $agent->wx_appid,
                'wx_key'   => $agent->wx_key,
            ];

            $payParams = WecahtOfficialAccount::getH5PayParams( $paydata, $wxConfig );

            $this->_return( 1, 'ok', [ 'params' => $payParams ] );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }


    }


    /**
     * 押金列表
     */
    protected function goldPressingList ()
    {
        $query = UserPressureGold::with( 'agent,user_type' )
            ->order( 'user_pressure_gold_id desc' )
            ->where( [ 'user_id' => $this->user->user_id, 'status' => 1 ] );


        $agent_id = $this->request->post( 'agent_id' );

        $data = [];
        if ( $agent_id ) {
            $data['userType'] = UserWallet::where( [ 'agent_id' => $agent_id, 'user_id' => $this->user->user_id ] )->find();
        }

        $agent_id && $query->where( [ 'agent_id' => $agent_id ] );
        $data['list'] = $query->select();

        $this->_return( 1, 'ok', $data );
    }


    /**
     * 退押金
     */
    protected function decompressionGold ()
    {
        $id    = $this->request->post( 'pressure_gold_id' );
        $user  = $this->user;
        $model = UserPressureGold::get( [ 'user_pressure_gold_id' => $id, 'user_id' => $user->user_id ] );

        try {
            if ( !$model ) {
                throw new \think\Exception( '没有记录' );
            }

            if ( $model->status != 1 ) {
                throw new \think\Exception( '该押金已申请退款' );
            }


            $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $model->agent_id ] );

            $order_bucket_num = $model->order->bucket_num;  //订单的桶数

            $have_bucket_num = $user_->bucket_num - $user_->use_bucket_num;   //可以使用的桶数


            if ( $have_bucket_num - $user_->give_bucket_num < $order_bucket_num ) {
                //不能退押金
                if ( $user_->give_bucket_num ) {
                    //虽然可用的桶数 >= 押金的桶数, 因为里面有些赠送的桶，所以不能退
                    throw new \think\Exception( '您现在可以使用的桶为' . $have_bucket_num . '押金的桶是' . $order_bucket_num . '个,因为有' . $user_->give_bucket_num
                        . '是赠送的,所以不能退这笔押金' );
                } else {
                    throw new \think\Exception( '您还' . ( $order_bucket_num - $have_bucket_num ) . '个桶,便可以退这笔押金' );
                }
            }

            //修改用户数据
            Db::startTrans();
            $user_->pressure_gold -= $model->price;
            $user_->bucket_num    -= $order_bucket_num;
            $user_->save();

            //添加退押金申请
            $refund     = [
                'user_id'           => $user->user_id,
                'recharge_order_id' => $model->recharge_order_id,
                'price'             => $model->price,
            ];
            $userRefund = UserRefund::add( $refund );

            //修改押金状态为，退款中
            $model->status = 2;
            $model->save();

            Db::commit();

            try {
                $order    = $model->order;
                $agent    = Agent::get( $model->agent_id );
                $certPath = ROOT_PATH . substr( str_replace( '/', DS, $agent->certPath ), 1 );
                $keyPath  = ROOT_PATH . substr( str_replace( '/', DS, $agent->keyPath ), 1 );
                if ( !is_file( $certPath ) || !is_file( $keyPath ) ) {
                    exception( '请先配置证书' );
                }

                $wxconfig = [
                    'wx_appid' => $agent->wx_appid,
                    'wx_mchid' => $agent->wx_mchid,
                    'wx_key'   => $agent->wx_key,
                    'certPath' => $certPath,
                    'keyPath'  => $keyPath,
                ];


                trace( '退款前' );
                trace( $wxconfig );
                trace( [ 'pay_number' => $order->trade_number, 'pay_money' => $order->price, 'back_money' => $order->price ] );

                $result = WecahtOfficialAccount::wechatrefund( [ 'pay_number' => $order->trade_number, 'pay_money' => $order->price, 'back_money' => $order->price ], $wxconfig );

                if ( $result == 'SUCCESS' ) {
                    $model->rtime  = time();
                    $model->status = 3;
                    $model->save();

                    //退款 记录改为已处理
                    $userRefund->status = 2;
                    $userRefund->save();

                } else {
                    $model->status = 4;
                    $model->rtime  = time();
                    $model->result = json_encode( $result );
                    $model->save();
                }

            } catch ( \think\Exception $err ) {
                $model->status = 4;
                $model->rtime  = time();
                $model->result = $err->getMessage();
                $model->save();
                throw $err;
            }

            $this->_return( 1, '退款成功' );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }

    }


    /**
     *  购水
     */
    protected function buyWater ()
    {

        $device_id = $this->request->post( 'device_id' );
        if ( !$device_id ) {
            $macno = $this->request->post( 'macno' );
            if ( $macno && $device = Device::get( [ 'motherboard_code' => $macno ] ) ) {
                $device_id = $device->device_id;
            }
        }

        try {
            extract( Order::createOrder( $this->user, $device_id ) );
            $this->_return( 1, '开门中!', [
                'rfid'         => $runningLog->rfid,
                'device_aisle' => $runningLog->device_aisle,
                'order'        => $order,
                'runningLog'   => $runningLog,
                'order_info '  => $orderInfo,
            ] );
        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }

    }


    /**
     *  购水
     */
    public function createOrder ()
    {

        $device_id = $this->request->post( 'device_id' );
        if ( !$device_id ) {
            $macno = $this->request->post( 'macno' );
            if ( $macno && $device = Device::get( [ 'motherboard_code' => $macno ] ) ) {
                $device_id = $device->device_id;
            }
        }

        try {
            $data = Order::createOrder2( $this->user, $device_id );
            $this->_return( 1, 'ok', $data );
        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }

    }


    /**
     * 充值
     */
    public function recharge ()
    {
        $setmeal_id = $this->request->post( 'setmeal_id/i' );
        $coupon_id = $this->request->post( 'coupon_id/i' );
        try {
            if ( empty( $setmeal_id ) ) {
                exception( '请上传套餐ID' );
            }

            $setmeal = SetMeal::get( $setmeal_id );
            if ( empty( $setmeal ) ) {
                exception( '套餐不存在' );
            }

            if ( $setmeal->price <= 0 ) {
                throw new \think\Exception( '套餐金额必须大于0' );
            }

            $user  = $this->user;
            $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $setmeal->agent_id ] );
            //优惠卷处理
            $coupon_price = 0;
            if($coupon_id){
                $coupon = Coupon::where(['user_id' => $user->user_id,'coupon_id' => $coupon_id])->find();
                if(!$coupon){
                    throw new \think\Exception( '优惠卷不存在' );
                }
                //判断是否失效
                if(time() >= $coupon->getData('ctime') + $coupon->stime * 86400){
                    throw new \think\Exception( '优惠卷失效' );
                }
                $coupon_price = $coupon->price;
                if($setmeal->price - $coupon_price <=0){
                    $this->_return( 0, '支付失败,支付数量不得等于0');
                }
            }
            //创建订单
            $orderData = [
                'user_id'     => $user->user_id,
                'price'       => $setmeal->price - $coupon_price,
                'set_meal_id' => $setmeal_id,
                'agent_id'    => $setmeal->agent_id,
                'give_price'  => $setmeal->give_price,
                'bucket_num'  => $setmeal->give_bucket,
                'coupon'   => $coupon_id ? $coupon_id : 0,
            ];
            $order     = RechargeOrder::add( $orderData );

            $paydata = [
                'openid'       => $user_->openid,
                'body'         => '山西桶装水',
                'out_trade_no' => 'rh' . $order->recharge_order_id,
                'total_fee'    => $order->price,
                'notify_url'   => config( 'pay_recharge_nofity_url' )
            ];


            $agent = Agent::get( [ 'agent_id' => $setmeal->agent_id ] );
            if ( !$agent || $agent->status == 2 ) {
                exception( '未绑定代理,或代理状态异常不能进行充值' );
            }

            $wxConfig = [
                'wx_mchid' => $agent->wx_mchid,
                'wx_appid' => $agent->wx_appid,
                'wx_key'   => $agent->wx_key,
            ];

            $payParams = WecahtOfficialAccount::getH5PayParams( $paydata, $wxConfig );

            $this->_return( 1, 'ok', [ 'params' => $payParams ] );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }


    }


    /**
     * IC卡列表
     */
    public function icCardList ()
    {

        $list = Ic::where( [ 'user_id' => $this->user->user_id ] )->select();
        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * IC卡，添加，修改，删除接口
     */
    public function icCard ()
    {
        $rq       = $this->request;
        $icNumber = $rq->post( 'icNumber' );
        $icId     = $rq->post( 'icId' );

        try {
            if ( $icNumber && $icId ) {
                $model = Ic::get( [ 'ic_id' => $icId, 'user_id' => $this->user->user_id ] );
                if ( !$model ) {
                    throw new \think\Exception( 'IC卡不存在' );
                }
                $model->change( [ 'car_number' => $icNumber ] );
            } else if ( $icNumber ) {
                Ic::add( [ 'user_id' => $this->user->user_id, 'car_number' => $icNumber ] );
            } else if ( $icId ) {
                $model = Ic::get( [ 'ic_id' => $icId, 'user_id' => $this->user->user_id ] );
                if ( !$model ) {
                    throw new \think\Exception( 'IC卡不存在' );
                }
                $model->del();
            } else {
                throw new \think\Exception( '请上传参数' );
            }

            $this->_return( 1, '完成' );
        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }

    }


    /**
     * 钱包明细
     */
    public function walletLog ()
    {
        $rq       = $this->request;
        $page     = $rq->post( 'page' );
        $pagesize = $rq->post( 'pagesize' );
        $list     = UserWalletLog::where( [ 'user_id' => $this->user->user_id ] )->order( 'id DESC' )->page( $page, $pagesize )->select();
        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * 支付订单列表
     */
    public function buyOrder ()
    {
        $rq       = $this->request;
        $page     = $rq->post( 'page' );
        $pagesize = $rq->post( 'pagesize' );

        $list = Order::with( 'orderInfo' )
            ->where( [ 'user_id' => $this->user->user_id ] )
            ->order( 'order_id DESC' )
            ->page( $page, $pagesize )
            ->select();

        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * 支付订单详情
     */
    public function buyOrderInfo ()
    {

        $order_id = $this->request->post( 'order_id' );
        if ( !$order_id ) {
            ;
            $this->_return( 0, '请上传参数' );
        }

        $order = Order::with( 'orderInfo.deliveryOrdern' )
            ->where( [ 'order_id' => $order_id, 'user_id' => $this->user->user_id ] )
            ->find();

        $this->_return( 1, 'ok', $order );
    }


    /**
     * 我的水桶
     */
    public function myBucket ()
    {
        $list = Bucket::where( [ 'user_id' => $this->user->user_id ] )
            ->field( 'rfid,0 AS is_give ' )
            ->select();

        $use_bucket_count = count( $list );

        $count = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->sum( 'give_bucket_num' );

        $bucket_count = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->sum( 'bucket_num' );

        $use_bucket_num = UserWallet::where( [ 'user_id' => $this->user->user_id ] )->sum( 'use_bucket_num' );

        for ( $give_bucket_num = $count, $i = 0; $use_bucket_count && $give_bucket_num; $give_bucket_num--, $use_bucket_count--, $i++ ) {
            $list[$i]['is_give'] = 1;
        }

        $this->_return( 1, 'ok', [ 'list' => $list, 'bucket_sum' => $bucket_count, 'use_bucket_num' => $bucket_count - $count ] );

    }


    /**
     * 修改用户资料
     */
    protected function modifyData ()
    {


        $data = $this->request->post();
        try {
            $this->user->modify( $data );
            $headImg = Upload::imageAddDomain( $this->user->head_img, $this->request->domain() );
            $this->_return( 1, '修改成功', [
                'head_img' => $headImg,
                'email'    => $this->user->email,
                'nickname' => $this->user->nickname,
                'mobile'   => $this->user->mobile,
            ] );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }


    }


    /**
     * 意见反馈
     */
    protected function hitch ()
    {

        $rq = $this->request;

        $data            = $rq->post();
        $data['user_id'] = $this->user_id;
        try {
            $model = Hitch::add( $data );
            $this->_return( 1, '提交成功' );
        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }


    }


    /**
     * 取信息列表
     */
    protected function getMessage ()
    {
        $page = intval( $this->request->post( 'page' ) );
        $page = $page <= 0 ? 1 : $page;
        //$data = DeviceWarningPushLog::where('user_id', $this->user->user_id)
        $data = DeviceWarningPushLog::field( 'id,content,ctime,read' )
            ->order( 'id DESC' )
            ->page( $page, 20 )
            ->select();

        $this->_return( 1, 'ok', [ 'list' => $data, 'page' => $page ] );
    }


    /**
     * 信息详情
     */
    protected function messageInfo ()
    {
        $id    = $this->request->post( 'id' );
        $model = DeviceWarningPushLog::get( [ 'id' => $id ] );
        if ( $model ) {
            $model->read = 1;
            $model->save();
            $this->_return( 1, 'ok', [
                'id'      => $model->id,
                'read'    => $model->read,
                'content' => $model->content,
                'ctime'   => $model->ctime,
            ] );
        } else {
            $this->_return( 0, '未找到内容' );
        }


    }


    /**
     * 设备详情
     */
    public function deviceInfo ()
    {
        $macno = $this->request->post( 'macno' );

        if ( !$macno ) {
            $this->_return( 0, '请上传设备编号' );
        }

        $device = Device::with( 'water_brand' )->where( [ 'macno' => $macno ] )->find();

        if ( !$device ) {
            $this->_return( 0, '设备不存在' );
        }

        if ( empty( $device->agent_id ) ) {
            $this->_return( 0, '设备未设置代理,不能使用' );
        }


        $user = $this->user;

        $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $device['agent_id'] ] );


        if ( empty( $user_ ) ) {
            $appid  = $device->agent->wx_appid;
            $device = $device->toArray();

            //用户未在该代理下注册
            $device['user'] = [
                'openid' => '',
                'appid'  => $appid
            ];

        } else {
            $device = $device->toArray();

            $device['user'] = [
                'wallet'                 => $user_->wallet,
                'unpaid_pressure'        => empty( $user_->bucket_num ) ? '1' : '0',                      //未交押金
                'unreturned_barrel'      => $user_->bucket_num <= $user_->use_bucket_num ? '1' : '0',  //未还桶
                'no_empty_cabinets'      => empty( $device['empty_frame_num'] ) ? '1' : '0',             //没有空框
                'not_sufficient_funds'   => $user_->wallet < $device['water_brand']['price'] ? '1' : '0',     //余额不足
                'repeated_pressure_gold' => !empty( $user_->userType->repeat ) && $user_->userType->repeat == 1 ? '1' : '0',    //就否可以通过再交押金，获得更多的桶数
                'user_type_id'           => $user_->user_type_id,
                'openid'                 => $user_->openid,
            ];
        }


        $this->_return( 1, '设备消息获取成功', $device );


    }


    /**
     * 把用户注册到某个代理下面
     */
    public function registerUser ()
    {
        $rq       = $this->request;
        $agent_id = $rq->post( 'agent_id' );
        $code     = $rq->post( 'code' );
        if ( !$agent_id ) {
            $this->_return( 0, '上传上代理ID' );
        }

        if ( !$code ) {
            $this->_return( 0, '上传上CODE' );
        }

        $agent = Agent::get( $agent_id );
        if ( !$agent || $agent->status == 2 ) {
            $this->_return( 0, '代理无效,不能进行该操作' );
        }

        $wxconfig = [
            'wx_appid'     => $agent->wx_appid,
            'wx_appsecret' => $agent->wx_appsecret,
        ];

        //取用户OPENid
        $result = WecahtOfficialAccount::getOpenid( $code, $wxconfig );


        if ( !empty( $result->errcode ) ) {
            $this->_return( 0, 'code无效' );
        }

        $openid = $result->openid;
        $user   = UserWallet::get( [ 'agent_id' => $agent->agent_id, 'openid' => $openid ] );

        if ( !$user ) {
            //新用户添加到数据库
            $data = [
                'user_id'  => $this->user->user_id,
                'agent_id' => $agent_id,
                'openid'   => $openid,
            ];
            ( new UserWallet() )->save( $data );   
            //新用户注册赠送优惠卷
            if($agent->re_coupon > 0){
                Coupon::add([
                    'agent_id' => $agent_id,
                    'name'  => '注册成功赠送优惠卷',
                    'user_id' => $this->user->user_id,
                    'price'  => $agent->re_couponv,
                    'type'  => 1,
                    'status' => 2,
                    'stime' => $agent->re_coupont,
                ]);
            }
        }
        $this->_return( 1, '绑定成功', [
            'openid' => $openid,
        ] );
    }


    /**
     * 订单列表
     */
    protected function orderList ()
    {
        $rq       = $this->request;
        $page     = $rq->post( 'page' );
        $pagesize = $rq->post( 'pagesize' );
        $list     = Order::with( 'orderProduct' )
            ->field( 'id,goods_num,order_no,total_price,status,pay_price' )
            ->where( [ 'user_id' => $this->user_id ] )
            ->order( 'id DESC' )
            ->page( $page, $pagesize )
            ->select()
            ->toArray();

        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * 订单详情
     */
    protected function orderInfo ()
    {
        $rq     = $this->request;
        $id     = $rq->post( 'id' );
        $result = Order::with( 'orderProduct' )
            ->where( [ 'id' => $id, 'user_id' => $this->user_id ] )
            ->order( 'id DESC' )
            ->find();

        if ( !$result ) {
            $this->_return( 0, '订单不存在' );
        }

        $this->_return( 1, 'ok', $result );
    }


    /**
     * 设备通道产品列表
     */
    protected function deviceProduct ()
    {
        $macno = $this->request->post( 'macno' );

        if ( !$macno ) {
            $this->_return( 0, '请上传设备编号' );
        }

        $device = Device::get( [ 'macno' => $macno ] );
        if ( !$device ) {
            $this->_return( 0, '设备不存在' );
        }

        $list = $device->getGoodsList();

        $this->_return( 1, 'ok', [ 'list' => $list ] );

    }


    /**
     * 意见反馈列表
     */
    protected function feedbackList ()
    {
        $rq       = $this->request;
        $page     = $rq->post( 'page' );
        $pagesize = $rq->post( 'pagesize' );
        $list     = Feedback::where( [ 'user_id' => $this->user_id ] )
            ->order( 'id DESC' )
            ->page( $page, $pagesize )
            ->select();

        $this->_return( 1, 'ok', [ 'list' => collection( $list )->toArray() ] );
    }


    /**
     * 取个人资料接口
     */
    protected function getProfile ()
    {
        $user = $this->user;
        $data = [
            'head_img' => $user->head_img,
            'mobile'   => $user->mobile,
            'wallet'   => $user->wallet,
            'nickname' => $user->nickname,
            'userType' => $user->userType->user_type_name, //类型名称
        ];

        $this->_return( 1, 'ok', $data );
    }


    /**
     * 设置个人资料接口
     */
    protected function setProfile ()
    {
        $rq   = $this->request;
        $post = $rq->post();
        $user = User::get( [ 'user_id', $this->user_id ] );
        $user && $user->modify( $post );

        $this->_return( 1, 'ok', [
            'nickname' => $user->nickname,
            'head_img' => $user->head_img,
            'mobile'   => $user->mobile,
        ] );
    }


    /**
     * 取广告
     */
    protected function ad ()
    {
        $data = Advertising::display();
        if ( $data ) {
            $log = new AdvertisingLog();
            $log->save( [ 'user_id' => $this->user_id, 'ad_id' => $data['id'] ] );
        }
        $this->_return( 1, 'ok', $data );
    }


    /**
     * 用户通知
     */
    protected function notice ()
    {
        $rq       = $this->request;
        $page     = $rq->post( 'page' );
        $pagesize = $rq->post( 'pagesize' );

        $list = UserNotice::where( [ 'user_id' => $this->user_id ] )->order( 'id DESC' )->page( $page, $pagesize )->select();

        $this->_return( 1, 'ok', [ 'list' => $list ] );
    }


    /**
     * 支付订单
     */
    public function payOrder ()
    {
        $rq       = $this->request;
        $order_id = $rq->post( 'order_id' );

        $order = Order::get( [ 'order_id' => $order_id, 'user_id' => $this->user->user_id ] );
        if ( !$order ) {
            $this->_return( 0, '订单不存在' );
        }
        if ( $order->pay_time ) {
            $this->_return( 0, '订单已支付' );
        }

        try {
            extract( $order->payOrder() );
            $this->_return( 1, '开门中!', [
                'rfid'         => $runningLog->rfid,
                'device_aisle' => $runningLog->device_aisle,
                'order'        => $order,
                'runningLog'   => $runningLog,
                'order_info '  => $orderInfo,
            ] );
        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }


    }


    /**
     * 上传图片
     */
    public function uploadImage ()
    {
        $data              = [];
        ProcessData::$rule = [
            'image' => 'saveImage',
        ];
        ProcessData::cooking( $data );

        $this->_return( 1, 'ok', [ 'url' => $data['image'] ] );
    }


    /**
     * 绑定用户类型
     */
    public function bindUserType ()
    {
        $rq = $this->request;
        $id = $rq->post( 'id' );


        try {
            if ( !$id ) {
                throw new \think\Exception( '请上传ID' );
            }

            $userType = UserType::getValid( $id );

            if ( !$userType ) {
                throw new \think\Exception( '用户类型不存在' );
            }

            $user = $this->user;

            $user_ = UserWallet::get( [ 'user_id' => $user->user_id, 'agent_id' => $userType->agent_id ] );


            $user_->user_type_id = $id;
            $user_->save();

            $this->_return( 1, '绑定成功' );

        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }
    }


}