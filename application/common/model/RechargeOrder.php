<?php
namespace app\common\model;

use think\Model;
use app\common\validate\RechargeOrderValidate;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\common\model\User;
use app\common\model\Agent;
use app\common\model\Coupon;

class RechargeOrder extends Model
{
    public static $statusOption = [
        1 => '待付款',
        2 => '已付款',
        /*3 => '已退款'*/
    ];

    public static function add($data)
    {
        $validate = new RechargeOrderValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField(true)->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new RechargeOrderValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField(true)->save($data) === false) {
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

    
    public function user()
    {
        return $this->hasOne('user', 'user_id', 'user_id');
    }


    public function payOk($trade_number)
    {
        if($this->status != 1) {
            return;
        }

        try{
            Db::startTrans();
            $this->status = 2;
            $this->pay_time = time();
            $this->trade_number = $trade_number;
            if($this->save() === false) {
               throw new \think\Exception($this->getError());
            }

            $user = $this->user;
            $user_ = UserWallet::get(['user_id' => $user->user_id, 'agent_id' => $this->agent_id]);
            $user_->wallet += $this->price + $this->give_price;
            if($user_->save() === false) {
                throw new \think\Exception($user->getError());
            }

            //取对应金额 的充值套餐
            $setModel = SetMeal::get($this->set_meal_id);
            if($this->bucket_num) {
                $user_->giveBucket($this->bucket_num);
            }

            $logData = [
                'user_id' => $user->user_id,
                'type' => 1,
                'num' => $this->price + $this->give_price ,
                'relevance' => $this->recharge_order_id,
                'direction' => 1,
                'agent_id' => $this->agent_id,
            ];
            UserWalletLog::add($logData);
            if(strlen($user_['openid']) > 8){
                $user = User::get($user_['user_id']);
                $agent = Agent::get($user_['agent_id']);
                $config = [
                    'debug'  => false,
                    /**
                     * 账号基本信息，请从微信公众平台/开放平台获取
                     */
                    'app_id'  => $agent['wx_appid'],         // AppID
                    'secret'  => $agent['wx_appsecret'],     // AppSecret
                    'token'   => 'your-token',          // Token
                    'aes_key' => '',            
                ];
                $app = new Application($config);
                $notice = $app->notice;
                $userId = $user_['openid'];
                if($agent['wx_mould2']){
                    $templateId = $agent['wx_mould2'];
                }else{
                    return(array('status' => 1,'msg' => '余额修改成功,模板消息发送失败,请配置模板消息'));
                }
                $url = 'http://www.zhengdaoyunke.com/h5/builded/index.html#/balance';
                $datas = array(
                    'first' => '尊敬的用户'.$user['nickname'].',您已经充值成功。',
                    'keyword1' => $this->price + $this->give_price,
                    'keyword2' => date('Y-m-d H:i:s'),
                    'keyword3' => $user_->wallet,
                    'remark'  => '感谢您对我们的信任，我们将为您提供更优质的服务。',
                );
                $notice->uses($templateId)->withUrl($url)->andData($datas)->andReceiver($userId)->send();
            }
            //套餐使用数+1
            $setModel->use_number++;
            $setModel->save();

            Db::commit();

        }catch (\think\Exception $err) {
            Db::rollback();
            return;
        }




    }

    public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

	

}
