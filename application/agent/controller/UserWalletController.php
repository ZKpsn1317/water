<?php

namespace app\agent\controller;
use app\common\model\Bucket;
use app\common\model\DeviceAisle;
use app\common\model\UserWallet;
use app\common\tool\Excel;
use EasyWeChat\Factory;
use app\common\model\User;
use app\common\model\UserWalletLog;


class UserWalletController extends BaseController
{

    protected $title = '$title';
    
    protected $exportField = [];   //需要导出为excel时, 配置
    
    /**
     * 查询配置参数
     * @return array
     */
    protected function search()
    {
        return [
            'user_id' => ['name' => '用户id', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'nickname' => ['name' => '用户名称', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'mobile' => ['name' => '手机号', 'value' => '', 'type' => 'text', 'searchType' => '='],
        ];
    }

    protected function assignOption()
    {

    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        if ( isset( $where['nickname'] ) && $where['nickname'] ) {
            $where['dlc_user.nickname'] = [ 'like', '%' . $where['nickname'] . '%' ];
            unset( $where['nickname'] );
        }
        if ( isset( $where['mobile'] ) && $where['mobile'] ) {
            $where['dlc_user.mobile'] = [ 'like', '%' . $where['mobile'] . '%' ];
            unset( $where['mobile'] );
        }
        if ( isset( $where['user_id'] ) && $where['user_id'] ) {
            $where['dlc_user.user_id'] = $where['user_id'];
            unset( $where['user_id'] );
        }
        $where['agent_id'] = $this->agent_id;
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = UserWallet::field( 'dlc_user_wallet.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_user_wallet.user_id=dlc_user.user_id' )->where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = UserWallet::field( 'dlc_user_wallet.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_user_wallet.user_id=dlc_user.user_id' )->where($where)->order('id DESC')->page($page,$psize)->select();  //
         }

          $count = UserWallet::field( 'dlc_user_wallet.*,dlc_user.nickname' )->join( 'dlc_user', 'dlc_user_wallet.user_id=dlc_user.user_id' )->where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        if($this->request->param('dialog')) {
            return $this->fetch();
        } else {
            echo $this->fetch();
        }
    }

    /**
     * 添加
     * @return array
     */
    public function add()
    {
        $rq = $this->request;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                UserWallet::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('user_wallet_log_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = UserWallet::get($id);

        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }

        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('user_wallet_log_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = UserWallet::get($id);

        if(!$model) {
            return(array('status' => 0,'msg' => '对象不存在'));
        }

        try{
            $model->del();
        } catch (\think\Exception $err) {
            return(array('status' => 0,'msg' => $err->getMessage()));
        }

        return(array('status' => 1,'msg' => '操作成功'));
    }
    //修改余额
    public function walletEdit()
    {
        $wallet_id = input('wallet_id');
        $type = input('type');
        $wallet = UserWallet::where('id',$wallet_id)->find();
        $this->assign('type',$type);
        $this->assign('wallet', $wallet);
        echo $this->fetch('user_wallet_edit');
    }
    public function walletSave()
    {
        $data = input('post.');
        $wallet = UserWallet::where('id',$data['id'])->find();
        if(!$wallet){
            return(array('status' => 0,'msg' => '通讯失败'));
        }
        switch ($data['type']) {
            case 'wallet':
                if(!$data['wallet']){
                    return(array('status' => 0,'msg' => '请填写余额'));
                }
                $or_wallet = $wallet->wallet;

                $wallet->wallet = $or_wallet + $data['wallet'];

                $wallet->save();
                //添加记录
                $walletLog = [
                    'user_id' => $wallet->user_id,
                    'type'  => $data['wallet'] > 0 ? 1 : 2,
                    'num'   => abs($data['wallet']),
                    'relevance'  => 0,
                    'direction'  => $data['wallet'] > 0 ? 1 : 2,
                    'agent_id'   => $wallet->agent_id,
                ];
                UserWalletLog::add($walletLog);
                //变动发送微信模板消息
                if(strlen($wallet['openid']) > 8){
                    $user = User::get($wallet['user_id']);
                    $config = [
                        'app_id' => '',
                        'secret' => '',
                        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
                        'response_type' => 'array',
                    ];
                    $app = Factory::officialAccount($config);
                    $res = $app->template_message->send([
                        'touser' => $wallet['openid'],
                        'template_id' => '',
                        'url' => 'http://www.zhengdaoyunke.com/h5/builded/index.html#/balance',
                        'data' => [
                            'first' => $user['nickname'],
                            'keyword1' => $data['wallet'],
                            'keyword2' => $wallet['wallet'],
                            'remark'  => '时间：'.date('Y-m-d H:i:s'),
                        ],
                    ]); 
                }
                break;
                case 'bucket_num':
                    $wallet->bucket_num = $data['bucket_num'];
                    $wallet->save();
                    break;
                case 'use_bucket_num':
                    $wallet->use_bucket_num = $data['use_bucket_num'];
                    $wallet->save();
                    break; 
                case 'give_bucket_num':
                    $wallet->give_bucket_num = $data['give_bucket_num'];
                    $wallet->save();
                    break;
            default:
                # code...
                break;
        }
        return(array('status' => 1,'msg' => '操作成功'));
    }
}