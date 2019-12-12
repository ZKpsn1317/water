<?php
/**
 * Created by PhpStorm.
 * User: 韩令恺
 * Date: 2018/5/17 0017
 * Time: 16:12
 */

namespace app\wxsite\controller;

use app\common\model\Adsource;
use app\common\model\Merchant;
use app\common\model\Muser;
use app\common\tool\Upload;
use app\common\model\MerchantType;
use app\common\model\MerchantCollect;
use app\common\model\AdsourceLog;
use app\common\tool\WecahtOfficialAccount;

class VedioController extends BaseController
{
    protected $user;

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


    //修改用户性别
    public function editSex()
    { 
        $rq    = $this->request;
        $sex = $rq->post( 'sex' );
        if(!$sex){
            $this->_return( 0, 'sex无效' );
        }
        try {
            $this->user->changeSex( $sex );
            $this->_return( 1, '修改成功' );
        } catch ( \think\Exception $err ) {
            $this->_return( 0, $err->getMessage() );
        }
    }


    //获取用户信息
    public function getMuserInfo()
    {
        $data = Muser::where( [ 'merchant_user_id' => $this->user->merchant_user_id ] )
            ->field( 'head_img,nickname,sex,mobile' )
            ->find();

        if ( !$data ) {
            $this->_return( 0, '未找到资料' );
        }

        $user               = $this->user;
        $data['head_img']   = Upload::imageAddDomain( $data['head_img'], $this->request->domain() );
        $data['bind_sex'] = $user->sex ? '1' : '0';       //是否绑定了手机号码

        $this->_return( 1, 'ok', $data );
    }


    //判断当前用户在当前商户领取类型
    public function collectionType()
    {
        $rq = $this->request;
        $merchant_id = $rq->post('merchant_id');
        if(!$merchant_id){
            $this->_return( 0, '商户参数不能为空' );
        }
        $merchant = Merchant::get(['id' => $merchant_id]);
        if ( !$merchant ) {
            $this->_return( 0, '商户不存在' );
        }
        $merchantcollect = new MerchantCollect();
        $merchant_type = MerchantType::where('merchant_type_id',$merchant->type)->find();
        //总的领取次数
        $merchant_collect = $merchantcollect->where('uid',$this->user->merchant_user_id)->count();
        //今天领取次数
        $merchant_collect_today = $merchantcollect->where('uid',$this->user->merchant_user_id)->whereTime('ctime','today')->count();
        //今日最近领取
        $merchant_collect_todaylist = $merchantcollect->where('uid',$this->user->merchant_user_id)->order('ctime desc')->whereTime('ctime','today')->find();

        //免费领取类型
        if($merchant_type->type == 0){
            if($merchant_type->agent_times && $merchant_collect >= $merchant_type->agent_times){
                $this->_return(0,'当前场地领取次数已达上限');
            }else if($merchant_type->wx_id_times && $merchant_collect_today >= $merchant_type->wx_id_times){
                $this->_return(0,'当前用户今日领取已达上限');
            }else if($merchant_type->time_times && time()-strtotime($merchant_collect_todaylist->ctime) < $merchant_type->time_times*60){
                $this->_return(0,'当前时间领取已达上限');
            }else{
                $this->_return(1,'可以免费领取');
            }
        }else{
            if($merchant_type->buy_times && $merchant_collect >= $merchant_type->buy_times){
                $this->_return(3,'当前购买次数已达上限');
            }else{
                $this->_return(2,'可以购买领取');
            }
        }
    }


    //用户领取记录
    /*
     *token
     *merchant_id
     *type
     *如果是购买领取则传订单id   merchant_order_id
     */
    public function collectLog()
    {
        $rq = $this->request;
        $merchant_id = $rq->post('merchant_id');
        $type = $rq->post('type');
        $merchant_order_id = $rq->post('merchant_order_id') ? $rq->post('merchant_order_id') : 0;
        //此段代码有问题
        // if(!$merchant_id || !$type){
        //     $this->_return(0,'参数错误');
        // }
        $collect = new MerchantCollect();
        $collect->uid = $this->user->merchant_user_id;
        $collect->merchant_id = $merchant_id;
        $collect->type = $type;
        $collect->merchant_order_id = $merchant_order_id;
        $collect->ctime = time();
        $collect->save();
        $this->_return(1,'记录存储成功');
    }


    //用户浏览记录
    /*
     *token
     *merchant_ad_id
     */
    public function adsourceLog()
    {
        $rq = $this->request;
        $merchant_ad_id = $rq->post('merchant_ad_id');
        if(!$merchant_ad_id){
            $this->_return(0,'参数错误');
        }
        $adsourceLog = new AdsourceLog();
        $adsourceLog->uid = $this->user->merchant_user_id;
        $adsourceLog->merchant_ad_id = $merchant_ad_id;
        $adsourceLog->ctime = time();
        $adsourceLog->save();
        $this->_return(1,'记录存储成功');
    }

    /**
     * 发送模板消息
     * @param  [type] $tem_id [模板ID]
     * @param  [type] $url    [回调链接]
     */
    public function send($url,$tem_id="LT0Mil1s24Iqm7sJzRhng4LqpszYhN7QCzH_l_B6CHM")
    {
        $acc_token = WecahtOfficialAccount::getAccessToken();
        $data['touser'] = $this->user->openid;
        $data['template_id'] = $temp_id;
        $data['url'] = $url;
        $data['data'] = array(
            'first'=>array("value"=>"微信用户正在浏览您的视频","color"=>"#173177"),
            'keyword1'=>array("value"=$this->user->merchant_user_id,"color"=>"#173177"),
            'keyword2'=>array("value"=$this->user->nickname,"color"=>"#173177"),
            'keyword3'=>array("value"=>date("Y-m-d H:i:s"),"color"=>"#173177"),
            'remark'=>array("value"=>"详情","color"=>"#173177")
        );
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $acc_token;
        $res = httpPost($url, $data);
        $res = json_decode($res, true);
        return $res;
    }
}