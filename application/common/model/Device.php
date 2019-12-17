<?php
namespace app\common\model;

use think\Model;
use app\common\validate\DeviceValidate;

class Device extends Model
{
    public static $statusOption = [
        1 => '在线',
        2 => '离线',
        3 => '故障',
    ];


    public static function add($data)
    {
        $validate = new DeviceValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if(isset($data['device_type']) && $data['device_type'] == 1) {
            $list = static::ailseList();
        } else {
            $list = static::ailseList18();
        }


        $model = new static();
        $data['motherboard_code'] = $data['macno'];
        $data['ctime'] = time();
        $data['empty_frame_num'] = count($list);
        $data['aisle_num'] =  $data['empty_frame_num'];

        if(!$model->allowField('area_id,device_address,empty_frame_num,empty_bucket_num,water_brand_id,device_status,water_recharge_id,macno,region_id,agent_id,lng,lat,ctime,motherboard_code,device_name,aisle_num')->save($data)) {
            throw new \think\Exception($model->getError());
        }



        foreach ($list as $k=>$v){
            $sql = "insert into dlc_device_aisle (device_id,aisle_num,col,row) value({$model->device_id}, {$v['aisle_num']}, {$v['col']}, {$v['row']})";
            \think\Db::execute($sql);
        }

        return $model;
    }


    public static function ailseList()
    {
        return [
            ['row' => 6, 'col' => 1, 'aisle_num' => 1],
            ['row' => 5, 'col' => 1, 'aisle_num' => 2],
            ['row' => 4, 'col' => 1, 'aisle_num' => 3],
            ['row' => 3, 'col' => 1, 'aisle_num' => 4],
            ['row' => 2, 'col' => 1, 'aisle_num' => 5],
            ['row' => 1, 'col' => 1, 'aisle_num' => 6],
            ['row' => 6, 'col' => 2, 'aisle_num' => 7],
            ['row' => 5, 'col' => 2, 'aisle_num' => 8],
            ['row' => 4, 'col' => 2, 'aisle_num' => 9],
            ['row' => 3, 'col' => 2, 'aisle_num' => 10],
            ['row' => 2, 'col' => 2, 'aisle_num' => 11],
            ['row' => 1, 'col' => 2, 'aisle_num' => 12],
            ['row' => 6, 'col' => 3, 'aisle_num' => 13],
            ['row' => 5, 'col' => 3, 'aisle_num' => 14],
            ['row' => 4, 'col' => 3, 'aisle_num' => 15],
            ['row' => 3, 'col' => 3, 'aisle_num' => 16],
            ['row' => 2, 'col' => 3, 'aisle_num' => 17],
            ['row' => 1, 'col' => 3, 'aisle_num' => 18],
            ['row' => 6, 'col' => 4, 'aisle_num' => 19],
            ['row' => 5, 'col' => 4, 'aisle_num' => 20],
            ['row' => 4, 'col' => 4, 'aisle_num' => 21],
            ['row' => 3, 'col' => 4, 'aisle_num' => 22],
            ['row' => 2, 'col' => 4, 'aisle_num' => 23],
            ['row' => 1, 'col' => 4, 'aisle_num' => 24],
        ];
    }

    public static function ailseList18()
    {
        return [
            ['row' => 6, 'col' => 1, 'aisle_num' => 1],
            ['row' => 5, 'col' => 1, 'aisle_num' => 2],
            ['row' => 4, 'col' => 1, 'aisle_num' => 3],
            ['row' => 3, 'col' => 1, 'aisle_num' => 4],
            ['row' => 2, 'col' => 1, 'aisle_num' => 5],
            ['row' => 1, 'col' => 1, 'aisle_num' => 6],
            ['row' => 6, 'col' => 2, 'aisle_num' => 7],
            ['row' => 5, 'col' => 2, 'aisle_num' => 8],
            ['row' => 4, 'col' => 2, 'aisle_num' => 9],
            ['row' => 3, 'col' => 2, 'aisle_num' => 10],
            ['row' => 2, 'col' => 2, 'aisle_num' => 11],
            ['row' => 1, 'col' => 2, 'aisle_num' => 12],
            ['row' => 6, 'col' => 3, 'aisle_num' => 13],
            ['row' => 5, 'col' => 3, 'aisle_num' => 14],
            ['row' => 4, 'col' => 3, 'aisle_num' => 15],
            ['row' => 3, 'col' => 3, 'aisle_num' => 16],
            ['row' => 2, 'col' => 3, 'aisle_num' => 17],
            ['row' => 1, 'col' => 3, 'aisle_num' => 18],
        ];
    }


