<?php
namespace app\common\model;

use think\Model;
use think\Db;

class DeviceStatusLog extends Model
{
    public static function record($device_id)
    {
        $device = Device::get($device_id);
        $deviceAisleModel = new DeviceAisle();
        $device_aisle_info = Db::table($deviceAisleModel->getTable())->where(['device_id' => $device_id])->select();
        $data = [
            'device_id' => $device_id,
            'device_info' => json_encode($device->getData()),
            'device_aisle_info' => json_encode($device_aisle_info),
            'request' => json_encode(\think\Request::instance()->post()),
        ];
        $model = new static();
        $model->save($data);
    }


    public function getDeviceAisleInfoAttr($value)
    {

        $aisleList = json_decode($value);
        $data = [];
        foreach($aisleList as $aisle) {

            if($aisle->rfid == '') {
                continue;
            }
            var_dump([
                'rfid' => $aisle->rfid,
                'aisle_num' => $aisle->aisle_num,
            ]);
            echo '<br />';
            /*$data[] = [
                'rfid' => $aisle->rfid,
                'aisle_num' => $aisle->aisle_num,
            ];*/
        }

        //var_dump($data);
    }


    public function getRequestAttr($value)
    {
        $data = json_decode($value);
        $type = [
            'giveBackResult' => '补水/还桶',
            'returnResult' => '还桶',
            'openDoor' => '购水',
        ];
        echo '类型：';
        if(isset($data->api_name) && $data->api_name == 'giveBackResult') {
            echo '补水</br>';
        } elseif(isset($data->type) && $data->type == 'returnResult') {
            echo '还桶</br>';
        } elseif(isset($data->type) && $data->type == 'openDoor') {
            echo '买水</br>';
        }

        foreach($data as $key => $value) {
            echo "$key => $value <br/>";
        }
    }


    public function getDeviceInfoAttr($value)
    {
        $info = json_decode($value);
        echo '空框:' . $info->empty_frame_num . '<br />';
        echo '空桶:' . $info->empty_bucket_num . '<br />';
        echo '有水数:' . $info->bucket_num . '<br />';

    }
}