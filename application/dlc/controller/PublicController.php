<?php
namespace app\dlc\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\View;
use think\Session;
use app\dlc\model\Role;
use app\common\tool\Power;


class PublicController extends Controller
{

    protected $admin;

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

    //通用登陆页面
    public function login()
    {
        $rq = $this->request;

        if ($rq->isPost()) {
            $data = $rq->post();
            $verify = new \verify\verify();

            if (!$verify->check($data['verify'])) {
                $this->error('请正确填写验证码！');
            }

            $admin = Db::name('admin')
				->where('username', $data['username'])
				->where('password', md5($data['userpass']))
				->find();

            if(empty($admin)) {
                $this->error('用户不存在，或密码错误！');
            }
            if($admin['status'] != 1) {
                $this->error('帐号已停用!');
            }

            Session::set('admin_id', $admin['id']);
            //取权限并缓存
            $role_oath = Role::getRoleOath($admin['roleid']);
          
            Power::cacheAllOaths($admin['id'], $role_oath);

            $this->redirect('dlc/index/index');
        }


        $arr = array(4, 5, 7, 7, 7, 10, 11, 12);
        $get = $arr[mt_rand(0, count($arr) - 1)];
        $wallpaper =  "/public/static/dlc/WallPage/" . $get . ".jpg";
        $this->assign('wallpaper', $wallpaper);
		return $this->fetch();
    }


    public function logout()
    {
		Session::delete('admin_id');
        $this->redirect('dlc/public/login');
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

    //百度地图
    public function baiduDitu()
    {
        $map['address'] = input('address');
        $map['lng'] = input('lng');
        $map['lat'] = input('lat');
        $this->assign('map', $map);
        return($this->fetch('baiduDitu'));
    }


    public function gouldDitu()
    {
        return($this->fetch());
    }


    public function tencentDitu()
    {
        return($this->fetch());
    }


   public function update_password(){
  	 $id = input('id');
  	 if($_POST) {
  	 	$old_password = input("old_password");
  	 	$password = input("password");
  	 	$password2 = input("password2");
  	 	if($password!=$password2)
  	 	{
  	 		  		$info['status'] = 0;
                    $info['msg'] = '密码和密码确认不一致！';
                    return($info);
  	 	}
  	 	 $user = Db::name('admin')
				->where('id',$this ->uid)
				->where('password', md5($old_password))
				->where('status',1)
				->find();
            if (!empty($user)) {
            	$user["password"]=md5($password);
				 $res=$user = Db::name('admin')
				->where('id',$this ->uid)
				->update($user);
  	 		  		$info['status'] = 1;
                    $info['msg'] = '修改成功';
                    return($info);
            } else {
  	 		  		$info['status'] = 0;
                    $info['msg'] = '原密码错误！修改失败';
                    return($info);
            }
  
  	 }
  	  echo $this->fetch();
  } 
  
  
}