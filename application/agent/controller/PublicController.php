<?php
namespace app\agent\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\View;
use think\Session;
use app\common\tool\Power;
use app\agent\model\RoleAgent;

class PublicController extends Controller
{
    protected static $CMS;

	//默认跳转至登陆页面
    public function index()
    {
        $this->redirect('Public/login');
    }

    //通用注册页面
    public function reg()
    {
        $this->display();
    }

    public function tencentDitu()
    {
        return($this->fetch());
    }


    //通用登陆页面
    public function login()
    {
        $rq = $this->request;

        if ($rq->isPost()) {
            $data = $rq->post();
            $verify = new \verify\verify();

            /*if (!$verify->check($data['verify'])) {
                $this->error('请正确填写验证码！');
            }*/

           $admin = Db::name('admin_agent')
				->where('username', $data['username'])
				->where('password', md5($data['userpass']))
				->find();

            if(empty($admin)) {
                $this->error('用户不存在，或密码错误！');
            }
            if($admin['status'] != 1) {
                $this->error('帐号已停用!');
            }

            Session::set('admin_agent_id', $admin['agent_id']);
            Session::set('agent_id', $admin['id']);
            //取权限并缓存
            $role_oath = RoleAgent::getRoleOath($admin['roleid']);
          
            
            Power::setKey('agent');
            Power::cacheAllOaths($admin['id'], $role_oath);

            $this->redirect('agent/index/index');
        }


        $arr = array(4, 5, 7, 7, 7, 10, 11, 12);
        $get = $arr[mt_rand(0, count($arr) - 1)];
        $wallpaper =  "/public/static/dlc/WallPage/" . $get . ".jpg";
        $this->assign('wallpaper', $wallpaper);
		return $this->fetch();
    }

    public function logout()
    {
		Session::delete('admin_agent_id');
        Session::delete('agent_id');
        $this->redirect('agent/public/login');
    }

    //通用验证码
    public function verify()
    {
		$Verify = new \verify\verify();
        $Verify->codeSet = '0123456789';
        $Verify->length = 4;
        $Verify->imageH = 0;
        $Verify->entry();
    }


}