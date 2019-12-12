<?php
namespace app\agent\model;

use think\Model;

class RoleAgent extends \app\common\basemodel\Role
{
    public static $oathClass = '\app\agent\model\RoleOathAgent';


}