    public function change($data)
    {
        /*$validate = new DeviceValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        if(!empty($data['macno'])) {
            $data['motherboard_code'] = $data['macno'];
        }


        if($this->allowField('area_id,device_address,empty_frame_num,empty_bucket_num,water_brand_id,device_status,water_recharge_id,region_id,agent_id,lng,lat,ctime,motherboard_code,device_name')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }


    public function del()
    {
        DeviceAisle::where(['device_id' => $this->device_id])->delete();
        $this->delete();
    }


    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


    public function waterRecharge()
    {
        return $this->hasOne('water_recharge', 'water_recharge_id', 'water_recharge_id');
    }


	public function region()
    {
        return $this->hasOne('region', 'region_id', 'region_id');
    }


	public function agent()
    {
        return $this->hasOne('agent', 'agent_id', 'agent_id');
    }

    
    public function waterBrand()
    {
        return $this->hasOne('water_brand', 'water_brand_id', 'water_brand_id');
    }

    //取空桶数
    public function getEmpty()
    {
        $empty_frame_num =  static::get()->empty_frame_num;
        $empty_bucket_num = static::get()->empty_bucket_num;
        return $empty_bucket_num+$empty_frame_num;
    }
    /**
     * 取后台商品列表
     * @return array
     */
    public function getCellGoods()
    {
        $result = DeviceAisle::where(['device_id' => $this->device_id])
            ->with('bucket')
            ->order('row DESC,col ASC')
            ->select();

        $result = collection($result)->toArray();

        $list = [];
        foreach($result as $row) {
            $list[$row['row']][] = $row;
        }
        return array_values($list);
    }


    /**
     * 添加多设备
     * @param $data
     * @throws \think\Exception
     */
    public static function adds($data)
    {
        $validate = new Validate([
            'start_macno|开始编号' => 'require',
            'end_macno|结束编号' => 'require',
            'row_num|行数' => 'require|number|>=:1',
            'col_num|列数' => 'require|number|>=:1',
        ]);

        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if($data['start_macno'] >= $data['end_macno']) {
            throw new \think\Exception('结束编号要大于开始编号');
        }

        $strlen = strlen($data['start_macno']);
        if($strlen != strlen($data['end_macno'])) {
            throw new \think\Exception('开始、结束长度不相等');
        }

        //取两个编号前面相同的部分
        $equalStr = [];
        for($i=0; $i<$strlen; $i++) {
            $s = substr($data['start_macno'], $i, 1);
            $s2 = substr($data['end_macno'], $i, 1);
            if($s != $s2) {
                break;
            }
            $equalStr[] = $s;
        }
        $equalStr = implode('', $equalStr);

        //看剩下的两个编号是不是数字
        $excess1 = substr($data['start_macno'], $i);
        $excess2 = substr($data['end_macno'], $i);
        if(!is_numeric($excess1) || !is_numeric($excess2)) {
            throw new \think\Exception('编号差异部分不是数字');
        }


        for(; $excess1 <= $excess2; $excess1++)
        {
            $data['macno'] = $equalStr . $excess1;
            $data['name'] = $data['macno'];
            $device = static::add($data);
            DeviceAisle::adds([
                'device_id' => $device->id,
                'macno' => $device->macno,
                'startRow' => 1,
                'endRow' => $data['row_num'],
                'startCol' => 1,
                'endCol' => $data['col_num']
            ]);
        }

    }


    /**
     * 关联的设备通道
     */
    public function deviceAisle()
    {
        return $this->hasMany('device_aisle', 'device_id', 'device_id');
    }


    public function onLine()
    {
        if($this->on_line_uctime + 30 < time()) {
            //更新过期重新更新
            $result = \hardware\WyjVendingMachine::deviceStatus($this->motherboard_code);
            if($result && $result->code == 1) {
                $this->device_status = 1;
            } else {
                $this->device_status = 2;
            }
            $this->on_line_uctime = time();
            $this->allowField(true)->save();
        }
        return $this->device_status;
    }
    
}
