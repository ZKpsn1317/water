<?php
namespace app\dlc\controller;

use think\Db;
class SystemController extends BaseController
{
    //默认页面
	public function index()
    {
       $bread = array(
            '0' => array(
                'name' => '系统设置',
                'url' => url('dlc/index/index?type=1'), 
            ), 
        );
		$type = input('type');
		if(empty($type))$type = 1;
        $this->assign('breadhtml', $this->getBread($bread));
		if($type == 1){
			$cache = model('set')->where('id','1')->find();
			$this->assign('cache',$cache);
        	echo $this->fetch();
		}elseif($type == 2){
			$cache = model('sms')->where('id','1')->find();
			$this->assign('cache',$cache);
			echo $this->fetch('sms_index');
		}
    }


    /**
     * 修改配置
     * @return mixed
     */
	public function set(){
		$data = input('post.');
		if ($data) {
			if($data['type'] == 1){
				$m = model('set');
			}elseif($data['type'] == 2){
				$m = model('sms');
			}elseif($data['type'] == 3){
				$m = model('wxpay');
			}elseif($data['type'] == 4){
				$m = model('alipay');
			}
			unset($data['type']);
            $re = $m->where('id',$data['id'])->update($data);
            if (FALSE !== $re) {
                $info['status'] = 1;
                $info['msg'] = '设置成功！';
            } else {
                $info['status'] = 0;
                $info['msg'] = '设置失败！';
            }
            return($info);
        }else{
            $info['status'] = 0;
            $info['msg'] = '设置失败！';
			return($info);
        }
	}


    /**
     * 关于我们
     * @return mixed
     */
	public function about(){
		$id = input('id');
        $m = model('about');
        //设置面包导航，主加载器请配置
        $bread = array(
			'0' => array(
                'name' => '系统设置',
                'url' => url('dlc/system/index?type=1'),
            ),
            '1' => array(
                'name' => '关于我们',
                'url' => url('dlc/system/about')
            ),

        );
        $this->assign('breadhtml', $this->getBread($bread));
        //处理POST提交
        $data = input('post.');
        if ($data) {
            $re = $m->update($data);
            if (FALSE !== $re) {
                $info['status'] = 1;
                $info['msg'] = '设置成功！';
            } else {
                $info['status'] = 0;
                $info['msg'] = '设置失败！';
            }
            return($info);
        }else{
            $cache = $m->where('id=1')->find();
            $this->assign('cache', $cache);
        }
        echo $this->fetch();
	}


}