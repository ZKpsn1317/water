<?php
namespace app\dlc\model;

use think\Model;
use think\Db;
class RoleOath extends Model
{

    public static function adds($data)
    {

        $model = static::get(['url' => $data['url']]);

        if($model) {
            return;
        }
        $model = new static();
        $childs = $data['childs'];
        unset($data['childs']);

        if($model->save($data)) {
            foreach($childs as $c) {
                $childModel = new static();
                $childModel->save($c);
            }
        }
    }


    public static function dels($url) {
        $result = static::where('url', 'IN', $url)->select(false);

        foreach($result as $power) {
            $power->delete();
        }

    }


}