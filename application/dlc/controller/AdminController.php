<?php

namespace app\dlc\controller;

class AdminController extends BaseController
{
    //主页
    public function index(){
        return view();
    }
	
	/**
     * 后台管理员、角色添加
	 * @return [none] [description]
     */
	public function adminEdit(){
		$bread = array(
            '0' => array(
                'name' => '菜单设置',
                'url' => url('Dlc/role/authDetail')
            ),
            '1' => array(
                'name' => '菜单列表',
                'url' => url('Dlc/role/roleList')
            ),
            '2' => array(
                'name' => '帐号编辑',
                'url' => url('Dlc/admin/adminEdit')
            )
        );
        $this->assign('breadhtml', $this->getBread($bread));
		$id = input('id');
		$roleid = input('role_id');
		$m = model('admin');
        if(request()->isPost()){
			$data = input('post.');
            if (!$data['roleid']){
                $info['status'] = 0;
                $info['msg'] = '请选择类型！';
            }
            if (!empty($id)) {
                if(!empty($data['password'])) {
                    $data['password'] = md5($data['password']);
                } else {
                    unset($data['password']);
                }

                $re = $m->where('id',$id)->update($data);
                if (FALSE !== $re) {
                    $info['status'] = 1;
                    $info['msg'] = '设置成功！';
                } else {
                    $info['status'] = 0;
                    $info['msg'] = '设置失败1！';
                }
            } else {
                $data['ctime'] = time();
                $data['password'] = md5($data['password']);
                $re = $m->insert($data);
                if (FALSE !== $re) {
                    $info['status'] = 1;
                    $info['msg'] = '设置成功！';
                } else {
                    $info['status'] = 0;
                    $info['msg'] = '设置失败！';
                }
            }
            return($info);
		}
		if (!empty($id)){
            $cache = $m->where('id = ' . $id)->find();
			$this->assign('cache', $cache);
        }
        $list = model('role')->select();
        $this->assign('list', $list);
        $this->assign('role_id',$roleid);
		echo $this->fetch('admin_adminEdit');
	}
	



    public function adminDel() {
        $id = input('id');
        if($id==1) {
            $info['status'] = 0;
            $info['msg'] = '不能删除admin';
        } else {
            $model = \app\common\model\Admin::get($id);
            if($model){
                $model->delete();
                $this->clearAoth($model->id);
            }
            $info['status'] = 1;
            $info['msg'] = '成功删除' . $model->username;
        }

        return $info;
    }
}