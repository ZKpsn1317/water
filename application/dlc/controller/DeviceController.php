<?php

namespace app\dlc\controller;
use app\common\model\Bucket;
use app\common\tool\Image;
use app\common\model\Device;
use app\common\tool\Excel;
use app\common\model\WaterBrand;
use app\common\model\WaterRecharge;
use app\common\model\Region;
use app\common\model\Agent;
use app\common\model\DeviceStatusLog;
use app\common\model\DeviceAisle;
use app\common\model\GoodsRunning;
use hardware\WyjVendingMachine;



class DeviceController extends BaseController
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
            'device_name' => ['name' => '设备名称', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
			'macno' => ['name' => '设备编号', 'value' => '', 'type' => 'text', 'searchType' => '%%'],
            'bucket_num' => ['name' => '有水桶数小于', 'value' => '', 'type' => 'text', 'searchType' => '<'],
			
        ];
    }

    protected function assignOption()
    {
        $this->assign('status', Device::$statusOption);
    }


    //在表单中使用的列表选项
    protected function assignFormOption()
    {
        $this->assign('waterBrand', $this->array_merge(['' => '请选择'], WaterBrand::column('water_brand_id,water_brand_name') ?: []));
        $this->assign('waterRecharge', $this->array_merge(['' => '请选择'],WaterRecharge::column('water_recharge_id,name') ?: []));
        $this->assign('region', $this->array_merge(['' => '请选择'],Region::column('region_id,region_name') ?: []));
        $this->assign('agent', $this->array_merge(['' => '请选择'],Agent::column('agent_id,agent_name') ?: []));
    }


    /**
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);

        $psize = 10;
        $page = input('page')?input('page'):1;
        if(input('export')) {
            $list = Device::where($where)->with('water_recharge,region,agent,water_brand')->select();
            Excel::export($list, $this->exportField);
        } else {
            $list = Device::where($where)->with('water_recharge,region,agent,water_brand')->page($page,$psize)->order('device_id DESC')->select();
        }

        $count = Device::where($where)->count();

        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');
   
        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
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
            try{
                $post = $rq->post();
                Device::add($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignFormOption();
        $this->assignOption();
        echo $this->fetch('device_form');
    }

    /**
     * 批量添加
     */
    public function adds()
    {
        $rq = $this->request;
        if($rq->isPost())
        {
            try{
                $post = $rq->post();
                Device::adds($post);
            } catch (\think\Exception $err) {
                return(array('status' => 0,'msg' => $err->getMessage()));
            }
            return(array('status' => 1,'msg' => '操作成功'));
        }
        $this->assignOption();
        echo $this->fetch('device_adds_form');
    }


    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        $rq = $this->request;
        $id = $rq->param('id');
     
        $model = Device::get($id);

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
        $this->assignFormOption();
        echo $this->fetch('device_form');
    }


    /**
     * 设备详细信息
     */
    public function info()
    {
        $deviceId = $this->request->param('id');

        $model = Device::get(['device_id' => $deviceId]);
        if(!$model) {
            exit('设备不存在');
        }

        $this->assign('status', Bucket::$statusOption);
        $list = $model->getCellGoods();

        $this->assign('deviceId', $deviceId);
        $this->assign('list', $list);

        echo $this->fetch();

    }

    /**
     * PHP生成二维码
     * @param $device_number
     */
    public function shareQrcode($url){
        $macno = substr($url, strpos($url, '=')+1);
        Image::createQrText($url, $macno);
        die;
    }


    public function outQrcode()
    {
        set_time_limit(600);
        $urlArr = Device::column('macno');
        if(empty($urlArr)) {
            echo '没有设备';die;
        }
        $data = [];
        foreach($urlArr as  $vo) {
            $data[$vo] = $this->request->domain() . '/h5/builded/buy_water.html?macno=' . $vo;
        }
        Image::createQrZip($data);
    }


    /**
     * 删除
     * @return array
     */
    public function del()
    {
        $id = $this->request->param('id');
        $model = Device::get($id);

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


    public function status()
    {

        $psize = 10;
        $page = input('page')?input('page'):1;

        $device_id = input('device_id');

        if($device_id) {
            $list = DeviceStatusLog::where(['device_id' => $device_id])->page($page,$psize)->order('ctime DESC')->select();
        } else  {
            $list = DeviceStatusLog::page($page,$psize)->order('ctime DESC')->select();
        }



        $this->assign('device_id', $device_id);

        if($device_id) {
            $count = DeviceStatusLog::where(['device_id' => $device_id])->count();
        } else  {
            $count = DeviceStatusLog::count();
        }




        $this->assign('searchHtml', '');
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();
        $this->assign('hasExport', $this->exportField);
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
    }
    public function opendoor()
    {
        $rq = $this->request;
        $device_id = $rq->post('device_id');
        $device_aisle_id = $rq->post('device_aisle_id');
        if(!$device_id) {
            $this->_return(0, '设备号不能为空');
        }
        if(!$device_aisle_id) {
            $this->_return(0, '通道号不能为空');
        }
        $device = Device::get(['device_id' => $device_id]);
        if(!$device) {
            $this->_return(0, '设备不存在');
        }

        $aisle_numeber = DeviceAisle::where(['device_aisle_id' => $device_aisle_id, 'device_id' => $device_id])->value('aisle_num');
        

        if(!$aisle_numeber) {
            $this->_return(0, '设备无门可开');
        }

        //创建硬件发送记录
        $runningLog = [
            'order_id' => $rechargeLog->water_recharge_log_id,
            'device_id' => $device_id,
            'running_type' => 3,
            'device_aisle' => $aisle_numeber,
            'rfid' => $device->motherboard_code,
            'async_result' => '',
        ];

        $runningLog = GoodsRunning::add($runningLog);

        $runningLog->synchro_result = WyjVendingMachine::machineCloudOpen('one_button_unlock',  $device->motherboard_code, $runningLog->id,  $aisle_numeber);
        $result = json_decode($runningLog->synchro_result);
        if($result && $result->code == 1) {
            $runningLog->status = 2;
        } else {
            $runningLog->status = 3;
        }
        $runningLog->save();

        if($runningLog->status != 2) {
            //发送失败
            $this->_return(0, '机器开门指令发送失败!');
        } else {
            $this->_return(1, '开门成功!');
        }
    }
}