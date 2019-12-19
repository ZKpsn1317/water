<?php
namespace app\vedio\controller;

use app\common\model\Adsource;
use app\common\model\Act;
use app\common\model\Merchant;
use app\common\model\MerchantType;
use app\common\model\Visitor;
use app\common\tool\WecahtOfficialAccount;
use EasyWeChat\Factory;
use app\common\model\AdsourceLog;
use app\common\model\MerchantCollect;
use app\wxsite\controller\WepayController;
use think\Db;

class IndexController extends BaseController
{

    /**
     * 轮播视频
     */
    public function vedio(){
        //根据sort排序，筛选要展示的活动
        $act = Merchant::all();
        //轮播的视频数组
        $atr = Adsource::all();
        //商户分类信息
        $category = MerchantType::all();
        //渲染数据
        $this->assign("act1",json_encode($act));
        $this->assign("vedio1",json_encode($atr));
        $this->assign("category1",json_encode($category));
        $this->assign("category",json_encode($category));
        $this->assign("act",$act);
        $this->assign("vedio",$atr);
        //dump($atr);exit;
        return $this->fetch();
    }
  


    /**
     * 根据商户类型查所有商户
     * @param  [int] $type 类型
     * @return [array]  符合条件的商户列表
     */
    public function category($type){
        $collect = Merchant::where('type',$type)->select();
        $this->assign("collect",$collect);
        return $this->fetch();
    }
  


    /**
     * 发送模板消息
     */
    public function send(){
        $acc_token = WecahtOfficialAccount::getAccessToken();
        $data['touser'] = "o2Bdq0y-nnpYQfvhw8anqNauME0I";
        $data['template_id'] = "LT0Mil1s24Iqm7sJzRhng4LqpszYhN7QCzH_l_B6CHM";
        $data['url'] = "www.baidu.com";
        $data['data'] = array(
            'first'=>array("value"=>"微信用户正在浏览您的视频","color"=>"#173177"),
            'keyword1'=>array("value"=>"2","color"=>"#173177"),
            'keyword2'=>array("value"=>"楚河汉界","color"=>"#173177"),
            'keyword3'=>array("value"=>"2019-10-14 15:54","color"=>"#173177"),
            'remark'=>array("value"=>"详情","color"=>"#173177")
        );
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $acc_token;
        $data = json_encode($data);
        $res = httpPost($url, $data);
        $res = json_decode($res, true);
        $info['code']=1;
        $info['msg']="OK";
        $info['data']=$res;
        return json_encode($info);  }



    public function test(){
        $group = array();
        //商家分类
        $merchant_types = MerchantType::all();
        //根据sort排序，筛选要展示的活动
        $act = Merchant::all();
        foreach ($merchant_types as $v) {
            foreach ($act as $vo) {
                if ($vo['type']==$v['merchant_type_id']) {
                    $group[$v['merchant_name']][]=$vo;
                }
            }
        }
        dump($group["餐饮"]);exit;
    }



    /**
     * 商家活动详情
     */
    public function detail($id){
        $act = Merchant::where('id',$id)->find();
        $this->assign('act',$act);
        return $this->fetch();
    }



    /**
     * 选择男女
     */
    public function setSex ()
    {
        return $this->fetch();
    }

  
  
    /**
     * 免费领取
     */
    public function free($token="",$loop=1){
      
        $rec = Db::name('dlc_merchant_collectionlog')->where('token',$token)->whereTime('ctime','today')->select();
        $act = Merchant::where('id',$arr['merchant_id'])->select();
        if (count($rec)<$loop) {
            $this->donate();
            $this->assign('act',$act);
            $this->assign('cotent',"领取成功");
            return $this->fetch('donate');
        }else{
            $this->assign('act',$act);
            $this->assign('cotent',"每人每天限领".$loop."次,明天再来哦！");
            return $this->fetch('donate');
        }
    }

    

    /**
     * 视频浏览记录
     */
    public function record(){
        $rq = $this->request;
        $arr=[
            'uid' => $rq->post( 'uid' ),
            'merchant_ad_id' => $rq->post( 'merchant_id' ),
            'ctime'=>time(),
        ];
        Db::name('dlc_merchant_log')->insert($arr);
    }
  


    /**
     * 用户中心
     */
    public function user(){
        return $this->fetch();
    }
  


    /**
    * 免费领取记录
    */
    public function donate(){
        $rq = $this->request;
        $arr=[
            'uid' => $rq->post( 'uid' ),
            'type' => $rq->post('type'),
            'merchant_id' => $rq->post( 'merchant_id' ),
            'ctime'=>time(),
        ];

        Db::name('dlc_merchant_collectionlog')->insert($arr);
    }
  


    /**
    * 点击购买成功
    */
    public function buy(){
        return $this->fetch();
    }


    /**
     * 微信JSAPI支付回调
     */
    public function notify(){
        $xmlData = file_get_contents('php://input');
        $jsonxml = json_encode(simplexml_load_string($xmlData,'SimpleXMLElement',LIBXML_NOCDATA));
        $result = json_decode($jsonxml,true);
        if($result){
        $out_trade_no = $result['out_trade_no'];
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] =='SUCCESS'){

            }
        }
    }
  
}