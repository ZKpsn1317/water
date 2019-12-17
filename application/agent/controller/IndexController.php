<?php
namespace app\agent\controller;

use think\Session;
use think\Db;
use app\agent\model\AdminAgent;
use app\common\model\Agent;
use app\common\tool\Power;
use app\common\model\Hitch;

class IndexController extends BaseController
{
	//CMS总入口
    public function index()
    {
        Power::setKey('agent');
        $allOath = Power::getAllOaths($this->admin_id);
        foreach($allOath as $row) {
            if(!$row['is_menu']) {
                continue;
            }

            if($row["url"]) {
                $row["url"] = url('agent/'.$row["url"]);
                $role_oath2[] = $row;
            } else {
                $role_oath1[] = $row;
            }
        }

        //检测还有几条未处理故障信息
        $h_num = Hitch::where(['agent_id' => $this->agent_id,'status' => 2])->count();
        $this->assign('h_num',$h_num);
        //$auth  = model('set')->find();


        $role_oath1['0']["url"] = 'main2';
        
        $array = array($role_oath1[0]);
        $this->assign('auth',[]);
        $this->assign('main',$array?:array());
        $this->assign('role_oath1',$role_oath1?:array());
        $this->assign('role_oath2',$role_oath2?:array());

        return $this->fetch();
    }


	public function main()
    {
        echo 'hello';
	}



	//修改用户密码
	public function modifypass(){
		//设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '修改密码',
                'url' => url('/agent/Index/modifypass'),
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



            $re = AdminAgent::get($this->uid);

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
}
