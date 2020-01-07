<?php

namespace app\dlc\controller;
use app\common\model\Bucket;
use app\common\tool\Excel;
use app\common\model\DeviceAisle;

class BucketController extends BaseController
{

    protected $title = '$title';
    
    protected $exportField = [];   //需要导出为excel时, 配置
    
    /**
     * 查询配置参数
     * @return array
     */
    protected function search()
    {
        return [
            'rfid' => ['name' => 'rfid', 'value' => '', 'type' => 'text', 'searchType' => '='],
            'status' => ['name' => '水桶状态', 'value' => '', 'type' => 'select', 'searchType' => '=', 'option' =>$this->array_merge(['' =>'请选择'],[1=>'有水', 2=> '无水', 3=>'未知'])],
			'device_id' => ['name' => '设备ID', 'value' => '', 'type' => 'text', 'searchType' => '='],
			'user_id' => ['name' => '用户', 'value' => '', 'type' => 'text', 'searchType' => '='],
			
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', Bucket::$statusOption);
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);

        $psize = input('psize')?input('psize'):10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Bucket::where($where)->with('device,user,area')->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Bucket::where($where)->with('device,user,area')->page($page,$psize)->order('bucket_id DESC')->select();
         }
        
        
        $count = Bucket::where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        if($this->request->param('dialog')) {
            return $this->fetch();
        } else {
            echo $this->fetch();
        }
    }

    /**
     * 添加
     * @return array
     */
    public function add()
    {
        $rq = $this->request;
        if($rq->isPost())
        {
            $rfid = $rq->post('rfid');
            $rfids = explode(',', $rfid);

            $data = [];
            if(empty($rfids)) {
                return(array('status' => 1,'msg' => '操作成功'));
            }

            $existRfid = Bucket::where('rfid', 'IN', $rfids)->column('rfid');
            foreach($rfids as $key => $rfid) {
                if(in_array($rfid, $existRfid)) {
                    unset($rfids[$key]);
                }
            }

            if(empty($rfids)) {
                return(array('status' => 1,'msg' => '操作成功'));
            }

            $time = time();
            foreach($rfids as $rfid)
            {
                $data[] = [
                    'rfid' => $rfid,
                    'ctime' => $time,
                ];
            }

            $model = new Bucket();
            $model->saveAll($data);

            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('bucket_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Bucket::get($id);

        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                $model->change($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }

        $this->assign('id', $id);
        $this->assign('model', $model);
        $this->assignOption();
        echo $this->fetch('bucket_form');
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Bucket::get($id);

        if(!$model) {
            return(array('status' => 0,'msg' => '对象不存在'));
        }

        try{
            $model->del();
        } catch (\think\Exception $err) {
            return(array('status' => 0,'msg' => $err->getMessage()));
        }

        return(array('status' => 1,'msg' => '操作成功'));
    }


    /**
     * 加收水桶
     */
    public function retrieve()
    {
        //查这个桶是否在别的设备在存在
        $id = $this->request->param('id');
        $bucket = Bucket::get(['bucket_id' => $id]);

        $existDeviceAisle = DeviceAisle::get(['rfid' => $bucket->rfid]);
        
        if($existDeviceAisle) {
            //存在,把这个桶，从那个设备中清空
            $existDeviceAisle->retrieveBucket();
        }
        if($bucket->user_id){
            $bucket->retrieveBucket();
        }
        return(array('status' => 1,'msg' => '操作成功'));
    }
}