<?php

namespace app\area\controller;
use app\area\model\RoleOathArea;
use app\area\model\RoleArea;
use app\area\model\AdminArea;
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
                'url' => url('area/role/authDetail')
            ),
            '1' => array(
                'name' => '菜单列表',
                'url' => url('area/role/roleList')
            ),
            '2' => array(
                'name' => '帐号编辑',
                'url' => url('area/admin/adminEdit')
            )
        );
        $this->assign('breadhtml', $this->getBread($bread));
		$id = input('id');
		$roleid = input('role_id');
		$m = new AdminArea();
        if(request()->isPost()){
			$data = input('post.');
            $data['area_id'] = $this->area_id;
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
                
                $model = $m->get(['id'=>$id]);
                if($model->username != $data['username'] && AdminArea::get(['username' => $data['username']])) {
                    return(array('status'=>0, 'msg' => '帐号已存在'));
                }

                $re = $m->where(['id'=>$id, 'area_id'=>$this->area_id])->update($data);
                if (FALSE !== $re) {
                    $info['status'] = 1;
                    $info['msg'] = '设置成功！';
                } else {
                    $info['status'] = 0;
                    $info['msg'] = '设置失败1！';
                }
            } else {
                if(AdminArea::get(['username' => $data['username']])) {
                    return(array('status'=>0, 'msg' => '帐号已存在'));
                }
                
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
            $cache = $m->where(['id'=>$id, 'area_id'=>$this->area_id])->find();
			$this->assign('cache', $cache);
        }
        $list = RoleArea::where(['area_id'=>$this->area_id])->select();
        $this->assign('list', $list);
        $this->assign('role_id',$roleid);
		echo $this->fetch('admin_adminEdit');
	}




    public function adminDel() {
        $id = input('id');



        $model = \app\area\model\AdminArea::get(['id'=>$id, 'area_id'=>$this->area_id]);
        if($model){
            if($model->is_admin) {
                $info['status'] = 0;
                $info['msg'] = '不能删除admin';
            } else {
                $model->delete();
                $this->clearAoth($model->id);
                $info['status'] = 1;
                $info['msg'] = '成功删除' . $model->username;
            }
        }
        return $info;
    }
}