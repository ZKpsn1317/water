<?php

namespace app\area\controller;

use think\Session;
use think\Db;
use app\area\model\AdminArea;
use app\common\model\Area;
use app\common\tool\Power;

class IndexController extends BaseController
{
    //CMS总入口
    public function index ()
    {
        Power::setKey( 'area' );
        $allOath = Power::getAllOaths( $this->admin_id );

        foreach ( $allOath as $row ) {
            if ( !$row['is_menu'] ) {
                continue;
            }

            if ( $row["url"] ) {
                $row["url"]   = url( 'area/' . $row["url"] );
                $role_oath2[] = $row;
            } else {
                $role_oath1[] = $row;
            }
        }

        $role_oath1['0']["url"] = 'main2';
        $array                  = [ $role_oath1[0] ];
        $this->assign( 'auth', [] );
        $this->assign( 'main', $array ? : [] );
        $this->assign( 'role_oath1', $role_oath1 ? : [] );
        $this->assign( 'role_oath2', $role_oath2 ? : [] );

        return $this->fetch();
    }


    public function main ()
    {
        echo 'hello';
    }


    //修改用户密码
    public function modifypass ()
    {
        //设置面包导航，主加载器请配置
        $bread = [
            '0' => [
                'name' => '修改密码',
                'url'  => url( '/area/Index/modifypass' ),
            ],
        ];
        $this->assign( 'breadhtml', $this->getBread( $bread ) );
        //处理POST提交
        $rq = $this->request;

        if ( $rq->isPost() ) {
            $data = $rq->post();

            if ( empty( $data['password'] ) ) {
                $info['status'] = 0;
                $info['msg']    = '请输入正确数据！';
                $this->ajaxReturn( $info );
            }

            $re = AdminArea::get( $this->admin_id );

            if ( $re && FALSE !== $re->save( [ 'password' => md5( $data['password'] ) ] ) ) {
                $info['status'] = 1;
                $info['msg']    = '修改成功';
            } else {
                $info['status'] = 0;
                $info['msg']    = '设置失败！';
            }
            return $info;
        }
        echo( $this->fetch( 'modifypass' ) );
    }
}
