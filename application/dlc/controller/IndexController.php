<?php
namespace app\dlc\controller;

use think\Session;
use think\Db;
use app\common\model\User;
use app\common\model\Order;
use app\common\tool\Power;

class indexController extends BaseController
{
	//CMS总入口
    public function index()
    {

        $allOath = Power::getAllOaths($this->admin_id);
    
        foreach($allOath as $row) {
            if(!$row['is_menu']) {
                continue;
            }

            if($row['url']) {
                $row['url'] = url('Dlc/'.$row['url']);
                $role_oath2[] = $row;
            } else {
                $role_oath1[] = $row;
            }
        }


        $auth  = model('set')->find();


        $role_oath1['0']['url'] = 'main2';
        $array = array($role_oath1[0]);
        $this->assign('auth',$auth);
        $this->assign('roleid',Session::get('CMS.user.roleid'));
        $this->assign('main',$array?:array());
        $this->assign('role_oath1',$role_oath1?:array());
        $this->assign('role_oath2',$role_oath2?:array());

        return $this->fetch();
    }
	
	
	public function main()
    {
  //       //设置面包导航，主加载器请配置
  //       $bread = array(
  //           '0' => array(
  //               'name' => '主控面板',
  //               'url' => url('dlc/Index/main'),
  //           ),
  //       );
  //       $breadhtml = $this->getBread($bread);
       
  //       $this->assign('breadhtml', $breadhtml);


  //       //今日起始
  //       $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
  //       $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
  //       $mapToday['ctime'] = array('between', array($beginToday, $endToday));
  //       //昨日起始
  //       $beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
  //       $endYesterday = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
  //       $mapYesterday['ctime'] = array('between', array($beginYesterday, $endYesterday));
  //       //上周起始
  //       $beginLastweek = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
  //       $endLastweek = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
  //       $mapLastweek['ctime'] = array('between', array($beginLastweek, $endLastweek));
  //       //本月起始
  //       $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
  //       $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
  //       $mapThismonth['ctime'] = array('between', array($beginThismonth, $endThismonth));

  //       $userCount = User::count();                                     //总用户数
  //       $todayUserCount = User::where($mapToday)->count();              //今日用户数
  //       $yesterdayUserCount = User::where($mapYesterday)->count();      //昨日用户数

  //       $this->assign('userCount', $userCount);
  //       $this->assign('todayUserCount', $todayUserCount);
  //       $this->assign('yesterdayUserCount', $yesterdayUserCount);

  //       $todayOrderCount = Order::where($mapToday)->count();                                //今日订单总数
  //       $todayReceiveCount = Order::where($mapToday)->where(['type' => 1])->count();        //今日赠送
  //       $todayBuy = Order::where($mapToday)->field('count(id) AS count,sum(goods_num) AS goods_num,sum(pay_price) AS pay_price')->where(['type' => 2])->find();            //今日购买总数

  //       $this->assign('todayOrderCount', $todayOrderCount);
  //       $this->assign('todayReceiveCount', $todayReceiveCount );
  //       $this->assign('todayBuy', $todayBuy);


  //       $yesterdayOrderCount = Order::where($mapYesterday)->count();   //昨日订单总数

  //       $yesterdayReceiveCount = Order::where($mapYesterday)->where(['type' => 1])->count();                    //昨日赠送

  //       $yesterdayBuy = Order::where($mapYesterday)->field('count(id) AS count,sum(goods_num) AS goods_num,sum(pay_price) AS pay_price')->where(['type' => 2])->find();                        //昨日购买总数

  //       $this->assign('yesterdayOrderCount', $yesterdayOrderCount);
  //       $this->assign('yesterdayReceiveCount', $yesterdayReceiveCount);
  //       $this->assign('yesterdayBuy', $yesterdayBuy);




        
		// echo $this->fetch();
	}


    //CMS后台图片浏览器
    public function appImgviewer()
    {
        $ids = input('ids');
        $type = input('type')?:0; 
        if($type) {
            $imgs = [];
            if(strpos($ids,",")) {
                $imgs = explode(",",$ids);
            } else {
                $imgs = [$ids];
            }
            $cache = $imgs;
        } else {
            $m = Db::name('upload');
            $map['id'] = array('in',parse_str($ids));
            $cache = $m->where($map)->select();

        }
        //dump($ids);
        $this->assign('type',$type);
        $this->assign('cache', $cache);
        return($this->fetch('appImgviewer'));
    }
	
	
	//修改用户密码
	public function modifypass(){
		//设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '修改密码',
                'url' => url('/dlc/Index/modifypass'),
            ),
        );
		$this->assign('breadhtml', $this->getBread($bread));
		//处理POST提交
		$rq = $this->request;

        if ($rq->isPost()) {
            $data = $rq->post();

            if (empty($data['password'])){
                $info['status'] = 0;
                $info['msg'] = '请输入正确数据！';
                $this->ajaxReturn($info);
            }



            $re = model('admin')->get($this->uid);

            if ($re && FALSE !== $re->save(['password'=>md5($data['password'])])) {
                $info['status'] = 1;
                $info['msg'] = '修改成功';
            } else {
                $info['status'] = 0;
                $info['msg'] = '设置失败！';
            }
            return $info;
        }
		echo ($this->fetch('modifypass'));
	}
}
