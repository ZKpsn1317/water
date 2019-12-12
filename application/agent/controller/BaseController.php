<?php

namespace app\Agent\controller;

use think\Controller;
use think\Model;
use think\Db;
use think\Request;
use think\Cache;
use think\Session;
use app\common\tool\Power;

class BaseController extends Controller
{
    protected $agent_id;
    protected $admin_id;

    //初始化验证模块
    protected function _initialize()
    {
        //检测登陆状态
        if (!Session::has('admin_agent_id')) {
            $this->redirect('agent/Public/login');
        }
        $this->agent_id = $agent_id = Session::get('admin_agent_id');
        $this->admin_id = $admin_id = Session::get('agent_id');
		$request = Request::instance();


        //请求的方法跟控制器
        $controller = $request->controller();
        $action =  $request->action();
        $requestPath = strtolower( $controller . '/' . $action );

        if(in_array($requestPath, ['index/index','index/main', 'device/shareqrcode', 'device/outqrcode', 'device/info', 'upload/indeximg','upload/doupimg','upload/delimgs', 'upload/getmoreimg'])) {
            return true;
        }

        
        /* 其他人通通检查 */
        Power::setKey('agent');
        Power::loadOaths($admin_id);
        if(!Power::check($requestPath)) {
            if($request->isAjax() && $request->isPost()) {
                echo json_encode(['status'=>0, 'msg'=>'没有权限']);die;
            } else {
                echo '没有权限';die;
            }
        }

    }

    /*
     * 执行这个方法的用户，就算已经登入，也会失去操作后台的权限
     */
    protected static function clearAoth($userId)
    {
        $aothCacheName = \app\common\config\Confg::getAgentOathCacheName($userId);
        \think\Cache::set($aothCacheName, ['-']);
    }

    /*
     * 执行后，用户权限，会从数据库中重新读取
     */
    public static function delAoth($userId)
    {
        $aothCacheName = \app\common\config\Confg::getAgentOathCacheName($userId);
        \think\Cache::rm($aothCacheName);
    }



    //拼装面包导航
    public function getBread($bread)
    {
        if ($bread) {
            $this->assign('bread', $bread);
            return $this->fetch('Base_bread');
        } else {
            $this->error('请传入面包导航！');
        }
    }



	/**
	 * 封装分页类
	 * @param $count 操作URL
	 * @param $psize 记录信息
	 * @param $loader 模块
	 * @param $loadername 模块名
	 * @param $searchname 搜索
	 * @param $map
	 */
	public function getPage($count, $psize, $loader, $loadername, $searchname){
		if (!$count && !$psize || !$loader || !$loadername) {
				die('缺少分页参数!');
		}
		$page = new \pagecms\pagecms($count, $psize); // 实例化分页类 传入总记录数和每页显示的记录数
		$page->setConfig('loader', $loader);
		$page->setConfig('loadername', $loadername);
		//绑定前端form搜索表单ID,默认为#App-search
		if ($searchname) {
			 $page->setConfig('searchname', $searchname);
		}
		$show = $page->show(); // 分页显示输出
		$this->assign('page', $show);
		return true;
	}


    /**
     * 创建查询html
     * @param $search
     * @return string
     */
    public function createSerachHtml($search)
    {

        $form = \form\Form::form();
        $html = '';

        foreach($search as $key => $s)
        {
            $html .= "\r\n{$s['name']}\r\n <label style=\"margin-bottom: 0px;\"> \r\n\t";
            switch($s['type'])
            {
                case 'text':
                    $html .= $form->text($key, $s['value'],['class'=>'form-control input-sm']);
                    break;
                case 'select':
                    $html .= $form->select($key, $s['option'] , $s['value']);
                    break;
                case 'search':
                    $html .= $form->input('search', $key, $s['value'],['class'=>'form-control input-sm']);
                    break;
                case 'date':
                    $style = $s['style'] ?: '';
                    $html .= "<div class=\"input-group\" style='{$style}'>
                                    <div class=\"input-group-addon\">
                                        <i class=\"fa fa-clock-o\"></i>
                                    </div>
                                    <input class=\"form-control pull-right\" name=\"$key\" value=\"{$s['value']}\" data-bv-field=\"expire_time\" type=\"date\"><i style=\"display: none;\" class=\"form-control-feedback\" data-bv-field=\"expire_time\"></i>
                                </div>";;
                    break;

            }
            $html .= "\r\n </label>  &nbsp;&nbsp;";
        }

        return $html;
    }




    /**
     * 载入接交的值
     */
    public function loadSearchValue(&$search)
    {
        $rq = $this->request;
        foreach($search as $key => $s) {
            if(!is_null($rq->param($key))) {
                $search[$key]['value'] = $rq->param($key);
            }
        }
    }



    /**
     * 生成查询条件
     * @param $search
     */
    public function buildSearchWhere(&$search)
    {
        $where = [];
        foreach($search as $key => $s) {
            if($s['value']) {
                switch ($s['searchType'])
                {
                    case '=':
                        $where[$key] = $s['value'];
                        break;
                    case '%%':
                        $where[$key] = ['like', "%{$s['value']}%" ];
                        break;
                    case 'l%':
                        $where[$key] = ['like', "%{$s['value']}" ];
                        break;
                    case 'r%':
                        $where[$key] = ['like', "{$s['value']}%" ];
                        break;
                    case '<':
                        $where[$key] = ['<', "{$s['value']}%" ];
                        break;
                }
            }
        }
        return $where;
    }


    protected function array_merge($arr, $arr2)
    {
        foreach($arr2 as $key => $value)
        {
            $arr[$key] = $value;
        }
        return $arr;

    }
      /**
     * 直接ajax返回成功信息（返回类型仅适用于json格式）
     * @param int $code  状态
     * @param string $msg   提示信息
     * @param array $data  json数据
     */
    protected static function _return($code,$msg,$data=array())
    {
        $info['code'] = $code;
        $info['msg'] = $msg;
        $info['data'] =$data?$data : new \stdClass();
        echo json_encode($info);die;

    }

}
?>
