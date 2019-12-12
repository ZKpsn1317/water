<?php
namespace app\common\model;

use think\Model;
use app\common\validate\DeviceAisleValidate;

class DeviceAisle extends Model
{
    public static function add($data)
    {
        /*$validate = new DeviceAisleValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }*/

        $model = new static();
        $data['ctime'] = time();
        if(!$model->allowField('device_id,rfid,row,col,aisle_num')->save($data)) {
            throw new \think\Exception($model->getError());
        }

        return $model;
    }


    public function change($data)
    {
        $validate = new DeviceAisleValidate();
        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }


        if($this->allowField('device_id,rfid,row,col,aisle_num')->save($data) === false) {
            throw new \think\Exception($this->getError());
        }
    }

    public function del()
    {
        $this->delete();
    }
    
    public function getCtimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    
    public function device()
    {
        return $this->hasOne('device', 'device_id', 'device_id');
    }


    protected function product()
    {
        return $this->hasOne('bucket', 'rfid', 'rfid');
    }


    //给设备添加多个货道
    public static function adds($data)
    {

        $validate = new Validate([
            'startNumber|开始通道号' => 'require|number',
            'endNumber|结束通道号' => 'require',
        ]);

        if(!$validate->check($data)) {
            throw new \think\Exception($validate->getError());
        }

        if($data['startNumber'] > $data['endNumber']) {
            throw new \think\Exception('结束通道号要大于或等于开始通道号');
        }



        for(; $data['startRow'] <= $data['endRow']; $data['startRow']++)
        {
            for($startCol = $data['startCol']; $startCol <= $data['endCol']; $startCol++)
            {
                $data['row'] = $data['startRow'];
                $data['col'] = $startCol;
                static::add($data);
            }
        }

    }


    /**
     * 关联的水桶
     */
    public function bucket()
    {
        return $this->hasOne('bucket', 'rfid', 'rfid');
    }


    /**
     * 回收这个通道中的水桶
     */
    public function retrieveBucket()
    {


   
        $bucket = Bucket::get(['rfid' => $this->rfid]);
        $device = $this->device;

        if($bucket->status == 2) {
            //取出的水桶是空的，设备空桶数-1
            $device->empty_bucket_num--;
            $device->empty_frame_num++;
        } else {

            $device->bucket_num--;
            $device->empty_frame_num++;
        }

        $device->save();

        $bucket->device_id = 0;
        $bucket->status = 3;
        $bucket->save();


        $this->rfid = '';
        $this->save();
        return;


    }

}
