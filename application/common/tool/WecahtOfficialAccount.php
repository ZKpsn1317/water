<?php
namespace app\common\tool;
use Curl\Curl;
use think\Request;

/**
 * 微信公众号类
 * Class WecahtOfficialAccount
 * @package app\common\tool
 */
class WecahtOfficialAccount
{

    /**
     * 取code
     */
    public static function getCode($wxconfig = [])
    {
        $auth_code = Request::instance()->get('auth_code');
        if($auth_code) {
            return $auth_code;
        }
        
        $url= \think\Request::instance()->domain() . "/wxsite/public/getCode2";

        $appid = isset($wxconfig['wx_appid']) ? $wxconfig['wx_appid'] :  config('wx_appid');
        $redirect_uri = urlencode ( $url );
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        header("location: $url");
    }


    /**
     * 通过CODE取 openID
     * @param $code
     * @return mixed
     * @throws \think\Exception
     */
    public static function getOpenid($code, $wxconfig)
    {
        foreach($wxconfig as $val) {
            if (empty($val)) {
                exception('请先配置代理微信参数');
            }
        }
        $curl = $curl = new \Curl\Curl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);

        //通过code取openid
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $wxconfig['wx_appid'] . '&secret='. $wxconfig['wx_appsecret'] .'&code=' . $code . '&grant_type=authorization_code';

        $curl->get($url);
        if ($curl->error) {
            throw new \think\Exception('获取失败,请稍候再试!');
        }

