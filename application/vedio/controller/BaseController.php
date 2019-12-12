<?php
namespace app\vedio\controller;

use think\Controller;
use think\Model;
use think\Db;
use app\common\tool\WecahtOfficialAccount;
use app\common\model\User;
use think\Session;

class BaseController extends Controller
{
    
    //接口入口
    public function api()
    {

        $api_name = $this->request->post('api_name');

        if(!$api_name) {
            $this->_return(404, '接口不能为空', new \stdClass());
        }

        if(!method_exists($this, $api_name)) {
            $this->_return(404, '接口不存在', ['api_name'=>$api_name]);
        }

        call_user_func([$this, $api_name]);

    }


    //获取用户token
    public static function createToken($user_id, $key = 'sub'){
        $token = new \Gamegos\JWT\Token();

        $token->setClaim($key, $user_id);
        $token->setClaim('exp', time() + config('jwt_time'));

        $encoder = new \Gamegos\JWT\Encoder();
        $encoder->encode($token, config('jwt_key'), config('jwt_alg'));
        return  $token->getJWT();
    }


    public static function decodeToken($token, $key = 'sub') {
        try {

            $validator = new \Gamegos\JWT\Validator();
            $token = $validator->validate($token, config('jwt_key'));
            $data = $token->getClaims();

            if($data['exp'] < time()) {
                static::_return(-1, 'token已失效');
            }
            return $data[$key];
        } catch (\Gamegos\JWT\Exception\JWTException $e) {
            static::_return(-1, 'token错误');
        }
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

