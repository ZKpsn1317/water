<?php
/**
 * jsapi支付
 */

namespace app\wxsite\controller;

use app\common\model\Muser;
use app\common\model\Morder;
class WepayController extends BaseController
{
    protected $user;
    protected $trade_no;
    protected $price;

    public function _initialize ()
    {
        $rq = $this->request;

        $token = $rq->post( 'token' );
        $user  = Muser::get( [ 'token' => $token ] );
        if ( !$user ) {
            $this->_return( 101, 'token无效' );
        }

        $this->user    = $user;
    }

    const KEY               = 'Eo5nylRXG2pygkGqvzRRBIATYF8UIp1f';                       //api密钥
    const APPID             = 'wx4529aaed6c655d02';                     //appid
    const SECRET            = '9d81e4b0b43f7a39b6da80a0f9284c1b';                    //商户密钥
    const MCH_ID            = '1512936971';                    //商户id
    const CODEURL           = 'https://open.weixin.qq.com/connect/oauth2/authorize?';   //微信获取code接口
    const REDIRECT_URI      = 'http://www.wepay.com/Return.php';             //授权回调地址
    const ACCESS_TOKEN_URL  = 'https://api.weixin.qq.com/sns/oauth2/access_token?';     //获取access_token接口
    const USER_INFO_URL     = 'https://api.weixin.qq.com/sns/userinfo?';                //拉取用户信息接口
    const NOTIFY_URL        = 'http://shuilian.jza2c.com/vedio/index/notify';            //支付结果通知地址
    const UNI_URL           = 'https://api.mch.weixin.qq.com/pay/unifiedorder';         //微信统一下单接口


     /*
     *获取用户openid
     微信文档地址:https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842
     */
     public function getOpenid()
     {
         return $this->user->openid;
     }
     
    /*
     *生成签名
     微信文档签名规则:https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
     */
    public function getSign($arr = [])
    {
        //1.参数的值为空不参与签名,过滤参数为空的key
        $isNotEmptyArr = array_filter($arr);
        //2.按照字典进行排序
        ksort($isNotEmptyArr);
        //3.组成URL键值对,使用urldecode是为了适应isNotEmptyArr中包含了中文串
        $queryStr = urldecode(http_build_query($isNotEmptyArr));
        //4.和KEY拼接
        $stringSignTemp = $queryStr . '&key='.SELF::KEY;
        //对结果进行md5之后,全部转换成大写
        $sign =strtoupper(md5($stringSignTemp));
        //返回签名
        return $sign;
    }

    /**
     * 返回一个带签名的数组
     */
    public function setSign($arr)
    {
        $arr['sign'] = $this->getSign($arr);
        return $arr;
    }
    /*
     *验证签名
     checkSign:需要校验的签名
     mySign:生成的校验签名
     返回:bool
     */
    public function checkSign($checkSign,$arr)
    {
        $mySign = $this->getSign($arr);
        return $mySign == $checkSign;
    }

    /**
     * 生成订单编号
     */
    public function getTradeNo()
    {
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 微信统一下单
     *微信文档地址:https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
     */
    public function unifiedOrder()
    {
        //由api文档可知 以下是必须参数
        $params = [
            'appid'            =>      self::APPID,
            'mch_id'           =>      self::MCH_ID,
            'openid'           =>      $this->getOpenid(),
            'nonce_str'        =>      md5(time()),
            'body'             =>      'test',
            'out_trade_no'     =>      $this->trade_no,
            'total_fee'        =>      $this->price,
            'spbill_create_ip' =>      $_SERVER['REMOTE_ADDR'],
            'notify_url'       =>      self::NOTIFY_URL,
            'trade_type'       =>      'JSAPI'
        ];
        //生成带签名的数组
        $params                =       $this->setSign($params);
        //微信统一下单api需要的参数是一个xml,先把数组转换成xml
        $xml                   =       $this->ArrToXml($params);
        //调用统一下单api,给微信发送以上xml,微信的返回结果也是一个xml
        $result                =       $this->postXml(self::UNI_URL,$xml);
        //为了方便看,把微信返回的订单xml转换成数组
        $orderData             =       $this->XmlToArr($result);
        //返回订单结果
        return $orderData;
    }

    /***
     * 给指定的url地址发送xml数据的方法
     */
    public function postXml($url,$postfields)
    {
        $ch                             = curl_init();
        $params[CURLOPT_URL]            = $url;             //请求url地址
        $params[CURLOPT_HEADER]         = false;            //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true;             //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true;             //是否重定向
        $params[CURLOPT_POST]           = true;             //请求方式
        $params[CURLOPT_POSTFIELDS]     = $postfields;
        $params[CURLOPT_SSL_VERIFYPEER] = false;            //是否跳过安全证书
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        curl_setopt_array($ch, $params);                    //传入curl参数
        $content                        = curl_exec($ch);   //执行
        curl_close($ch);                                    //关闭连接
        return $content;
    }

    /*
    xml转换成数组
    */
    public static function XmlToArr($xml)
    {
        if($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    /**
     * 数组转换成xml
     */
    public function ArrToXml($arr)
    {
        if(!is_array($arr) || count($arr) == 0)
            return '';
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /*
     *获取微信的prepayid
     */
    public function getPrePayId()
    {
        $orderData = $this->unifiedOrder();
        return $orderData['prepay_id'];
    }

    /**
     * 获取到微信支付的getPrePayId参数后,构建支付所需要的json参数
     */
    public function getJsParams()
    {
        $this->createOrder();
        $params = [
            'appId'        =>      self::APPID,
            'timeStamp'    =>      strval(time()),//解决苹果手机JSAPI支付缺少参数:timestamp错误
            'nonceStr'     =>      md5(time()),
            'package'      =>      "prepay_id=".$this->getPrePayId(),
            'signType'     =>      'MD5',
        ];
        //注意 微信支付的签名参数是paySign 而不是sign
        $params['paySign'] = $this->setSign($params)['sign'];
        //返回前端支付的基本参数(json)
        $params = json_encode($params);
        $this->_return( 1, 'ok', $params );
    }


    /**
     * 生成订单接口
     */
    public function createOrder(){
        $rq = $this->request;
        $merchant_id = $rq->post("merchant_id");
        $data['user_id'] = $this->user->merchant_user_id;
        $data['price'] = $rq->post("price");
        $data['pay_time'] = date("Y-m-d h:i:sa", time());
        $data['pay_number'] = $this->getTradeNo();
        $this->trade_no = $pay_number;
        $this->price = $price;
        $data['status'] = 1;
        $data['type'] = $rq->post("type");
        Morder::add($data);
    }
}
?>