        return json_decode($curl->response);
    }


    public static function getUserInfo($accessToken, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openid}&lang=zh_CN";
        $curl = $curl = new \Curl\Curl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);

        $curl->get($url);
        if ($curl->error) {
            throw new \think\Exception('获取失败,请稍候再试!');
        }

        return json_decode($curl->response);

    }


    /**
     * 刷新access_token
     * @param $refresh_token
     */
    public static function refreshToken($refresh_token) {
        $curl = $curl = new \Curl\Curl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);

        $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=' . config('wx_appid') . '&grant_type=refresh_token&refresh_token=' . $refresh_token;

        $curl->get($url);
        if ($curl->error) {
            throw new \think\Exception('获取失败,请稍候再试!');
        }

        return json_decode($curl->response);
    }


    /**
     * 是否关注了公众号
     * @param $openid
     * @return mixed
     * @throws \think\Exception
     */
    public static function isSubscribe($openid)
    {
        $accessToken = static::getAccessToken();

        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$accessToken}&openid={$openid}";

        $curl = $curl = new \Curl\Curl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);

        $curl->get($url);
        if ($curl->error) {
            throw new \think\Exception('获取失败,请稍候再试!');
        }
        $result = json_decode($curl->response);
        return $result->subscribe;
    }


    /**
     * 取基本 access_token
     * @return mixed
     */
    public static function getAccessToken()
    {
        $jssdk = new \limx\tools\wx\JsSdk(config('wx_appid'),config('wx_appsecret'));
        $token = $jssdk->getAccessToken();
        return $token;
    }


    /**
     * 微信退款
     * */
    public static function wechatrefund($data, $wxconfig){

        foreach($wxconfig as $val) {
            if(empty($val)) {
                exception('请配置后台微信设置!');
            }
        }

        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
        $params->appID = $wxconfig['wx_appid'];
        $params->mch_id = $wxconfig['wx_mchid'];
        $params->key = $wxconfig['wx_key'];
        $params->certPath = $wxconfig['certPath'];
        $params->keyPath = $wxconfig['keyPath'];

        // SDK实例化，传入公共配置
        $sdk = new \Yurun\PaySDK\Weixin\SDK($params);
        $request = new \Yurun\PaySDK\Weixin\Refund\Request;
        $request->transaction_id = $data['pay_number']; // 微信订单号，与商户订单号二选一
        $request->out_refund_no = 'refund' . time() . mt_rand(10000000, 99999999); // 商户退款单号
        $request->total_fee = $data['pay_money'] * 100; // 订单总金额，单位为分，只能为整数
        $request->refund_fee = $data['back_money'] * 100; // 退款总金额，订单总金额，单位为分，只能为整数
        $result = $sdk->execute($request);
        if($sdk->checkResult()) {
            return $result['return_code'];
        }else{
            trace('退款:');
            trace($sdk->getError());
            throw new \think\Exception('失败,请稍候再试');
        }
    }



    /**
     * 生成公众号H5支付 参数
     * @param $data
     *  $data['body'] = '火影T2电脑';                                        // 商品描述
        $data['out_trade_no'] = 'test' . mt_rand(10000000,99999999); // 订单号
        $data['total_fee'] = 1;                                 // 订单总金额，单位为：分
        $data['openid'] = $openid;                              // 必须设置openid
     */
    public static function getH5PayParams($data, $wxConfig)
    {
        foreach($wxConfig as $key => $val) {
            if(empty($val)) {
                exception('微信支付参数未配置!');
            }
        }
        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
        $params->mch_id = $wxConfig['wx_mchid'];
        $params->appID = $wxConfig['wx_appid'];
        $params->key = $wxConfig['wx_key'];


        $pay = new \Yurun\PaySDK\Weixin\SDK($params);
        // 支付接口
        $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\Pay\Request;
        $request->body = $data['body'];                                 // 商品描述
        $request->out_trade_no = $data['out_trade_no'];                 // 订单号
        $request->total_fee = $data['total_fee'] * 100;                 // 订单总金额，单位为：分
        $request->notify_url = $data['notify_url'];                     // 异步通知地址
        $request->openid = $data['openid'];                             // 必须设置openid

        // 调用接口
        $result = $pay->execute($request);
        if($pay->checkResult())
        {
            $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\JSParams\Request;
            $request->prepay_id = $result['prepay_id'];
            $jsapiParams = $pay->execute($request);
            return $jsapiParams;
        } else {

            throw new \think\Exception('支付创建失败,请稍候再试');
        }
    }


    public static function getAndroidPayParams($data)
    {
        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
        $params->mch_id = config('wx_mchid');
        $params->appID = config('wx_appid');
        $params->key = config('wx_key');

        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\Weixin\SDK($params);
        // 支付接口
        $request = new \Yurun\PaySDK\Weixin\APP\Params\Pay\Request;
        $request->body = $data['body']; // 商品描述
        $request->out_trade_no = $data['out_trade_no']; // 订单号
        $request->total_fee = $data['total_fee'] * 100; // 订单总金额，单位为：分
        $request->spbill_create_ip = \think\Request::instance()->ip(); // 客户端ip，必须传正确的用户ip，否则会报错
        $request->notify_url = $data['notify_url']; // 异步通知地址
/*        $request->scene_info->store_id = '门店唯一标识，选填';
        $request->scene_info->store_name = '门店名称，选填';*/
        // 调用接口
        $result = $pay->execute($request);
        if($pay->checkResult())
        {
            $clientRequest = new \Yurun\PaySDK\Weixin\APP\Params\Client\Request;
            $clientRequest->prepayid = $result['prepay_id'];
            $pay->prepareExecute($clientRequest, $url, $data);
            return $data;
        }
        else
        {
            throw new \think\Exception('支付创建失败,请稍候再试');
            //var_dump($pay->getErrorCode() . ':' . $pay->getError());
        }
    }


    /**
     * 微信支付回调
     */
    public static function notify($orderClass)
    {
/*        $data = '<xml><appid><![CDATA[wx4529aaed6c655d02]]></appid>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<device_info><![CDATA[WEB]]></device_info>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[Y]]></is_subscribe>
<mch_id><![CDATA[1512936971]]></mch_id>
<nonce_str><![CDATA[2575fb3ea4def1b1cfacbbfe46850267]]></nonce_str>
<openid><![CDATA[o2Bdq03ZXqtltFl488wHHqA2d2jE]]></openid>
<out_trade_no><![CDATA[rg10060]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[7F0D50BE3F45116471F6A22BD8024B97]]></sign>
<time_end><![CDATA[20181102155547]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[JSAPI]]></trade_type>
<transaction_id><![CDATA[4200000209201811021069663955]]></transaction_id>
</xml>';*/
        $data = \Yurun\PaySDK\Lib\XML::fromString(file_get_contents('php://input'));
        $order = $orderClass::get(substr($data['out_trade_no'], 2));
        $agent = $order->agent;


        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
        $params->mch_id = $agent->wx_mchid;
        $params->appID = $agent->wx_appid;
        $params->key = $agent->wx_key;
        $sdk = new \Yurun\PaySDK\Weixin\SDK($params);


        $payNotify = new PayNotify;
        $payNotify->orderClass = $orderClass;

        try {
  
            $sdk->notify($payNotify);

        }catch (\Exception $err) {
            $payNotify->reply('', $err->getMessage());
        }catch (\think\Exception $err) {
            $payNotify->reply('', $err->getMessage());
        }
    }


}


class PayNotify extends \Yurun\PaySDK\Weixin\Notify\Pay
{
    public $orderClass;
    /**
     * 后续执行操作
     * @return void
     */
    protected function __exec()
    {

        $data = $this->getNotifyData();

        $modelNmae = $this->orderClass;
        $out_trade_no = substr($data['out_trade_no'], 2);
        $order = $modelNmae::get($out_trade_no);

        if(!$order) {
            throw new \Exception('定单未找到');
        }

        $order->payOk($data['transaction_id']);
        $this->reply('SUCCESS', 'OK');
        return;
    }

    
}