<?php
namespace app\area\controller;

use think\Request;
use think\Db;
use app\area\model\RoleOathArea;
use app\area\model\RoleArea;
use app\area\model\AdminArea;


class RoleController extends BaseController
{

    //主页
    public function index(){
        return view();
    }

	/**
     * 后台菜单设置
     * @param int $sel 用户名
     * @param int $inp 密码
     * @param string $icon 邮箱
	 * @return [none] [description]
     */
	public function authDetail(){
        $bread = array(
            '0' => array(
                'name' => '菜单设置',
                'url' => url('area/role/authDetail')
            ),
            '1' => array(
                'name' => '菜单列表',
                'url' => url('area/role/authDetail')
            )
        );
        $this->assign('breadhtml',$this->getBread($bread));
        $area_id = \think\Session::get('area.areaId');
        if(request()->isPost()) {
            $sel = I('post.sel')?:1;
            $inp = I('post.inp')?:1;
            $post=I('post.');
            if ($post['icon']){
                $data['icon']=$post['icon'];
            }
            if($sel == 1 || $inp == 1) {
                $data['parent_id'] = I('post.parent_id');
                $data['url'] = trim(I('post.url'));
                $data['name'] = trim(I('post.navName'))?:trim(I('post.navName1'))?:$this -> ajaxReturn(array('status' => 0,'msg' => '导航菜单名不能为空',$data));
            } else if($sel == 2 || $inp == 2) {
                $data['name'] = trim(I('post.name'))?:$this -> ajaxReturn(array('status' => 0,'msg' => '子菜单名不能为空'));
                $data['url'] = trim(I('post.url'))?:$this -> ajaxReturn(array('status' => 0,'msg' => 'url不能为空'));
                $data['parent_id'] = I('post.parent_id');
                if(!strpos($data['url'], "/"))
                {
                    return(array('status' => 0,'msg' => 'Url不合法'));
                }
            } else {
                $info['status'] = 0;
                $info['msg'] = '未知错误!';
                $info['aa'] = $sel;
                $info['bb'] = $inp;
            }
            if($data['menu_id'] = I('post.menu_id')) {
                $data['is_menu'] = 0;
            } else {
                $data['is_menu'] = 1;
            }

            $data['order'] = (int)I('post.order')?:0;
            $data['area_id'] = $area_id;
            if($data['oath_id'] = I('post.oath_id')) { //修改
                if(!$data['parent_id'] && $sel == 2) {
                    return(array('status' => 0,'msg' => '请选择上级导航'));
                }
                $a = (new RoleOathArea())->save($data);
            } else {
                $a = (new RoleOathArea())->add($data);
                $Dlc_auth = Db::name('role_area')-> where(array('role_id' => 1))-> getField('auth');
                (new RoleArea())->save(array('role_id' => 1,'auth' => $Dlc_auth.",".$a));
            }
            if($a) {
                return(array('status' => 1,'msg' => '操作成功'));
            } else {
                return(array('status' => 0,'msg' => '操作失败'));
            }
        }

        $oath1 = Db::name('role_oath_area')->where(array('module' => 'Dlc','is_menu' => 1,'parent_id' => 0))-> order(array('order' => 'desc')) -> select();
        $oath2 = Db::name('role_oath_area')->where(array('module' => 'Dlc','parent_id' => array('NEQ',0), 'is_menu' => 1))-> order(array('order' => 'desc')) -> select();
        $this->assign('oath1', $oath1);
        $this->assign('oath2', $oath2);
        echo $this->fetch('role_authDetail');
    }

	//roleAdd
	/**
     * 编辑菜单
     * @param int $outh_id 角色id
	 * @return [none] [description]
    */
    public function roleEdit(){
        $outh_id = input('outh_id');
		$roleList = model('role_oath')->where(array('parent_id'=>0))->select();
		$this->assign('roleList', $roleList);
        if($outh_id){
            $parent = model('role_oath')->where(array('oath_id'=>$outh_id))-> field('oath_id,name,parent_id,url,order,icon')->find();
            $this->assign('parent', $parent);
			echo $this->fetch('role_roleEdit');
        }else{
			echo $this->fetch('role_roleAdd');
		}

    }

