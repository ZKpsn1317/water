<?php
namespace app\area\model;

use think\Model;

class RoleArea extends \app\common\basemodel\Role
{
    public static $oathClass = '\app\area\model\RoleOathArea';


}