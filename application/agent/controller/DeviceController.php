<?php

namespace app\agent\controller;
use app\common\model\Device;
use app\common\tool\Excel;
use app\common\model\WaterBrand;
use app\common\tool\Image;
use app\common\model\WaterRecharge;
use app\common\model\Region;
use app\common\model\Area;
use app\common\model\Bucket;
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
        $this->assign('waterBrand', $this->array_merge(['' => '请选择'], WaterBrand::where(['agent_id' => $this->agent_id])->column('water_brand_id,water_brand_name') ?: []));
        $this->assign('waterRecharge', $this->array_merge(['' => '请选择'], WaterRecharge::where(['agent_id' => $this->agent_id])->column('water_recharge_id,name') ?: []));
        $this->assign('region', $this->array_merge(['' => '请选择'], Region::column('region_id,region_name') ?: []));
        $this->assign('area', $this->array_merge(['' => '请选择'], Area::where(['agent_id' => $this->agent_id])->column('area_id,area_name') ?: []));

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
     * 列表
     */
    public function index()
    {
        $search = $this->search();
        $this->loadSearchValue($search);

        $where = $this->buildSearchWhere($search);
        $where['agent_id'] = $this->agent_id;
        $psize = 10;
        $page = input('page')?input('page'):1;
         if(input('export')) {
            $list = Device::where($where)->select();
            Excel::export($list, $this->exportField);
         } else {
            $list = Device::where($where)->page($page,$psize)->order('device_id DESC')->select();  
         }
        
        
        $count = Device::where($where)->count();


        $this->assign('searchHtml', $this->createSerachHtml($search));
        $this->assign('list', $list);
        $this->assign('title', $this->title);
        $this->assignOption();

        $this->assign('hasExport', !empty($this->exportField));
        $this->getPage($count, $psize, 'App-loader', '列表', 'App-search');

        $this->assign('empty','<tr><td colspan="9" style="line-height:32px;text-align:center;">暂无数据！</td></tr>');
        echo $this->fetch();
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
                $post['agent_id'] = $this->agent_id;
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

    public function opendoor()
    {
        $rq = $this->request;
        $device_id = $rq->post('device_id');
        $device_aisle_id = $rq->post('aisle_num');
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
 
        $res = WyjVendingMachine::machineCloudOpen('one_button_unlock',  $device->motherboard_code, 0,  $device_aisle_id);
        $result = json_decode($res);

        if($result->code != 1) {
            //发送失败
            $this->_return(0, '机器开门指令发送失败!');
        } else {
            $this->_return(1, '开门成功!');
        }
    }

}