	/**
     * 保存菜单
     * @param from 表单
	 * @return [none] [description]
    */
	public function oathSave(){
		$data = input('post.');
        if(empty($data['name'])){
            $info['status'] = 0;
            $info['msg'] = '请输入菜单名或方法名';
            return($info);
        }
        if(!empty($data['oath_id'])){
            $role = model('role_oath')->where('oath_id',$data['oath_id'])->update($data);
        }else{
			if($data['parent_id'] == 0) $data['is_menu'] = 1;
            $role = model('role_oath')->insert($data);
			//更新超级管理员权限
			$role_oath_list = model('role_oath')->column('oath_id');
			$update_role['auth'] = implode(',',$role_oath_list);
			model('role')->where('role_id',1)->update($update_role);
        }
        if($role !== false){
            $info['status'] = 1;
            $info['msg'] = '操作成功';
        }else{
            $info['status'] = 0;
            $info['msg'] = '操作失败';
        }
        return($info);
	}

	/**
     * 删除菜单
	 * @param int $oath_id 角色权限id
	 * @return [array] [description]
    */
   public function oathDel(){
        $oath_id = (int)(input('post.oath_id'))?:0;
        if($oath_id == 1)return(array('status' => 0,'msg' => '你不能删除该模块'));
        $del = 0;
        if($find = model('role_oath')->where('oath_id',$oath_id)->find()) {
            if($find['parent_id'] == 0) {
                if(model('role_oath')->where(array('parent_id' => $oath_id,'module' => 'Dlc','is_menu' => 1))->select()) {
                    return(array('status' => 0,'msg' => '该导航菜单存在子菜单，不能删除'));
                }
                if(model('role_oath')->where('oath_id',$oath_id)->delete()) {
                    $del = 1;
                }
            } else {
                if($res = model('role_oath')->where(array('menu_id' => $oath_id,'module' => 'Dlc','is_menu' => 0))->select()) {
                    foreach ($res as $k => $v) {
                        $delIds[] = $v['oath_id'];
                    }
                    model('role_oath') -> startTrans();
                    $a = model('role_oath')->where(array('oath_id' => array('in',$delIds)))->delete();
                    $b = model('role_oath')->where('oath_id',$oath_id)->delete();
                    if($a && $b){
                        model('role_oath')->commit();
                        $del = 1;
                    } else {
                        model('role_oath')->rollback();
                        return(array('status' => 0,'msg' => '删除失败'));
                    }
                } else {
                    if(model('role_oath')->where('oath_id',$oath_id)->delete()){
                        $del = 1;
                    } else {
                        return(array('status' => 1,'msg' => '删除失败'));
                    }
                }
            }
            if($del == 1) {
				if(!isset($delIds)){
					$delIds = array();
				}
                $delIds[] = $oath_id;
                if($role_auth_all = model('role')->select()){
                    foreach ($role_auth_all as $k => $v) {
                        if($v['auth']) {
                            if(strpos($v['auth'],',')) {
                                $authArr = explode(",", $v['auth']);
                                foreach ($authArr as $a => $b) {
                                    if(in_array($b,$delIds)) {
                                        unset($authArr[$a]);
                                    }
                                }
                                model('role')->where(array('role_id' => $v['role_id']))->update(array('role_id' => $v['role_id'],'auth' => implode(",", $authArr)));
                            } else {
                                if(in_array($v['auth'],$delIds)) {
                                    model('role')->where(array('role_id' => $v['role_id']))->update(array('role_id' => $v['role_id'],'auth' => ''));
                                }
                            }
                        } else {
                            continue;
                        }
                    }
                }
                return(array('status' => 1,'msg' => '删除成功'));
            }
        } else {
            return(array('status' => 0,'msg' => '找不到该记录'));
        }
    }

	/**
     * 后台角色列表
	 * @return [none] [description]
    */
    public function roleList(){
        //设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '菜单设置',
                'url' => url('area/role/authDetail')
            ),
            '1' => array(
                'name' => '角色列表',
                'url' => url('area/role/roleList')
            )
        );

        $area_id = \think\Session::get('area.areaId');
        $this->assign('breadhtml',$this->getBread($bread));
        $list = RoleArea::where(['area_id' => $area_id])->select();
		$count = RoleArea::where(['area_id' => $area_id])->count();
        $psize = 10;
        $this->getPage($count, $psize, 'App-loader', '角色列表', 'App-search');

        $this->assign('list',$list);
        echo $this->fetch('role_roleList');
    }

	/**
     * 后台角色详情
	 * @return [none] [description]
    */
	public function roleDetail(){
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
                'name' => '角色权限',
                'url' => url('area/role/roleDetail')
            )
        );
        $this->assign('breadhtml', $this->getBread($bread));
        $area_id = \think\Session::get('area.areaId');
        if(request()->isPost()) {
            $role_name = trim(input('post.role_name'));
            if(!$role_name) {
                return(array('status' => 0,'msg' => '请填写角色名'));
            }

            $oath = rtrim(input('post.oath'),",");
            if($role_id = input('post.role_id')) {
                $re = RoleArea::where('role_id',$role_id)->update(array('role_name' => $role_name,'auth' => $oath));
            } else {
                $re = RoleArea::insert(array('role_name' => $role_name,'auth' => $oath,'ctime' => time(), 'area_id'=>$area_id));
            }
            if($re !== false) {
                return(array('status' => 1,'msg' => '操作成功'));
            } else {
                return(array('status' => 0,'msg' => '操作失败或未作信息内容修改'));
            }
			$this->assign('oath',$oath);
        } else {
            if($role_id = input('role_id')) {
                $res = RoleArea::where('role_id',$role_id)->find();
                if(!$res) return(array('status' => 0,'msg' => '该角色不存在'));
                $this->assign('res',$res);
            }
        }
        $oath1 = Db::name('role_oath_area')->where(array('is_menu' => 1,'parent_id' => 0))->order(array('order' => 'desc'))->select();
        $oath2 = Db::name('role_oath_area')->where(array('parent_id' => array('NEQ',0)))->order(array('is_menu' => 'desc','order' => 'desc'))->select();
        $this->assign('oath1', $oath1);
        $this->assign('oath2', $oath2);
        echo $this->fetch('role_roleDetail');
    }

	/**
     * 后台账户列表
	 * @return [none] [description]
    */
    public function authEdit(){
        $bread = array(
            '0' => array(
                'name' => '系统设置',
                'url' => url('area/Index/rolelist')
            ),
            '1' => array(
                'name' => '角色权限',
                'url' => url('area/Index/roleList')
            )
        );
		$this->assign('breadhtml',$this->getBread($bread));
        $m = new AdminArea();
        $p = 1;
        $role_id = input('role_id');
        $map['a.area_id'] = $this->area_id;
        $map['a.is_admin'] = ['<>', '1'];
        if ($role_id){
        	$map['a.roleid'] = $role_id ;
        	$this->assign('role_id', $role_id);
        }
        $psize = 20;
        $cache = $m->alias('a')->join('dlc_role_area b ','a.roleid = b.role_id', 'left')->field('a.*,b.role_name')->where($map)->page($p, $psize)->select();
        $count = $m->alias('a')->join('dlc_role_area b ','a.roleid = b.role_id')->field('a.*,b.role_name')->where($map)->count();
        $this->getPage($count, $psize, 'App-loader', '角色权限', 'App-search');
		$this->assign('empty', '<td colspan="7" style="text-align:center;height:44px;line-height:44px;">暂无相关角色的帐号</td>');
        $this->assign('cache', $cache);
		//print_r($cache);
        echo $this->fetch('role_authEdit');
    }



    //删除角色
    public function roleDel()
    {
        $id = input('id');
        $area_id = \think\Session::get('area.areaId');
        if($id==1) {
            $info['status'] = 0;
            $info['msg'] = '不能删除超级管理员';
        } else {
            $model = \app\area\model\RoleArea::get(['role_id'=>$id, 'area_id'=>$area_id]);
            if($model){
                $model->delete();

                /*删除后,这个角色后,角色下面的帐号都将失去权限*/
                $admins = \app\area\model\AdminArea::where('roleid', $model->role_id)->column('id');
                foreach ($admins as $adminId) {
                    $this->clearAoth($adminId);
                }
            }

            $info['status'] = 1;
            $info['msg'] = '成功删除';
        }
        return $info;
    }
}